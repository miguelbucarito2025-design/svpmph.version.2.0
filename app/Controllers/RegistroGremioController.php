<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Helpers\Validar;
use App\Models\ArchivosModel;
use App\Models\GremioModel;
use App\Models\MencionModel;
use App\Models\PromocionModel;
use App\Traits\CifrarTrait;
use App\Traits\GeneradorCodigoTrait;
use App\Traits\ManejoArchivosR2Trait;
use Throwable;

class RegistroGremioController extends Controller
{
    use GeneradorCodigoTrait;
    use ManejoArchivosR2Trait;
    use CifrarTrait;

    public function  index(): void
    {
        $this->requerirAutenticacion();

        $urlPublica = $this->obtenerArchivo($this->session->get('foto_perfil'));



        $this->vista->render(
            'usuario/registroGremio',
            [
                'token'   => $this->session->get('csrf_token'),
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'nombreRol' => $this->session->get('nombre_rol'),
                'titlePag' => 'Registro',
                'pag' => 'registroGremio',
                'grup' => 'gremio',
                'fotoUsuario' => $urlPublica


            ],
            'usuario'
        );
    }







    public function save(): void
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();


        $datos = $this->filtrarDatos([
            'mencion_id' => 'esDesencriptarId',
            'promocion_id' => 'esDesencriptarId',
        ]);

        $datos['cuenta_id'] = $this->session->get('usuario_id');

        $model = new GremioModel;
        try {
            $model->guardar($datos);
            $this->respuesta->json(
                true,
                201,
            );
        } catch (Throwable $e) {
            if ($e->getCode() == 409) {
                $condicion = array_pop($datos);
                $datos['estado'] = false;
                $model->actualizar($condicion, $datos);
                $this->respuesta->json(
                    true,
                    200
                );
            }
            throw $e;
        }
    }


    public function estatus(): void
    {
        $this->requerirAutenticacion();

        $urlPublica = $this->obtenerArchivo($this->session->get('foto_perfil'));




        $this->vista->render(
            'usuario/gremioEstatus',
            [
                'token'   => $this->session->get('csrf_token'),
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'nombreRol' => $this->session->get('nombre_rol'),
                'titlePag' => 'Estatus',
                'pag' => 'gremioEstatus',
                'grup' => 'gremio',
                'fotoUsuario' => $urlPublica


            ],
            'usuario'
        );
    }


    public function obtenerEstatus(): void
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();
        $model = new GremioModel;
        $id = $this->session->get('usuario_id');

        $resul['datos'] = $model->getDatosPorId($id);
        $requisitos = $resul['datos']['requisitos'] ?? [];

        $archivos = new ArchivosModel;
        $resul['requisitos'] = $archivos->traerArchivos($id, $requisitos) ?? [];

        $this->respuesta->json($resul ?? []);
    }















    public function select(): void
    {
        $this->verificarCSRF();

        $model = new GremioModel;

        $resultaGremio = $model->getDatosPorId($this->session->get('usuario_id'));


        $mencion = new MencionModel();

        $ini[] = ['id' => $resultaGremio['mencion_id'] ?? '', 'mencion' => $resultaGremio['mencion']  ?? '', 'img' => $resultaGremio['img_mencion'] ?? '', 'requisitos' => $resultaGremio['requisitos'] ?? ''];
        $resultMencion['mencion'] = array_merge($ini, $mencion->selectImgNombreId(Validar::esDesencriptarId($resultaGremio['mencion_id'] ?? '')));
        $promocion = new  PromocionModel();
        $fin[] = ['id' => $resultaGremio['id'] ?? '', 'promocion' => $resultaGremio['promocion']  ?? '', 'img' => $resultaGremio['img'] ?? ''];
        $resultPromocion['promocion'] =  array_merge($fin, $promocion->selectImgNombreId(Validar::esDesencriptarId($resultaGremio['id'] ?? '')));


        $result = array_merge($resultMencion, $resultPromocion);


        $this->respuesta->json($result ?? []);
    }


    public function retirar()
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();
        $datos = ['cuenta_id' => $this->session->get('usuario_id')];
        $model = new GremioModel;
        $model->delete($datos);

        $this->respuesta->json(true);
    }







    public function agremiadosObtener()
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();
        $datos = $this->filtrarDatos([
            'limit' => 'esEntero',
            'offset' => 'esEntero'
        ]);

        $param = $this->getDatosEntrada();

        $datos['buscar']      = Validar::esTexto($param['buscar']);
        $datos['promocion_id']  = Validar::esDesencriptarId($param['promocion_id']);
        $datos['mencion_id']   = Validar::esDesencriptarId($param['mencion_id']);
        $datos['estado'] = Validar::esEntero($param['estado']);

        $model = new GremioModel;

        $result = $model->paginar($datos);
        $total = $model->select('count');

        $this->respuesta->json($result, 200, '', [], $total);
    }


    public function agremiados()
    {
        $this->requerirAutenticacion();

        $urlPublica = $this->obtenerArchivo($this->session->get('foto_perfil'));
        $mencion = new MencionModel;
        $promo = new PromocionModel;

        $this->vista->render(
            'usuario/agremiados',
            [
                'token'   => $this->session->get('csrf_token'),
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'nombreRol' => $this->session->get('nombre_rol'),
                'titlePag' => 'Agremiados',
                'pag' => 'agremiados',
                'grup' => 'gremio',
                'fotoUsuario' => $urlPublica,
                'mencion' => $mencion->traerIds(),
                'promocion' => $promo->traerIds()



            ],
            'usuario'
        );
    }
}
