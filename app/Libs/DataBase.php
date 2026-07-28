<?php

declare(sytict_types=1);


/* Conexion a la base de datos con patron Singleton */

namespace App\Libs;

use PDO;
use PDOException;
use App\Libs\Exceptions\DatabaseException;
use Exception;

class DataBase
{
    private string $host;
    private string $db_name;
    private string $port;
    private string $user;
    private string $password;
    private string $charset = 'utf8mb4'; // utf8mb4 es el estándar recomendado

    public ?PDO $pdo = null;

    private static ?DataBase $instancia = null;

    private function __construct()
    {
        try {
            $this->host     = getenv('HOSTDB') ?: 'localhost';
            $this->db_name  = getenv('DB_NAME') ?: '';
            $this->port     = getenv('PORT') ?: '3306';
            $this->user     = getenv('USER') ?: 'root';
            $this->password = getenv('PASSWORD') ?: '';

            $dsn = "mysql:host=" . trim($this->host) . ";port=" . trim($this->port) . ";dbname=" . trim($this->db_name) . ";charset=" . trim($this->charset);

            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $this->pdo = new PDO($dsn, $this->user, $this->password, $opciones);
        } catch (PDOException $e) {
            // Pasamos el mensaje, la consulta vacía ("") y el código de estado 500
            throw new DatabaseException("Error de conexión a BD: " . $e->getMessage(), "", 500);
        }
    }

    public static function getConnect(): PDO
    {
        if (self::$instancia === null) {
            self::$instancia = new DataBase();
        }
        return self::$instancia->pdo;
    }

    public static function estaActiva(): bool
    {
        try {
            $conexion = self::getConnect();
            return (bool) $conexion->query('SELECT 1');
        } catch (Exception $e) {
            throw new DatabaseException("No se pudo verificar la conexión a la Base de Datos.", "", 500);
        }
    }

    public static function desconectar(): void
    {
        if (self::$instancia !== null) {
            self::$instancia->pdo = null;
            self::$instancia = null;
        }
    }

    // Bloqueamos la clonación y deserialización para proteger el Singleton
    private function __clone() {}
    public function __wakeup()
    {
        throw new Exception("No puedes deserializar una instancia de Singleton.");
    }

    public function __destruct()
    {
        $this->pdo = null;
    }
}
