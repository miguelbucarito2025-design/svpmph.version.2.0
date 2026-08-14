<?php

declare(strict_types=1);

namespace App\Libs;

/**
 * Clase Session
 *
 * Administra el ciclo de vida de la sesión HTTP del usuario de forma segura.
 * Ofrece persistencia de datos, mensajería de un solo uso (Flash), prevención 
 * de fijación/secuestro de sesión mediante huella digital, control automático de inactividad
 * y generación/validación de tokens CSRF.
 *
 * @package App\Libs
 */
class Session
{
    /**
     * Tiempo máximo de inactividad permitido antes de expirar la sesión (en segundos).
     *
     * @var int
     */
    private const TIEMPO_INACTIVIDAD = 1800; // 30 minutos

    /**
     * Inicializa la sesión PHP aplicando directivas estrictas de seguridad para cookies,
     * evaluando la huella digital y el tiempo de expiración de forma atómica.
     *
     * @return void
     */
    public function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Extraer únicamente el nombre del host sin puerto para evitar el rechazo de la cookie
            $host = $_SERVER['HTTP_HOST'] ?? '';
            if (str_contains($host, ':')) {
                $host = explode(':', $host)[0];
            }

            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'domain'   => $host,
                'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            session_start();
        }

        // Si la huella digital no coincide o el tiempo expiró, se reinicia la sesión limpiamente
        if (!$this->validarHuellaDigital() || !$this->comprobarInactividad()) {
            $this->reiniciarSesionLimpia();
        }

        $this->asegurarTokenCSRF();
    }

    /**
     * Regenera el identificador de la sesión activa para prevenir ataques de fijación de sesión.
     *
     * @param bool $borrarSesionAnterior Si es true, destruye el archivo de sesión antiguo.
     * @return void
     */
    public function regenerarId(bool $borrarSesionAnterior = true): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id($borrarSesionAnterior);
        }
    }

    /**
     * Compara en tiempo constante un token recibido contra el token CSRF activo de la sesión.
     *
     * @param string|null $tokenRecibido Token proveniente del cliente.
     * @return bool True si coinciden exactamente, False en caso contrario.
     */
    public function esTokenValido(?string $tokenRecibido): bool
    {
        $tokenSesion = $this->get('csrf_token');

        if (!is_string($tokenRecibido) || !is_string($tokenSesion)) {
            return false;
        }

        return hash_equals($tokenSesion, $tokenRecibido);
    }

    /**
     * Almacena una variable dentro del contenedor de sesión.
     *
     * @param string $clave Nombre o identificador del elemento.
     * @param mixed $valor Valor que se almacenará.
     * @return void
     */
    public function set(string $clave, mixed $valor): void
    {
        $_SESSION[$clave] = $valor;
    }

    /**
     * Obtiene el valor de una variable almacenada en la sesión.
     *
     * @param string $clave Identificador del elemento.
     * @param mixed $default Valor devuelto en caso de que la clave no exista.
     * @return mixed
     */
    public function get(string $clave, mixed $default = null): mixed
    {
        return $_SESSION[$clave] ?? $default;
    }

    /**
     * Comprueba si una clave existe dentro de la sesión.
     *
     * @param string $clave Identificador del elemento.
     * @return bool
     */
    public function has(string $clave): bool
    {
        return isset($_SESSION[$clave]);
    }

    /**
     * Elimina una variable específica del contenedor de sesión.
     *
     * @param string $clave Identificador del elemento a eliminar.
     * @return void
     */
    public function remove(string $clave): void
    {
        unset($_SESSION[$clave]);
    }

    /**
     * Almacena un mensaje de un solo uso (Flash data).
     *
     * @param string $clave Identificador del mensaje.
     * @param mixed $valor Contenido del mensaje.
     * @return void
     */
    public function setFlash(string $clave, mixed $valor): void
    {
        $_SESSION['_flash'][$clave] = $valor;
    }

    /**
     * Obtiene y elimina de forma inmediata un mensaje temporal de un solo uso.
     *
     * @param string $clave Identificador del mensaje.
     * @param mixed $default Valor devuelto si el mensaje no existe.
     * @return mixed
     */
    public function getFlash(string $clave, mixed $default = null): mixed
    {
        $valor = $_SESSION['_flash'][$clave] ?? $default;
        unset($_SESSION['_flash'][$clave]);
        return $valor;
    }

    /**
     * Garantiza la existencia de un token CSRF activo en la sesión.
     *
     * @return string Token CSRF de 64 caracteres hexadecimales.
     */
    public function asegurarTokenCSRF(): string
    {
        if (!$this->has('csrf_token')) {
            $this->set('csrf_token', bin2hex(random_bytes(32)));
        }
        return (string) $this->get('csrf_token');
    }

    /**
     * Evalúa si el tiempo transcurrido desde la última interacción supera
     * el límite de inactividad configurado.
     *
     * @return bool True si está dentro del tiempo permitido, False si caducó.
     */
    public function comprobarInactividad(): bool
    {
        $ahora = time();
        $ultimoAcceso = $this->get('ultimo_acceso', $ahora);

        if (($ahora - $ultimoAcceso) > self::TIEMPO_INACTIVIDAD) {
            return false;
        }

        $this->set('ultimo_acceso', $ahora);
        return true;
    }

    /**
     * Valida el hash basado en el User-Agent del navegador.
     *
     * @return bool True si coincide o es nueva, False si la huella es inválida.
     */
    private function validarHuellaDigital(): bool
    {
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'desconocido';
        $huellaActual = hash('sha256', $agent);

        if ($this->has('_user_fingerprint')) {
            return hash_equals((string) $this->get('_user_fingerprint'), $huellaActual);
        }

        $this->set('_user_fingerprint', $huellaActual);
        return true;
    }

    /**
     * Destruye la sesión actual e inicia una completamente nueva en memoria.
     *
     * @return void
     */
    private function reiniciarSesionLimpia(): void
    {
        $this->destroy();
        session_start();
    }

    /**
     * Destruye de forma segura y completa la sesión activa en el servidor y cliente.
     *
     * @return void
     */
    public function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];

            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }

            session_destroy();
        }
    }
}
