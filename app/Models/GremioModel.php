<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;
use App\Traits\GeneradorCodigoTrait;
use App\Models\MencionModel;
use App\Models\ArchivosModel;
use App\Traits\ManejoArchivosR2Trait;
use App\Traits\ModificarArraysTrait;
use Throwable;

class GremioModel  extends Model
{
    use ManejoArchivosR2Trait;
    use GeneradorCodigoTrait;
    use ModificarArraysTrait;

    protected string $tabla = 'gremio';

    protected array $campos = [
        'id' => 'esEntero',
        'cuenta_id' => 'esEntero',
        'mencion_id' => 'esEntero',
        'estado' => 'esEntero',
        'creacion' => 'esFecha',
        'codigo' => 'esTexto',
        'promocion_id' => 'esEntero'
    ];

    protected array $camposMinimos = [
        'cuenta_id',
        'mencion_id',
        'promocion_id'
    ];

    protected array $camposUnicos = [
        'cuenta_id',
        'codigo'
    ];






    public function guardar(array $datos): bool
    {



        $datos['estado'] = false;
        $datos['creacion'] = date('Y-m-d');

        $mencion = new MencionModel;
        $resultMencion = $mencion->obtenerRequisitosPorId($datos['mencion_id']);

        $datos['codigo'] = $this->generarCodigoProfesional($resultMencion['codigo'], $datos['cuenta_id']);

        $requisitos = $resultMencion['requisitos'];
        try {

            $this->db->beginTransaction();
            $this->save($datos);

            $archivos = new ArchivosModel();
            $archivos->insertMultiple($datos['cuenta_id'], $requisitos);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getDatosPorId(int $id)
    {
        $sql = 'SELECT 
                   p.promocion, 
                   m.mencion,
                   m.img as img_mencion,
                   p.img,
                   p.id,
                   g.mencion_id,
                   m.requisitos,
                   g.codigo,
                   g.estado,
                   d.nombre,
                   d.s_nombre,
                   d.apellido,
                   d.s_apellido,
                   d.id_cedula
               FROM gremio g LEFT JOIN mencion m ON g.mencion_id=m.id 
               LEFT JOIN promocion p ON g.promocion_id=p.id 
               LEFT JOIN datos d ON g.cuenta_id=d.cuenta_id
               WHERE g.cuenta_id=?;
        ';
        $result = $this->db->select($sql, [$id], 'row');
        if (!empty($result)) {

            $result['img'] = $this->ObtenerArchivo($result['img']);
            $result['img_mencion'] = $this->ObtenerArchivo($result['img_mencion']);
            $result = $this->cifrarDatos($result, ['id', 'mencion_id']);
            if ($result['estado'] !== 1) $result['codigo'] = 'No Verificado';
            $result['mencion'] .= ' (Actual)';
            $result['promocion'] .= ' (Actual)';
            $result['requisitos'] = explode(',', $result['requisitos']);
        }
        return $result;
    }


    public function actualizar(int $cuenta, array $datos)
    {
        $mencion = new MencionModel;
        $requisitos = $mencion->obtenerRequisitosPorId($datos['mencion_id'])['requisitos'];
        try {

            $this->db->beginTransaction();
            $this->update($datos, ['cuenta_id' => $cuenta]);

            $archivos = new ArchivosModel();
            $archivos->insertMultiple($cuenta, $requisitos);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Realiza la búsqueda paginada sobre la tabla de agremiados.
     *
     * @param array|null $datos Parámetros de entrada
     * @return array Conjunto de agremiados con su foto de perfil resoluta.
     */
    public function paginar(?array $datos): array
    {
        $limit   = (int)($datos['limit'] ?? 10);
        $offset  = (int)($datos['offset'] ?? 0);
        $buscar  = trim($datos['buscar'] ?? '');
        $promo   = (int)($datos['promocion_id'] ?? 0);
        $carrera = (int)($datos['mencion_id'] ?? 0);
        $estado  = isset($datos['estado']) && $datos['estado'] !== '' ? (int)$datos['estado'] : null;

        $sql = "SELECT 
                    g.id AS id_gremio,
                    c.id AS id_cuenta,
                    d.nombre,
                    d.apellido,
                    d.id_cedula,
                    d.foto AS foto_usuario,
                    m.img AS foto_mencion,
                    r.img AS foto_rol,
                    g.estado,
                    g.codigo
                FROM gremio g
                INNER JOIN cuentas c ON c.id = g.cuenta_id
                LEFT JOIN datos d ON d.cuenta_id = c.id
                LEFT JOIN mencion m ON m.id = g.mencion_id 
                LEFT JOIN promocion p ON p.id = g.promocion_id
                LEFT JOIN rol r ON r.id = c.rol_id
                ";

        $whereClauses = [];
        $params       = [];

        // 1. Cuentas activas en el sistema
        $whereClauses[] = 'c.estado = ?';
        $params[]       = 1;

        $whereClauses[] = 'c.rol_id != ?';
        $params[]       = 5;

        // 2. Buscador textual
        if ($buscar !== '' && $buscar !== 'all') {
            $camposBusqueda = ['d.nombre', 'd.apellido', 'd.id_cedula', 'g.codigo'];
            $paramBuscar    = mb_strtoupper($buscar, 'UTF-8') . '%';

            $whereClauses[] = $this->construirClausulaBusqueda($camposBusqueda);

            foreach ($camposBusqueda as $c) {
                $params[] = $paramBuscar;
            }
        }

        // 3. Filtros opcionales
        if ($promo > 0) {
            $whereClauses[] = "g.promocion_id = ?";
            $params[]       = $promo;
        }

        if ($estado !== null) {
            $whereClauses[] = "g.estado = ?";
            $params[]       = $estado;
        }

        if ($carrera > 0) {
            $whereClauses[] = "g.mencion_id = ?";
            $params[]       = $carrera;
        }

        if (!empty($whereClauses)) {
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        $sql .= " ORDER BY d.nombre ASC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $result = $this->db->select($sql, $params, 'all');

        if (empty($result)) {
            return [];
        }

        // 4. Mapeo para encriptar IDs y resolver la foto priorizada
        return array_map(function ($row) {
            // Ciframos IDs para seguridad en el frontend
            $rowCifrado = $this->cifrarDatos($row, ['id_gremio', 'id_cuenta']);

            // Jerarquía para la foto de perfil:
            // 1° Foto personal del usuario (datos.foto)
            // 2° Foto por defecto de la mención (mencion.img)
            // 3° Foto por defecto del rol (rol.img)
            $fotoKey = !empty($row['foto_usuario'])
                ? $row['foto_usuario']
                : (!empty($row['foto_mencion']) ?  $row['foto_rol'] : $row['foto_mencion']);

            // Firmamos la URL amigable desde R2
            $rowCifrado['foto_url'] = !empty($fotoKey) ? $this->obtenerArchivo($fotoKey, 60) : null;

            // Limpiamos llaves temporales
            unset($rowCifrado['foto_usuario'], $rowCifrado['foto_mencion'], $rowCifrado['foto_rol']);

            return $rowCifrado; // ¡RESERVA CRUCIAL DEL RETURN!
        }, $result);
    }
}
