<?php

declare(strict_types=1);

namespace App\Libs;

use App\Libs\Response;

/* Departamento de Seguridad - MVC Dinámico */

class Seguridad
{
    private static string $key = '';
    private static string $method = "aes-256-cbc";
    private static string $iv = '';

    public static function init(): void
    {
        if (empty(self::$key)) {
            self::$key = $_ENV['DB_KEY'] ?? getenv('key') ?: 'clave_secreta_por_defecto_32bytes';
            self::$iv  = $_ENV['DB_IV']  ?? getenv('iv')  ?: '1234567890123456'; // 16 bytes exactos
        }
    }

    public static function encriptarID(int|string $id): string
    {
        self::init();
        $encriptado = openssl_encrypt((string)$id, self::$method, self::$key, OPENSSL_RAW_DATA, self::$iv);

        if ($encriptado === false) {
            return '';
        }

        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($encriptado));
    }

    public static function desencriptarID(string $idEncriptado): ?string
    {
        self::init();

        $data = str_replace(['-', '_'], ['+', '/'], $idEncriptado);
        $modulo = strlen($data) % 4;
        if ($modulo) {
            $data .= str_repeat('=', 4 - $modulo);
        }

        $bytes = base64_decode($data, true);

        if ($bytes === false) {
            return null;
        }

        $desencriptado = openssl_decrypt($bytes, self::$method, self::$key, OPENSSL_RAW_DATA, self::$iv);

        return ($desencriptado !== false) ? $desencriptado : null;
    }

    /**
     * Empaqueta un arreglo asociativo y le añade tiempo de expiración.
     * 
     * @param array $params Datos a ocultar (ej: ['cedula' => 821928192, 'seccion' => 1])
     * @param int $minutosExpiracion Minutos de validez. Usar 0 para enlaces permanentes.
     */
    public static function encriptarParams(array $params, int $minutosExpiracion = 60): string
    {
        if ($minutosExpiracion > 0) {
            $params['_exp'] = time() + ($minutosExpiracion * 60);
        }

        $json = json_encode($params);
        if ($json === false) {
            return '';
        }

        return self::encriptarID($json);
    }

    /**
     * Desencripta el token y valida integridad y expiración.
     * Devuelve el arreglo de datos original, un arreglo vacío si no vino token, 
     * o NULL si el token fue manipulado o expiró.
     */
    public static function desencriptarParams(string $token): ?array
    {
        if (empty($token)) {
            return [];
        }

        $json = self::desencriptarID($token);
        if ($json === null) {
            return null; // Token corrupto o alterado
        }

        $params = json_decode($json, true);
        if (!is_array($params)) {
            return null;
        }

        // Validación de expiración
        if (isset($params['_exp'])) {
            if (time() > $params['_exp']) {
                return null; // Enlace caducado
            }
            unset($params['_exp']); // Limpiar metadata antes de entregar los datos
        }

        return $params;
    }

    public static function detectorDeBots(): void
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $botsConocidos = ['Python', 'curl', 'Wget', 'libwww-perl'];
        foreach ($botsConocidos as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                self::registrarYAbortar("Acceso denegado: Bot no permitido.", 403);
            }
        }

        $ahora = microtime(true);
        if (isset($_SESSION['ultima_peticion'])) {
            $tiempoTranscurrido = $ahora - $_SESSION['ultima_peticion'];
            if ($tiempoTranscurrido < 0.2) {
                self::registrarYAbortar("Demasiadas peticiones consecutivas.", 429);
            }
        }
        $_SESSION['ultima_peticion'] = $ahora;
    }

    private static function registrarYAbortar(string $detalle, int $codigoHttp): void
    {
        $fecha = date("Y-m-d H:i:s");
        $logDir = 'app/Logs/';

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $mensaje = "[" . $fecha . "] [{$codigoHttp}] $detalle - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'desconocida') . "\n";
        file_put_contents($logDir . '/erroresApp.log', $mensaje, FILE_APPEND);

        Response::json(null, $codigoHttp, $detalle);
    }
}
