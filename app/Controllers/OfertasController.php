<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Libs\Exceptions\AppException;
use App\Models\NucleoModel;
use App\Helpers\R2Service;
use App\Helpers\Validar;
use App\Models\ModalidadModel;
use App\Models\OfertasModel;
use App\Models\ProgramasModel;
use App\Traits\CifrarTrait;
use App\Traits\ManejoArchivosR2Trait;

class OfertasController extends Controller
{

    use ManejoArchivosR2Trait;
    use CifrarTrait;

    public function  index(): void
    {
        $this->requerirAutenticacion();
        $r2Service = new R2Service();
        $urlPublica = $this->obtenerArchivo($this->session->get('foto_perfil'));

        $nucleos = new NucleoModel;
        $programas = new ProgramasModel;
        $modos = new ModalidadModel;


        $programasResult = $this->cifrarDatos($programas->selectAllNombresIdsProgramas(), ['id']);
        $nucleosResult = $this->cifrarDatos($nucleos->selectAllNombresIds(), ['id']);
        $modosResul = $this->cifrarDatos($modos->select('all'), ['id']);

        $this->vista->render(
            'usuario/ofertas',
            [
                'token'   => $this->session->get('csrf_token'),
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'nombreRol' => $this->session->get('nombre_rol'),
                'titlePag' => 'Ofertas',
                'pag' => 'ofertas',
                'grup' => 'administracion',
                'fotoUsuario' => $urlPublica,
                'nucleos' => $nucleosResult,
                'programas' => $programasResult,
                'modalidades' => $modosResul

            ],
            'usuario'
        );
    }



    public function guardar(): void
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();


        $datos = $this->filtrarDatos([
            'nucleo_id' => 'esDesencriptarId',
            'programa_id' => 'esDesencriptarId',
            'fecha_ini' => 'esFecha',
            'fecha_fin' => 'esFecha',
            'costo_inscripcion' => 'esDecimal',
            'costo_total' => 'esDecimal',
            'cuotas' => 'esEntero',
            'modo_cuotas' => 'esDesencriptarId',
        ]);

        $datos['estado'] = 1;

        $reglasImagen = [['jpg', 'jpeg', 'png', 'webp'], 2];
        $logoFiltrado = $this->filtrarArchivo('flyer', ...$reglasImagen);

        $resLogo = $this->subirArchivoR2($logoFiltrado, 'flyers', 'flyer');
        if (!$resLogo['exito']) {
            $this->respuesta->json(null, 400, $resLogo['error']);
            return;
        }


        $datos['flyer'] = $resLogo['key'];

        $model = new OfertasModel;
        try {
            if (!$model->save($datos)) {
                $this->eliminarArchivosR2([$resLogo['key']]);
                throw new AppException('error Interno del servidor al intentar guardar', 500);
            }
        } catch (\Exception $e) {
            $this->eliminarArchivosR2([$resLogo['key']]);
            throw new AppException('Error Interno del servidor Al guardar la oferta' . $e->getMessage(), 500);
        }

        $this->respuesta->json(
            true,
            201,
            'Oferta creado con éxito.'
        );
    }



    public function actualizar(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();

        $datos = $this->filtrarDatos([
            'id' => 'esDesencriptarId',
            'nucleo_id' => 'esDesencriptarId',
            'programa_id' => 'esDesencriptarId',
            'fecha_ini' => 'esFecha',
            'fecha_fin' => 'esFecha',
            'costo_inscripcion' => 'esDecimal',
            'costo_total' => 'esDecimal',
            'cuotas' => 'esEntero',
            'modo_cuotas' => 'esDesencriptarId',
            'estado' => 'esBooleano'
        ]);

        $idPrograma = $datos['id'];
        unset($datos['id']);
        $condicion = ['id' => $idPrograma];

        $model = new OfertasModel;
        $nucleoActual = $model->flyerPorId($idPrograma);

        if (!$nucleoActual) {
            $this->respuesta->json(null, 404, 'El Oferta no existe.');
            return;
        }

        $archivosNuevosSubidos = [];
        $archivosViejosAEliminar = [];
        $reglasImagen = [['jpg', 'jpeg', 'png', 'webp'], 2];

        if (!empty($_FILES['flyer']['name'])) {
            $logoFiltrado = $this->filtrarArchivo('flyer', ...$reglasImagen);
            $resLogo = $this->subirArchivoR2($logoFiltrado, 'flyers', 'flyer');

            if (!$resLogo['exito']) {
                $this->respuesta->json(null, 400, $resLogo['error']);
                return;
            }

            $datos['flyer'] = $resLogo['key'];
            $archivosNuevosSubidos[] = $resLogo['key'];
            if (!empty($nucleoActual['flyer'])) {
                $archivosViejosAEliminar[] = $nucleoActual['flyer'];
            }
        }



        try {
            if (!$model->update($datos, $condicion)) {
                $this->eliminarArchivosR2($archivosNuevosSubidos);

                throw new AppException('error Interno del servidor', 500);
            }
        } catch (\Exception $e) {
            $this->eliminarArchivosR2($archivosNuevosSubidos);
            $this->respuesta->json(null, 500, 'Error interno del servidor: ');
            return;
        }
        $this->eliminarArchivosR2($archivosViejosAEliminar);

        $this->respuesta->json($datos, 200, 'Nucleo actualizado correctamente.');
    }


    /**
     * Endpoint para obtener las ofertas paginadas vía AJAX/JSON.
     */
    public function buscar(): void
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();



        $datos = $this->filtrarDatos([
            'limit' => 'esEntero',
            'offset' => 'esEntero',
            'estado' => 'esEntero'

        ]);

        $filtros = $this->getDatosEntrada();
        $datos['buscar'] = Validar::esTexto($filtros['buscar']);
        $datos['programa_id'] = Validar::esDesencriptarId($filtros['programa_id']);
        $datos['nucleo_id'] = Validar::esDesencriptarId($filtros['nucleo_id']);
        $datos['modalidad_id'] = Validar::esDesencriptarId($filtros['modalidad_id']);

        if ($datos['offset'] < 0) {
            $datos['offset'] = 0;
        }

        $model = new OfertasModel();
        $ofertas = $model->paginar($datos);
        $total = $model->select('count'); // Conteo total de ofertas


        $ofertas = $this->cifrarDatos($ofertas, [
            'id',
            'modo_cuotas',
            'nucleo_id',
            'programa_id'
        ]);
        // Procesamos el listado para cifrar IDs y armar las URLs de R2
        if (!empty($ofertas)) {

            foreach ($ofertas as &$o) {

                $o['flyer'] = !empty($o['flyer']) ? $this->obtenerArchivo($o['flyer']) : null;
            }
            unset($o);
        }

        $this->respuesta->json($ofertas, 200, "Ofertas obtenidas con éxito", [], (int)$total);
    }









    public function eliminarMasivo(): void
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();
        $entrada = $this->getDatosEntrada();
        $datos = $entrada['ids'] ?? [];

        $idsOriginales = [];

        if (is_array($datos) && !empty($datos)) {
            foreach ($datos as $d) {
                $idDesencriptado = Validar::esDesencriptarId($d);

                $idEntero = (int) $idDesencriptado;
                if ($idEntero > 0) {
                    $idsOriginales[] = $idEntero;
                }
            }
        }
        $model = new OfertasModel;
        $r2Service = new R2Service();
        $recursos = $model->selecionarPorIds($idsOriginales);
        foreach ($recursos as $r) {

            $flyerEliminar = $r2Service->eliminarArchivo($r['flyer']);

            if (!$flyerEliminar) {

                throw new AppException('No se pudo eliminar El recurso ' . $r['logo'], 500);
            }
        }
        $registrosEliminados = $model->eliminarPorIds($idsOriginales);

        if ($registrosEliminados > 0) {
            $this->respuesta->json([
                'exito' => true,
                'mensaje' => "Se eliminaron {$registrosEliminados} nucleos(s) correctamente."
            ], 200, '');
        } else {
            $this->respuesta->json([
                'exito' => false,
                'mensaje' => 'No se pudo eliminar ninguno de los registros seleccionados.'
            ], 409, '');
        }
    }


    public function select(): void
    {

        $this->verificarCSRF();
        $this->requerirAutenticacion();
        $datos = $this->filtrarDatos(['nucleo_id' => 'esDesencriptarId']);

        $model = new OfertasModel();
        $result = $model->traerPorNucleo($datos['nucleo_id']);

        $this->respuesta->json($result);
    }
}
