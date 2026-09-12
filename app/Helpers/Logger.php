<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Libs\Session;

/**
 * Clase Logger
 *
 * Se encarga de la escritura estructurada y segura de eventos
 * y errores en archivos de registro dentro del disco.
 *
 * @package App\Helpers
 */
class Logger
{
    /**
     * Escribe un mensaje formateado en un archivo de registro específico.
     *
     * @param string $filename Nombre del archivo de log (sin extensión).
     * @param string $message Detalle principal del evento.
     * @param array<string, mixed> $context Información adicional de contexto.
     * @return void
     */
    public static function log(string $filename, string $message, array $context = []): void
    {
        // Resolución de ruta hacia la carpeta app/Logs
        $logDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Logs';

        if (!is_dir($logDir) && !mkdir($logDir, 0755, true) && !is_dir($logDir)) {
            return;
        }

        // 1. Extraer metadata del entorno y usuario
        $metaContexto = self::obtenerMetaContexto();

        // 2. Fusionar contexto recibido con el meta contexto (sin sobrescribir lo que envíe el desarrollador)
        $contextoCompleto = array_merge($metaContexto, $context);

        $date = date('Y-m-d H:i:s');
        $contextString = !empty($contextoCompleto)
            ? ' | Context: ' . json_encode($contextoCompleto, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : '';

        $formattedMessage = "[{$date}] {$message}{$contextString}" . PHP_EOL;

        // Escritura atómica
        file_put_contents(
            $logDir . DIRECTORY_SEPARATOR . "{$filename}.log",
            $formattedMessage,
            FILE_APPEND | LOCK_EX
        );
    }

    /**
     * Extrae información técnica del dispositivo y del usuario en sesión.
     *
     * @return array<string, mixed>
     */
    private static function obtenerMetaContexto(): array
    {
        $meta = [
            'ip'     => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'equipo' => self::parsearUserAgent($_SERVER['HTTP_USER_AGENT'] ?? '')
        ];

        if (session_status() === PHP_SESSION_ACTIVE || isset($_SESSION)) {
            $session = new Session();

            // Claves reales de tu sesión
            $usuarioNombre = $session->get('usuario_nombre') ?? $session->get('usuario');
            $usuarioId     = $session->get('usuario_id') ?? $session->get('id');
            $rolId         = $session->get('usuario_rol') ?? $session->get('rol_id') ?? $session->get('rol');

            if ($usuarioNombre || $usuarioId) {
                $idTexto = !empty($usuarioId) ? " (ID: {$usuarioId})" : "";
                $meta['usuario'] = trim("{$usuarioNombre}{$idTexto}");
            }

            if ($rolId !== null) {
                // Mapeo rápido de Rol o muestra de ID de Rol
                $meta['rol'] = self::obtenerNombreRol((int)$rolId);
            }
        }

        return $meta;
    }

    /**
     * Traduce el ID del rol a su etiqueta correspondiente
     */
    private static function obtenerNombreRol(int $rolId): string
    {
        $roles = [
            1 => 'Usuario',
            2 => 'Docente',
            3 => 'Facilitador',
            4 => 'Presidente',
            5 => 'Administrador'
        ];

        return $roles[$rolId] ?? "Rol #{$rolId}";
    }
    /**
     * Simplifica el User-Agent a una cadena corta de SO y Navegador.
     */
    private static function parsearUserAgent(string $ua): string
    {
        if (empty($ua)) return 'Desconocido';

        $so = 'Desconocido';
        $navegador = 'Navegador';

        // Detectar Sistema Operativo
        if (preg_match('/windows/i', $ua)) $so = 'Windows';
        elseif (preg_match('/macintosh|mac os x/i', $ua)) $so = 'MacOS';
        elseif (preg_match('/linux/i', $ua)) $so = 'Linux';
        elseif (preg_match('/android/i', $ua)) $so = 'Android';
        elseif (preg_match('/iphone|ipad/i', $ua)) $so = 'iOS';

        // Detectar Navegador
        if (preg_match('/edg/i', $ua)) $navegador = 'Edge';
        elseif (preg_match('/chrome/i', $ua)) $navegador = 'Chrome';
        elseif (preg_match('/firefox/i', $ua)) $navegador = 'Firefox';
        elseif (preg_match('/safari/i', $ua)) $navegador = 'Safari';

        return "{$so} ({$navegador})";
    }
}
