<?php

namespace App\Helpers;

/**
 * Gestor de almacenamiento persistente de tokens individuales en archivos independientes.
 * Elimina totalmente la contención y los bloqueos en peticiones concurrentes.
 */
class TokenStorageHelper
{
    /**
     * Directorio donde se almacenan los archivos de token.
     * 
     * @return string
     */
    private static function obtenerDirectorio(): string
    {
        $dir = __DIR__ . '/../../storage/data/tokens';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Construye la ruta absoluta para el archivo de un token específico.
     * 
     * @param string $token Token de 16 caracteres.
     * @return string
     */
    private static function obtenerRutaToken(string $token): string
    {
        // Sanitizamos la cadena por seguridad
        $tokenLimpio = preg_replace('/[^a-f0-9]/i', '', $token);
        return self::obtenerDirectorio() . '/' . $tokenLimpio . '.json';
    }

    /**
     * Guarda un token en su propio archivo independiente.
     * Ejecuta una autolimpieza automática (Garbage Collection) con probabilidad 1/50.
     *
     * @param string $token Token de 16 caracteres.
     * @param string $cadenaCifrada Datos cifrados con AES.
     * @param int|null $expiraEn Timestamp UNIX de vencimiento.
     * @return bool True si se escribió correctamente.
     */
    public static function guardarToken(string $token, string $cadenaCifrada, ?int $expiraEn = null): bool
    {
        // AUTOLIMPIEZA AUTOMÁTICA: 1 de cada 50 peticiones purga los archivos huérfanos expirados
        if (rand(1, 50) === 1) {
            self::purgarTokensExpirados();
        }

        $archivo = self::obtenerRutaToken($token);

        $datos = [
            'data'      => $cadenaCifrada,
            'expira_en' => $expiraEn
        ];

        $json = json_encode($datos);
        if ($json === false) {
            return false;
        }

        return file_put_contents($archivo, $json, LOCK_EX) !== false;
    }

    /**
     * Lee de forma independiente el archivo del token solicitado.
     *
     * @param string $token Token corto.
     * @return string|null Cadena cifrada o null si no existe o expiró.
     */
    public static function obtenerToken(string $token): ?string
    {
        $archivo = self::obtenerRutaToken($token);

        if (!file_exists($archivo)) {
            return null;
        }

        $contenido = @file_get_contents($archivo);
        if (empty($contenido)) {
            return null;
        }

        $item = json_decode($contenido, true);
        if (!is_array($item)) {
            return null;
        }

        // Validar expiración
        if (isset($item['expira_en']) && time() > $item['expira_en']) {
            self::eliminarToken($token); // Auto-limpieza si expiró
            return null;
        }

        return $item['data'] ?? null;
    }

    /**
     * Elimina el archivo individual de un token.
     * 
     * @param string $token
     * @return void
     */
    public static function eliminarToken(string $token): void
    {
        $archivo = self::obtenerRutaToken($token);

        if (file_exists($archivo)) {
            @unlink($archivo);
        }
    }

    /**
     * Tarea de mantenimiento/Cron para purgar archivos huérfanos que hayan expirado.
     * Se puede llamar periódicamente o con una probabilidad (ej. 1 de cada 100 peticiones).
     * 
     * @return void
     */
    public static function purgarTokensExpirados(): void
    {
        $directorio = self::obtenerDirectorio();
        $archivos = glob($directorio . '/*.json');

        if (empty($archivos)) {
            return;
        }

        $ahora = time();
        foreach ($archivos as $archivo) {
            $contenido = @file_get_contents($archivo);
            if ($contenido) {
                $item = json_decode($contenido, true);
                if (isset($item['expira_en']) && $ahora > $item['expira_en']) {
                    @unlink($archivo);
                }
            }
        }
    }
}
