<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Libs\Exceptions\AppException;
use App\Helpers\R2Service;
use App\Helpers\Validar;
use App\Traits\CifrarTrait;
use App\Traits\ManejoArchivosR2Trait;
use App\Models\TramitesModel;

class TramitesController extends Controller
{

    use ManejoArchivosR2Trait;
    use CifrarTrait;

    public function  index(): void
    {
        $this->requerirAutenticacion();

        $urlPublica = $this->obtenerArchivo($this->session->get('foto_perfil'));



        $this->vista->render(
            'usuario/tramites',
            [
                'token'   => $this->session->get('csrf_token'),
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'nombreRol' => $this->session->get('nombre_rol'),
                'titlePag' => 'Tramites',
                'pag' => 'tramites',
                'grup' => 'gremio',
                'fotoUsuario' => $urlPublica


            ],
            'usuario'
        );
    }







    public function guardar(): void
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();


        $datos = $this->filtrarDatos([
            'tramite' => 'esTexto',
            'descripcion' => 'esTexto',
            'precio_usd' => 'esDecimal',
            'dias_entrega_estimados' => 'esEntero',
            'estado' => 'esBooleano'

        ]);

        $datos['creado'] = date('Y-m-d H:i:s');

        $reglasImagen = [['jpg', 'jpeg', 'png', 'webp'], 2];
        $imgFiltrado = $this->filtrarArchivo('img', ...$reglasImagen);

        $resImg = $this->subirArchivoR2($imgFiltrado, 'rol', 'img');
        if (!$resImg['exito']) {
            $this->respuesta->json(null, 400, $resImg['error']);
            return;
        }

        $datos['img'] = $resImg['key'];

        $model = new TramitesModel;
        try {
            if (!$model->save($datos)) {
                $this->eliminarArchivosR2([$resImg['key']]);
                throw new AppException('Error Interno del servidor al intentar guardar', 500);
            }
        } catch (\Exception $e) {
            $this->eliminarArchivosR2([$resImg['key']]);
            throw new AppException('Error Interno del servidor Al guardar el Rol: ' . $e->getMessage(), 500);
        }

        $this->respuesta->json(
            true,
            201,
            'rol creado con éxito.'
        );
    }



    public function actualizar(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();

        $datos = $this->filtrarDatos([
            'id' => 'esDesencriptarId',
            'tramite' => 'esTexto',
            'descripcion' => 'esTexto',
            'precio_usd' => 'esDecimal',
            'dias_entrega_estimados' => 'esEntero',
            'estado' => 'esBooleano'

        ]);

        $datos['actualizado'] = date('Y-m-d H:i:s');

        $id = $datos['id'];
        unset($datos['id']);
        $condicion = ['id' => $id];

        $model = new TramitesModel;
        $imgActual = $model->imgPorId($id);

        if (!$imgActual) {
            $this->respuesta->json(null, 404, 'El Tramite no existe.');
            return;
        }

        $archivosNuevosSubidos = [];
        $archivosViejosAEliminar = [];
        $reglasImagen = [['jpg', 'jpeg', 'png', 'webp'], 2];

        if (!empty($_FILES['img']['name'])) {
            $imgFiltrada = $this->filtrarArchivo('img', ...$reglasImagen);
            $resImg = $this->subirArchivoR2($imgFiltrada, 'rol', 'img');

            if (!$resImg['exito']) {
                $this->respuesta->json(null, 400, $resImg['error']);
                return;
            }

            $datos['img'] = $resImg['key'];
            $archivosNuevosSubidos[] = $resImg['key'];
            if (!empty($imgActual['img'])) {
                $archivosViejosAEliminar[] = $imgActual['img'];
            }
        }



        try {
            if (!$model->update($datos, $condicion)) {
                $this->eliminarArchivosR2($archivosNuevosSubidos);

                throw new AppException('error Interno del servidor', 500);
            }
        } catch (\Exception $e) {
            $this->eliminarArchivosR2($archivosNuevosSubidos);
            $this->respuesta->json(null, 500, 'Error interno del servidor ');
            return;
        }
        $this->eliminarArchivosR2($archivosViejosAEliminar);

        $this->respuesta->json($datos, 200, 'Rol actualizado correctamente.');
    }


    /**
     * Endpoint para obtener las ofertas paginadas vía AJAX/JSON.
     */
    public function paginar(): void
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();



        $datos = $this->filtrarDatos([
            'limit' => 'esEntero',
            'offset' => 'esEntero',

        ]);


        if ($datos['offset'] < 0 || $datos['offset'] > 50) {
            $datos['offset'] = 0;
        }

        $model = new TramitesModel();
        $roles = $model->paginar($datos);
        $total = $model->select('count');


        $this->respuesta->json($roles, 200, "Tramites obtenidos con éxito", [], (int)$total);
    }









    public function eliminar(): void
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
        $model = new TramitesModel;
        $r2Service = new R2Service();
        $recursos = $model->imgPorIds($idsOriginales);
        foreach ($recursos as $r) {

            $imgEliminar = $r2Service->eliminarArchivo($r['img']);

            if (!$imgEliminar) {

                throw new AppException('No se pudo eliminar El recurso ' . $r['img'], 500);
            }
        }
        $registrosEliminados = $model->eliminarPorIds($idsOriginales);

        if ($registrosEliminados > 0) {
            $this->respuesta->json([
                'exito' => true,
                'mensaje' => "Se eliminaron {$registrosEliminados} Tramite(s) correctamente."
            ], 200, '');
        } else {
            $this->respuesta->json([
                'exito' => false,
                'mensaje' => 'No se pudo eliminar ninguno de los registros seleccionados.'
            ], 409, '');
        }
    }


    public function solicitar(): void
    {
        $this->requerirAutenticacion();
        $urlPublica = $this->obtenerArchivo($this->session->get('foto_perfil'));



        $this->vista->render(
            'usuario/tramitesSolicitudes',
            [
                'token'   => $this->session->get('csrf_token'),
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'nombreRol' => $this->session->get('nombre_rol'),
                'titlePag' => 'Tramites y Solicitudes',
                'pag' => 'tramitesSolicitudes',
                'grup' => 'gremio',
                'fotoUsuario' => $urlPublica


            ],
            'usuario'
        );
    }


    public function ofertas(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();
        $datos = $this->filtrarDatos([
            'limit' => 'esEntero',
            'offset' => 'esEntero',

        ]);

        $buscar = $this->getDatosEntrada();
        $datos['buscar'] = Validar::esTexto($buscar['buscar']);


        if ($datos['offset'] < 0 || $datos['offset'] > 50) {
            $datos['offset'] = 0;
        }

        $model = new TramitesModel();
        $roles = $model->ofertas($datos);
        $total = count($roles);


        $this->respuesta->json($roles, 200, "Tramites obtenidos con éxito", [], (int)$total);
    }
}
