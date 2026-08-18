<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;
use App\Libs\Exceptions\AppException;

/**
 * Clase CuentasModel
 *
 * Modelo encargado de la gestión de persistencia, autenticación,
 * recuperación pública de claves y gestión del perfil de usuario.
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
        'id'               => 'esEntero',
        'usuario'          => 'esNombreUsuario',
        'contrasena'       => 'esPassword',
        'estado'           => 'esEntero',
        'codigo'           => 'esTexto',
        'fecha_codigo'     => 'esFechaHora',
        'rol_id'           => 'esEntero',
        'correo'           => 'esCorreo',
        'correo_pendiente' => 'esCorreo',
        'token_intentos'   => 'esEntero'
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

    /* =========================================================================
     * MÉTODOS PARA AUTENTICACIÓN Y RECUPERACIÓN PÚBLICA (AuthController)
     * ========================================================================= */

    /**
     * Consulta una cuenta por su nombre de usuario (Usado en el Login).
     *
     * @param string $usuario Nombre de usuario registrado.
     * @return array<string, mixed>|null Fila con los datos de la cuenta o null.
     */
    public function obtenerPorUsuario(string $usuario): ?array
    {
        $sql = 'SELECT 
                    c.id,
                    c.usuario,
                    c.contrasena,
                    c.estado,
                    c.correo,
                    c.rol_id,
                    r.rol
                FROM cuentas c 
                LEFT JOIN rol r ON c.rol_id = r.id
                WHERE c.usuario = ? AND c.estado = 1
                LIMIT 1';

        /** @var array<string, mixed>|false|null $resultado */
        $resultado = $this->db->select($sql, [$usuario], 'row');

        return is_array($resultado) ? $resultado : null;
    }

    /**
     * Valida la existencia y estado activo de un correo electrónico.
     *
     * @param string $correo Correo electrónico a consultar.
     * @return bool True si el correo está disponible y activo.
     * @throws AppException Si el usuario no existe o está inactivo.
     */
    public function correoExis(string $correo): bool
    {
        $sql = 'SELECT id, correo, estado, usuario FROM cuentas WHERE correo = ? LIMIT 1';
        $resul = $this->db->select($sql, [$correo], 'row');

        if (empty($resul)) {
            throw new AppException('Usuario no encontrado', 400);
        }

        if (((int) $resul['estado']) === 0) {
            throw new AppException('Usuario bloqueado o inactivo', 400);
        }

        return true;
    }

    /**
     * Guarda el código de recuperación generado para un correo.
     *
     * @param string $correo Correo del usuario.
     * @param string $token Código de 6 dígitos.
     * @param string $fecha Expiración en formato Y-m-d H:i:s.
     * @return bool
     */
    public function insertarCodigo(string $correo, string $token, string $fecha): bool
    {
        $this->correoExis($correo);

        return $this->update([
            'codigo'       => $token,
            'fecha_codigo' => $fecha
        ], ['correo' => $correo]);
    }

    /**
     * Valida un token de recuperación pública.
     *
     * @param string $correo Correo registrado.
     * @param string $token Código de 6 dígitos recibido.
     * @param string $tiempoActual Fecha/hora del servidor.
     * @return array Datos de la cuenta verificada.
     * @throws AppException Si el token venció o los datos son inválidos.
     */
    public function validarToken(string $correo, string $token, string $tiempoActual): array
    {
        $sql = "SELECT 
                    c.id,
                    c.usuario,
                    c.contrasena,
                    c.estado,
                    c.correo,
                    c.rol_id,
                    r.rol
                FROM cuentas c 
                LEFT JOIN rol r ON c.rol_id = r.id
                WHERE c.correo = ? AND c.codigo = ? AND c.fecha_codigo > ? AND c.estado = 1 
                LIMIT 1";

        $result = $this->db->select($sql, [$correo, $token, $tiempoActual], 'row');

        if (empty($result)) {
            throw new AppException('El código está vencido o los datos son incorrectos.', 400);
        }

        return $result;
    }

    /* =========================================================================
     * MÉTODOS PARA GESTIÓN DE PERFIL AUTENTICADO (CuentaController)
     * ========================================================================= */

    /**
     * Consulta una cuenta por su ID primario de sesión.
     *
     * @param int $id ID de la cuenta en sesión.
     * @return array<string, mixed>|null
     */
    public function obtenerPorId(int $id): ?array
    {
        $sql = 'SELECT 
                    c.id,
                    c.usuario,
                    c.contrasena,
                    c.estado,
                    c.correo,
                    c.correo_pendiente,
                    c.codigo,
                    c.fecha_codigo,
                    c.token_intentos,
                    c.rol_id,
                    r.rol
                FROM cuentas c 
                LEFT JOIN rol r ON c.rol_id = r.id
                WHERE c.id = ? AND c.estado = 1
                LIMIT 1';

        /** @var array<string, mixed>|false|null $resultado */
        $resultado = $this->db->select($sql, [$id], 'row');

        return is_array($resultado) ? $resultado : null;
    }

    /**
     * Comprueba si un nombre de usuario ya existe en otra cuenta.
     *
     * @param string $usuario Nombre a verificar.
     * @param int $idActual ID del usuario logueado.
     * @return bool
     */
    public function existeUsuarioEnOtroId(string $usuario, int $idActual): bool
    {
        $sql = 'SELECT id FROM cuentas WHERE usuario = ? AND id != ? LIMIT 1';
        $resultado = $this->db->select($sql, [$usuario, $idActual], 'row');

        return !empty($resultado);
    }

    /**
     * Actualiza la contraseña encriptada de una cuenta por ID.
     *
     * @param int $id ID del usuario.
     * @param string $hashPassword Contraseña procesada con password_hash.
     * @return bool
     */
    public function actualizarContrasena(int $id, string $hashPassword): bool
    {
        return $this->update([
            'contrasena' => $hashPassword
        ], ['id' => $id]);
    }

    /**
     * Actualiza el nombre de usuario de una cuenta por ID.
     *
     * @param int $id ID del usuario.
     * @param string $nuevoUsuario Nombre de usuario validado.
     * @return bool
     */
    public function actualizarNombreUsuario(int $id, string $nuevoUsuario): bool
    {
        return $this->update([
            'usuario' => $nuevoUsuario
        ], ['id' => $id]);
    }


    /* =========================================================================
     * MÉTODOS DE CAMBIO DE CORREO Y VERIFICACIÓN DE SEGURIDAD (Perfil)
     * ========================================================================= */

    /**
     * Guarda la solicitud de cambio de correo electrónico generando un token de verificación.
     *
     * @param int $id ID de la cuenta en sesión.
     * @param string $nuevoCorreo Correo al que se desea cambiar.
     * @param string $token Código de 6 dígitos.
     * @param string $fecha Expiración del código (Y-m-d H:i:s).
     * @return bool
     * @throws AppException Si el nuevo correo ya pertenece a otra cuenta.
     */
    public function guardarTokenVerificacion(int $id, string $nuevoCorreo, string $token, string $fecha): bool
    {
        // Validar que el nuevo correo no lo tenga otra cuenta
        $sql = 'SELECT id FROM cuentas WHERE correo = ? AND id != ? LIMIT 1';
        $existe = $this->db->select($sql, [$nuevoCorreo, $id], 'row');

        if (!empty($existe)) {
            throw new AppException('El correo electrónico ya se encuentra registrado por otro usuario.', 400);
        }

        return $this->update([
            'correo_pendiente' => $nuevoCorreo,
            'codigo'           => $token,
            'fecha_codigo'     => $fecha,
            'token_intentos'   => 0
        ], ['id' => $id]);
    }

    /**
     * Valida el token de verificación por ID evaluando expiración e intentos fallidos (Máximo 3).
     *
     * @param int $id ID de la cuenta.
     * @param string $token Código de 6 dígitos ingresado.
     * @param string $tiempoActual Fecha y hora del servidor.
     * @return bool True si el código es válido.
     * @throws AppException Si venció, superó intentos o el código es incorrecto.
     */
    public function validarTokenPorId(int $id, string $token, string $tiempoActual): bool
    {
        $cuenta = $this->obtenerPorId($id);

        if (empty($cuenta) || empty($cuenta['codigo'])) {
            throw new AppException('No hay ningún código activo para verificar.', 400);
        }

        $intentosActuales = (int) ($cuenta['token_intentos'] ?? 0);

        // 1. Evaluar si superó los 3 intentos
        if ($intentosActuales >= 3) {
            $this->limpiarTokenPorId($id);
            throw new AppException('Ha superado el límite de 3 intentos permitidos. Solicite un nuevo código.', 400);
        }

        // 2. Evaluar fecha de expiración
        if ($cuenta['fecha_codigo'] < $tiempoActual) {
            $this->limpiarTokenPorId($id);
            throw new AppException('El código de verificación ha expirado. Solicite uno nuevo.', 400);
        }

        // 3. Comparar el código enviado contra el guardado
        if ($cuenta['codigo'] !== $token) {
            $nuevosIntentos = $intentosActuales + 1;
            $this->update(['token_intentos' => $nuevosIntentos], ['id' => $id]);

            $restantes = 3 - $nuevosIntentos;
            if ($restantes <= 0) {
                $this->limpiarTokenPorId($id);
                throw new AppException('Código incorrecto. Ha agotado sus intentos.', 400);
            }

            throw new AppException("Código incorrecto. Le quedan {$restantes} intento(s).", 400);
        }

        return true;
    }

    /**
     * Promueve el 'correo_pendiente' a 'correo' principal y limpia las columnas temporales.
     *
     * @param int $id ID de la cuenta.
     * @param string $nuevoCorreo Correo electrónico confirmado.
     * @return bool
     */
    public function confirmarNuevoCorreo(int $id, string $nuevoCorreo): bool
    {
        return $this->update([
            'correo'           => $nuevoCorreo,
            'correo_pendiente' => null,
            'codigo'           => null,
            'fecha_codigo'     => null,
            'token_intentos'   => 0
        ], ['id' => $id]);
    }

    /**
     * Resetea el token, la expiración, los intentos y la solicitud pendiente por ID.
     *
     * @param int $id ID de la cuenta.
     * @return bool
     */
    public function limpiarTokenPorId(int $id): bool
    {
        return $this->update([
            'correo_pendiente' => null,
            'codigo'           => null,
            'fecha_codigo'     => null,
            'token_intentos'   => 0
        ], ['id' => $id]);
    }
}
