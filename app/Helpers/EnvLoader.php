<?php

declare(sytict_types=1);

namespace App\Helpers;

use Exception;

/**
 *clase encargada de leer en archivo .env y usar las claves para iniciar las configuraciones del sistema
 *como nombre de la base de datos , claves y todo eso;
 *esta clase no retorna nada solo ejecuta esa accion
 **/

class EnvLoader
{

    public static function load($path)
    {
        if (!file_exists($path)) {
            throw new Exception(' No se encontro el archivo de configuracion ');
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);


            if (empty($line) || str_starts_with($line, '#')) continue;
            if (!str_contains($line, '=')) continue;


            list($key, $value) = explode('=', $line, 2);


            $key = trim($key);
            $value = rtrim(trim($value), ';');


            if ((str_starts_with($key, "'") && str_ends_with($key, "'")) ||
                (str_starts_with($key, '"') && str_ends_with($key, '"'))
            ) {
                $key = substr($key, 1, -1);
            }


            if ((str_starts_with($value, "'") && str_ends_with($value, "'")) ||
                (str_starts_with($value, '"') && str_ends_with($value, '"'))
            ) {
                $value = substr($value, 1, -1);
            }


            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}
