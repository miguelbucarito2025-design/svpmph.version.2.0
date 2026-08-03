<?php

declare(strict_types=1);

namespace App\Models;

use App\Libs\BuilderQuery;
use App\Libs\Exceptions\AppException;
use App\Libs\Exceptions\DataBaseException;
use Throwable;

class NucleoModel
{
    /**
     * Inyección de dependencia limpia mediante Promoted Properties de PHP 8+
     */
    public function __construct(
        private BuilderQuery $db
    ) {}



    /**
     * Funcion para guardar un nucleo
     * 
     * @param array{
     *     nucleo: string,
     *     descripcion: string,
     *     direccion: string,
     *     logo: string
     * } $datos Arreglo con los datos a guardar
     * 
     * @return bool Devuelve true si fue exitosa la insercion
     * 
     * @throws AppException Si falta alguno de los campos o estan vacios
     * @throws DataBaseException Si ocurre un fallo en la base de datos
     */
    public function save(array $datos): bool
    {
        $reglas = [
            'nucleo',
            'descripcion',
            'direccion',
            'logo'
        ];

        foreach ($reglas as $r) {
            if (!isset($datos[$r]) || empty(trim((string)$datos[$r]))) {
                throw new AppException('El campo ' . $r . ' es obligatorio', 400);
            }
        }

        try {
            return $this->db->insert(
                'nucleo',
                [
                    'nucleo'      => trim((string)$datos['nucleo']),
                    'descripcion' => trim((string)$datos['descripcion']),
                    'direccion'   => trim((string)$datos['direccion']),
                    'logo'        => trim((string)$datos['logo'])
                ]
            );
        } catch (Throwable $e) {
            throw new DataBaseException('No se pudo guardar el nucleo en la base de datos', $e->getMessage(), 500);
        }
    }





    /**
     * Funcion para actualizar el nucleo
     *
     * @param int $id ID del núcleo a actualizar
     * @param array{
     *     nucleo?: string,
     *     descripcion?: string,
     *     direccion?: string,
     *     logo?: string
     * } $datos Arreglo con los datos a actualizar
     * 
     * @return bool Devuelve true si la actualización fue exitosa
     * 
     * @throws AppException Cuando el ID es inválido o no se envían datos a actualizar
     * @throws DataBaseException Cuando ocurre un fallo en la base de datos
     */
    public function update(int $id, array $datos): bool
    {
        if ($id <= 0) {
            throw new AppException('ID de núcleo inválido', 400);
        }

        $reglas = [
            'nucleo',
            'descripcion',
            'direccion',
            'logo'
        ];

        $valores = [];

        foreach ($reglas as $r) {
            if (isset($datos[$r]) && !empty(trim((string)$datos[$r]))) {
                $valores[$r] = trim((string)$datos[$r]);
            }
        }

        if (empty($valores)) {
            throw new AppException('No se proporcionaron datos válidos para actualizar', 400);
        }

        try {
            return $this->db->update(
                'nucleo',
                $valores,
                [
                    'id' => $id
                ]
            );
        } catch (Throwable $e) {
            throw new DataBaseException('No se pudo actualizar el núcleo en la base de datos', $e->getMessage(), 500);
        }
    }






    /**
     * Obtiene registros de la tabla núcleo según condiciones dinámicas
     *
     * @param array<string, mixed>|null $condicion Array asociativo ['campo' => 'valor']
     * @param string $mode Modo de retorno ('all', 'one', 'count', etc.)
     * 
     * @return array<mixed>|int|null
     * 
     * @throws AppException Si se intenta filtrar por una columna no permitida
     * @throws DataBaseException Si ocurre un fallo en la base de datos
     */
    public function select(?array $condicion = null, string $mode = 'all'): array|int|null
    {
        $where = '';
        $valores = [];

        if (!empty($condicion)) {
            $columnasPermitidas = ['id', 'nucleo', 'descripcion', 'direccion', 'logo'];

            $clauses = [];
            foreach ($condicion as $campo => $valor) {
                if (!in_array($campo, $columnasPermitidas, true)) {
                    throw new AppException("El campo de búsqueda '{$campo}' no es válido", 400);
                }
                $clauses[] = "{$campo} = ?";
                $valores[] = $valor;
            }

            $where = ' WHERE ' . implode(' AND ', $clauses);
        }

        try {
            $sql = 'SELECT * FROM nucleo' . $where;

            return $this->db->select(
                $sql,
                $valores,
                $mode
            );
        } catch (Throwable $e) {
            throw new DataBaseException('No se pudo obtener el núcleo de la base de datos', $e->getMessage(), 500);
        }
    }





    /**
     * Elimina un registro de la tabla núcleo según el ID proporcionado
     *
     * @param int $id ID del núcleo a eliminar
     * 
     * @return bool Devuelve true si la eliminación fue exitosa
     * 
     * @throws AppException Si el ID es inválido
     * @throws DataBaseException Si ocurre un fallo en la base de datos
     */
    public function delete(int $id): bool
    {
        if ($id <= 0) {
            throw new AppException('ID de núcleo inválido', 400);
        }

        try {
            return $this->db->delete(
                'nucleo',
                [
                    'id' => $id
                ]
            );
        } catch (Throwable $e) {
            throw new DataBaseException('No se pudo eliminar el núcleo de la base de datos', $e->getMessage(), 500);
        }
    }
}
