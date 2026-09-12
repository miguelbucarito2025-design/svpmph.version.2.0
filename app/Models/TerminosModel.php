<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;
use App\Libs\Exceptions\AppException;
use App\Libs\Exceptions\DatabaseException;
use App\Traits\TimestampsTrait;

class TerminosModel extends Model
{
    use TimestampsTrait;

    protected string $tabla = 'terminos';

    protected array $campos = [
        'id' => 'esEntero',
        'titulo'    => 'esTexto',
        'version'   => 'esAlfanumerico',
        'contenido' => 'esHTML',
        'estado'    => 'esBooleano',
        'fecha'     => 'esFecha',
        'rol_id'    => 'esEntero'
    ];

    protected array $camposMinimos = [
        'estado',
        'rol_id'
    ];

    protected array $camposUnicos = [];



    public function seleccionarPorRol(int $id): array
    {
        $sql = 'SELECT * FROM terminos WHERE rol_id=?';

        return $this->db->select($sql, [(int)$id], 'row');
    }


    /**
     * Obtiene todos los términos y condiciones registrados.
     *
     * @return array
     * @throws AppException Si ocurre un error en la base de datos.
     */
    public function selectAll(): array
    {
        $sql = "SELECT * FROM {$this->tabla}";

        try {
            return $this->db->select(
                $sql,
                [],
                'all'
            );
        } catch (DatabaseException $e) {
            throw new AppException("Error al obtener los registros: " . $e->getMessage(), 500);
        }
    }

    public function selectId(int $id): array
    {
        $sql = "SELECT * FROM {$this->tabla} WHERE id=?";

        try {
            return $this->db->select(
                $sql,
                [$id],
                'row'
            );
        } catch (DatabaseException $e) {
            throw new AppException("Error al obtener los terminos y condiciones", 500);
        }
    }
    public function selecRolTermminostId(int $id): array
    {
        $sql = "SELECT * FROM {$this->tabla} WHERE rol_id=?";

        try {
            return $this->db->select(
                $sql,
                [(int)$id],
                'row'
            );
        } catch (DatabaseException $e) {
            throw new AppException("Error al obtener los terminos y condiciones", 500);
        }
    }



    public function vincular(array $datos): bool
    {
        $datos['fecha'] = date('Y-m-d');
        $datos['estado'] = true;
        $datos['version'] += 0.1;

        $terminosPasados = ['estado' => false];
        $condicion = ['rol_id' => $datos['rol_id']];

        try {
            $this->db->beginTransaction();
            $update = $this->update($terminosPasados, $condicion);
            if (!$update) {
                throw new DatabaseException('Error al Actualizar');
            }


            $insert = $this->save($datos);
            if (!$insert) {
                throw new DatabaseException('Error al Insertar');
            }


            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw new DatabaseException('Error en Ejecucion de la Consulta : ' . $e->getMessage());
        }
    }
}
