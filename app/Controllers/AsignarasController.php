<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Libs\Exceptions\AppException;
use App\Models\AsignaturasModel;
use App\Models\PensumModel;

class AsignaturasController extends Controller
{

    private AsignaturasModel $model;

    public function __construct()
    {
        $this->model = new AsignaturasModel;
        parent::__construct();
    }


    /**
     * Metodo para guardar una asignatura
     *
     * @return void
     */
    public function guardar(): void
    {

        $this->requerirAutenticacion();
        $this->verificarCSRF();
        $datos = $this->filtrarDatos(
            [
                'asignatura' => 'esTexto',
                'codigo' => 'esCadena',
                'programa_id' => 'esEntero'
            ]
        );

        $modelo = $this->model->save($datos);

        if (!$modelo) {
            throw new AppException('Error al guardar en la base de datos', 500);
        }

        $this->respuesta->json(true, 201, 'Guardado con exito');
    }

    /**
     * Metodo para Actualizar una asignatura
     *
     * @return void
     */
    public function actualizar(): void
    {

        $this->requerirAutenticacion();
        $this->verificarCSRF();
        $datos = $this->filtrarDatos(
            [
                'asignatura' => 'esTexto',
                'codigo' => 'esCadena',
                'programa_id' => 'esEntero',
                'id' => 'esEntero'
            ]
        );
        $id = ['id' => $datos['id']];
        unset($datos['id']);

        $modelo = $this->model->update($datos, $id);

        if (!$modelo) {
            throw new AppException('Error al Actualizar', 500);
        }

        $this->respuesta->json(true, 200, 'Actualizado con exito');
    }


    /**
     * Metodo q me permite registar la asignatura en el pensum
     *
     * @return void
     */
    public function vincularPensum(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();
        $datos = $this->filtrarDatos([
            'p_modalidad_id' => 'esEntero',
            'asignatura_id' => 'esEntero'
        ]);

        $modelo = new PensumModel;
        $modelo->save($datos);
        if (!$modelo) {
            throw new AppException('Error al registrar en el pensum', 500);
        }
        $this->respuesta->json(true, 201, 'Se registro en pensum correctamente');
    }
}
