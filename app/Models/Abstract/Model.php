<?php

declare(strict_types=1);

namespace App\Models\Abstract;

use App\Libs\BuilderQuery;
use App\Libs\Exceptions\AppException;
use App\Helpers\Validar;
use App\Traits\CifrarTrait;

/**
 * Modelo Base Abstracto
 * ---------------------
 * Proporciona la infraestructura de persistencia, validación estricta y sanitización
 * de atributos para los modelos concretos de la aplicación.
 */
abstract class Model
{
    use CifrarTrait;

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
     * Valida, sanitiza y mapea los datos de entrada según las reglas del modelo.
     *
     * @param array $datos Arreglo de pares columna => valor.
     * @param bool $esInsercion Flag para definir si aplica validación de campos mínimos obligatorios.
     * @return array Arreglo de datos limpios y mapeados para la consulta SQL.
     * @throws AppException Si falta un campo obligatorio o falla la regla de validación.
     */
    protected function validarCampos(array $datos, bool $esInsercion = true): array
    {
        if ($esInsercion) {
            foreach ($this->camposMinimos as $campoObligatorio) {
                // Modificado: Se permite '0' y 'false' como valores válidos en campos obligatorios
                if (!isset($datos[$campoObligatorio]) || ($datos[$campoObligatorio] !== false && trim((string)$datos[$campoObligatorio]) === '')) {
                    throw new AppException("El campo '{$campoObligatorio}' es obligatorio.", 400);
                }
            }
        }

        $datosLimpios = [];

        foreach ($datos as $columna => $valor) {
            if (!array_key_exists($columna, $this->campos)) {
                continue;
            }

            // 1. Conservación explícita de NULL en actualizaciones
            if (!$esInsercion && is_null($valor)) {
                $datosLimpios[$columna] = null;
                continue;
            }

            // 2. Aceptación directa de valores BOOLEANOS (tanto en inserción como en actualización)
            // Convierte true => 1 y false => 0 (ideal para MySQL TINYINT/BOOLEAN)
            if (is_bool($valor)) {
                $datosLimpios[$columna] = (int)$valor;
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
        $datosValidados = $this->validarCampos($datos, true);
        $datosUnicos = array_intersect_key($datosValidados, array_flip($this->camposUnicos));

        return $this->db->insert(
            $this->tabla,
            $datosValidados,
            $datosUnicos
        );
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
        $datosValidados = $this->validarCampos($datos, false);

        return $this->db->update(
            $this->tabla,
            $datosValidados,
            $condicionesValidadas
        );
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

        $condicionesValidadas = $this->validarCampos($condiciones, false);
        return $this->db->delete($this->tabla, $condicionesValidadas);
    }

    /**
     * Función para traer registros de una tabla según el modo especificado.
     *
     * @param string $modo 'all' (matriz de registros), 'row' (un solo registro), 'count' (total de filas).
     * @return mixed
     */
    public function select(string $modo = 'all'): mixed
    {
        $sql = 'select * from ' . $this->tabla;

        return $this->db->select(
            $sql,
            [],
            $modo
        );
    }
}
