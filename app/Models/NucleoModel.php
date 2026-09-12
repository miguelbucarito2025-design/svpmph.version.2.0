<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;
use App\Libs\Exceptions\AppException;
use App\Libs\DataBase;

class  NucleoModel extends Model
{

    protected string $tabla = 'nucleo';

    protected array $campos = [
        'id' => 'esEntero',
        'nucleo' => 'esTexto',
        'descripcion' => 'esTexto',
        'direccion' => 'esTexto',
        'logo' => 'esRutaArchivo',
        'img' => 'esRutaArchivo'
    ];
    protected array $camposMinimos = [
        'nucleo'
    ];

    protected array $camposUnicos = ['nucleo'];

    public function find(int $id)
    {
        $sql = "SELECT * FROM nucleo WHERE id=?";
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


        $campos = array_keys($this->campos);
        $camposSelect = array_filter($campos, fn($c) => $c !== 'idj');
        $selectString = implode(', ', $camposSelect);

        $sql = "SELECT {$selectString} FROM {$this->tabla}";
        $whereClauses = [];
        $params = [];

        if ($buscar !== 'all' && $buscar !== '') {
            $paramBuscar = '%' . mb_strtoupper($buscar, 'UTF-8') . '%';

            $likeConditions = array_map(fn($campo) => "UPPER({$campo}) LIKE ?", $camposSelect);
            $whereClauses[] = '(' . implode(' OR ', $likeConditions) . ')';

            foreach ($camposSelect as $unused) {
                $params[] = $paramBuscar;
            }
        }

        if (!empty($whereClauses)) {
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

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
    public function eliminarPorIds(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        // 1. Generamos los marcadores de posición dinámicos: ?, ?, ?
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // 2. Definimos la consulta SQL (Borrado Físico)
        // NOTA: Si usas borrado lógico/suave, cambia a: UPDATE programas SET estado = 0 WHERE id IN ($placeholders)
        $sql = "DELETE FROM nucleo WHERE id IN ($placeholders)";
        $db = DataBase::getConnect();
        $stmt = $db->prepare($sql);

        // 3. Pasamos el arreglo indexado con los valores numéricos
        $stmt->execute(array_values($ids));

        // 4. Retornamos la cantidad de filas realmente afectadas
        return $stmt->rowCount();
    }



    /**
     * Selectciona múltiples registros según la lista de IDs decodificados.
     * solo devuelve las columnas ['img'] y ['logo']
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

        $sql = "SELECT logo,img FROM {$this->tabla} WHERE id IN ($placeholders)";
        $db = DataBase::getConnect();
        $stmt = $db->prepare($sql);

        $stmt->execute(array_values($ids));

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    public function selectAllNombresIds(): array
    {
        $sql = 'SELECT id,nucleo FROM ' . $this->tabla . ' ';
        return $this->db->select($sql, [], 'all');
    }



    public function all()
    {
        $sql = 'SELECT id,nucleo FROM ' . $this->tabla;
        $result = $this->db->select($sql, [], 'all');
        $result = $this->cifrarDatos($result, ['id']);
        return $result ?? [];
    }
}
