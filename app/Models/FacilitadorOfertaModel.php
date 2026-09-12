<?php


declare(strict_types=1);

namespace App\Models;

use App\Libs\DataBase;
use App\Libs\Exceptions\AppException;
use App\Models\Abstract\Model;
use App\Traits\ManejoArchivosR2Trait;

class FacilitadorOfertaModel  extends Model
{
    use ManejoArchivosR2Trait;

    protected string $tabla = 'facilitador_oferta';

    protected array $campos = [
        'id' => 'esEntero',
        'cuenta_id' => 'esEntero',
        'oferta_id' => 'esEntero',
        'estado' => 'esBooleano'
    ];

    protected array $camposMinimos = [
        'cuenta_id',
        'oferta_id',
        'estado'
    ];




    /**
     * Paginación de facilitadores con empaquetamiento de sus ofertas asignadas.
     *
     * @param array $datos Parámetros de la petición (limit, offset, buscar).
     * @param int $rol ID del rol a filtrar.
     * @return array Lista de registros procesados con sus ofertas asociadas.
     */
    public function paginarPorRol(array $datos, int $rol): array
    {
        $limit  = max(1, (int)($datos['limit'] ?? 10));
        $offset = max(0, (int)($datos['offset'] ?? 0));
        $buscar = trim($datos['buscar'] ?? '');

        $rolId = ($rol > 0 && $rol <= 4) ? $rol : null;

        $columnSelect = 'd.nombre, d.apellido, d.s_nombre, d.s_apellido, d.id_cedula, d.tlf, d.edad, d.direccion, d.ingreso, d.foto, d.cuenta_id, c.usuario, c.correo, c.estado, c.rol_id, r.rol, r.img';

        $sql = " SELECT {$columnSelect},
                    COUNT(o.id) AS total_ofertas,
                    GROUP_CONCAT(DISTINCT o.id SEPARATOR ',') AS ofertas_ids,
                    GROUP_CONCAT(
                        DISTINCT CONCAT(
                            o.id, '::', 
                            IFNULL(p.programa, 'Sin Programa'), '::', 
                            IFNULL(n.nucleo, 'Sin Núcleo'), '::', 
                            IFNULL(n.id, 0), '::',
                            IFNULL(m.modalidad, 'N/A')
                        ) SEPARATOR '|||'
                    ) AS paquete_ofertas
             FROM datos d 
             INNER JOIN cuentas c ON d.cuenta_id = c.id 
             INNER JOIN rol r ON c.rol_id = r.id 
             LEFT JOIN facilitador_oferta f ON c.id = f.cuenta_id 
             LEFT JOIN ofertas o ON f.oferta_id = o.id
             LEFT JOIN programas p ON o.programa_id = p.id
             LEFT JOIN nucleo n ON o.nucleo_id = n.id
             LEFT JOIN modalidad m ON o.modo_cuotas = m.id ";

        $whereClauses = [];
        $params = [];

        $whereClauses[] = 'r.id != 5';

        if ($buscar !== '' && $buscar !== 'all') {
            $paramBuscar = '%' . mb_strtoupper($buscar, 'UTF-8') . '%';
            $whereClauses[] = '(UPPER(d.nombre) LIKE ? OR UPPER(d.apellido) LIKE ? OR UPPER(d.id_cedula) LIKE ? OR UPPER(c.usuario) LIKE ? OR UPPER(p.programa) LIKE ? OR UPPER(n.nucleo) LIKE ?)';

            $params[] = $paramBuscar;
            $params[] = $paramBuscar;
            $params[] = $paramBuscar;
            $params[] = $paramBuscar;
            $params[] = $paramBuscar;
            $params[] = $paramBuscar;
        }

        if ($rolId !== null) {
            $whereClauses[] = 'c.rol_id = ?';
            $params[] = $rolId;
        }

        if (!empty($whereClauses)) {
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        // Agrupación por usuario
        $sql .= " GROUP BY c.id, d.id ";
        $sql .= " ORDER BY d.apellido ASC LIMIT {$limit} OFFSET {$offset}";

        $resultados = $this->db->select($sql, $params, 'all');

        if (empty($resultados)) {
            return [];
        }

        $resultados = $this->cifrarDatos($resultados, ['rol_id', 'cuenta_id']);

        // Transformamos la cadena concatenada en un arreglo de objetos para JavaScript
        return array_map(function (array $registro): array {
            if (!empty($registro['foto'])) {
                $registro['foto'] = $this->ObtenerArchivo($registro['foto']);
            } else {
                $registro['img'] = $this->ObtenerArchivo($registro['img']);
            }

            $registro['lista_ofertas'] = [];
            if (!empty($registro['paquete_ofertas'])) {
                $filas = explode('|||', $registro['paquete_ofertas']);
                foreach ($filas as $fila) {
                    $partes = explode('::', $fila);
                    if (count($partes) >= 3) {
                        $registro['lista_ofertas'][] = [
                            'oferta_id'   => $partes[0],
                            'programa'    => $partes[1],
                            'nucleo'      => $partes[2],
                            'nucleo_id'   => $partes[3] ?? null,
                            'modalidad'   => $partes[4] ?? ''
                        ];
                    }
                }
            }

            unset($registro['paquete_ofertas']);

            return $registro;
        }, $resultados);
    }


    public function guardar(array $datos)
    {
        $valores = [$datos['oferta_id'], $datos['cuenta_id']];

        $sql = 'SELECT * FROM  ' . $this->tabla . ' WHERE oferta_id=? AND cuenta_id=? ';
        $result = $this->db->select($sql, $valores, 'count');

        if ($result === 0) {
            return $this->save($datos);
        } else {
            throw new AppException('El Usuario Ya tiene registrada esa oferta', 400);
        }
    }


    public function traerPorUsuario(int $id): array
    {
        $sql = 'SELECT f.id,p.programa  FROM facilitador_oferta f LEFT JOIN ofertas o  ON f.oferta_id=o.id LEFT JOIN programas p  ON o.programa_id=p.id WHERE f.cuenta_id=?';
        $datos = $this->db->select($sql, [$id]);
        $result = $this->cifrarDatos($datos, ['id']);
        return $result;
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
        $sql = "DELETE FROM {$this->tabla} WHERE id IN ($placeholders)";
        $db = DataBase::getConnect();
        $stmt = $db->prepare($sql);

        $stmt->execute(array_values($ids));
        return $stmt->rowCount();
    }
}
