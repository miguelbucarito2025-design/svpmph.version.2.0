<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;
use App\Libs\DataBase;
use App\Libs\Exceptions\AppException;

class OfertasModel extends Model
{

    protected string $tabla = 'ofertas';

    protected array $campos = [
        'id' => 'esEntero',
        'nucleo_id' => 'esEntero',
        'programa_id' => 'esEntero',
        'fecha_ini' => 'esFecha',
        'fecha_fin' => 'esFecha',
        'costo_inscripcion' => 'esDecimal',
        'costo_total' => 'esDecimal',
        'cuotas' => 'esEntero',
        'estado' => 'esBooleano',
        'flyer' => 'esRutaArchivo',
        'modo_cuotas' => 'esEntero'

    ];

    protected array $camposMinimos = [
        'nucleo_id',
        'programa_id',
        'fecha_ini',
        'fecha_fin',
        'costo_inscripcion',
        'costo_total',
        'cuotas',
        'estado'
    ];

    /**
     * Realiza la búsqueda paginada y filtrada sobre la tabla de ofertas.
     *
     * @param array $datos Parámetros de entrada (limit, offset, buscar, programa_id, nucleo_id, modalidad_id).
     * @return array Conjunto de ofertas obtenidas con sus relaciones.
     */
    public function paginar(array $datos): array
    {
        $limit       = (int)($datos['limit'] ?? 10);
        $offset      = (int)($datos['offset'] ?? 0);
        $buscar      = trim($datos['buscar'] ?? '');
        $programaId  = (int)($datos['programa_id'] ?? 0);
        $nucleoId    = (int)($datos['nucleo_id'] ?? 0);
        $modalidadId = (int)($datos['modalidad_id'] ?? 0);
        $estado = (int)($datos['estado'] ?? null);

        // Selección explícita de campos con JOIN a sus tablas correspondientes
        $sql = "SELECT 
                o.id,
                o.estado,
                o.fecha_ini,
                o.fecha_fin,
                o.costo_inscripcion,
                o.costo_total,
                o.cuotas,
                o.modo_cuotas,
                o.flyer,
                o.programa_id,
                o.estado,
                o.nucleo_id,
                p.programa AS programa,
                p.logo AS programa_logo,
                n.nucleo AS nucleo,
                n.descripcion AS nucleo_descripcion,
                n.direccion AS nucleo_direccion,
                n.logo AS nucleo_logo,
                n.img AS nucleo_img,
                m.modalidad AS modalidad
            FROM ofertas o
            INNER JOIN programas p ON o.programa_id = p.id
            INNER JOIN nucleo n ON o.nucleo_id = n.id
            LEFT JOIN modalidad m ON o.modo_cuotas = m.id";

        $whereClauses = [];
        $params = [];

        // Filtro general por texto (Programa o Núcleo)
        if ($buscar !== '' && $buscar !== 'all') {
            $paramBuscar = mb_strtoupper($buscar, 'UTF-8') . '%';
            $whereClauses[] = "(UPPER(p.programa) LIKE ? OR UPPER(n.nucleo) LIKE ?)";
            $params[] = $paramBuscar;
            $params[] = $paramBuscar;
        }

        // Filtros específicos por selects
        if ($programaId > 0) {
            $whereClauses[] = "o.programa_id = ?";
            $params[] = $programaId;
        }
        if ($estado !== null) {
            $whereClauses[] = "o.estado = ?";
            $params[] = $estado;
        }

        if ($nucleoId > 0) {
            $whereClauses[] = "o.nucleo_id = ?";
            $params[] = $nucleoId;
        }

        if ($modalidadId > 0) {
            $whereClauses[] = "o.modo_cuotas = ?";
            $params[] = $modalidadId;
        }

        if (!empty($whereClauses)) {
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        $sql .= " ORDER BY o.id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->select($sql, $params, 'all');
    }

    public function flyerPorId(int $id)
    {
        $sql = "SELECT id,flyer FROM ofertas WHERE id=?";
        return $this->db->select($sql, [$id], 'row');;
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
        $sql = "DELETE FROM ofertas WHERE id IN ($placeholders)";
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

        $sql = "SELECT id,flyer FROM {$this->tabla} WHERE id IN ($placeholders)";
        $db = DataBase::getConnect();
        $stmt = $db->prepare($sql);

        $stmt->execute(array_values($ids));

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    public function traerPorNucleo(int $id): array
    {

        $sql = 'SELECT o.id,p.programa FROM ofertas o lEFT JOIN programas p ON o.programa_id=p.id WHERE nucleo_id=?';
        $result = $this->db->select($sql, [$id]);
        $result = $this->cifrarDatos($result, ['id']);


        return $result ?? [];
    }
}
