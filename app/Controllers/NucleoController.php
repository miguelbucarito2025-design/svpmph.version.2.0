<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Libs\Exceptions\AppException;
use App\Models\NucleoModel;
use App\Libs\Seguridad;
use App\Helpers\R2Service;
use App\Helpers\Validar;
use App\Traits\ManejoArchivosR2Trait;

class NucleoController extends Controller
{

    use ManejoArchivosR2Trait;
    public function  index(): void
    {
        $this->requerirAutenticacion();

        $urlPublica = $this->obtenerArchivo($this->session->get('foto_perfil'));


        $this->vista->render(
            'usuario/nucleos',
            [
                'token'   => $this->session->get('csrf_token'),
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'nombreRol' => $this->session->get('nombre_rol'),
                'titlePag' => 'Nucleos',
                'pag' => 'nucleos',
                'grup' => 'administracion',
                'fotoUsuario' => $urlPublica,

            ],
            'usuario'
        );
    }



    public function guardar(): void
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();


        $datos = $this->filtrarDatos([
            'nucleo' => 'esTexto',
            'descripcion' => 'esTexto',
            'direccion' => 'esTexto'
        ]);


        $reglasImagen = [['jpg', 'jpeg', 'png', 'webp'], 2];
        $logoFiltrado = $this->filtrarArchivo('logo', ...$reglasImagen);
        $fondoFiltrado = $this->filtrarArchivo('img', ...$reglasImagen);

        $resLogo = $this->subirArchivoR2($logoFiltrado, 'logos', 'logo');
        if (!$resLogo['exito']) {
            $this->respuesta->json(null, 400, $resLogo['error']);
            return;
        }

        $resImg = $this->subirArchivoR2($fondoFiltrado, 'img', 'img');
        if (!$resImg['exito']) {
            $this->eliminarArchivosR2([$resLogo['key']]);
            $this->respuesta->json(null, 400, $resImg['error']);
            return;
        }

        $datos['logo'] = $resLogo['key'];
        $datos['img'] = $resImg['key'];

        $model = new NucleoModel;
        try {
            if (!$model->save($datos)) {
                $this->eliminarArchivosR2([$resLogo['key'], $resImg['key']]);
                throw new AppException('error Interno del servidor al intentar guardar', 500);
            }
        } catch (\Exception $e) {
            $this->eliminarArchivosR2([$resLogo['key'], $resImg['key']]);
            throw new AppException('error Interno del servidor ' . $e->getMessage(), 500);
        }

        $this->respuesta->json(
            true,
            201,
            'Programa creado con éxito.'
        );
    }



    public function actualizar(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();

        $datos = $this->filtrarDatos([
            'id'            => 'esDesencriptarId',
            'nucleo'        => 'esTexto',
            'descripcion'   => 'esTexto',
            'direccion'     => 'esTexto'
        ]);

        $idPrograma = $datos['id'];
        unset($datos['id']);
        $condicion = ['id' => $idPrograma];

        $model = new NucleoModel;
        $nucleoActual = $model->find($idPrograma);

        if (!$nucleoActual) {
            $this->respuesta->json(null, 404, 'El Nucleo no existe.');
            return;
        }

        $archivosNuevosSubidos = [];
        $archivosViejosAEliminar = [];
        $reglasImagen = [['jpg', 'jpeg', 'png', 'webp'], 2];

        if (!empty($_FILES['logo']['name'])) {
            $logoFiltrado = $this->filtrarArchivo('logo', ...$reglasImagen);
            $resLogo = $this->subirArchivoR2($logoFiltrado, 'logos', 'logo');

            if (!$resLogo['exito']) {
                $this->respuesta->json(null, 400, $resLogo['error']);
                return;
            }

            $datos['logo'] = $resLogo['key'];
            $archivosNuevosSubidos[] = $resLogo['key'];
            if (!empty($nucleoActual['logo'])) {
                $archivosViejosAEliminar[] = $nucleoActual['logo'];
            }
        }

        if (!empty($_FILES['img']['name'])) {
            $fondoFiltrado = $this->filtrarArchivo('img', ...$reglasImagen);
            $resFondo = $this->subirArchivoR2($fondoFiltrado, 'img', 'img');

            if (!$resFondo['exito']) {
                $this->eliminarArchivosR2($archivosNuevosSubidos);
                $this->respuesta->json(null, 400, $resFondo['error']);
                return;
            }

            $datos['img'] = $resFondo['key'];
            $archivosNuevosSubidos[] = $resFondo['key'];
            if (!empty($nucleoActual['img'])) {
                $archivosViejosAEliminar[] = $nucleoActual['img'];
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


    public function buscar(): void
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();

        $datos = $this->filtrarDatos([
            'limit' => 'esEntero',
            'offset' => 'esEntero',
        ]);

        $buscar = $this->getDatosEntrada();
        $datos['buscar'] = Validar::esCadena($buscar['buscar']);

        if ($datos['offset'] < 1) {
            $datos['offset'] = 0;
        }

        $model = new NucleoModel;
        $resul = $model->paginar($datos);
        $total = $model->select('count');


        if (isset($result['id'])) {
            $resul['id'] = Seguridad::encriptarID($result['id']);
            $resul['logo'] = $this->obtenerArchivo($resul['logo']);
            $resul['img'] = $this->obtenerArchivo($resul['img']);
        } else {
            foreach ($resul as &$r) {
                if (isset($r['id'])) {
                    $r['id'] = Seguridad::encriptarID($r['id']);
                    $r['logo'] = $this->obtenerArchivo($r['logo']);
                    $r['img'] = $this->obtenerArchivo($r['img']);
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
        $model = new NucleoModel;
        $r2Service = new R2Service();
        $recursos = $model->selecionarPorIds($idsOriginales);
        foreach ($recursos as $r) {

            $logoEliminar = $r2Service->eliminarArchivo($r['img']);
            $certificadoEliminar = $r2Service->eliminarArchivo($r['logo']);

            if (!$logoEliminar) {

                throw new AppException('No se pudo eliminar El recurso ' . $r['logo'], 500);
            }

            if (!$certificadoEliminar) {
                throw new AppException('No se pudo eliminar El recurso ' . $r['img'], 500);
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

    public function traerTodos(): void
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();

        $model = new NucleoModel;
        $result = $model->all();
        $this->respuesta->json($result);
    }
}
