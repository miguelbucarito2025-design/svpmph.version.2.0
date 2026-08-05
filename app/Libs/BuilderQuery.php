<?php

declare(strict_types=1);


namespace App\Libs;

use App\Libs\Exceptions\DatabaseException;
use App\Libs\Exceptions\AppException;
use PDO;
use PDOException;

class BuilderQuery
{
    private ?PDO $db = null;
    public mixed $resul = null;

    public function __construct()
    {

        $this->db = DataBase::getConnect();
    }

    /**
     * Ejecuta una inserción segura en la base de datos controlando duplicados.
     * 
     * @param string $tabla Nombre de la tabla.
     * @param array $datos Datos a insertar ['campo' => 'valor'].
     * @param array|null $condicion Campos para verificar duplicados ['campo' => 'valor'].
     * 
     * @return bool
     * 
     * @throws AppException
     * @throws DatabaseException
     */
    public function insert(string $tabla, array $datos, ?array $condicion = null): bool
    {
        $campos = implode(', ', array_keys($datos));
        $valores = array_values($datos);

        if (empty($valores)) {
            throw new AppException("No se enviaron valores para insertar.", 400);
        }

        // 2. Control de duplicados opcional
        if (!empty($condicion)) {
            try {
                // Generamos 'campo1 = ? AND campo2 = ?' de forma limpia
                $clausulas = array_map(fn($campo) => "{$campo} = ?", array_keys($condicion));
                $whereSql = implode(' AND ', $clausulas);

                $checkSql = "SELECT COUNT(*) FROM {$tabla} WHERE {$whereSql}";
                $checkStmt = $this->db->prepare($checkSql);

                // Ejecutamos pasando solo el arreglo plano de valores
                $checkStmt->execute(array_values($condicion));

                if ($checkStmt->fetchColumn() > 0) {
                    $camposDuplicados = implode(', ', array_keys($condicion));
                    throw new AppException("Ya existe un registro con el mismo ({$camposDuplicados}).", 409);
                }
            } catch (PDOException $e) {
                throw new DatabaseException($e->getMessage(), 'Error en verificación de duplicados', 500);
            }
        }

        // 3. Inserción
        $placeholders = implode(', ', array_fill(0, count($valores), '?'));
        $sql = "INSERT INTO {$tabla} ({$campos}) VALUES ({$placeholders})";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($valores);
        } catch (PDOException $e) {
            throw new DatabaseException($e->getMessage(), $sql, 500);
        }
    }



    /**
     * Ejecuta una actualización dinámica y segura en la base de datos.
     * 
     * @param string $tabla Nombre de la tabla objetivo.
     * @param array $datos Arreglo asociativo de datos a actualizar ['columna' => 'nuevo_valor'].
     * @param array $condicion Arreglo asociativo para la cláusula WHERE ['columna' => 'valor_buscar'].
     * 
     * @return bool Devuelve true si la consulta se ejecutó con éxito.
     * 
     * @throws AppException Si no se envían datos o condiciones.
     * @throws DatabaseException Si ocurre un error de ejecución SQL.
     */
    public function update(string $tabla, array $datos, array $condicion): bool
    {
        // 1. Validaciones iniciales
        if (empty($datos)) {
            throw new AppException("No se enviaron datos para actualizar.", 400);
        }

        if (empty($condicion)) {
            throw new AppException("Se requiere al menos una condición (WHERE) para actualizar.", 400);
        }

        // 2. Construir el SET dinámico ('columna1 = ?, columna2 = ?')
        $setClauses = array_map(fn($campo) => "{$campo} = ?", array_keys($datos));
        $setSql = implode(', ', $setClauses);

        // 3. Construir el WHERE dinámico ('id = ? AND estado = ?')
        $whereClauses = array_map(fn($campo) => "{$campo} = ?", array_keys($condicion));
        $whereSql = implode(' AND ', $whereClauses);

        // 4. Armar la consulta SQL final
        $sql = "UPDATE {$tabla} SET {$setSql} WHERE {$whereSql}";

        // 5. Unir los valores para el execute (Primero los del SET, luego los del WHERE)
        $valores = array_merge(array_values($datos), array_values($condicion));

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($valores);
        } catch (PDOException $e) {
            throw new DatabaseException($e->getMessage(), $sql, 500);
        }
    }

    /**
     * Ejecuta una consulta SELECT flexible y parametrizada con PDO.
     */
    public function select(string $sql, array $params = [], string $modo = 'all'): mixed
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return match ($modo) {
                'all'   => $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [],
                'row'   => $stmt->fetch(PDO::FETCH_ASSOC) ?: null,
                'count' => $stmt->rowCount(),
                default => false,
            };
        } catch (PDOException $e) {
            throw new DatabaseException($e->getMessage(), $sql, 500);
        }
    }

    /**
     * Elimina registros de una tabla según las condiciones indicadas.
     * 
     * @param string $tabla Nombre de la tabla objetivo
     * @param array<string, mixed> $where Arreglo asociativo ['campo' => valor]
     * 
     * @return bool True si la ejecución fue exitosa
     * 
     * @throws AppException Si falta la tabla o los criterios
     * @throws DatabaseException Si ocurre un error en la consulta PDO
     */
    public function delete(string $tabla, array $where): bool
    {
        $tablaLimpia = trim($tabla);

        if (empty($tablaLimpia) || empty($where)) {
            throw new AppException("No se proporcionaron la tabla o los criterios de eliminación.", 400);
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tablaLimpia)) {
            throw new AppException("El nombre de la tabla es inválido.", 400);
        }

        $whereClauses = [];
        $valores = [];

        foreach ($where as $campo => $valor) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $campo)) {
                throw new AppException("El campo '{$campo}' en la condición es inválido.", 400);
            }
            $whereClauses[] = "{$campo} = ?";
            $valores[] = $valor;
        }

        $whereSql = implode(' AND ', $whereClauses);
        $sql = "DELETE FROM {$tablaLimpia} WHERE {$whereSql}";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($valores);
        } catch (\PDOException $e) {
            throw new DatabaseException("Error al intentar eliminar en la base de datos", $e->getMessage(), 500);
        }
    }
}
