<?php

declare(strict_types=1);


namespace App\Libs\Exceptions;

use Exception;
use App\Helpers\Logger;

class AppException extends Exception
{
    public function __construct(string $message, int $code = 400)
    {
        parent::__construct($message, $code);

        // Se registra en el log general de la app
        Logger::log('log_app', "APP ERROR [{$code}]: {$message}", [
            'file' => $this->getFile(),
            'line' => $this->getLine()
        ]);
    }
}
