<?php

declare(strict_types=1);

namespace App\Models\Abstract;

use App\Libs\BuilderQuery;
use App\Libs\Exceptions\AppException;
use App\Libs\Exceptions\DatabaseException;
use App\Helpers\Validar;

abstract class Model
{
    protected string $tabla;
    protected array $campos = [];
    protected array $camposMinimos = [];
    protected array $camposUnicos = [];

    // Propiedad protegida para acceso directo en consultas personalizadas de los modelos hijos
    protected BuilderQuery $db;

    public function __construct()
    {
        if (empty($this->campos) || empty($this->camposMinimos) || empty($this->tabla)) {
            throw new AppException("Error de configuración: Las propiedades \$campos, \$camposMinimos y \$tabla deben estar definidas.", 500);
        }

        $this->db = new BuilderQuery();
    }

    /**
     * Valida los campos antes de pasarlos al constructor de la consulta
     *
     * @param array $datos Lista de datos con sus respectivas llaves para validar 
     * @param bool $esInsercion Regla para ejecutar validación estricta en true y opcional en false
     * @return array Lista de datos validados a usar en la consulta
     * @throws AppException Si algún campo no cumple con la validación
     */
    protected function validarCampos(array $datos, bool $esInsercion = true): array
    {
        if ($esInsercion) {
            foreach ($this->camposMinimos as $campoObligatorio) {
                if (!isset($datos[$campoObligatorio]) || trim((string)$datos[$campoObligatorio]) === '') {
                    throw new AppException("El campo '{$campoObligatorio}' es obligatorio.", 400);
                }
            }
        }

        $datosLimpios = [];

        foreach ($datos as $columna => $valor) {
            if (!array_key_exists($columna, $this->campos)) {
                continue;
            }

            $valorTexto = trim((string)$valor);

            if (!$esInsercion && $valorTexto === '') {
                continue;
            }

            $metodoRegla = $this->campos[$columna];

            if (method_exists(Validar::class, $metodoRegla)) {

                $resultado = Validar::$metodoRegla($valorTexto);

                if ($resultado === false) {
                    throw new AppException("El campo '{$columna}' tiene un formato inválido.", 400);
                }

                $datosLimpios[$columna] = ($resultado === true) ? $valorTexto : $resultado;
            } else {
                throw new AppException("La regla de validación '{$metodoRegla}' no existe en la clase Validar.", 500);
            }
        }

        if (empty($datosLimpios)) {
            throw new AppException("No se proporcionaron datos válidos para procesar.", 400);
        }

        return $datosLimpios;
    }

    /**
     * Función para guardar un registro en la base de datos.
     *
     * @param array $datos Arreglo asociativo con los datos a guardar.
     * @return bool True si se guardó correctamente.
     * @throws AppException Si falla la validación (400) o ocurre un error en la base de datos (500).
     */
    public function save(array $datos): bool
    {
        try {
            $datosValidados = $this->validarCampos($datos, true);
            $datosUnicos = array_intersect_key($datosValidados, array_flip($this->camposUnicos));

            return $this->db->insert(
                $this->tabla,
                $datosValidados,
                $datosUnicos
            );
        } catch (DatabaseException $e) {
            throw new AppException("Error al guardar el registro: " . $e->getMessage(), 500);
        }
    }

    /**
     * Función para actualizar registros en la base de datos.
     *
     * @param array $datos Arreglo asociativo con los datos a actualizar.
     * @param array $condiciones Criterios de filtrado (WHERE) para la actualización.
     * @return bool True si la operación se ejecutó correctamente.
     * @throws AppException Si la validación falla (400) o hay un error en la base de datos (500).
     */
    public function update(array $datos, array $condiciones): bool
    {
        if (empty($condiciones)) {
            throw new AppException("Se requieren condiciones válidas para actualizar un registro.", 400);
        }

        $condicionesValidadas = $this->validarCampos($condiciones, false);

        try {
            $datosValidados = $this->validarCampos($datos, false);

            return $this->db->update(
                $this->tabla,
                $datosValidados,
                $condicionesValidadas
            );
        } catch (DatabaseException $e) {
            throw new AppException("Error al actualizar el registro: " . $e->getMessage(), 500);
        }
    }

    /**
     * Función para eliminar registros de la base de datos.
     *
     * @param array $condiciones Criterios de filtrado (WHERE) para la eliminación.
     * @return bool True si la operación se ejecutó correctamente.
     * @throws AppException Si no se proporcionan condiciones (400) o hay un error en la base de datos (500).
     */
    public function delete(array $condiciones): bool
    {
        if (empty($condiciones)) {
            throw new AppException("Se requieren condiciones válidas para eliminar un registro.", 400);
        }

        try {
            $condicionesValidadas = $this->validarCampos($condiciones, false);
            return $this->db->delete($this->tabla, $condicionesValidadas);
        } catch (DatabaseException $e) {
            throw new AppException("Error al eliminar el registro: " . $e->getMessage(), 500);
        }
    }
}
