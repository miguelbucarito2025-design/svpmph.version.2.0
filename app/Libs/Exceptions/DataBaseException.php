<?php

declare(strict_types=1);

namespace App\Libs\Exceptions;

use Exception;
use App\Helpers\Logger;


class DatabaseException extends Exception
{
    public function __construct(string $message, string $query = '', int $code = 500)
    {
        parent::__construct($message, $code);

        // Guarda el detalle técnico internamente en el log de BD
        Logger::log('log_db', "DB ERROR [{$code}]: {$message}", [
            'sql'  => $query,
            'file' => $this->getFile(),
            'line' => $this->getLine()
        ]);
    }
}
