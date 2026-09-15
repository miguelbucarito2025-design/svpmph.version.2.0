<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Helpers\R2Service;
use App\Helpers\Validar;
use App\Libs\Seguridad;
use App\Models\FacilitadorOfertaModel;
use App\Models\NucleoModel;
use App\Models\SeccionesModel;
use App\Traits\CifrarTrait;
use App\Libs\Exceptions\AppException;

class SeccionController extends Controller
{
    use CifrarTrait;

    public function index(): void
    {
        $this->requerirAutenticacion();
        $userRol = $this->session->get('usuario_rol');
        if ($userRol !== 3) {
            $nucleos = new NucleoModel;
            $nucleos = $this->cifrarDatos($nucleos->selectAllNombresIds(), ['id']);
            $datosNulos[] = ['id' => Seguridad::encriptarID('Nulo'), 'nucleo' => 'Seleccione'];

            $nucleos = array_merge($datosNulos, $nucleos);
        } else {
            $nucleos = new FacilitadorOfertaModel;
            $nucleos = $nucleos->traerNucleo($this->session->get("usuario_id"));
        }

        $r2Service = new R2Service();
        $urlPublica = $r2Service->obtenerUrlPublica($this->session->get('foto_perfil'));

        $datosNulos[] = ['id' => Seguridad::encriptarID('Nulo'), 'nucleo' => 'Seleccione'];

        $nucleosResult = $nucleos;

        $this->vista->render(
            'usuario/secciones',
            [
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'nombreRol' => $this->session->get('nombre_rol'),
                'titlePag' => 'Seccciones',
                'fotoUsuario' => $urlPublica,
                'pag' => 'secciones',
                'nucleos' => $nucleosResult
            ],
            'usuario'
        );
    }

    public function paginar(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();



        $datos = $this->filtrarDatos([
            'limit' => 'esEntero',
            'offset' => 'esEntero',

        ]);
        $rol = $this->session->get('usuario_rol') == 3 ? true : false;
        $filtros = $this->getDatosEntrada();
        $datos['nucleo_id'] = Validar::esDesencriptarId($filtros['nucleo_id']);
        $datos['buscar'] = Validar::esTexto($filtros['buscar']);
        if ($rol && $datos['nucleo_id'] == NULL) {
            $datos['nucleo_id'] = 10000;
        }

        if ($datos['offset'] < 0) {
            $datos['offset'] = 0;
        }
        $datos['estado'] = 1;
        $model = new SeccionesModel();
        $ofertas = $model->paginar($datos);
        $total = $rol ? count($ofertas) : $model->select("count"); // Conteo total de ofertas

        $r2Service = new R2Service();
        $ofertas = $this->cifrarDatos($ofertas, [
            'id',
            'modo_cuotas',
            'nucleo_id',
            'programa_id'
        ]);

        if (!empty($ofertas)) {

            foreach ($ofertas as &$o) {

                $o['flyer'] = !empty($o['flyer']) ? $r2Service->obtenerUrlPublica($o['flyer']) : null;
            }
            unset($o);
        }

        $this->respuesta->json($ofertas, 200, "Ofertas obtenidas con éxito", [], (int)$total);
    }

    public function save(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();
        $datos = $this->filtrarDatos([
            'oferta_id' => 'esDesencriptarId',
            'grupo_whatsapp' => 'esGrupoWhatsapp',
            'seccion' => 'esTexto',
            'cantidad_max' => 'esEntero'
        ]);
        $datos['estado'] = 1;

        $model = new SeccionesModel();
        $model->save($datos);
        $this->respuesta->json(true);
    }

    public function update(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();
        $datos = $this->filtrarDatos([
            'grupo_whatsapp' => 'esGrupoWhatsapp',
            'seccion' => 'esTexto',
            'cantidad_max' => 'esEntero'
        ]);

        $condicion = $this->filtrarDatos([
            'id' => 'esDesencriptarId',
        ]);

        $model = new SeccionesModel();
        $model->update($datos, $condicion);
        $this->respuesta->json(true);
    }






    public function delete(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();
        $datos = $this->filtrarDatos([
            'id' => 'esDesencriptarId'
        ]);

        $model = new SeccionesModel;
        $result = $model->delete($datos);

        $this->respuesta->json($result);
    }
}
