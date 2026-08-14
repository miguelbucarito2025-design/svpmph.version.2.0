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
    public int $idSave;
    public function __construct()
    {
        $this->db = DataBase::getConnect();
    }

    /**
     * Inicia una transacción en la base de datos.
     * 
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    /**
     * Confirma (commit) los cambios de la transacción actual.
     * 
     * @return bool
     */
    public function commit(): bool
    {
        return $this->db->commit();
    }

    /**
     * Revierte (rollBack) los cambios realizados durante la transacción.
     * 
     * @return bool
     */
    public function rollBack(): bool
    {
        return $this->db->rollBack();
    }

    /**
     * Obtiene el ID del último registro insertado.
     * 
     * @param string|null $name Nombre de la secuencia si el motor lo requiere (ej. PostgreSQL).
     * @return string|false
     */
    public function lastInsertId(?string $name = null): string|false
    {
        return $this->db->lastInsertId($name);
    }

    /**
     * Ejecuta una inserción segura en la base de datos controlando duplicados
     * y devolviendo el ID del registro insertado.
     * 
     * @param string $tabla Nombre de la tabla.
     * @param array $datos Datos a insertar ['campo' => 'valor'].
     * @param array|null $condicion Campos para verificar duplicados ['campo' => 'valor'].
     * 
     * @return string|false Devuelve el ID de la inserción o false en caso de fallo.
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

        // Control de duplicados opcional
        if (!empty($condicion)) {
            try {
                $clausulas = array_map(fn($campo) => "{$campo} = ?", array_keys($condicion));
                $whereSql = implode(' OR ', $clausulas);

                $checkSql = "SELECT COUNT(*) FROM {$tabla} WHERE {$whereSql}";
                $checkStmt = $this->db->prepare($checkSql);
                $checkStmt->execute(array_values($condicion));

                if ($checkStmt->fetchColumn() > 0) {
                    $camposDuplicados = implode(', ', array_keys($condicion));
                    throw new AppException("Ya existe un registro con el mismo ({$camposDuplicados}).", 409);
                }
            } catch (PDOException $e) {
                throw new DatabaseException($e->getMessage(), 'Error en verificación de duplicados', 500);
            }
        }

        // Inserción
        $placeholders = implode(', ', array_fill(0, count($valores), '?'));
        $sql = "INSERT INTO {$tabla} ({$campos}) VALUES ({$placeholders})";

        try {
            $stmt = $this->db->prepare($sql);
            $ejecutado = $stmt->execute($valores);

            if ($ejecutado) {


                return true;
            }

            return false;
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
        if (empty($datos)) {
            throw new AppException("No se enviaron datos para actualizar.", 400);
        }

        if (empty($condicion)) {
            throw new AppException("Se requiere al menos una condición (WHERE) para actualizar.", 400);
        }

        $setClauses = array_map(fn($campo) => "{$campo} = ?", array_keys($datos));
        $setSql = implode(', ', $setClauses);

        $whereClauses = array_map(fn($campo) => "{$campo} = ?", array_keys($condicion));
        $whereSql = implode(' AND ', $whereClauses);

        $sql = "UPDATE {$tabla} SET {$setSql} WHERE {$whereSql}";
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
