<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;
use App\Libs\Exceptions\AppException;
use App\Libs\DataBase;

class AsignaturasModel  extends Model
{

    protected string $tabla = 'asignaturas';

    protected array $campos = [
        'id' => 'esEntero',
        'asignatura' => 'esTexto',
        'codigo' => 'esTexto',
        'horas_teoricas' => 'esEntero',
        'horas_practicas' => 'esEntero',
        'programa_id' => 'esEntero',
    ];
    protected array $camposMinimos = [
        'asignatura',
        'codigo',
        'programa_id'

    ];

    protected array $camposUnicos = [
        'asignatura',
        'codigo',

    ];


    /**
     * Realiza la búsqueda paginada y filtrada sobre la tabla del modelo.
     *
     * @param array $datos Parámetros de entrada (limit, offset, buscar, tipo_programa, estado).
     * @return array Conjunto de registros obtenidos de la base de datos.
     * @throws AppException Si faltan parámetros requeridos o son inválidos.
     */
    public function paginar(array $datos): array
    {
        $limit  = (int)($datos['limit'] ?? 10);
        $offset = (int)($datos['offset'] ?? 0);
        $buscar = trim($datos['buscar'] ?? 'all');

        // 1. Captura limpia de los nuevos filtros (null si no vienen o están vacíos)
        $tipoPrograma = isset($datos['programa']) && $datos['programa'] !== '' ? (int)$datos['programa'] : null;

        // 2. Selección de columnas
        $campos = array_keys($this->campos);
        $camposSelect = array_filter($campos, fn($c) => $c !== 'idj');
        $selectString = implode(', ', $camposSelect);

        $sql = "SELECT {$selectString} FROM {$this->tabla}";
        $whereClauses = [];
        $params = [];

        // 3. Filtro por Búsqueda General (LIKE en todas las columnas)
        if ($buscar !== 'all' && $buscar !== '') {
            $paramBuscar = '%' . mb_strtoupper($buscar, 'UTF-8') . '%'; // Nota: faltaba el % al final

            $likeConditions = array_map(fn($campo) => "UPPER({$campo}) LIKE ?", $camposSelect);
            $whereClauses[] = '(' . implode(' OR ', $likeConditions) . ')';

            foreach ($camposSelect as $unused) {
                $params[] = $paramBuscar;
            }
        }

        // 4. Filtro por Tipo de Programa (Curso / Taller / etc.)
        if ($tipoPrograma !== null) {
            $whereClauses[] = "programa_id = ?";
            $params[] = $tipoPrograma; // Guardamos EL VALOR en los parámetros
        }


        // 6. Ensamblado automático de la cláusula WHERE
        if (!empty($whereClauses)) {
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        // 7. Ordenamiento y Paginación
        $columnaOrden = in_array('asignatura', $camposSelect) ? 'asignatura' : $camposSelect[0];
        $sql .= " ORDER BY {$columnaOrden} ASC LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        return $this->db->select($sql, $params, 'all');
    }

    /**
     * Elimina múltiples registros según la lista de IDs decodificados.
     * 
     * @param array $ids Arreglo de enteros con los IDs reales [1, 2, 3...]
     * @return int Número de filas afectadas/eliminadas
     */
    public function eliminarProgramasPorIds(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "DELETE FROM asignaturas WHERE id IN ($placeholders)";
        $db = DataBase::getConnect();
        $stmt = $db->prepare($sql);

        $stmt->execute(array_values($ids));

        return $stmt->rowCount();
    }
}
