<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Libs\Response;

/**
 * Middleware de Inspección de Seguridad, Control de Tasa de Peticiones (Rate Limiting)
 * y Freno de Emergencia por Sobrecarga Global del Servidor.
 */
class SeguridadMiddleware
{
    private const LOG_DIR = 'app/Logs/';
    private const TIEMPO_BLOQUEO_SEGUNDOS = 5; // Ventana de evaluación en segundos por IP
    private const MAX_PETICIONES = 30;         // Máximo de peticiones HTTP permitidas por IP en la ventana

    // --- PROTECCIÓN DE SOBRECARGA GLOBAL ---
    private const MAX_PETICIONES_GLOBALES_SEGUNDO = 150; // Límite máximo que soporta el servidor por segundo

    /**
     * Herramientas conocidas de escaneo de vulnerabilidades y bots maliciosos.
     */
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
     * Evalúa la petición entrante analizando la identidad del cliente, la frecuencia individual
     * y la carga general del servidor.
     *
     * @return void
     */
    public static function inspeccionarPeticion(): void
    {
        // 1. CAPA DE EMERGENCIA: Freno por Sobrecarga Global del Servidor
        if (self::excedeCargaGlobalServidor()) {
            self::registrarYAbortar("Servidor saturado por alto volumen de tráfico. Intente de nuevo.", 503, self::obtenerIpReal());
        }

        $ip = self::obtenerIpReal();
        $userAgent = trim($_SERVER['HTTP_USER_AGENT'] ?? '');

        // 2. REGLA 1: Exigir User-Agent y bloquear herramientas de escaneo conocidas
        if (empty($userAgent)) {
            self::registrarYAbortar("Acceso denegado: Petición sin User-Agent.", 403, $ip);
        }

        foreach (self::BOTS_PROHIBIDOS as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                self::registrarYAbortar("Acceso denegado: Cliente no permitido ($bot).", 403, $ip);
            }
        }

        // 3. REGLA 2: Rate Limiting individual por IP usando bloqueo atómico
        if (self::excedeLimitePeticiones($ip)) {
            self::registrarYAbortar("Demasiadas peticiones consecutivas (Rate Limit Exceeded).", 429, $ip);
        }
    }

    /**
     * Revisa la suma global de peticiones que recibe el servidor por segundo.
     * Si un ataque distribuido de miles de IPs intenta colapsar PHP/Web, responde HTTP 503.
     * 
     * @return bool True si el servidor superó la capacidad máxima por segundo.
     */
    private static function excedeCargaGlobalServidor(): bool
    {
        $segundoActual = time();
        $archivoGlobal = sys_get_temp_dir() . '/rate_global_server.json';

        $fp = @fopen($archivoGlobal, 'c+');
        if (!$fp) {
            return false;
        }

        $sobrecargado = false;

        if (flock($fp, LOCK_EX)) {
            $contenido = stream_get_contents($fp);
            $datos = ['segundo' => $segundoActual, 'total' => 0];

            if (!empty($contenido)) {
                $decodificado = json_decode($contenido, true);
                if (is_array($decodificado)) {
                    $datos = $decodificado;
                }
            }

            // Si cambiamos de segundo, reiniciamos el contador global
            if ($datos['segundo'] !== $segundoActual) {
                $datos['segundo'] = $segundoActual;
                $datos['total'] = 1;
            } else {
                $datos['total']++;
            }

            if ($datos['total'] > self::MAX_PETICIONES_GLOBALES_SEGUNDO) {
                $sobrecargado = true;
            }

            // Guardar estado global
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($datos));
            fflush($fp);
            flock($fp, LOCK_UN);
        }

        fclose($fp);
        return $sobrecargado;
    }

    /**
     * Determina si una IP supera la tasa máxima de peticiones permitidas.
     * Utiliza apertura y bloqueo exclusivo (flock) para evitar condiciones de carrera.
     * 
     * @param string $ip Dirección IP del cliente.
     * @return bool True si la IP ha superado el límite.
     */
    private static function excedeLimitePeticiones(string $ip): bool
    {
        $ahora = microtime(true);
        $archivoIp = sys_get_temp_dir() . '/rate_' . md5($ip) . '.json';

        $fp = fopen($archivoIp, 'c+');
        if (!$fp) {
            return false; // Ante fallo del sistema de archivos, se permite la petición por resguardo
        }

        $excedido = false;

        if (flock($fp, LOCK_EX)) {
            $contenido = stream_get_contents($fp);
            $datos = ['inicio' => $ahora, 'peticiones' => 0];

            if (!empty($contenido)) {
                $decodificado = json_decode($contenido, true);
                if (is_array($decodificado)) {
                    $datos = $decodificado;
                }
            }

            // Si expiró la ventana de tiempo, reiniciamos el contador
            if (($ahora - $datos['inicio']) > self::TIEMPO_BLOQUEO_SEGUNDOS) {
                $datos['inicio'] = $ahora;
                $datos['peticiones'] = 1;
            } else {
                $datos['peticiones']++;
            }

            if ($datos['peticiones'] > self::MAX_PETICIONES) {
                $excedido = true;
            }

            // Guardar cambios
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($datos));
            fflush($fp);
            flock($fp, LOCK_UN);
        }

        fclose($fp);
        return $excedido;
    }

    /**
     * Extrae la IP real del cliente evaluando proxies y Cloudflare.
     * 
     * @return string
     */
    private static function obtenerIpReal(): string
    {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Escribe la anomalía en el archivo de logs y detiene la ejecución enviando un JSON.
     * 
     * @param string $detalle Razón del bloqueo.
     * @param int $codigoHttp Código HTTP de respuesta.
     * @param string $ip IP del cliente.
     * @return void
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
        exit;
    }
}
