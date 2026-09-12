<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;
use App\Traits\CifrarTrait;
use App\Traits\ManejoArchivosR2Trait;
use App\Traits\ModificarArraysTrait;

class DatosModel  extends Model
{
    use ManejoArchivosR2Trait;
    use CifrarTrait;
    use ModificarArraysTrait;

    protected string $tabla = 'datos';

    protected array $campos = [
        'id' => 'esEntero',
        'nombre' => 'esCadena',
        'apellido' => 'esCadena',
        's_nombre' => 'esCadena',
        's_apellido' => 'esCadena',
        'id_cedula' => 'esCedula',
        'tlf' => 'esTlf',
        'direccion' => 'esTexto',
        'edad' => 'esFecha',
        'foto' => 'esRutaArchivo',
        'ingreso' => 'esFechaHora',
        'cuenta_id' => 'esEntero'
    ];
    protected array $camposMinimos = [
        'nombre',
        'apellido',
        'id_cedula',
        'ingreso',
        'cuenta_id'
    ];

    protected array $camposUnicos = [
        'id_cedula',
        'tlf',
        'cuenta_id'
    ];




    public function datosPersonales(int $idCuenta)
    {

        $sql = 'SELECT 
                nombre,
                apellido,
                s_nombre,
                s_apellido,
                id_cedula,
                tlf,
                direccion,
                edad
                FROM
                datos
                WHERE cuenta_id=?
     ';

        return $this->db->select($sql, [$idCuenta], 'row');
    }

    public function datosPersonalesYLaborales(int $id)
    {



        $sql = 'SELECT 
                d.nombre,
                d.apellido,
                d.s_nombre,
                d.s_apellido,
                d.id_cedula,
                d.tlf,
                d.direccion,
                d.edad,
                d.foto,
                l.institucion_id,
                l.cargo_id
                FROM
                datos d LEFT JOIN datos_laborales l
                ON d.cuenta_id=l.cuenta_id
                WHERE d.cuenta_id=?
     ';

        return $this->db->select($sql, [$id], 'row');
    }





    /**
     * Actualiza la foto de perfil de una cuenta y retorna la foto anterior.
     *
     * @param int|string $idCuenta Identificador único de la cuenta.
     * @param string $key Nombre o ruta del nuevo archivo de imagen.
     * @return string|null|bool Retorna el nombre de la foto anterior o none si el campo esta vacio,  null si la cuenta no existe y false si falla la actualización .
     * @throws \Throwable Si ocurre un error inesperado en la base de datos.
     */
    public function guardarFoto($idCuenta, string $key): string|bool|null
    {
        try {
            // 1. Verificación previa fuera de la transacción (operación de lectura)
            $sql = 'SELECT foto, cuenta_id FROM datos WHERE cuenta_id = ?';
            $result = $this->db->select($sql, [$idCuenta], 'row');

            if (empty($result['cuenta_id'])) {
                return null; // La cuenta no existe, salimos sin abrir transacción
            }

            // 2. Inicamos la transacción únicamente para la escritura
            $this->db->beginTransaction();

            $actualizar = $this->update(['foto' => $key], ['cuenta_id' => $idCuenta]);
            if (!$actualizar) {
                $this->db->rollBack();
                return false;
            }

            $this->db->commit();

            // Retornamos el valor de la foto anterior de forma consistente
            return $result['foto'] ?? 'none';
        } catch (\Throwable $e) {
            // Solo hacemos rollback si la transacción sigue abierta
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * devuelve la cantidad total de usuario pero sin contar el administrador
     *
     * @return integer
     */
    public  function total(): int
    {
        $sql = 'SELECT d.id 
            FROM datos d 
            INNER JOIN cuentas c ON d.cuenta_id=c.id
            WHERE c.rol_id!=5
                    ';
        return $this->db->select($sql, [], 'count');
    }

    /**
     * Realiza la paginación de usuarios aplicando filtros de búsqueda y rol.
     *
     * @param array $datos Parámetros de la petición (limit, offset, buscar, rol_id).
     * @return array Listado de registros procesados, cifrados y con archivos resueltos.
     */
    public function paginar(array $datos): array
    {
        $limit  = (int)($datos['limit'] ?? 10);
        $offset = (int)($datos['offset'] ?? 0);
        $buscar = trim($datos['buscar'] ?? '');
        $rolId  = (int)($datos['rol_id'] ?? 0);

        $columnSelect = 'd.nombre, d.apellido, d.s_nombre, d.s_apellido, d.id_cedula, d.tlf, d.edad, d.direccion, d.ingreso, d.foto, c.usuario, c.correo, c.estado, c.rol_id, r.rol,r.img';

        $sql = " SELECT {$columnSelect}, d.cuenta_id
             FROM datos d
             INNER JOIN cuentas c ON d.cuenta_id = c.id
             INNER JOIN rol r ON c.rol_id = r.id ";

        $whereClauses = [];
        $params = [];

        // Limpiamos los espacios en blanco alrededor de cada columna explotada
        $campos = array_map('trim', explode(',', $columnSelect));

        if ($buscar !== '' && $buscar !== 'all') {
            $paramBuscar = mb_strtoupper($buscar, 'UTF-8') . '%';

            $whereClauses[] = $this->construirClausulaBusqueda($campos);
            foreach ($campos as $c) {
                $params[] = $paramBuscar;
            }
        }

        if ($rolId > 0) {
            $whereClauses[] = 'c.rol_id = ?';
            $params[] = $rolId;
        }

        if (!empty($whereClauses)) {
            $whereClauses[] = 'r.id!=5';
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        } else {
            $sql .= ' WHERE r.id!=5';
        }

        $sql .= ' ORDER BY d.apellido ASC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        $resultados = $this->db->select($sql, $params, 'all');

        if (empty($resultados)) {
            return [];
        }

        $resultados = $this->cifrarDatos($resultados, ['rol_id', 'cuenta_id']);

        return array_map(function (array $registro): array {
            if (!empty($registro['foto'])) {
                $registro['foto'] = $this->ObtenerArchivo($registro['foto']);
            } else {
                $registro['img'] = $this->ObtenerArchivo($registro['img']);
            }


            return $registro;
        }, $resultados);
    }

    /**
     * Realiza la paginación de usuarios aplicando filtros de búsqueda y rol.
     * Utiliza LEFT JOIN para garantizar la presencia de usuarios aun sin oferta asignada.
     *
     * @param array $datos Parámetros de la petición (limit, offset, buscar).
     * @param int $rol ID del rol a filtrar.
     * @return array Listado de registros procesados, cifrados y con archivos resueltos.
     */
    public function paginarPorRol(array $datos, int $rol): array
    {
        $limit  = max(1, (int)($datos['limit'] ?? 10));
        $offset = max(0, (int)($datos['offset'] ?? 0));
        $buscar = trim($datos['buscar'] ?? '');

        // Evaluamos si el rol debe aplicar como filtro
        $rolId = ($rol > 0 && $rol <= 4) ? $rol : null;

        $columnSelect = 'd.nombre, d.apellido, d.s_nombre, d.s_apellido, d.id_cedula, d.tlf, d.edad, d.direccion, d.ingreso, d.foto, d.cuenta_id, c.usuario, c.correo, c.estado, c.rol_id, r.rol, r.img';

        // LEFT JOIN en facilitador_oferta, ofertas y nucleo para evitar que la falta de vinculación oculte registros
        $sql = " SELECT {$columnSelect}, f.id AS oferta_id, o.id AS id_oferta_real, n.nucleo
             FROM datos d 
             INNER JOIN cuentas c ON d.cuenta_id = c.id 
             INNER JOIN rol r ON c.rol_id = r.id 
             LEFT JOIN facilitador_oferta f ON c.id = f.cuenta_id 
             LEFT JOIN ofertas o ON f.oferta_id = o.id
             LEFT JOIN nucleo n ON o.nucleo_id = n.id ";

        $whereClauses = [];
        $params = [];

        // Exclusión de roles de administración alta según tu regla
        $whereClauses[] = 'r.id != 5';

        // Filtro de Búsqueda Dinámica
        if ($buscar !== '' && $buscar !== 'all') {
            $paramBuscar = '%' . mb_strtoupper($buscar, 'UTF-8') . '%';
            $whereClauses[] = '(UPPER(d.nombre) LIKE ? OR UPPER(d.apellido) LIKE ? OR UPPER(d.id_cedula) LIKE ? OR UPPER(c.usuario) LIKE ?)';

            $params[] = $paramBuscar;
            $params[] = $paramBuscar;
            $params[] = $paramBuscar;
            $params[] = $paramBuscar;
        }

        // Filtro por Rol de usuario
        if ($rolId !== null) {
            $whereClauses[] = 'c.rol_id = ?';
            $params[] = $rolId;
        }

        if (!empty($whereClauses)) {
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        $sql .= " ORDER BY d.apellido ASC LIMIT {$limit} OFFSET {$offset}";

        $resultados = $this->db->select($sql, $params, 'all');

        if (empty($resultados)) {
            return [];
        }

        // Cifrado de identificadores sensibles para la respuesta del cliente
        $resultados = $this->cifrarDatos($resultados, ['rol_id', 'cuenta_id']);
        if (isset($resultados['oferta_id']) !== null) {
            $resultados = $this->cifrarDatos($resultados, ['oferta_id']);
        }
        if (isset($resultados['programa_id']) !== null) {
            $resultados = $this->cifrarDatos($resultados, ['programa_id']);
        }
        // Mapeo y resolución de archivos/imágenes de perfil
        return array_map(function (array $registro): array {
            if (!empty($registro['foto'])) {
                $registro['foto'] = $this->ObtenerArchivo($registro['foto']);
            } else {
                $registro['img'] = $this->ObtenerArchivo($registro['img']);
            }

            return $registro;
        }, $resultados);
    }
}
