<?php

declare(strict_types=1);

namespace App\Controllers;

use Exception;
use App\Controllers\Abstract\Controller;
use App\Models\ProgramasModel;
use App\Helpers\R2Service;
use App\Helpers\Validar;
use App\Libs\Exceptions\AppException;
use App\Libs\Seguridad;
use App\Models\TipoProgramaModel;
use App\Traits\ManejoArchivosR2Trait;

/**
 * Controlador de Programas
 *
 * @package App\Controllers
 */
class ProgramasController extends Controller
{
    use ManejoArchivosR2Trait;

    private ProgramasModel $programa;

    public function __construct()
    {
        parent::__construct();
        $this->programa = new ProgramasModel();
    }


    public function  index(): void
    {



        $this->requerirAutenticacion();



        $urlPublica = $this->obtenerArchivo($this->session->get('foto_perfil'));
        $tipo = new TipoProgramaModel;
        $model = $tipo->select('all');
        if (isset($model['id'])) {
            $model['id'] = Seguridad::encriptarID($model['id']);
        } else {
            foreach ($model as &$m) {
                if (isset($m['id'])) {
                    $m['id'] = Seguridad::encriptarID($m['id']);
                }
            }
            unset($m);
        }


        $this->vista->render(
            'usuario/programas',
            [
                'token'   => $this->session->get('csrf_token'),
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'nombreRol' => $this->session->get('nombre_rol'),
                'titlePag' => 'Programas',
                'pag' => 'programas',
                'grup' => 'administracion',
                'fotoUsuario' => $urlPublica,
                'tipo' => $model

            ],
            'usuario'
        );
    }




    /**
     * Crea un nuevo programa incluyendo logo y plantilla de certificado.
     *
     * @return void
     */
    public function guardar(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();

        // 1. Filtrar datos de texto y enteros
        $datos = $this->filtrarDatos([
            'programa'      => 'esTexto',
            'requisitos'   => 'esTexto',
            'descripcion'   => 'esTexto',
            'duracion'      => 'esTexto',
            'tipo_programa' => 'esDesencriptarId',
        ]);
        $datos['estado'] = 1; // Estado activo por defecto

        // 2. Validar y filtrar archivos (usando método nativo del controlador)
        // Definimos las reglas una sola vez
        $reglasImagen = [['jpg', 'jpeg', 'png', 'webp'], 2]; // Extensiones y 2MB máx
        $logoFiltrado = $this->filtrarArchivo('logo', ...$reglasImagen);
        $fondoFiltrado = $this->filtrarArchivo('certificado', ...$reglasImagen);

        // 3. Subir Logo a R2 usando el Trait
        // Asumimos que son obligatorios para crear
        $resLogo = $this->subirArchivoR2($logoFiltrado, 'logos', 'logo');
        if (!$resLogo['exito']) {
            $this->respuesta->json(null, 400, $resLogo['error']);
            return;
        }

        // 4. Subir Plantilla de Fondo a R2 usando el Trait
        $resFondo = $this->subirArchivoR2($fondoFiltrado, 'certificados', 'certificado');
        if (!$resFondo['exito']) {
            // ROLLBACK: Si falla el segundo archivo, eliminamos el primero de R2
            $this->eliminarArchivosR2([$resLogo['key']]);
            $this->respuesta->json(null, 400, $resFondo['error']);
            return;
        }

        // 5. Asignar las keys de R2 a los datos para la base de datos
        $datos['logo'] = $resLogo['key'];
        $datos['certificado'] = $resFondo['key'];

        // 6. Intentar guardar en la Base de Datos
        try {
            if (!$this->programa->save($datos)) {
                // ROLLBACK: Si falla la BD, eliminamos ambos archivos de R2
                $this->eliminarArchivosR2([$resLogo['key'], $resFondo['key']]);

                throw new AppException('error Interno del servidor al intentar guardar', 500);
            }
        } catch (Exception $e) {
            // ROLLBACK: Ante cualquier excepción de BD
            $this->eliminarArchivosR2([$resLogo['key'], $resFondo['key']]);
            throw new AppException('error Interno del servidor', 500);
        }

        $this->respuesta->json(
            true,
            201,
            'Programa creado con éxito.'
        );
    }





    /**
     * Actualiza los datos de un programa existente, manejando subida opcional de archivos.
     *
     * @return void
     */
    public function actualizar(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();

        // 1. Filtrar datos (incluyendo el ID desencriptado)
        $datos = $this->filtrarDatos([
            'id'            => 'esDesencriptarId', // Obtenemos el ID real
            'programa'      => 'esTexto',
            'descripcion'   => 'esTexto',
            'requisitos'   => 'esTexto',
            'estado'     => 'esBooleano',
            'duracion'      => 'esTexto',
            'tipo_programa' => 'esDesencriptarId',
        ]);

        // Separar ID para la condición
        $idPrograma = $datos['id'];
        unset($datos['id']);
        $condicion = ['id' => $idPrograma];


        // 2. Buscar registro actual para conocer archivos viejos (necesario para reemplazo)
        $programaActual = $this->programa->find($idPrograma);
        if (!$programaActual) {
            $this->respuesta->json(null, 404, 'El programa no existe.');
            return;
        }

        // Listas para gestionar el rollback y la limpieza
        $archivosNuevosSubidos = [];
        $archivosViejosAEliminar = [];
        $reglasImagen = [['jpg', 'jpeg', 'png', 'webp'], 2];

        // 3. Procesar Logo (Opcional)
        if (!empty($_FILES['logo']['name'])) {
            $logoFiltrado = $this->filtrarArchivo('logo', ...$reglasImagen);
            $resLogo = $this->subirArchivoR2($logoFiltrado, 'logos', 'logo');

            if (!$resLogo['exito']) {
                $this->respuesta->json(null, 400, $resLogo['error']);
                return;
            }

            // Mapear nuevo archivo, guardar key para rollback y key vieja para eliminar luego
            $datos['logo'] = $resLogo['key'];
            $archivosNuevosSubidos[] = $resLogo['key'];
            if (!empty($programaActual['logo'])) {
                $archivosViejosAEliminar[] = $programaActual['logo'];
            }
        }

        // 4. Procesar Plantilla de Fondo (Opcional)
        if (!empty($_FILES['certificado']['name'])) {
            $fondoFiltrado = $this->filtrarArchivo('certificado', ...$reglasImagen);
            $resFondo = $this->subirArchivoR2($fondoFiltrado, 'certificados', 'certificado');

            if (!$resFondo['exito']) {
                // ROLLBACK: Eliminar logos nuevos subidos en esta petición si falla el fondo
                $this->eliminarArchivosR2($archivosNuevosSubidos);
                $this->respuesta->json(null, 400, $resFondo['error']);
                return;
            }

            $datos['certificado'] = $resFondo['key'];
            $archivosNuevosSubidos[] = $resFondo['key'];
            if (!empty($programaActual['certificado'])) {
                $archivosViejosAEliminar[] = $programaActual['certificado'];
            }
        }

        try {
            if (!$this->programa->update($datos, $condicion)) {
                // ROLLBACK: Si falla la BD, eliminamos todos los archivos nuevos subidos a R2
                $this->eliminarArchivosR2($archivosNuevosSubidos);

                throw new AppException('error Interno del servidor', 500);
            }
        } catch (Exception $e) {
            // ROLLBACK: Ante excepción de BD
            $this->eliminarArchivosR2($archivosNuevosSubidos);
            $this->respuesta->json(null, 500, 'Error interno del servidor: ');
            return;
        }

        // 6. ÉXITO: Limpieza de archivos viejos reemplazados en R2
        // Solo llegamos aquí si la BD se actualizó correctamente
        $this->eliminarArchivosR2($archivosViejosAEliminar);

        $this->respuesta->json($datos, 200, 'Programa actualizado correctamente.');
    }






    public function buscar(): void
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();

        $datos = $this->filtrarDatos([
            'limit' => 'esEntero',
            'offset' => 'esEntero',
        ]);

        $buscar = $this->getDatosEntrada();
        $datos['tipo_programa'] = Validar::esDesencriptarId($buscar['tipo']);
        $datos['buscar'] = Validar::esCadena($buscar['buscar']);
        $datos['estado'] = Validar::esBooleano($buscar['estado']);

        if ($datos['offset'] < 1) {
            $datos['offset'] = 0;
        }


        $resul = $this->programa->paginar($datos);
        $total = $this->programa->select('count');

        if (isset($result['id'])) {
            $resul['id'] = Seguridad::encriptarID($result['id']);
            $resul['tipo_programa'] = Seguridad::encriptarID($resul['tipo_programa']);
            $resul['logo'] = $this->obtenerArchivo($resul['logo']);
            $resul['certificado'] = $this->obtenerArchivo($resul['certificado']);
        } else {
            foreach ($resul as &$r) {
                if (isset($r['id'])) {
                    $r['id'] = Seguridad::encriptarID($r['id']);
                    $r['tipo_programa'] = Seguridad::encriptarID($r['tipo_programa']);
                    $r['logo'] = $this->obtenerArchivo($r['logo'] ?? '');
                    $r['certificado'] = $this->obtenerArchivo($r['certificado'] ?? '');
                }
            }
            unset($r);
        }



        $this->respuesta->json($resul, 200, "", [], (int)$total);
    }




    public function eliminarMasivo(): void
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();
        // Recibimos la estructura completa parseada
        $entrada = $this->getDatosEntrada();
        $datos = $entrada['ids'] ?? [];

        $idsOriginales = [];

        if (is_array($datos) && !empty($datos)) {
            foreach ($datos as $d) {
                // Aquí podrías usar tu otro método validador/desencriptador por clave
                $idDesencriptado = Validar::esDesencriptarId($d);

                $idEntero = (int) $idDesencriptado;
                if ($idEntero > 0) {
                    $idsOriginales[] = $idEntero;
                }
            }
        }

        $recursos = $this->programa->selecionarProgramasPorIds($idsOriginales);
        $r2Service = new R2Service();
        foreach ($recursos as $r) {

            $logoEliminar = $r2Service->eliminarArchivo($r['certificado']);
            $certificadoEliminar = $r2Service->eliminarArchivo($r['logo']);

            if (!$logoEliminar) {

                throw new AppException('No se pudo eliminar El recurso ' . $r['logo'], 500);
            }

            if (!$certificadoEliminar) {
                throw new AppException('No se pudo eliminar El recurso ' . $r['certificado'], 500);
            }
        }
        $registrosEliminados = $this->programa->eliminarProgramasPorIds($idsOriginales);

        if ($registrosEliminados > 0) {
            $this->respuesta->json([
                'exito' => true,
                'mensaje' => "Se eliminaron {$registrosEliminados} programa(s) correctamente."
            ], 200, '');
        } else {
            $this->respuesta->json([
                'exito' => false,
                'mensaje' => 'No se pudo eliminar ninguno de los registros seleccionados.'
            ], 409, '');
        }
    }
}
