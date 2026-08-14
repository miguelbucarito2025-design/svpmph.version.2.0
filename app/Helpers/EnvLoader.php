<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Libs\Exceptions\AppException;

/**
 * Clase EnvLoader
 *
 * Encargada de leer, parsear y cargar las variables definidas en el archivo .env
 * en el entorno de ejecución del sistema ($_ENV, $_SERVER y putenv).
 *
 * @package App\Helpers
 */
class EnvLoader
{
    /**
     * Lee un archivo de entorno y registra las variables en el sistema.
     *
     * @param string $path Ruta relativa o absoluta del archivo .env.
     * @return void
     * @throws AppException Si el archivo no existe, no es legible o falla la lectura.
     */
    public static function load(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new AppException("No se pudo acceder al archivo de entorno en la ruta: {$path}", 500);
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            throw new AppException("Error al leer las líneas del archivo de entorno: {$path}", 500);
        }

        foreach ($lines as $line) {
            $line = trim($line);

            // Ignorar líneas vacías o comentarios principales
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Ignorar líneas que no contengan asignación
            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);

            $key   = trim($key);
            $value = trim($value);

            // Limpieza de comillas en la clave
            if (strlen($key) >= 2 && self::esEntrecomillado($key)) {
                $key = substr($key, 1, -1);
            }

            if ($key === '') {
                continue;
            }

            // Procesamiento del valor: preservar comentarios si está entrecomillado
            if (strlen($value) >= 2 && self::esEntrecomillado($value)) {
                $value = substr($value, 1, -1);
            } else {
                // Si no tiene comillas, se permite la extracción de comentarios en línea
                if (str_contains($value, ' #')) {
                    $value = explode(' #', $value, 2)[0];
                    $value = trim($value);
                }
            }

            // Asignación segura en el entorno del sistema
            if (function_exists('putenv')) {
                putenv("{$key}={$value}");
            }

            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
        }
    }

    /**
     * Evalúa si una cadena de texto está delimitada por comillas simples o dobles.
     *
     * @param string $cadena Cadena a verificar.
     * @return bool True si la cadena comienza y termina con el mismo tipo de comilla.
     */
    private static function esEntrecomillado(string $cadena): bool
    {
        $primerCaracter = $cadena[0];
        $ultimoCaracter = $cadena[strlen($cadena) - 1];

        return ($primerCaracter === '"' && $ultimoCaracter === '"') ||
            ($primerCaracter === "'" && $ultimoCaracter === "'");
    }
}
