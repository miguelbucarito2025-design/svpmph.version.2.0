<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Controllers\ErrorController;
use App\Libs\Seguridad;

/**
 * Middleware de Inspección de Seguridad Avanzada, Control de Tasa (Rate Limiting),
 * Detección de Fuzzing/WordPress y Freno de Emergencia por Sobrecarga Global.
 */
class SeguridadMiddleware
{
    private const LOG_DIR = 'app/Logs/';
    private const TIEMPO_BLOQUEO_SEGUNDOS = 5; // Ventana de evaluación en segundos por IP
    private const MAX_PETICIONES = 30;         // Máximo de peticiones HTTP permitidas por IP en la ventana

    // --- PROTECCIÓN DE SOBRECARGA GLOBAL ---
    private const MAX_PETICIONES_GLOBALES_SEGUNDO = 150;

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
     * Patrones de URL maliciosos o escaneos típicos de otros CMS (como WordPress) 
     * que no tienen nada que hacer en tu sistema MVC puro.
     */
    private const PATRONES_ESCANEO_PROHIBIDOS = [
        'wp-admin',
        'wp-login',
        'wp-content',
        'wp-includes',
        'xmlrpc.php',
        '.env',
        'eval-config',
        'phpmyadmin',
        'admin.php',
        'setup.php',
        '.git',
        '.yml',
        '.php'

    ];

    /**
     * Evalúa la petición entrante analizando baneos previos, rutas prohibidas, bots y frecuencia.
     *
     * @return void
     */
    public static function inspeccionarPeticion(): void
    {
        $ip = self::obtenerIpReal();

        // 0. VERIFICAR BANEO PERMANENTE: Si la IP ya está baneada, se rechaza de inmediato.
        if (Seguridad::ipBaneadaPorFuzzing()) {
            self::registrarYAbortar("Acceso denegado: Su dirección IP ha sido bloqueada permanentemente por actividad sospechosa.", 403, $ip);
        }

        // 1. CAPA DE EMERGENCIA: Freno por Sobrecarga Global del Servidor
        if (self::excedeCargaGlobalServidor()) {
            self::registrarYAbortar("Servidor saturado por alto volumen de tráfico. Intente de nuevo.", 503, $ip);
        }

        $userAgent = trim($_SERVER['HTTP_USER_AGENT'] ?? '');
        $rutaSolicitada = strtolower($_SERVER['REQUEST_URI'] ?? '');

        // 2. REGLA 1: Exigir User-Agent y bloquear herramientas de escaneo conocidas
        if (empty($userAgent)) {
            self::registrarFaltaGraveYBanear("Petición sin User-Agent (posible bot o script malicioso).", $ip);
        }

        foreach (self::BOTS_PROHIBIDOS as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                self::registrarFaltaGraveYBanear("Uso de herramienta prohibida detectado ($bot).", $ip);
            }
        }

        // 3. REGLA 2: Detección de Fuzzing y escaneo de carpetas ajenas (ej. buscando WordPress)
        foreach (self::PATRONES_ESCANEO_PROHIBIDOS as $patron) {
            if (strpos($rutaSolicitada, $patron) !== false) {
                self::registrarFaltaGraveYBanear("Escaneo de rutas no autorizadas detectado (intento de buscar $patron).", $ip);
            }
        }

        // 4. REGLA 3: Rate Limiting individual por IP usando bloqueo atómico
        if (self::excedeLimitePeticiones($ip)) {
            self::registrarYAbortar("Demasiadas peticiones consecutivas (Rate Limit Exceeded).", 429, $ip);
        }
    }

    /**
     * Registra una falta GRAVE (como buscar WordPress o usar bots de ataque) 
     * y aplica el castigo acumulativo hasta banear la IP permanentemente.
     */
    private static function registrarFaltaGraveYBanear(string $motivo, string $ip): void
    {
        // Forzamos el registro de la falta utilizando el sistema de baneo permanente
        // (Nota: puedes invocar el método interno o dejar que Seguridad gestione el contador).
        for ($i = 0; $i < 3; $i++) {
            // Incrementamos las faltas de golpe o de forma acumulativa para acelerar el baneo ante un ataque evidente
            $reflectionMethod = new \ReflectionMethod(Seguridad::class, 'registrarFaltaManipulacion');
            $reflectionMethod->setAccessible(true);
            $reflectionMethod->invoke(null);
        }

        self::registrarYAbortar("Acceso denegado: $motivo", 403, $ip);
    }

    /**
     * Revisa la suma global de peticiones que recibe el servidor por segundo.
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

            if ($datos['segundo'] !== $segundoActual) {
                $datos['segundo'] = $segundoActual;
                $datos['total'] = 1;
            } else {
                $datos['total']++;
            }

            if ($datos['total'] > self::MAX_PETICIONES_GLOBALES_SEGUNDO) {
                $sobrecargado = true;
            }

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
     */
    private static function excedeLimitePeticiones(string $ip): bool
    {
        $ahora = microtime(true);
        $archivoIp = sys_get_temp_dir() . '/rate_' . md5($ip) . '.json';

        $fp = fopen($archivoIp, 'c+');
        if (!$fp) {
            return false;
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

            if (($ahora - $datos['inicio']) > self::TIEMPO_BLOQUEO_SEGUNDOS) {
                $datos['inicio'] = $ahora;
                $datos['peticiones'] = 1;
            } else {
                $datos['peticiones']++;
            }

            if ($datos['peticiones'] > self::MAX_PETICIONES) {
                $excedido = true;
            }

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
     */
    private static function registrarYAbortar(string $detalle, int $codigoHttp, string $ip): void
    {
        if (!is_dir(self::LOG_DIR)) {
            mkdir(self::LOG_DIR, 0755, true);
        }

        $fecha = date("Y-m-d H:i:s");
        $route = $_SERVER['REQUEST_URI']    ?? 'desconocida';
        $mensaje = "[$fecha] [$codigoHttp] $detalle - IP: $ip - Ruta: $route\n";

        file_put_contents(self::LOG_DIR . 'seguridad.log', $mensaje, FILE_APPEND);

        $error = new ErrorController;
        $error->index($codigoHttp, $detalle);
        exit;
    }
}
