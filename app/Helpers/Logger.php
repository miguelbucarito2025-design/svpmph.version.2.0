<?php

declare(strict_types=1);


namespace App\Helpers;

class Logger
{
    public static function log(string $filename, string $message, array $context = []): void
    {
        $logDir = '/app/Logs/';

        // Crear la carpeta /logs si no existe
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $date = date('Y-m-d H:i:s');
        $contextString = !empty($context) ? ' | Context: ' . json_encode($context) : '';

        // Formato: [2026-07-23 14:30:00] ERROR: Mensaje de error | Context: {...}
        $formattedMessage = "[{$date}] {$message}{$contextString}" . PHP_EOL;

        // Escribir en el archivo de log especificado sin borrar lo anterior (FILE_APPEND)
        file_put_contents("{$logDir}/{$filename}.log", $formattedMessage, FILE_APPEND);
    }
}
