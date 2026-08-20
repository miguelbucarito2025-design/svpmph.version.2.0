<?php

namespace App\Middlewares;

use App\Libs\Response;

class SeguridadMiddleware
{
    private const LOG_DIR = 'app/Logs/';
    private const TIEMPO_BLOQUEO_SEGUNDOS = 1; // Ventana de tiempo a evaluar
    private const MAX_PETICIONES = 5;          // Máximo de peticiones permitidas en esa ventana

    // Lista negra ampliada de herramientas automáticas y escáneres
    private const BOTS_PROHIBIDOS = [
        'python',
        'curl',
        'wget',
        'libwww-perl',
        'nikto',
        'sqlmap',
        'nmap',
        'go-http-client',
        'phpcrawl',
        'headless'
    ];

    /**
     * Evalúa la petición entrante. Si detecta comportamiento anómalo o herramientas
     * no autorizadas, registra el evento y aborta la ejecución de inmediato.
     *
     * @return void
     */
    public static function inspeccionarPeticion(): void
    {
        $ip = self::obtenerIpReal();
        $userAgent = trim($_SERVER['HTTP_USER_AGENT'] ?? '');

        // 1. REGLA 1: Exigir User-Agent y bloquear herramientas de escaneo conocidas
        if (empty($userAgent)) {
            self::registrarYAbortar("Acceso denegado: Petición sin User-Agent.", 403, $ip);
        }

        foreach (self::BOTS_PROHIBIDOS as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                self::registrarYAbortar("Acceso denegado: Cliente no permitido ($bot).", 403, $ip);
            }
        }

        // 2. REGLA 2: Rate Limiting por IP (No por sesión)
        if (self::excedeLimitePeticiones($ip)) {
            self::registrarYAbortar("Demasiadas peticiones consecutivas (Rate Limit Exceeded).", 429, $ip);
        }
    }

    /**
     * Determina si una IP específica está realizando ráfagas de peticiones sospechosas
     * guardando la marca de tiempo en el sistema de archivos temporal del servidor.
     */
    private static function excedeLimitePeticiones(string $ip): bool
    {
        $ahora = microtime(true);
        // Creamos un archivo temporal único por IP dentro del directorio del sistema
        $archivoIp = sys_get_temp_dir() . '/rate_' . md5($ip) . '.json';

        $datos = ['inicio' => $ahora, 'peticiones' => 0];

        if (file_exists($archivoIp)) {
            $contenido = json_decode(file_get_contents($archivoIp), true);
            if (is_array($contenido)) {
                $datos = $contenido;
            }
        }

        // Si ha transcurrido más tiempo del límite, reiniciamos el contador
        if (($ahora - $datos['inicio']) > self::TIEMPO_BLOQUEO_SEGUNDOS) {
            $datos['inicio'] = $ahora;
            $datos['peticiones'] = 1;
        } else {
            $datos['peticiones']++;
        }

        // Guardamos el nuevo estado de la IP
        file_put_contents($archivoIp, json_encode($datos), LOCK_EX);

        return $datos['peticiones'] > self::MAX_PETICIONES;
    }

    /**
     * Extrae la IP real del cliente evaluando cabeceras de proxy o Cloudflare.
     */
    private static function obtenerIpReal(): string
    {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP']; // Cloudflare
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]); // La primera IP es la del cliente original
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Escribe la anomalía en el archivo de logs y detiene la ejecución enviando un JSON.
     */
    private static function registrarYAbortar(string $detalle, int $codigoHttp, string $ip): void
    {
        if (!is_dir(self::LOG_DIR)) {
            mkdir(self::LOG_DIR, 0755, true);
        }

        $fecha = date("Y-m-d H:i:s");
        $route = $_SERVER['REQUEST_URI'] ?? 'desconocida';
        $mensaje = "[$fecha] [$codigoHttp] $detalle - IP: $ip - Ruta: $route\n";

        file_put_contents(self::LOG_DIR . 'seguridad.log', $mensaje, FILE_APPEND);

        Response::json(null, $codigoHttp, $detalle);
        exit; // Detención absoluta del proceso
    }
}
