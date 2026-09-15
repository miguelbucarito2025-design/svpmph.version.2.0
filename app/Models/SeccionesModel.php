<?php


declare(strict_types=1);

namespace App\Models;

use App\Libs\Seguridad;
use App\Models\Abstract\Model;
use App\Traits\ManejoArchivosR2Trait;

class SeccionesModel  extends Model
{
    use ManejoArchivosR2Trait;

    protected string $tabla = 'secciones';

    protected array $campos = [
        'id' => 'esEntero',
        'seccion' => 'esTexto',
        'oferta_id' => 'esEntero',
        'cantidad_max' => 'esEntero',
        'grupo_whatsapp' => 'esGrupoWhatsapp',
        'estado' => 'esBooleano'
    ];

    protected array $camposMinimos = [
        'seccion',
        'oferta_id',
        'cantidad_max'
    ];



    /**
     * Paginación de Ofertas Académicas con empaquetamiento de sus Secciones registradas.
     *
     * @param array $datos Parámetros de la petición (limit, offset, buscar, programa_id, nucleo_id, estado).
     * @return array Lista de ofertas con sus secciones asociadas y metadatos procesados.
     */
    public function paginar(array $datos): array
    {
        $limit      = max(1, (int)($datos['limit'] ?? 10));
        $offset     = max(0, (int)($datos['offset'] ?? 0));
        $buscar     = trim($datos['buscar'] ?? '');
        $programaId = (int)($datos['programa_id'] ?? 0);
        $nucleoId   = (int)($datos['nucleo_id'] ?? 0);
        $estado     = isset($datos['estado']) && $datos['estado'] !== '' ? (int)$datos['estado'] : null;

        // Selección de campos de la Oferta y empaquetado dinámico de sus Secciones
        $sql = "SELECT 
                    o.id as oferta_id,
                    o.estado AS oferta_estado,
                    o.flyer,
                    p.programa AS programa,
                    p.logo AS programa_logo,
                    n.nucleo AS nucleo,
                    m.modalidad AS modalidad,
                    COUNT(s.id) AS total_secciones,
                    GROUP_CONCAT(
                        DISTINCT CONCAT(
                            s.id, '::',
                            IFNULL(s.seccion, 'Sin Nombre'), '::',
                            IFNULL(s.cantidad_max, 0), '::',
                            IFNULL(s.grupo_whatsapp, ''), '::',
                            IFNULL(s.estado, 1)
                        ) SEPARATOR '|||'
                    ) AS paquete_secciones
                FROM ofertas o
                INNER JOIN programas p ON o.programa_id = p.id
                INNER JOIN nucleo n ON o.nucleo_id = n.id
                LEFT JOIN modalidad m ON o.modo_cuotas = m.id
                LEFT JOIN secciones s ON o.id = s.oferta_id ";

        $whereClauses = [];
        $params       = [];

        // 1. Filtro general por texto (Programa, Núcleo o nombre de Sección)
        if ($buscar !== '' && $buscar !== 'all') {
            $paramBuscar    = '%' . mb_strtoupper($buscar, 'UTF-8') . '%';
            $whereClauses[] = '(UPPER(p.programa) LIKE ? OR UPPER(n.nucleo) LIKE ? OR UPPER(s.seccion) LIKE ?)';
            $params[]       = $paramBuscar;
            $params[]       = $paramBuscar;
            $params[]       = $paramBuscar;
        }

        // 2. Filtros específicos
        if ($programaId > 0) {
            $whereClauses[] = "o.programa_id = ?";
            $params[]       = $programaId;
        }

        if ($nucleoId > 0) {
            $whereClauses[] = "o.nucleo_id = ?";
            $params[]       = $nucleoId;
        }

        if ($estado !== null) {
            $whereClauses[] = "o.estado = ?";
            $params[]       = $estado;
        }

        // 3. Ensamblado de cláusulas WHERE
        if (!empty($whereClauses)) {
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        // 4. Agrupamiento por Oferta y Paginación
        $sql .= " GROUP BY o.id ";
        $sql .= " ORDER BY o.id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $resultados = $this->db->select($sql, $params, 'all');

        if (empty($resultados)) {
            return [];
        }

        // 5. Cifrado de llaves primarias y foráneas por seguridad
        $resultados = $this->cifrarDatos($resultados, ['id', 'oferta_id']);

        // 6. Procesamiento de URLs de imágenes y desempaquetado de secciones para JS
        return array_map(function (array $registro): array {
            // Resolver URL completa del flyer o imagen por defecto
            if (!empty($registro['flyer'])) {
                $registro['flyer_url'] = $this->ObtenerArchivo($registro['flyer']);
            } else if (!empty($registro['programa_logo'])) {
                $registro['flyer_url'] = $this->ObtenerArchivo($registro['programa_logo']);
            } else {
                $registro['flyer_url'] = 'public/img/default-flyer.jpg';
            }

            // Desempaquetar la cadena concatenada de secciones
            $registro['secciones'] = [];
            if (!empty($registro['paquete_secciones'])) {
                $filasSecciones = explode('|||', $registro['paquete_secciones']);
                foreach ($filasSecciones as $filaSec) {
                    $partes = explode('::', $filaSec);
                    if (count($partes) >= 3) {
                        $registro['secciones'][] = [
                            'id' => Seguridad::encriptarID($partes[0]),
                            'seccion'        => $partes[1],
                            'cantidad_max'   => (int)$partes[2],
                            'grupo_whatsapp' => $partes[3] ?? '',
                            'estado'         => (int)($partes[4] ?? 1)
                        ];
                    }
                }
            }

            // Limpieza de campo crudo de MySQL
            unset($registro['paquete_secciones']);

            return $registro;
        }, $resultados);
    }
}
