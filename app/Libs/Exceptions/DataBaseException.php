<?php

declare(strict_types=1);

namespace App\Libs\Exceptions;

use Exception;
use Throwable;

/**
 * Clase DatabaseException
 *
 * Excepción especializada para errores durante la ejecución de consultas
 * o conexiones a la base de datos.
 *
 * @package App\Libs\Exceptions
 */
class DatabaseException extends Exception
{
    /**
     * Consulta SQL que provocó la excepción.
     *
     * @var string
     */
    private string $query;

    /**
     * Inicializa la excepción de base de datos registrando la consulta afectada.
     *
     * @param string $message Mensaje de error retornado por la base de datos.
     * @param string $query Sentencia SQL ejecutada.
     * @param int $code Código de error (Por defecto 500).
     * @param Throwable|null $previous Excepción previa (ej. PDOException).
     */
    public function __construct(string $message, string $query = '', int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->query = $query;
    }

    /**
     * Retorna la consulta SQL que generó la falla.
     *
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }
}
