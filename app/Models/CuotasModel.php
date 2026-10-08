<?php


declare(strict_types=1);

namespace App\Models;

use App\Helpers\TasaBCV;
use App\Libs\Exceptions\AppException;
use App\Models\Abstract\Model;
use App\Traits\LikesSQLTraits;
use App\Traits\ManejoArchivosR2Trait;

class CuotasModel  extends Model
{
    use LikesSQLTraits;
    use ManejoArchivosR2Trait;

    protected string $tabla = 'cuotas';

    protected array $campos = [
        'id' => 'esEntero',
        'cuenta_id' => 'esEntero',
        'cuota' => 'esTexto',
        'pago_id' => 'esEntero',
        'monto' => 'esDecimal',
        'status' => 'esCadena',
        'corte' => 'esFechaHora',
        'origen' => 'esCadena',
        'origen_id' => 'esEntero'
    ];
    protected array $camposMinimos = [
        'cuenta_id',
        'cuota',
        'monto',
        'status',
        'corte',
        'origen',
        'origen_id'
    ];

    protected array $camposUnicos = [];

    public function totalCuotasUser(int $id)
    {
        $sql = "SELECT cuota FROM {$this->tabla} WHERE cuenta_id=?";
        return $this->db->select($sql, [$id], 'count');
    }


    /**
     * Nombre del método: traerPorUsuario
     * Descripción: Obtiene las cuotas de un usuario con los detalles de su pago correspondiente (si existe).
     * Autor: Aprendiz de Backend
     * Fecha: 2026-10-04
     * 
     * @param int $cuenta ID de la cuenta/usuario a consultar
     * @param int $limit Límite de registros a recuperar
     * @param int $offset Desplazamiento para paginación
     * @return array|null Colección de cuotas asociadas
     * @throws AppException Si se supera el límite permitido de registros
     */
    public function traerPorUsuario(int $cuenta, int $limit = 10, int $offset = 0, ?int $destin = null): ?array
    {
        if ($limit >= 90) {
            throw new AppException('Límite de registros alcanzado. No puede sobrecargar el DOM', 400);
        }

        if ($destin !== null) {
            $destin = 'AND d.cuenta_id=' . $destin;
        }

        $sql = 'SELECT 
                c.id,
                c.cuota,
                c.status,
                c.corte,
                c.monto AS monto_usd,
                c.pago_id,
                p.metodo,
                c.origen,
                p.referencia,
                p.destinario_id,
                p.fecha,
                p.img,
                p.monto AS monto_pago_bs, 
                d.destinario,
                s.tramite_id,
                i.oferta_id 
            FROM ' . $this->tabla . ' c 
            LEFT JOIN pagos p ON c.pago_id = p.id
            LEFT JOIN destinario d ON d.id=p.destinario_id
            LEFT JOIN solicitudes_insumos s ON c.origen_id=s.id 
            LEFT JOIN inscripcion i ON c.origen_id=i.oferta_id
            WHERE c.cuenta_id = ? 
            ' . $destin . '
            ORDER BY c.id DESC 
            LIMIT ? OFFSET ?';

        $result = $this->db->select($sql, [$cuenta, $limit, $offset]);

        if (!empty($result)) {
            // Instanciamos el servicio una sola vez fuera del mapeo para optimizar memoria
            $tasa = new TasaBCV();

            $result = array_map(function (array $array) use ($tasa): array {
                $array = $this->cifrarDatos($array, ['id', 'destinario_id', 'tramite_id', 'pago_id', 'oferta_id']);

                // Mapeo de montos en bolívares calculados desde el servidor
                $array['monto_bs'] = number_format($tasa->convertirUsdABs($array['monto_usd']), 2, ',', '.');
                $array['monto_bs_pagar'] = number_format($tasa->convertirUsdABs($array['monto_usd']), 2, '.', '');
                $array['img'] = $this->obtenerArchivo($array['img']);
                $array['origen_id'] = $array['tramite_id'] ?? $array['oferta_id'];
                unset($array['tramite_id']);
                unset($array['oferta_id']);
                return $array;
            }, $result);
        }

        return $result ?? [];
    }

    /**
     * optiene el todo por el id
     *
     * @param integer $id
     * @return array devuelve el arreglo
     */
    public function obtenerPorId(int $id): ?array
    {
        $sql = 'SELECT c.*,d.nombre,d.apellido,d.id_cedula,p.img FROM ' . $this->tabla . ' c LEFT JOIN datos d ON c.cuenta_id=d.cuenta_id 
        LEFT JOIN pagos p ON p.id=c.pago_id  WHERE c.id=? ';
        return $this->db->select($sql, [$id], 'row');
    }


    /**
     * @summary Obtiene el total de cuotas por estado indexado por el nombre del estado.
     * @description Realiza una consulta agrupada por 'status' para una 'cuenta_id' específica.
     *              Transforma la matriz devuelta por la base de datos en un arreglo asociativo
     *              donde cada clave (key) corresponde al estado y su valor a la cantidad total.
     * 
     * @param int $id Identificador único de la cuenta.
     * @table cuotas
     * @column cuenta_id - Filtro asociativo por cuenta.
     * @column status - Nombre o valor del estado de la cuota.
     * 
     * @returns array|null Retorna ['ESTADO' => cantidad] o null en caso de no haber datos.
     */
    public function obtenerTotalPorId(int $id): ?array
    {
        $sql = 'SELECT 
                status, 
                COUNT(*) AS cantidad_total
            FROM cuotas 
            WHERE cuenta_id = ?
            GROUP BY status';

        $resultados = $this->db->select($sql, [$id], 'all');

        if (empty($resultados)) {
            return null;
        }

        $totalesPorClave = [];
        foreach ($resultados as $fila) {
            // Asignamos el estado como CLAVE y la cantidad como VALOR
            $totalesPorClave[$fila['status']] = (int) $fila['cantidad_total'];
        }

        return $totalesPorClave;
    }

    /**
     * @summary Obtiene el conteo total de cuotas agrupadas por su estado según la cuenta o destinatario.
     * @description Realiza un LEFT JOIN entre cuotas, pagos y destinatarios. Aplica un filtro WHERE
     *              sobre 'd.cuenta_id' únicamente cuando $destin no es igual a 5, y agrupa los
     *              resultados por el estado ('c.status') devolviendo un arreglo [estado => total].
     * 
     * @param int $id Identificador único de la cuenta.
     * @param int|null $destin Identificador opcional de destino o modo de filtrado.
     * @table cuotas c
     * @table pagos p
     * @table destinario d
     * 
     * @returns array|null Arreglo asociativo tipo ['ESTADO' => cantidad] o null si no hay registros.
     */
    public function obtenerTotal(int $id, ?int $destin = null): ?array
    {
        $where = '';
        $params = [];

        // Si $destin es distinto de 5, filtramos por la cuenta
        if ($destin !== 5) {
            $where = 'WHERE d.cuenta_id = ?';
            $params[] = $id;
        }

        $sql = "SELECT 
                c.status, 
                COUNT(*) AS cantidad_total
            FROM cuotas c 
            LEFT JOIN pagos p ON p.id = c.pago_id 
            LEFT JOIN destinario d ON p.destinario_id = d.id
            {$where}
            GROUP BY c.status";

        $resultados = $this->db->select($sql, $params, 'all');

        if (empty($resultados)) {
            return null;
        }

        $totalesPorClave = [];
        foreach ($resultados as $fila) {
            $totalesPorClave[$fila['status']] = (int) $fila['cantidad_total'];
        }

        return $totalesPorClave;
    }



    public function verificarPagoExist(int $id)
    {
        $sql = 'SELECT c.id FROM cuotas c RIGHT JOIN pagos p ON p.pago_id=p.id WHERE c.id=? ';
        return $this->db->select($sql, [$id], 'row');
    }

    /**
     * Nombre del método: paginar
     * Descripción: Pagina a los agremiados. Si el rol es 5 (Admin), trae a TODOS los agremiados
     *              (tengan o no cuotas registadas). Si es distinto de 5, filtra únicamente por
     *              la cuenta y destinatario asociado.
     * Autor: Aprendiz & Mentor Backend
     * Fecha: 2026-10-07
     * 
     * @param array $datos Parámetros de entrada (limit, offset, buscar, status, id, rol/destin).
     * @return array Lista de agremiados paginados.
     * @throws AppException Si no se proporciona una cuenta válida.
     */
    public function paginar(array $datos): array
    {
        $limit  = (int)($datos['limit'] ?? 3);
        $offset = (int)($datos['offset'] ?? 0);
        $buscar = $datos['buscar'] ?? null;
        $status = !empty($datos['status']) ? $datos['status'] : null;
        $rol    = (int)($datos['rol'] ?? $datos['destin'] ?? 0);

        if (!isset($datos['id'])) {
            throw new AppException("Cuenta Invalida", 500);
        }

        // 1. Campos a seleccionar
        $campos = 'c.id AS cuenta_id, 
               d.nombre, 
               d.apellido, 
               d.id_cedula, 
               d.foto, 
               r.id AS rol_id,
               r.img AS rol_img, 
               g.codigo AS codigo_gremio, 
               d.tlf, 
               d.ingreso,
               COALESCE(m.total_deuda_usd, 0.00) AS total_deuda_usd,
               COALESCE(m.cuotas_pendientes, 0) AS cuotas_pendientes,
               COALESCE(m.cuotas_en_proceso, 0) AS cuotas_en_proceso,
               COALESCE(m.cuotas_pagadas, 0) AS cuotas_pagadas,
               COALESCE(m.cuotas_rechazadas, 0) AS cuotas_rechazadas,
               COALESCE(m.total_cuotas, 0) AS total_cuotas';

        // 2. Condiciones base generales
        $condicionesBase = ["c.estado = 1", "r.id = 1"];
        $joinDestinatario = "";

        // Si NO es Rol 5 (Admin Global), restringimos a los datos del destinatario/cuenta específica
        if ($rol !== 5) {
            $condicionesBase[] = "t.cuenta_id = " . (int)$datos['id'];
            $joinDestinatario  = "
            LEFT JOIN cuotas cu ON cu.cuenta_id = c.id
            LEFT JOIN pagos p ON cu.pago_id = p.id
            LEFT JOIN destinario t ON p.destinario_id = t.id";
        }

        $clausIni = implode(' AND ', $condicionesBase);

        // 3. Generación de filtro LIKE
        $camposlike = ['d.nombre', 'd.apellido', 'd.id_cedula'];
        $like       = $this->likeClaus($camposlike, $buscar, $clausIni);

        $where  = $like['claus'] ?? '';
        $params = $like['values'] ?? [];

        // 4. Filtro opcional por status de cuota
        if ($status !== null) {
            $where .= (empty($where) ? ' WHERE ' : ' AND ') . "COALESCE(m.tiene_status, 0) > 0";
        }

        // 5. Consulta SQL con LEFT JOIN en las métricas (Asegura traer usuarios sin cuotas)
        $sql = "SELECT 
                $campos
            FROM cuentas c
            INNER JOIN datos d ON c.id = d.cuenta_id 
            LEFT JOIN rol r ON c.rol_id = r.id 
            LEFT JOIN gremio g ON c.id = g.cuenta_id
            $joinDestinatario
            LEFT JOIN (
                SELECT 
                    cuenta_id,
                    COUNT(id) AS total_cuotas,
                    SUM(CASE WHEN status = 'Pendiente' THEN monto ELSE 0 END) AS total_deuda_usd,
                    COUNT(CASE WHEN status = 'Pendiente' THEN 1 END) AS cuotas_pendientes,
                    COUNT(CASE WHEN status = 'En Proceso' THEN 1 END) AS cuotas_en_proceso,
                    COUNT(CASE WHEN status = 'Pagado' OR status = 'Aprobado' THEN 1 END) AS cuotas_pagadas,
                    COUNT(CASE WHEN status = 'Rechazado' THEN 1 END) AS cuotas_rechazadas,
                    COUNT(CASE WHEN status = " . ($status !== null ? "'$status'" : "''") . " THEN 1 END) AS tiene_status
                FROM cuotas
                GROUP BY cuenta_id
            ) m ON m.cuenta_id = c.id
            $where 
            GROUP BY c.id, d.id, r.id, g.id, m.cuenta_id, m.total_cuotas, m.total_deuda_usd, m.cuotas_pendientes, m.cuotas_en_proceso, m.cuotas_pagadas, m.cuotas_rechazadas
            ORDER BY d.nombre ASC 
            LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        $result = $this->db->select($sql, $params, 'all');

        if (empty($result)) {
            return [];
        }

        // 6. Mapeo y formateo final
        return array_map(function ($array) {
            $array = $this->cifrarDatos($array, ['cuenta_id']);
            $array['foto'] = $this->obtenerArchivo($array['foto'] ?? $array['rol_img']);
            unset($array['rol_img']);

            return $array;
        }, $result);
    }
}
