<?php

declare(strict_types=1);

namespace App\Helpers;

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
        // Resolución correcta y limpia de la ruta absoluta hacia la carpeta Logs/
        $logDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'app/Logs';

        if (!is_dir($logDir) && !mkdir($logDir, 0755, true) && !is_dir($logDir)) {
            return;
        }

        $date = date('Y-m-d H:i:s');
        $contextString = !empty($context)
            ? ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : '';

        $formattedMessage = "[{$date}] {$message}{$contextString}" . PHP_EOL;

        // Uso obligatorio de LOCK_EX para evitar colisiones entre procesos
        file_put_contents(
            $logDir . DIRECTORY_SEPARATOR . "{$filename}.log",
            $formattedMessage,
            FILE_APPEND | LOCK_EX
        );
    }
}
