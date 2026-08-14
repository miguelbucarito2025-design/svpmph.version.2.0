<?php

declare(strict_types=1);

namespace App\Libs;

use Throwable;
use ErrorException;
use App\Helpers\Logger;
use App\Libs\Exceptions\DatabaseException;

/**
 * Clase ManejadorExcepciones
 *
 * Registra y gestiona de forma centralizada todas las excepciones y errores no
 * capturados en el ciclo de vida de la aplicación. Garantiza respuestas en formato
 * JSON estandarizado para la API (Web y Aplicación Móvil) y HTML únicamente en
 * vistas explícitas.
 *
 * @package App\Libs
 */
class ManejadorExcepciones
{
    /**
     * Registra los controladores nativos de PHP para errores, excepciones y apagado del script.
     *
     * @return void
     */
    public static function registrar(): void
    {
        set_error_handler([self::class, 'mantenimientoErroresNativos']);
        set_exception_handler([self::class, 'mantenimientoExcepciones']);
        register_shutdown_function([self::class, 'mantenimientoErroresFatales']);
    }

    /**
     * Captura cualquier excepción no atrapada por un bloque try-catch.
     *
     * @param Throwable $excepcion La excepción o error generado.
     * @return void
     */
    public static function mantenimientoExcepciones(Throwable $excepcion): void
    {
        $codigoHttp = (int) $excepcion->getCode();

        if ($codigoHttp < 400 || $codigoHttp > 599) {
            $codigoHttp = 500;
        }

        self::registrarEnLog($excepcion);
        self::responder($excepcion, $codigoHttp);
    }

    /**
     * Convierte advertencias, avisos y errores nativos de PHP en excepciones procesables.
     *
     * @param int $nivel Nivel del error (E_WARNING, E_NOTICE, etc.).
     * @param string $mensaje Descripción del error.
     * @param string $archivo Ruta del archivo fuente.
     * @param int $linea Línea donde ocurrió el error.
     * @return bool
     * @throws ErrorException
     */
    public static function mantenimientoErroresNativos(int $nivel, string $mensaje, string $archivo, int $linea): bool
    {
        if (!(error_reporting() & $nivel)) {
            return false;
        }

        throw new ErrorException($mensaje, 0, $nivel, $archivo, $linea);
    }

    /**
     * Intercepta errores fatales al finalizar la ejecución del script (Shutdown).
     *
     * @return void
     */
    public static function mantenimientoErroresFatales(): void
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            $excepcion = new ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            );

            self::mantenimientoExcepciones($excepcion);
        }
    }

    /**
     * Escribe el detalle de las excepciones en los archivos de log correspondientes.
     *
     * @param Throwable $excepcion
     * @return void
     */
    private static function registrarEnLog(Throwable $excepcion): void
    {
        $contexto = [
            'file' => $excepcion->getFile(),
            'line' => $excepcion->getLine(),
        ];

        if ($excepcion instanceof DatabaseException) {
            $contexto['sql'] = $excepcion->getQuery();
            Logger::log('log_db', "DB ERROR [{$excepcion->getCode()}]: {$excepcion->getMessage()}", $contexto);
            return;
        }

        Logger::log('log_app', "APP ERROR [{$excepcion->getCode()}]: {$excepcion->getMessage()}", $contexto);
    }

    /**
     * Limpia los búferes y emite la respuesta al cliente en el formato correspondiente.
     *
     * @param Throwable $excepcion
     * @param int $codigoHttp
     * @return void
     */
    private static function responder(Throwable $excepcion, int $codigoHttp): void
    {
        // Limpiar cualquier búfer de salida previo para no mezclar contenido
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        http_response_code($codigoHttp);

        $modoDepuracion = strtolower($_ENV['APP_DEBUG'] ?? 'false') === 'true';
        $esErrorCliente = ($codigoHttp >= 400 && $codigoHttp < 500);

        $mensaje = ($modoDepuracion || $esErrorCliente)
            ? $excepcion->getMessage()
            : 'Ha ocurrido un error interno en el servidor.';

        $respuesta = [
            'status'  => 'error',
            'code'    => $codigoHttp,
            'message' => $mensaje,
            'data'    => null
        ];

        if ($modoDepuracion) {
            $respuesta['debug'] = [
                'exception' => get_class($excepcion),
                'file'      => $excepcion->getFile(),
                'line'      => $excepcion->getLine(),
                'trace'     => explode("\n", $excepcion->getTraceAsString())
            ];
        }

        // Si la petición debe ser tratada como JSON (por defecto para APIs y métodos POST/PUT/DELETE)
        if (self::esPeticionJson()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        // Si es una petición navegable web explícita y no estamos en depuración
        $vistaError = dirname(__DIR__, 2) . "/views/errores/{$codigoHttp}.php";
        if (!$modoDepuracion && file_exists($vistaError)) {
            require_once $vistaError;
            exit;
        }

        // Salida HTML plana únicamente para peticiones de navegación web directa
        echo "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'><title>Error {$codigoHttp}</title></head><body>";
        echo "<h1>Error {$codigoHttp}</h1>";
        echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') . "</p>";
        echo "</body></html>";
        exit;
    }

    /**
     * Determina si la petición entrante debe recibir una respuesta en JSON.
     * 
     * Regla de arquitectura: Todas las peticiones HTTP que no sean GET, o que provengan
     * de un cliente API/Móvil, deben recibir JSON obligatoriamente.
     *
     * @return bool
     */
    private static function esPeticionJson(): bool
    {
        $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        // 1. Si no es GET (POST, PUT, DELETE, PATCH), la respuesta SIEMPRE es JSON en la API
        if (strtoupper($metodo) !== 'GET') {
            return true;
        }

        // 2. Comprobaciones estándar de cabeceras y rutas
        return str_contains($accept, 'text/html') === false ||
            str_contains($accept, 'application/json') ||
            str_contains($contentType, 'application/json') ||
            strtolower($requestedWith) === 'xmlhttprequest' ||
            str_contains($uri, '/api/');
    }
}
