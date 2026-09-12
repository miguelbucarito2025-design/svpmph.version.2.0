<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Libs\Exceptions\AppException;
use App\Helpers\R2Service;
use App\Helpers\Validar;
use App\Models\OfertasModel;
use App\Models\RolModel;
use App\Traits\CifrarTrait;
use App\Traits\ManejoArchivosR2Trait;

class RolesController extends Controller
{

    use ManejoArchivosR2Trait;
    use CifrarTrait;

    public function  index(): void
    {
        $this->requerirAutenticacion();
        $r2Service = new R2Service();
        $urlPublica = $r2Service->obtenerUrlPublica($this->session->get('foto_perfil'));

        $r2Service = new R2Service();


        $this->vista->render(
            'usuario/roles',
            [
                'token'   => $this->session->get('csrf_token'),
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'nombreRol' => $this->session->get('nombre_rol'),
                'titlePag' => 'Roles',
                'pag' => 'roles',
                'grup' => 'administracion',
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
            'rol' => 'esCadena',
            'descripcion' => 'esTexto',

        ]);
        $reglasImagen = [['jpg', 'jpeg', 'png', 'webp'], 2];
        $imgFiltrado = $this->filtrarArchivo('img', ...$reglasImagen);

        $resImg = $this->subirArchivoR2($imgFiltrado, 'rol', 'img');
        if (!$resImg['exito']) {
            $this->respuesta->json(null, 400, $resImg['error']);
            return;
        }

        $datos['img'] = $resImg['key'];

        $model = new RolModel;
        try {
            if (!$model->guardar($datos)) {
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
            'rol' => 'esCadena',
            'descripcion' => 'esTexto',
        ]);

        $id = $datos['id'];
        unset($datos['id']);
        $condicion = ['id' => $id];

        $model = new RolModel;
        $imgActual = $model->imgPorId($id);

        if (!$imgActual) {
            $this->respuesta->json(null, 404, 'El Rol no existe.');
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
    public function buscar(): void
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

        $model = new RolModel();
        $roles = $model->paginar($datos);
        $total = $model->select('count');

        $r2Service = new R2Service();
        $roles = $this->cifrarDatos($roles, [
            'rol_id',
            'id_rol',
            'id_terminos'
        ]);

        if (!empty($roles)) {

            foreach ($roles as &$r) {

                $r['img'] = !empty($r['img']) ? $r2Service->obtenerUrlPublica($r['img']) : null;
            }
            unset($o);
        }

        $this->respuesta->json($roles, 200, "Roles obtenidos con éxito", [], (int)$total);
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
        $model = new RolModel;
        $r2Service = new R2Service();
        $recursos = $model->selecionarPorIds($idsOriginales);
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
                'mensaje' => "Se eliminaron {$registrosEliminados} Rol(es) correctamente."
            ], 200, '');
        } else {
            $this->respuesta->json([
                'exito' => false,
                'mensaje' => 'No se pudo eliminar ninguno de los registros seleccionados.'
            ], 409, '');
        }
    }


    public function cambiar(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();
        $datos = $this->filtrarDatos(['rol_id' => 'esDesencriptarId']);

        $model = new RolModel();
        $result = $model->cambiarRol($datos['rol_id']);

        $this->respuesta->json($result, 200);
    }
}
