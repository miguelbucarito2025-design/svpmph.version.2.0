<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;
use App\Libs\DataBase;
use App\Libs\Exceptions\AppException;
use App\Traits\ManejoArchivosR2Trait;
use App\Helpers\TasaBCV;
use App\Traits\LikesSQLTraits;

class TramitesModel  extends Model
{

    use ManejoArchivosR2Trait;
    use LikesSQLTraits;

    protected string $tabla = 'tramites_insumos';

    protected array $campos = [
        'id' => 'esEntero',
        'tramite' => 'esTexto',
        'descripcion' => 'esTexto',
        'precio_usd' => 'esDecimal',
        'dias_entrega_estimados' => 'esEntero',
        'estado' => 'esBooleano',
        'creado' => 'esFechaHora',
        'actualizado' => 'esFechaHora',
        'img' => 'esRutaArchivo'
    ];

    protected array $camposMinimos = [
        'tramite',
        'dias_entrega_estimados',
        'estado',
        'precio_usd',
        'creado'
    ];

    protected array $camposUnicos = [
        'id'
    ];


    public function imgPorId(int $id)
    {
        $sql = "SELECT id,img FROM {$this->tabla} WHERE id=?";
        return $this->db->select($sql, [$id], 'row');;
    }


    public function paginar(array $datos): array
    {
        $limit       = (int)($datos['limit'] ?? 3);
        $offset      = (int)($datos['offset'] ?? 0);

        $sql = "SELECT
                    id, 
                    tramite,
                    descripcion,
                    precio_usd,
                    dias_entrega_estimados,
                    estado,
                    creado,
                    actualizado,
                    img
                FROM {$this->tabla} ";

        $params = [];


        $sql .= " ORDER BY creado DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $result = $this->db->select($sql, $params, 'all');
        $result = array_map(function ($tramite): array {
            $tramite['img'] = $this->obtenerArchivo($tramite['img']);
            $tramite = $this->cifrarDatos($tramite, ['id']);
            return $tramite;
        }, $result);
        return $result ?? [];
    }


    public function ofertas(array $datos): array
    {
        $limit       = (int)($datos['limit'] ?? 3);
        $offset      = (int)($datos['offset'] ?? 0);
        $buscar     = $datos['buscar'];

        $campos = 'id, 
                    tramite,
                    descripcion,
                    precio_usd,
                    dias_entrega_estimados,
                    img';

        $camposlike = 'tramite,descripcion, precio_usd,dias_entrega_estimados';

        $clausLike = explode(',', $camposlike);
        $like = $this->likeClaus($clausLike, $buscar, 'estado=1');
        $where = $like['claus'];
        $params = $like['values'];

        $sql = "SELECT
                    $campos
                FROM {$this->tabla} ";



        $sql .= "$where ORDER BY creado DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $result = $this->db->select($sql, $params, 'all');
        if (empty($result)) {
            return [];
        }

        $result = array_map(function ($tramite): array {
            $tramite['img'] = $this->obtenerArchivo($tramite['img']);
            $tramite = $this->cifrarDatos($tramite, ['id']);
            $tasa = new TasaBCV;
            $tramite['precio_bs'] = number_format($tasa->convertirUsdABs($tramite['precio_usd']), 2, ',', '.');
            return $tramite;
        }, $result);
        return $result ?? [];
    }


    /**
     * optiene el todo por el id
     *
     * @param integer $id
     * @return array devuelve solo el nombre
     */
    public function obtenerPorId(int $id): ?array
    {
        $sql = 'SELECT * FROM ' . $this->tabla . ' WHERE id=?';
        return $this->db->select($sql, [$id], 'row');
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
        $sql = "DELETE FROM {$this->tabla} WHERE id IN ($placeholders) AND id!=5";
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
    public function imgPorIds(array $ids): array
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
}
