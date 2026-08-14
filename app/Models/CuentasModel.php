<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;
use App\Libs\Exceptions\DatabaseException;
use App\Libs\Exceptions\AppException;
use Throwable;

/**
 * Clase CuentasModel
 *
 * Modelo encargado de la gestión de persistencia, validación y consultas
 * para la tabla de cuentas de usuario en la base de datos.
 *
 * @package App\Models
 */
class CuentasModel extends Model
{
    /**
     * Nombre de la tabla principal en la base de datos.
     *
     * @var string
     */
    protected string $tabla = 'cuentas';

    /**
     * Mapa de campos de la tabla y sus respectivas reglas de validación.
     *
     * @var array<string, string>
     */
    protected array $campos = [
        'id'           => 'esEntero',
        'usuario'      => 'esNombreUsuario',
        'contrasena'   => 'esPassword',
        'estado'       => 'esEntero',
        'codigo'       => 'esTexto',
        'fecha_codigo' => 'esFechaHora',
        'rol_id'       => 'esEntero',
        'correo'       => 'esCorreo'
    ];

    /**
     * Campos estrictamente obligatorios para la creación de un nuevo registro.
     *
     * @var array<int, string>
     */
    protected array $camposMinimos = [
        'usuario',
        'contrasena',
        'estado',
        'correo'
    ];

    /**
     * Campos que requieren verificación de unicidad en la tabla.
     *
     * @var array<int, string>
     */
    protected array $camposUnicos = [
        'usuario',
        'correo'
    ];

    /**
     * Valida, encripta la contraseña y guarda una nueva cuenta de usuario.
     *
     * @param array{
     *     usuario: string,
     *     contrasena: string,
     *     estado: int,
     *     correo: string
     * } $datos Datos de la cuenta a registrar.
     * 
     * @return bool Retorna true si la inserción fue exitosa.
     * @throws AppException Si ocurre un error durante el proceso de validación o inserción.
     */
    public function save(array $datos): bool
    {
        $datosValidados = $this->validarCampos($datos, true);
        $datosValidados['contrasena'] = password_hash($datosValidados['contrasena'], PASSWORD_DEFAULT);
        $datosUnicos = array_intersect_key($datosValidados, array_flip($this->camposUnicos));

        return (bool) $this->db->insert(
            $this->tabla,
            $datosValidados,
            $datosUnicos
        );
    }

    /**
     * Consulta una cuenta de usuario y sus datos personales mediante el nombre de usuario.
     *
     * @param string $usuario Nombre de usuario registrado a consultar.
     * @return array<string, mixed>|null Arreglo asociativo con los datos o null si no se encuentra.
     * @throws AppException Si la infraestructura de datos falla durante la consulta.
     */
    /**
     * Consulta una cuenta de usuario y su perfil asociado mediante el nombre de usuario.
     *
     * @param string $usuario Nombre de usuario a consultar.
     * @return array<string, mixed>|null Retorna los datos de la cuenta o null si no se encuentra.
     * @throws DatabaseException Si ocurre una falla en la ejecución de la consulta SQL.
     */
    public function obtenerPorUsuario(string $usuario): ?array
    {
        $sql = 'SELECT 
                    c.id,
                    c.usuario,
                    c.contrasena,
                    c.estado,
                    c.rol_id,
                    d.nombre,
                    d.apellido,
                    d.s_nombre,
                    d.s_apellido,
                    d.id_cedula,
                    d.tlf,
                    d.direccion,
                    d.edad,
                    d.foto,
                    d.ingreso
                FROM cuentas c 
                LEFT JOIN datos d ON c.id = d.cuenta_id
                WHERE c.usuario = ? 
                LIMIT 1';

        /** @var array<string, mixed>|false|null $resultado */
        $resultado = $this->db->select($sql, [$usuario], 'row');

        return is_array($resultado) ? $resultado : null;
    }


    public function correoExis(string $correo): bool
    {
        $sql = 'SELECT id,correo,estado,usuario FROM cuentas WHERE correo=? ';
        $resul = $this->db->select($sql, [$correo], 'row');
        if (empty($resul)) {
            throw new AppException('Usuario no Encontrado', 400);
        }

        if (((int)$resul['estado']) === 0) {
            throw new AppException('Usuario Bloqueado', 400);
        }
        return true;
    }



    /**
     * Valida si un token de seguridad es legítimo, se encuentra dentro del margen 
     * de tiempo permitido y pertenece a una cuenta activa en el sistema.
     * 
     * @param string $correo Correo electrónico registrado del usuario.
     * @param int $token Código numérico de 6 dígitos ingresado en la vista.
     * @param string $tiempoActual Marca de tiempo actual generada por PHP (Formato: Y-m-d H:i:s).
     * @return array Retorna un arreglo asociativo con los datos de la cuenta y los datos personales.
     * @throws AppException Lanza una excepción si el token ha expirado, no coincide o la cuenta está inactiva.
     */
    public function validarToken(string $correo, int $token, string $tiempoActual): array
    {
        $sql = "SELECT 
                    c.id,
                    c.usuario,
                    c.contrasena,
                    c.estado,
                    c.rol_id,
                    c.correo,
                    c.fecha_codigo,
                    d.nombre,
                    d.apellido,
                    d.s_nombre,
                    d.s_apellido,
                    d.id_cedula,
                    d.tlf,
                    d.direccion,
                    d.edad,
                    d.foto,
                    d.ingreso
                FROM cuentas c 
                LEFT JOIN datos d ON c.id = d.cuenta_id
                WHERE c.correo = ? AND c.codigo = ? AND c.fecha_codigo > ? AND c.estado = 1 
                LIMIT 1";

        $result = $this->db->select(
            $sql,
            [
                $correo,
                $token,
                $tiempoActual
            ],
            'row'
        );

        if (empty($result)) {
            throw new AppException('El token está vencido o los datos son incorrectos.');
        }

        return $result;
    }


    /**
     * Metodo que valida la existencia y guarda el token del usuario
     *
     * @param string $correo correo del usuario a quien se le va a realizar la operacion
     * @param int  $token  es el token a guardar
     * @param string $fecha momento de creacion del token
     * 
     * @return bool al cumplirse las condiciones devuelve el token insertado
     * 
     * @throws AppException en caso de hacer un error durante la insercion
     * @throws Throwable si ocurre un error inesperado
     */
    public function insertarCodigo(string $correo, int $token, string $fecha): bool
    {

        try {
            $this->db->beginTransaction();


            $this->correoExis($correo);
            $saveCodigo = $this->update([
                'codigo' => $token,
                'fecha_codigo' => $fecha
            ], ['correo' => $correo]);
            if (!$saveCodigo) {
                throw new AppException('No se pudo guardar el Codigo', 500);
            }

            $this->db->commit();

            return true;
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
