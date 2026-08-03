<?php

declare(strict_types=1);

namespace App\Models;

use App\Libs\BuilderQuery;
use App\Libs\Exceptions\AppException;
use App\Libs\Exceptions\DatabaseException;

class UserModel
{
    private BuilderQuery $db;

    public function __construct()
    {
        $this->db = new BuilderQuery();
    }



    /**
     * Registra un nuevo usuario en la base de datos validando duplicados por correo.
     * 
     * @param array{
     *      usuario:string,
     *      contrasena:string,
     *      correo:string,
     *      rol_id:int
     * } $valores Arreglo indexado con los datos [usuario,contrasena, correo,rol_id].
     * @return bool
     * @throws AppException en caso de haber duplicados o error en la validadcion de campos para realizar el registro
     *@throws DatabaseException si es un error de ejecucion critico de sql
     */
    public function save(array $valores): bool
    {



        $reglas = [
            'usuario',
            'contrasena',
            'correo',
        ];

        foreach ($reglas as $campo) {

            if (!isset($valores[$campo]) || empty(trim((string)$valores[$campo]))) {
                throw new AppException("El campo {$campo} es obligatorio.", 400);
            }
        }


        if ((int)$valores['rol_id'] <= 0) {
            throw new AppException("El rol seleccionado no es válido.", 400);
        }

        $datos = [
            'usuario' => $valores['usuario'] ?? null,
            'contrasena' => $valores['contrasena'] ?? null,
            'estado' => 1,
            'correo' => $valores['correo'] ?? null,
            'rol_id' => $valores['rol_id'] ?? null
        ];

        $condicion = [
            'correo' => $datos['correo'],
            'usuario' => $datos['usuario']
        ];


        try {

            return $this->db->insert(
                'cuentas',
                $datos,
                $condicion
            );
        } catch (AppException $e) {

            if ($e->getCode() === 409) {
                throw new AppException("El correo o usuario ya está registrado.", 409);
            }
            throw $e;
        } catch (\Throwable $e) {
            throw new DatabaseException("Error al registrar el usuario: " . $e->getMessage(), (string)500);
        }
    }






    /**
     * Permite seleccionar datos de la tabla 'nat' construyendo la cláusula WHERE dinámicamente.
     * 
     * Ejemplo de uso:
     * $model->selectNat(['correo' => 'miguel@ejemplo.com', 'id_user' => 10], 'row');
     * 
     * Si quieres traer registros sin condiciones, pasa null en $condicion:
     * $model->selectNat(null, 'all');
     * 
     * @param array<string, mixed>|null $condicion Arreglo asociativo ['columna' => 'valor'] o null.
     * @param string $mode Modo de retorno: 'all' (lista), 'row' (un solo registro), 'count' (total).
     * 
     * @return array|int|null Devuelve un arreglo con los datos, el entero del conteo o null.
     * 
     * @throws AppException En caso de que ocurra un error en la ejecución.
     */
    public function selectNat(?array $condicion = null, string $mode = 'all'): array|int|null
    {
        $where = '';
        $valores = [];

        if (!empty($condicion)) {
            $clauses = array_map(fn($campo) => "{$campo} = ?", array_keys($condicion));
            $where = 'WHERE ' . implode(' AND ', $clauses);
            $valores = array_values($condicion);
        }

        $sql = "SELECT * FROM nat {$where}";

        return $this->db->select($sql, $valores, $mode);
    }



    /**
     * Permite seleccionar datos de la tabla 'usuario' construyendo la cláusula WHERE dinámicamente.
     * 
     * Ejemplo de uso:
     * $model->selectNat(['correo' => 'miguel@ejemplo.com', 'id_user' => 10], 'row');
     * 
     * Si quieres traer registros sin condiciones, pasa null en $condicion:
     * $model->selectUser(null, 'all');
     *  
     * @param array<string, mixed>|null $condicion Arreglo asociativo ['columna' => 'valor'] o null.
     * @param string $mode Modo de retorno: 'all' (lista), 'row' (un solo registro), 'count' (total).
     * 
     * @return array|int|null Devuelve un arreglo con los datos, el entero del conteo o null.
     * 
     * @throws AppException En caso de que ocurra un error en la ejecución.
     */
    public function selectUser(array|Null $condicion, string $mode): array|int|null
    {
        $where = '';
        $valores = [];

        if (!empty($condicion)) {
            $clauses = array_map(fn($campo) => "{$campo} = ?", array_keys($condicion));
            $where = 'WHERE ' . implode(' AND ', $clauses);
            $valores = array_values($condicion);
        }

        $sql = 'select * from usuarios u left join rol  r 
        on u.rol_id=r.id 
        left join nat n
        on u.nat_id=n.id
        ';
        $sql .= $where;

        return $this->db->select($sql, $valores, $mode);
    }


    /**
     * Registra una nueva nacionalidad/identificación en la tabla nat.
     * 
     * asegurarte de seguir los campos de la tabla 
     * esta vez lo hice para q hagas algo asi 
     * 
     *   $valores = [
     *      'id' => null,
     *      'nacionalidad' => 'Venezuela',
     *      'código' => '+58',
     *      'nat' => 'VEN'
     * ];
     *
     * 
     * @param array $valores Arreglo asociativo con las columnas y valores a insertar.
     * 
     * @return bool Devuelve true si la inserción fue exitosa.
     * 
     * @throws AppException Si ocurre un error de ejecución en la base de datos.
     */
    public function inserNat(array $valores): bool
    {

        $datos = [
            'id' => null,
            'nacionalidad' => $valores[0],
            'codigo' => $valores[1],
            'nat' => $valores[2]
        ];

        return $this->db->insert('nat', $datos);
    }

    /**
     * Registra un nuevo token de recuperación o actualiza el existente si el usuario ya solicitó uno.
     * 
     * @param array $valores Arreglo indexado [id_user, codigo, time_limit]
     * @return bool
     * @throws AppException|DatabaseException
     */
    public function recuperacion(array $valores): bool
    {
        $tabla = 'recuperacion';

        $datos = [
            'id_user'    => $valores[0] ?? null,
            'codigo'     => $valores[1] ?? null,
            'time_limit' => $valores[2] ?? null
        ];

        // Condición correcta apuntando a id_user ($valores[0])
        $condicion = ['id_user' => $datos['id_user']];

        try {
            return $this->db->insert($tabla, $datos, $condicion);
        } catch (AppException $e) {
            // Si el registro ya existe (HTTP 409 Conflict), actualizamos el código y tiempo
            if ($e->getCode() === 409) {
                return $this->db->update($tabla, $datos, $condicion);
            }

            // Si es otro tipo de AppException, lo dejamos fluir hacia arriba
            throw $e;
        }
    }



    /**
     * Actualiza los datos de un usuario de forma dinámica según los campos enviados.
     * 
     * Ejemplo de uso:
     * $model->updateUser([
     *     'correo' => 'miguel@gmail.com',
     *     'pass'   => 'password123'
     * ], $userId);
     * 
     * @param array<string, mixed> $datos Arreglo asociativo con los campos a actualizar ['columna' => 'valor'].
     * @param int $userId ID único del usuario a actualizar.
     * 
     * @return bool Devuelve true si la actualización se ejecutó con éxito.
     * 
     * @throws AppException Si los datos están vacíos (400) o el ID de usuario es inválido (400).
     * @throws DatabaseException Si ocurre un error durante la ejecución en la base de datos (500).
     */
    public function updateUser(array $datos, int $userId): bool
    {
        if ($userId <= 0) {
            throw new AppException("El ID de usuario proporcionado no es válido.", 400);
        }

        if (empty($datos)) {
            throw new AppException("No se proporcionaron datos para actualizar el usuario.", 400);
        }

        $condicion = ['id_user' => $userId];

        return $this->db->update('usuario', $datos, $condicion);
    }
}
