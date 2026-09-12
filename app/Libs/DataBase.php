<?php

declare(strict_types=1);

namespace App\Libs;

use PDO;
use PDOException;
use App\Libs\Exceptions\DatabaseException;
use Exception;

/**
 * Clase DataBase
 * 
 * Gestiona la conexión a la base de datos mediante el patrón de diseño Singleton.
 * Asegura la reutilización de una única instancia del objeto PDO y la reconexión
 * automática en caso de pérdida de socket durante peticiones concurrentes.
 * 
 * @package App\Libs
 */
class DataBase
{
    /** @var string Host del servidor de base de datos */
    private string $host;

    /** @var string Nombre de la base de datos */
    private string $db_name;

    /** @var string Puerto de conexión */
    private string $port;

    /** @var string Usuario de la base de datos */
    private string $user;

    /** @var string Contraseña del usuario */
    private string $password;

    /** @var string Juego de caracteres utilizado */
    private string $charset = 'utf8mb4';

    /** @var PDO|null Instancia nativa de PDO */
    private ?PDO $pdo = null;

    /** @var DataBase|null Instancia única de la clase DataBase */
    private static ?DataBase $instancia = null;

    /**
     * Constructor privado para impedir la instanciación directa.
     * Inicializa los parámetros de entorno y establece la conexión PDO.
     * 
     * @throws DatabaseException Si ocurre un error al conectar con la base de datos.
     */
    private function __construct()
    {
        try {
            // Prioridad: $_ENV > $_SERVER > getenv
            $this->host     = $_ENV['HOSTDB']   ?? $_SERVER['HOSTDB']   ?? getenv('HOSTDB')   ?: 'localhost';
            $this->db_name  = $_ENV['DB_NAME']  ?? $_SERVER['DB_NAME']  ?? getenv('DB_NAME')  ?: '';
            $this->port     = $_ENV['PORT']     ?? $_SERVER['PORT']     ?? getenv('PORT')     ?: '3306';
            $this->user     = $_ENV['USER']     ?? $_SERVER['USER']     ?? getenv('USER')     ?: 'root';
            $this->password = $_ENV['PASSWORD'] ?? $_SERVER['PASSWORD'] ?? getenv('PASSWORD') ?: '';

            // Si DB_NAME llegó vacío, lanzamos excepción inmediata antes de conectar
            if (empty(trim($this->db_name))) {
                throw new DatabaseException("Error de configuración: La variable DB_NAME no fue cargada en el entorno.", "", 500);
            }

            $dsn = "mysql:host=" . trim($this->host) .
                ";port=" . trim($this->port) .
                ";dbname=" . trim($this->db_name) .
                ";charset=" . trim($this->charset);

            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false
            ];

            $this->pdo = new PDO($dsn, trim($this->user), trim($this->password), $opciones);

            // BLINDAJE EXTRA: Seleccionar la base de datos explícitamente por si la DSN perdió el parámetro
            $this->pdo->exec("USE `" . trim($this->db_name) . "`;");
        } catch (PDOException $e) {
            throw new DatabaseException("Error de conexión a BD: " . $e->getMessage(), "", 500);
        }
    }

    /**
     * Obtiene la conexión activa de PDO (Patrón Singleton con verificación de Socket).
     * 
     * @return PDO Instancia activa de conexión PDO.
     */
    public static function getConnect(): PDO
    {
        // 1. Si no existe la instancia, la creamos
        if (self::$instancia === null) {
            self::$instancia = new DataBase();
        }

        // 2. CORRECCIÓN CRÍTICA: Verificar si la propiedad $pdo se destruyó o perdió el socket
        if (self::$instancia->pdo === null) {
            self::$instancia = new DataBase();
        }

        return self::$instancia->pdo;
    }

    /**
     * Comprueba si la conexión a la base de datos se encuentra respondiendo.
     * 
     * @return bool True si la BD responde correctamente, False en caso contrario.
     */
    public static function estaActiva(): bool
    {
        try {
            $conexion = self::getConnect();
            $stmt = $conexion->query('SELECT 1');
            return $stmt !== false;
        } catch (Exception $e) {
            // Un método booleano debe retornar false ante un fallo, no lanzar una excepción
            return false;
        }
    }

    /**
     * Cierra de forma explícita la conexión PDO y elimina la instancia Singleton.
     * 
     * @return void
     */
    public static function desconectar(): void
    {
        if (self::$instancia !== null) {
            self::$instancia->pdo = null;
            self::$instancia = null;
        }
    }

    /**
     * Previene la clonación del objeto Singleton.
     */
    private function __clone() {}

    /**
     * Previene la deserialización del objeto Singleton.
     * 
     * @throws Exception Si se intenta deserializar la instancia.
     */
    public function __wakeup()
    {
        throw new Exception("No puedes deserializar una instancia de Singleton.");
    }

    /**
     * Destructor de la clase para liberar la conexión al destruir el objeto.
     */
    public function __destruct()
    {
        $this->pdo = null;
    }
}
