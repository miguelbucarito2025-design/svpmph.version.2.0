<?php

namespace App\Helpers;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

/**
 * Servicio de gestión de almacenamiento en la nube para Cloudflare R2 via S3 API.
 */
class R2Service
{
    private S3Client $s3;
    private string $bucket;

    public function __construct()
    {
        $this->bucket = getenv('R2_BUCKET_NAME') ?: ($_ENV['R2_BUCKET_NAME'] ?? '');

        $this->s3 = new S3Client([
            'version'     => 'latest',
            'region'      => 'auto',
            'endpoint'    => getenv('R2_ENDPOINT') ?: ($_ENV['R2_ENDPOINT'] ?? ''),
            'credentials' => [
                'key'    => getenv('R2_ACCESS_KEY_ID') ?: ($_ENV['R2_ACCESS_KEY_ID'] ?? ''),
                'secret' => getenv('R2_SECRET_ACCESS_KEY') ?: ($_ENV['R2_SECRET_ACCESS_KEY'] ?? ''),
            ],
        ]);
    }

    /**
     * Sube un archivo local al bucket de R2.
     *
     * @param string $rutaTemporal Ruta absoluta del archivo en /tmp.
     * @param string $nombreDestino Key de destino (ej: "perfiles/usuario_1.jpg").
     * @param string $mime Tipo MIME verificado (ej: "image/jpeg").
     * @return array{exito: bool, ruta?: string, error?: string}
     */
    public function subirArchivo(string $rutaTemporal, string $nombreDestino, string $mime): array
    {
        try {
            $this->s3->putObject([
                'Bucket'      => $this->bucket,
                'Key'         => $nombreDestino,
                'SourceFile'  => $rutaTemporal,
                'ContentType' => $mime,
            ]);

            return [
                'exito' => true,
                'ruta'  => $nombreDestino
            ];
        } catch (AwsException $e) {
            error_log("Error R2 AWS: " . $e->getMessage());
            return [
                'exito' => false,
                'error' => 'No se pudo subir el archivo al almacenamiento en la nube.'
            ];
        } catch (\Exception $e) {
            error_log("Error R2 General: " . $e->getMessage());
            return [
                'exito' => false,
                'error' => 'Error inesperado durante la carga del archivo.'
            ];
        }
    }

    /**
     * Elimina un archivo del bucket.
     */
    public function eliminarArchivo(string $nombreDestino): bool
    {
        try {
            $this->s3->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => $nombreDestino,
            ]);
            return true;
        } catch (\Exception $e) {
            error_log("Error al eliminar en R2: " . $e->getMessage());
            return false;
        }
    }



    /**
     * Construye y retorna la URL pública directa para un objeto almacenado en Cloudflare R2.
     *
     * Documentación: Lee la variable R2_PUBLIC_URL definida en el entorno (.env)
     * y la concatena con la clave (Key) del archivo, sanitizando las barras diagonales.
     *
     * @param string $key Ruta relativa del objeto dentro del bucket (ej: 'perfiles/usuario_16_12345.jpg').
     * @return string URL pública completa para el cliente.
     */
    public function obtenerUrlPublica(string $key): string|null
    {
        if ($key == null || $key == '') {
            return null;
        }
        // Leemos la URL base pública desde las variables de entorno
        $baseUrl = $_ENV['R2_PUBLIC_URL'] ?? getenv('R2_PUBLIC_URL') ?? '';

        // Limpiamos barras inclinadas sobrantes para evitar URLs dobles (ej: domain.com//perfiles/foto.jpg)
        $baseUrl = rtrim($baseUrl, '/');
        $keyPath = ltrim($key, '/');

        return "{$baseUrl}/{$keyPath}";
    }
}
