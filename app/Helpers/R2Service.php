<?php

namespace App\Helpers;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Exception;

/**
 * Servicio para la gestión de almacenamiento en la nube en Cloudflare R2 vía API S3.
 *
 * Permite la subida, eliminación, lectura de flujo de objetos para proxy interno
 * y generación de URLs firmadas temporales.
 */
class R2Service
{
    /**
     * Instancia del cliente AWS S3 configurado para Cloudflare R2.
     * 
     * @var S3Client
     */
    private S3Client $s3;

    /**
     * Nombre del bucket de Cloudflare R2.
     * 
     * @var string
     */
    private string $bucket;

    /**
     * Inicializa las credenciales y la conexión con el endpoint de Cloudflare R2.
     */
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
     * Sube un archivo local hacia el bucket de Cloudflare R2.
     *
     * @param string $rutaTemporal Ruta absoluta del archivo temporal en el servidor local.
     * @param string $nombreDestino Clave única (Key) de destino dentro del bucket.
     * @param string $mime Tipo MIME verificado del archivo.
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
        } catch (Exception $e) {
            error_log("Error R2 General: " . $e->getMessage());
            return [
                'exito' => false,
                'error' => 'Error inesperado durante la carga del archivo.'
            ];
        }
    }

    /**
     * Elimina un objeto almacenado en el bucket de R2 mediante su clave.
     *
     * @param string $nombreDestino Clave (Key) del archivo a eliminar.
     * @return bool Devuelve true si la operación fue exitosa, false en caso contrario.
     */
    public function eliminarArchivo(string $nombreDestino): bool
    {
        try {
            $this->s3->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => $nombreDestino,
            ]);
            return true;
        } catch (Exception $e) {
            error_log("Error al eliminar en R2: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el flujo de datos binarios y metadatos de un objeto privado desde R2.
     * 
     * Método fundamental utilizado por el controlador proxy para servir archivos
     * directamente al navegador sin exponer el bucket.
     *
     * @param string $key Clave única del objeto en el bucket.
     * @return array{ContentType: string, ContentLength: int, Body: mixed}|null
     */
    public function obtenerObjeto(string $key): ?array
    {
        if (empty($key)) {
            return null;
        }

        try {
            $resultado = $this->s3->getObject([
                'Bucket' => $this->bucket,
                'Key'    => $key,
            ]);

            return [
                'ContentType'   => $resultado['ContentType'] ?? 'application/octet-stream',
                'ContentLength' => $resultado['ContentLength'] ?? 0,
                'Body'          => $resultado['Body'],
            ];
        } catch (AwsException $e) {
            error_log("Error al leer objeto en R2 AWS: " . $e->getMessage());
            return null;
        } catch (Exception $e) {
            error_log("Error al leer objeto en R2 General: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Genera una URL temporal firmada (Presigned URL) para acceder a recursos privados.
     *
     * @param string $key Ruta relativa o clave del objeto dentro del bucket.
     * @param int $minutosExpiracion Tiempo de validez del enlace en minutos (Por defecto: 15).
     * @return string|null URL firmada temporal o null en caso de falla o clave inválida.
     */
    public function obtenerUrlPrivada(string $key, int $minutosExpiracion = 15): ?string
    {
        if (empty($key)) {
            return null;
        }

        try {
            $comando = $this->s3->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key'    => $key,
            ]);

            $peticionFirmada = $this->s3->createPresignedRequest($comando, "+{$minutosExpiracion} minutes");

            return (string) $peticionFirmada->getUri();
        } catch (AwsException $e) {
            error_log("Error R2 Presigned URL AWS: " . $e->getMessage());
            return null;
        } catch (Exception $e) {
            error_log("Error R2 Presigned URL General: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Construye y retorna la URL pública directa para un objeto almacenado.
     *
     * @deprecated Este método queda en desuso ya que el bucket es estrictamente privado.
     * @param string $key Ruta relativa del objeto dentro del bucket.
     * @return string|null URL pública estática o null si la clave es inválida.
     */
    protected function obtenerUrlPublica(string $key): ?string
    {
        if (empty($key)) {
            return null;
        }

        $baseUrl = getenv('R2_PUBLIC_URL') ?: ($_ENV['R2_PUBLIC_URL'] ?? '');

        if (empty($baseUrl)) {
            return null;
        }

        $baseUrl = rtrim($baseUrl, '/');
        $keyPath = ltrim($key, '/');

        return "{$baseUrl}/{$keyPath}";
    }
}
