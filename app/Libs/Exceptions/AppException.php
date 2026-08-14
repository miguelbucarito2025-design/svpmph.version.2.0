<?php

declare(strict_types=1);

namespace App\Libs\Exceptions;

use Exception;
use Throwable;

/**
 * Clase AppException
 *
 * Excepción base para errores genéricos de la aplicación.
 *
 * @package App\Libs\Exceptions
 */
class AppException extends Exception
{
    /**
     * Inicializa la excepción del sistema permitiendo el encadenamiento de errores.
     *
     * @param string $message Mensaje descriptivo del error.
     * @param int $code Código de estado HTTP asignado (Por defecto 400).
     * @param Throwable|null $previous Excepción previa capturada.
     */
    public function __construct(string $message, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
