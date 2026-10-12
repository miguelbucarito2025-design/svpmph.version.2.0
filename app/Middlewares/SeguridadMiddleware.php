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
    private const MAX_PETICIONES = 30;         // Máximo de peticiones HTTP permitidas por IP

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
     * Patrones de URL maliciosos, escaneos de frameworks ajenos, source maps 
     * y herramientas de depuración prohibidas en tu MVC puro.
     */
    private const PATRONES_ESCANEO_PROHIBIDOS = [
        // Paneles y CMS ajenos
        'wp-admin',
        'wp-login',
        'wp-content',
        'wp-includes',
        'xmlrpc.php',
        'phpmyadmin',
        'admin.php',
        'setup.php',

        // Archivos de entorno, credenciales y secretos
        '.env',
        'secrets.env',
        'auth.json',
        'credentials',
        'config.json',
        'settings.json',
        'env.json',
        'appsettings.json',

        // Credenciales de Cloud, Firebase y AWS
        'firebase',
        'gcp-',
        'google-',
        'service-account',
        'serviceaccount',
        'sa.json',
        'push_config.json',
        'aws',
        'credentials.json',

        // Contenedores, despliegues y orquestadores
        'dockerfile',
        'docker-compose',
        'compose.yaml',
        'compose.yml',
        'terraform.tfstate',
        'serverless.yml',
        'wrangler.toml',
        'vercel.json',
        'netlify.toml',
        'railway',
        'fly.toml',
        'supabase/config.toml',

        // Llaves SSH, certificados y Source Maps (Vite/Webpack)
        'id_rsa',
        'id_dsa',
        'id_ecdsa',
        'id_ed25519',
        'server.key',
        'key.pem',
        'host.key',
        'localhost.key',
        'privatekey',
        '.mjs.map',
        '.js.map',
        '__vite_rsc_',

        // Endpoints genéricos de APIs que no usas (GraphQL, Actuator, Debug)
        'graphql',
        'actuator',
        'debug/vars',
        'debug/pprof',

        // Inyecciones de código, RCE y LFI
        'allow_url_include',
        'auto_prepend_file',
        'php://input',
        '/proc/self/',
        'eval-config',

        // Archivos de respaldo comprimidos y volcados de base de datos
        '.tar.gz',
        '.zip',
        '.rar',
        '.7z',
        '.sql',
        '.bak',
        '.old'
    ];

    /**
     * Evalúa la petición entrante analizando baneos previos, rutas prohibidas, bots y frecuencia.
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
        $rutaSolicitada = strtolower(urldecode($_SERVER['REQUEST_URI'] ?? ''));


        // 3.1 NUEVO: Verificar si la ruta ya fue aprendida como maliciosa por el sistema autónomo
        if (Seguridad::esRutaAprendidaMaliciosa($rutaSolicitada)) {
            Seguridad::banearIpInmediata($ip, "Escaneo repetido de ruta maliciosa aprendida dinámicamente ($rutaSolicitada).");
            self::registrarYAbortar("Acceso denegado: Actividad maliciosa detectada.", 403, $ip);
        }
        // 2. REGLA 1: Exigir User-Agent y bloquear herramientas de escaneo conocidas
        if (empty($userAgent)) {
            self::registrarFaltaGraveYBanear("Petición sin User-Agent (posible bot o script malicioso).", $ip);
        }

        foreach (self::BOTS_PROHIBIDOS as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                self::registrarFaltaGraveYBanear("Uso de herramienta prohibida detectado ($bot).", $ip);
            }
        }

        // 3. REGLA 2: Detección de Fuzzing y escaneo de carpetas ajenas
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

    private static function registrarFaltaGraveYBanear(string $motivo, string $ip): void
    {
        // Baneo inmediato a la primera ofensa sin rodeos ni contadores lentos
        Seguridad::banearIpInmediata($ip, $motivo);

        self::registrarYAbortar("Acceso denegado: $motivo", 403, $ip);
    }

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

            // CORRECCIÓN: Se agregó el operador menos (-) que faltaba en la resta
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

    private static function registrarYAbortar(string $detalle, int $codigoHttp, string $ip): void
    {
        if (!is_dir(self::LOG_DIR)) {
            mkdir(self::LOG_DIR, 0755, true);
        }

        $fecha = date("Y-m-d H:i:s");
        $route = $_SERVER['REQUEST_URI'] ?? 'desconocida';

        $mensaje = "[$fecha] [$codigoHttp] $detalle - IP: $ip - Ruta: $route\n";
        file_put_contents(self::LOG_DIR . 'seguridad.log', $mensaje, FILE_APPEND);

        // Corte seco y cortante: respuesta limpia sin gastar recursos de la aplicación
        http_response_code($codigoHttp);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            "error" => true,
            "codigo" => $codigoHttp,
            "mensaje" => $detalle
        ]);
        exit;
    }
}
