<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;
use App\Libs\DataBase;
use App\Libs\Exceptions\AppException;
use App\Traits\ManejoArchivosR2Trait;

class PromocionModel  extends Model
{
    use ManejoArchivosR2Trait;



    protected string $tabla = 'promocion';

    protected array $campos = [
        'id' => 'esEntero',
        'promocion' => 'esTexto',
        'periodo' => 'esTexto',
        'img' => 'esRutaArchivo',
    ];

    protected array $camposMinimos = [
        'promocion'
    ];

    protected array $camposUnicos = [
        'promocion',
    ];

    public function imgPorId(int $id)
    {
        $sql = "SELECT id,img FROM " . $this->tabla . " WHERE id=?";
        return $this->db->select($sql, [$id], 'row');;
    }




    public function paginar(array $datos): array
    {
        $limit       = (int)($datos['limit'] ?? 3);
        $offset      = (int)($datos['offset'] ?? 0);

        $sql = "SELECT * FROM {$this->tabla} ";

        $params = [];

        $sql .= " ORDER BY id ASC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->select($sql, $params, 'all');
    }


    /**
     * optiene el promocion por el id
     *
     * @param integer $id
     * @return string devuelve solo el nombre
     */
    public function obtenerPorId(int $id): string
    {
        $sql = 'SELECT promocion,img FROM promocion WHERE id=?';
        return $this->db->select($sql, [$id], 'row')['promocion'];
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
        $sql = "DELETE FROM promocion WHERE id IN ($placeholders) AND id!=5";
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

        $sql = "SELECT id,promocion FROM {$this->tabla} WHERE id!=5";
        $result = $this->db->select($sql, [], 'all');
        $result = $this->cifrarDatos($result, ['id']);
        return $result;
    }

    public function cambiarMencion(int $id): array
    {
        $sql = 'SELECT id,promocion FROM promocion WHERE id!=5 AND id!=?';
        $result = $this->db->select($sql, [$id]);

        $result = $this->cifrarDatos($result, ['id']);
        return $result;
    }

    public function selectImgNombreId(?int $id)
    {

        $param = '';
        if ($id !== null) {
            $param = 'WHERE id!=?';
            $valor[] = $id;
        }
        $sql = "SELECT id,promocion,img,periodo FROM promocion  " . $param;
        $result = $this->db->select($sql, $valor ?? [], 'all');


        return array_map(function (array $busqueda): array {
            $busqueda = $this->cifrarDatos($busqueda, [
                'id'
            ]);

            $busqueda['promocion'] .= ' ' . $busqueda['periodo'];
            $busqueda['img'] = $this->ObtenerArchivo($busqueda['img']);
            return $busqueda;
        }, $result);
    }
}
