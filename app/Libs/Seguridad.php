<?php

declare(sytict_types=1);



namespace App\Libs;

/* Departamento de seguridad - MVC Dinámico */

class Seguridad
{
    private static $key;
    private static $method = "aes-256-cbc";
    private static $iv;

    public static function init()
    {
        if (self::$key === null) {
            // Usamos la superglobal $_ENV para garantizar compatibilidad total con Laragon/XAMPP
            self::$key = $_ENV['DB_KEY'] ?? getenv('key') ?: '';
            self::$iv = $_ENV['DB_IV'] ?? getenv('iv') ?: '';
        }
    }




    public static function encriptarID($id)
    {
        self::init();
        $encriptado = openssl_encrypt($id, self::$method, self::$key, 0, self::$iv);
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($encriptado));
    }

    public static function desencriptarID($id_encriptado)
    {
        self::init();
        $data = str_replace(['-', '_'], ['+', '/'], $id_encriptado);
        return openssl_decrypt(base64_decode($data), self::$method, self::$key, 0, self::$iv);
    }

    public static function setParams(string $cadena, string $separacion): array
    {
        $cadenaLimpia = rtrim($cadena, $separacion);
        return explode($separacion, $cadenaLimpia);
    }

    public static function responderError($mensaje, $titulo = "Error de Sistema", $codigo = 500)
    {
        $fecha = date("Y-m-d H:i:s");

        // 💡 Obtenemos el rastro de la ejecución para saber quién llamó a este método
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $archivoFallo = isset($trace[0]['file']) ? basename($trace[0]['file']) : 'Desconocido';
        $lineaFallo   = isset($trace[0]['line']) ? $trace[0]['line'] : '0';

        // 💡 Formateamos el mensaje incluyendo el Archivo y la Línea exacta
        $msj = "[" . $fecha . "] Error en la App [Archivo: $archivoFallo | Línea: $lineaFallo]: $mensaje\n";
        file_put_contents('app/class/log/erroresApp.log', $msj, FILE_APPEND);

        http_response_code($codigo);
        header('Content-Type: application/json');
        echo json_encode([
            "status"  => "error",
            "code"    => $codigo,
            "title"   => $titulo,
            "message" => 'Hubo un problema al procesar la solicitud de forma segura.',
            "data"    => null
        ]);
        exit;
    }

    public static function responderExito($mensaje, $datos = null, $titulo = "Operación Exitosa")
    {
        // 💡 Si por error llega un objeto, lo casteamos a array para que json_encode no explote
        if (is_object($datos)) {
            // Si el objeto tiene un método para convertirse en array (ej: toArray), lo usamos
            if (method_exists($datos, 'toArray')) {
                $datos = $datos->toArray();
            } else {
                // Si no, lo forzamos a un array de sus propiedades públicas
                $datos = (array) $datos;
            }
        }

        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode([
            "status"  => "success",
            "code"    => 200,
            "title"   => $titulo,
            "message" => $mensaje,
            "data"    => $datos
        ]);
        exit;
    }

    public static function detectorDeBots()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // 1. Bloqueo por Identidad Sospechosa
        $botsConocidos = ['Python', 'curl', 'PostmanRuntime', 'Wget', 'libwww-perl'];
        foreach ($botsConocidos as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                manejarError("Acceso denegado: Herramienta no permitida. Bot Detectado 403");
            }
        }

        // 2. Control de Velocidad (Rate Limiting)
        $ahora = microtime(true);
        if (isset($_SESSION['ultima_peticion'])) {
            $tiempoTranscurrido = $ahora - $_SESSION['ultima_peticion'];
            if ($tiempoTranscurrido < 0.2) { // 200 ms
                manejarError("Demasiadas peticiones. Calma, humano. Flood Control 429");
            }
        }
        $_SESSION['ultima_peticion'] = $ahora;
    }
}

function manejarError($detalle)
{
    $fecha = date("Y-m-d H:i:s");
    $mensaje = "[" . $fecha . "] Error en el servidor: $detalle\n";
    file_put_contents(__DIR__ . '/log/erroresApp.log', $mensaje, FILE_APPEND);

    http_response_code(404);
}
