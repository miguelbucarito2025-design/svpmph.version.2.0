<?php

declare(strict_types=1);

namespace App\Controllers;

use Exception;
use App\Controllers\Abstract\Controller;
use App\Models\ProgramaModel;

/**
 * Controlador de Programas
 *
 * @package App\Controllers
 */
class ProgramasController extends Controller
{
    /**
     * Instancia del modelo de programas.
     *
     * @var ProgramaModel
     */
    private ProgramaModel $programa;

    /**
     * Inicializa el controlador y prepara el modelo de datos.
     */
    public function __construct()
    {
        parent::__construct(); // Llamada correcta sin usar 'return'
        $this->programa = new ProgramaModel();
    }

    /**
     * Procesa la solicitud para guardar un nuevo programa en el sistema.
     *
     * @return void
     * @throws Exception
     */
    public function guardar(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();

        $datos = $this->filtrarDatos([
            'programa'      => 'esTexto',
            'descripcion'   => 'esTexto',
            'tipo_programa' => 'esEntero',
        ]);

        // Se corrigió la llamada a la propiedad correcta de la clase
        if (!$this->programa->save($datos)) {
            throw new Exception('Ocurrió un error interno al intentar crear el programa en la base de datos.', 500);
        }

        $this->respuesta->json(true, 201, 'Programa creado con éxito.');
    }

    /**
     * Actualiza los datos de un programa existente.
     *
     * @return void
     * @throws Exception
     */
    public function actualizar(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();

        $datos = $this->filtrarDatos([
            'id'            => 'esEntero',
            'programa'      => 'esTexto',
            'descripcion'   => 'esTexto',
            'tipo_programa' => 'esEntero',
        ]);

        $idCondicion = ['id' => $datos['id']];
        unset($datos['id']);

        if (!$this->programa->update($datos, $idCondicion)) {
            throw new Exception('No se pudo actualizar el programa en la base de datos.', 500);
        }

        $this->respuesta->json(true, 200, 'Programa actualizado correctamente.');
    }


    /**
     * Actualiza los datos de un programa existente.
     *
     * @return void
     * @throws Exception
     */
    public function eliminar(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();

        $datos = $this->filtrarDatos([
            'id'            => 'esEntero'
        ]);


        if (!$this->programa->delete($datos)) {
            throw new Exception('No se pudo Eliminar el programa en la base de datos.', 500);
        }

        $this->respuesta->json(true, 200, 'Programa actualizado correctamente.');
    }
}
