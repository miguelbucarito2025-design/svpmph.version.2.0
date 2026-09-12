<?php


declare(strict_types=1);

namespace App\Models;

use App\Libs\Exceptions\DatabaseException;
use App\Models\Abstract\Model;
use App\Models\TerminosModel;
use App\Libs\DataBase;
use App\Libs\Exceptions\AppException;

class RolModel  extends Model
{

    protected string $tabla = 'rol';

    protected array $campos = [
        'id' => 'esEntero',
        'rol' => 'esCadena',
        'descripcion' => 'esTexto',
        'img' => 'esRutaArchivo'
    ];

    protected array $camposMinimos = [
        'rol'
    ];

    protected array $camposUnicos = [
        'rol',
        'id'
    ];

    public function imgPorId(int $id)
    {
        $sql = "SELECT id,img FROM rol WHERE id=?";
        return $this->db->select($sql, [$id], 'row');;
    }


    public function guardar(array $datos): bool
    {
        $db = $this->db;
        try {
            $db->beginTransaction();

            $rol = $this->save($datos);
            if (!$rol) {
                throw new  DatabaseException('Error en la creacion del Rol');
            }
            $id = $db->lastInsertId();

            $terminos = new TerminosModel;
            $terminos->save([
                'estado' => true,
                'rol_id' => $id
            ]);

            if (!$terminos) {
                throw new  DatabaseException('Error en la creacion de los Termino y condiciones');
            }

            $db->commit();
            return true;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw new  DatabaseException('Error en el Guardado' . $e->getMessage());
        }
    }


    public function paginar(array $datos): array
    {
        $limit       = (int)($datos['limit'] ?? 3);
        $offset      = (int)($datos['offset'] ?? 0);

        $sql = "SELECT 
                    r.id AS id_rol,
                    r.rol,
                    r.descripcion,
                    r.img,
                    t.id AS id_terminos,
                    t.titulo,
                    t.version,
                    t.contenido,
                    t.fecha,
                    t.rol_id
                FROM rol r 
                LEFT JOIN terminos t
                ON r.id=t.rol_id ";

        $params = [];





        $sql .= ' WHERE  estado=1 ';


        $sql .= " ORDER BY r.id ASC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->select($sql, $params, 'all');
    }


    /**
     * optiene el rol por el id
     *
     * @param integer $id
     * @return string devuelve solo el nombre
     */
    public function obtenerPorId(int $id): string
    {
        $sql = 'SELECT rol,img FROM rol WHERE id=?';
        return $this->db->select($sql, [$id], 'row')['rol'];
    }

    /**
     * Elimina múltiples registros según la lista de IDs decodificados.
     * 
     * @param array $ids Arreglo de enteros con los IDs reales [1, 2, 3...]
     * @return int Número de filas afectadas/eliminadas
     */
    public function eliminarPorIds(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "DELETE FROM rol WHERE id IN ($placeholders) AND id!=5";
        $db = DataBase::getConnect();
        $stmt = $db->prepare($sql);

        $stmt->execute(array_values($ids));
        return $stmt->rowCount();
    }

    /**
     * Selectciona múltiples registros según la lista de IDs decodificados.
     * solo devuelve las columnas ['flyer'] 
     * 
     * @param array $ids Arreglo de enteros con los IDs reales [1, 2, 3...]
     * @return array Número de filas afectadas/eliminadas
     */
    public function selecionarPorIds(array $ids): array
    {
        if (empty($ids)) {
            throw new AppException('No se proporcionaron datos', 400);
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "SELECT id,img FROM {$this->tabla} WHERE id IN ($placeholders)";
        $db = DataBase::getConnect();
        $stmt = $db->prepare($sql);

        $stmt->execute(array_values($ids));

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    public function traerIds(): array
    {

        $sql = "SELECT id,rol FROM {$this->tabla} WHERE id!=5";
        $result = $this->db->select($sql, [], 'all');
        $result = $this->cifrarDatos($result, ['id']);
        return $result;
    }

    public function cambiarRol(int $id): array
    {
        $sql = 'SELECT id,rol FROM rol WHERE id!=5 AND id!=?';
        $result = $this->db->select($sql, [$id]);

        $result = $this->cifrarDatos($result, ['id']);
        return $result;
    }
}
