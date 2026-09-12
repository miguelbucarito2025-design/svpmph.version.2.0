<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Libs\Exceptions\AppException;
use App\Models\AsignaturasModel;
use App\Libs\Seguridad;
use App\Helpers\R2Service;
use App\Models\ProgramasModel;
use App\Helpers\Validar;

class AsignaturasController extends Controller
{


    public function  index(): void
    {



        $this->requerirAutenticacion();

        $r2Service = new R2Service();
        $urlPublica = $r2Service->obtenerUrlPublica($this->session->get('foto_perfil'));

        $progarmas = new ProgramasModel;
        $model = $progarmas->selectAllNombresIdsProgramas();
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
            'usuario/asignaturas',
            [
                'token'   => $this->session->get('csrf_token'),
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'nombreRol' => $this->session->get('nombre_rol'),
                'titlePag' => 'Asignaturas',
                'pag' => 'asignaturas',
                'grup' => 'administracion',
                'fotoUsuario' => $urlPublica,
                'programas' => $model

            ],
            'usuario'
        );
    }



    public function guardar(): void
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();


        $datos = $this->filtrarDatos([
            'asignatura' => 'esTexto',
            'codigo' => 'esTexto',
            'teoricas' => 'esEntero',
            'practicas' => 'esEntero',
            'programa_id' => 'esDesencriptarId'
        ]);

        $datos['horas_teoricas'] = $datos['teoricas'];
        $datos['horas_practicas'] = $datos['practicas'];




        $model = new AsignaturasModel;
        $result = $model->save($datos);
        if (!$result) {
            throw new AppException('No se pudo Guardar la Asignatura', 500);
        }
        $this->respuesta->json(
            true,
            200,
            'Guardado Existoso'
        );
    }



    public function actualizar(): void
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();


        $datos = $this->filtrarDatos([
            'asignatura' => 'esTexto',
            'codigo' => 'esTexto',
            'teoricas' => 'esEntero',
            'practicas' => 'esEntero',
            'programa_id' => 'esDesencriptarId'
        ]);
        $datos['horas_teoricas'] = $datos['teoricas'];
        $datos['horas_practicas'] = $datos['practicas'];

        $condicion = $this->filtrarDatos(['id' => 'esDesencriptarId']);



        $model = new AsignaturasModel;
        $result = $model->update($datos, $condicion);
        if (!$result) {
            throw new AppException('No se pudo Actualizar la Asignatura', 500);
        }
        $this->respuesta->json(
            true,
            200,
            'Existo al Actualizar'
        );
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
        $datos['programa'] = Validar::esDesencriptarId($buscar['programa']);
        $datos['buscar'] = Validar::esCadena($buscar['buscar']);

        if ($datos['offset'] < 1) {
            $datos['offset'] = 0;
        }

        $asignatura = new AsignaturasModel;
        $resul = $asignatura->paginar($datos);
        $total = $asignatura->select('count');

        if (isset($result['id']) || isset($result['programa_id'])) {
            $resul['id'] = Seguridad::encriptarID($result['id']);
            $resul['programa_id'] = trim(Seguridad::encriptarID($result['programa_id']));
        } else {
            foreach ($resul as &$r) {
                if (isset($r['id']) || isset($r['programa_id'])) {
                    $r['id'] = Seguridad::encriptarID($r['id']);
                    $r['programa_id'] = trim(Seguridad::encriptarID($r['programa_id']));
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
        $model = new AsignaturasModel;
        $registrosEliminados = $model->eliminarProgramasPorIds($idsOriginales);

        if ($registrosEliminados > 0) {
            $this->respuesta->json([
                'exito' => true,
                'mensaje' => "Se eliminaron {$registrosEliminados} asignaturas(s) correctamente."
            ], 200, '');
        } else {
            $this->respuesta->json([
                'exito' => false,
                'mensaje' => 'No se pudo eliminar ninguno de los registros seleccionados.'
            ], 409, '');
        }
    }
}
