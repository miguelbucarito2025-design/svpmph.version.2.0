<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Helpers\R2Service;
use App\Helpers\Validar;
use App\Models\FacilitadorOfertaModel;
use App\Traits\ManejoArchivosR2Trait;

class FacilitadorController extends Controller
{

    use ManejoArchivosR2Trait;

    public function index(): void
    {
        $this->requerirAutenticacion();


        $r2Service = new R2Service();
        $foto = $this->session->get('foto_perfil');
        $urlPublica = $this->obtenerArchivo($foto);

        $this->vista->render(
            'usuario/facilitador',
            [
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'nombreRol' => $this->session->get('nombre_rol'),
                'titlePag' => 'Facilitadores',
                'fotoUsuario' => $urlPublica,
                'pag' => 'facilitador'
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
            'offset' => 'esEntero'
        ]);

        $condicion = $this->getDatosEntrada();
        $datos['buscar'] = Validar::esCadena($condicion['buscar']);
        $datos['rol_id'] = 3;

        $model = new FacilitadorOfertaModel();
        $result = $model->paginarPorRol($datos, 3);
        $total = count($result);

        $this->respuesta->json(
            $result,
            200,
            '',
            [],
            $total
        );
    }

    public function save(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();
        $datos = $this->filtrarDatos([
            'cuenta_id' => 'esDesencriptarId',
            'oferta_id' => 'esDesencriptarId'
        ]);
        $datos['estado'] = 1;

        $model = new FacilitadorOfertaModel();
        $model->guardar($datos);
        $this->respuesta->json(true);
    }

    public function update(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();

        // 1. Desencriptar / filtrar los datos que vienen del cliente (AJAX / POST)
        $postData = $this->filtrarDatos([
            'cuenta_id'       => 'esDesencriptarId',
            'oferta_actual'   => 'esDesencriptarId', // ID de la oferta a reemplazar (WHERE)
            'oferta_id_nueva' => 'esDesencriptarId'  // ID de la oferta nueva (SET)
        ]);

        // 2. Construimos el arreglo de actualización usando LA CLAVE EXACTA PERMITIDA EN $campos ('oferta_id')
        $datosActualizar = [
            'oferta_id' => (int)$postData['oferta_id_nueva']
        ];

        // 3. Construimos la condición del WHERE
        $condicion = [
            'cuenta_id' => (int)$postData['cuenta_id'],
            'id' => (int)$postData['oferta_actual']
        ];

        $model = new FacilitadorOfertaModel();

        // Al llamar a update(), $datosActualizar solo tiene 'oferta_id', que SÍ está en $campos
        $resultado = $model->update($datosActualizar, $condicion);

        $this->respuesta->json($resultado);
    }



    public function traerPorUsuario(): void
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();
        $datos = $this->filtrarDatos([
            'cuenta_id' => 'esDesencriptarId'
        ]);

        $model = new FacilitadorOfertaModel();
        $result = $model->traerPorUsuario($datos['cuenta_id']);

        $this->respuesta->json($result);
    }



    public function delete(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();

        $entrada = $this->getDatosEntrada();
        $datos = Validar::esDesencriptarId($entrada['id']) ?? [];

        $idsOriginales = [$datos];


        $model = new FacilitadorOfertaModel;
        $result = $model->eliminarPorIds($idsOriginales);

        $this->respuesta->json($result);
    }
}
