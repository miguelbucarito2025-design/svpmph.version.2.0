<?php


declare(strict_types=1);

namespace App\Models;

use App\Libs\DataBase;
use App\Libs\Exceptions\AppException;
use App\Models\Abstract\Model;

class ProgramasModel  extends Model
{

    protected string $tabla = 'programas';

    protected array $campos = [
        'id' => 'esEntero',
        'programa' => 'esTexto',
        'descripcion' => 'esTexto',
        'logo' => 'esRutaArchivo',
        'duracion' => 'esTexto',
        'requisitos' => 'esTexto',
        'tipo_programa' => 'esEntero',
        'estado' => 'esEntero',
        'certificado' => 'esRutaArchivo'

    ];

    protected array $camposMinimos = [
        'programa',
        'descripcion',
        'tipo_programa',

    ];

    protected array $camposUnicos = [
        'programa'
    ];


    public function find(int $id)
    {
        $sql = "SELECT * FROM programas WHERE id=?";
        return $this->db->select($sql, [$id], 'row');;
    }
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
        $tipoPrograma = isset($datos['tipo_programa']) && $datos['tipo_programa'] !== '' ? (int)$datos['tipo_programa'] : null;
        $estado       = isset($datos['estado']) && $datos['estado'] !== '' ? (int)$datos['estado'] : null;

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
            $whereClauses[] = "tipo_programa = ?";
            $params[] = $tipoPrograma; // Guardamos EL VALOR en los parámetros
        }

        // 5. Filtro por Estado (Activo: 1 / Inactivo: 0)
        if ($estado !== null) {
            $whereClauses[] = "estado = ?";
            $params[] = $estado; // Guardamos EL VALOR en los parámetros
        }

        // 6. Ensamblado automático de la cláusula WHERE
        if (!empty($whereClauses)) {
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        // 7. Ordenamiento y Paginación
        $columnaOrden = in_array('programa', $camposSelect) ? 'programa' : $camposSelect[0];
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

        // 1. Generamos los marcadores de posición dinámicos: ?, ?, ?
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // 2. Definimos la consulta SQL (Borrado Físico)
        // NOTA: Si usas borrado lógico/suave, cambia a: UPDATE programas SET estado = 0 WHERE id IN ($placeholders)
        $sql = "DELETE FROM programas WHERE id IN ($placeholders)";
        $db = DataBase::getConnect();
        $stmt = $db->prepare($sql);

        // 3. Pasamos el arreglo indexado con los valores numéricos
        $stmt->execute(array_values($ids));

        // 4. Retornamos la cantidad de filas realmente afectadas
        return $stmt->rowCount();
    }


    public function selectAllNombresIdsProgramas(): array
    {
        $sql = 'SELECT id,programa FROM programas ';
        return $this->db->select($sql, [], 'all');
    }


    /**
     * Selectciona múltiples registros según la lista de IDs decodificados.
     * solo devuelve las columnas ['certificado'] y ['logo']
     * 
     * @param array $ids Arreglo de enteros con los IDs reales [1, 2, 3...]
     * @return array Número de filas afectadas/eliminadas
     */
    public function selecionarProgramasPorIds(array $ids): array
    {
        if (empty($ids)) {
            throw new AppException('No se proporcionaron datos', 400);
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "SELECT logo,certificado FROM programas WHERE id IN ($placeholders)";
        $db = DataBase::getConnect();
        $stmt = $db->prepare($sql);

        $stmt->execute(array_values($ids));

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
