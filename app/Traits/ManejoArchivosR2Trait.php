<?php

namespace App\Traits;

use App\Helpers\R2Service;
use App\Libs\Seguridad;
use App\Libs\Session;

/**
 * Trait para la gestión de archivos y generación de URLs privadas hacia Cloudflare R2.
 */
trait ManejoArchivosR2Trait
{
    /**
     * Sube un archivo filtrado previamente por el controlador hacia Cloudflare R2.
     * 
     * @param array $archivoEstructura Resultado de $this->filtrarArchivo()
     * @param string $prefijoCarpeta Carpeta destino en R2 (ej: 'logos', 'certificados')
     * @param string $nombreCampo Nombre del campo en el formulario (para logs/errores)
     * @return array
     */
    protected function subirArchivoR2(array $archivoEstructura, string $prefijoCarpeta, string $nombreCampo): array
    {
        if (empty($archivoEstructura['valido'])) {
            return [
                'exito' => false,
                'key'   => null,
                'error' => $archivoEstructura['error'] ?? "El archivo {$nombreCampo} no es válido."
            ];
        }

        // Sanitización estricta de ruta y nombre de campo
        $prefijoLimpio = trim(preg_replace('/[^a-zA-Z0-9_\-\/]/', '', $prefijoCarpeta), '/');
        $campoLimpio   = preg_replace('/[^a-zA-Z0-9_\-]/', '', $nombreCampo);
        $extension     = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $archivoEstructura['extension']));

        $time = time();
        $keyDestino = "{$prefijoLimpio}/{$campoLimpio}_{$time}_" . uniqid() . ".{$extension}";

        $r2Service = new R2Service();
        $resultado = $r2Service->subirArchivo($archivoEstructura['tmp_name'], $keyDestino, $archivoEstructura['mime']);

        if (!$resultado['exito']) {
            return [
                'exito' => false,
                'key'   => null,
                'error' => $resultado['error'] ?? "Error al almacenar {$nombreCampo} en la nube."
            ];
        }

        return [
            'exito' => true,
            'key'   => $keyDestino,
            'error' => null
        ];
    }

    /**
     * Elimina una lista de keys de Cloudflare R2.
     * 
     * @param array $keys Lista de rutas en R2.
     * @return void
     */
    protected function eliminarArchivosR2(array $keys): void
    {
        if (empty($keys)) {
            return;
        }

        $r2Service = new R2Service();
        foreach ($keys as $key) {
            if (is_string($key) && !empty($key) && !str_contains($key, '..')) {
                $r2Service->eliminarArchivo($key);
            }
        }
    }

    /**
     * Genera la URL amigable segura del backend para acceder a un archivo.
     * 
     * Mantiene la firma original del método para evitar reescribir vistas o bases de datos,
     * pero empaqueta la $key y la sesión del usuario en un token cifrado.
     * 
     * @param string|null $key Clave almacenada en la base de datos (ej: 'logos/empresa_123.jpg').
     * @param int $minutosExpiracion Minutos de validez del enlace generado (por defecto 60 min).
     * @return string|null URL amigable completa para el frontend o null si la clave es vacía.
     */
    protected function obtenerArchivo(?string $key, int $minutosExpiracion = 5): ?string
    {
        if (empty($key) || str_contains($key, '..')) {
            return null;
        }

        $session = new Session();
        $usuarioId = $session->get('usuario_id');
        // Empaquetamos la key y la identidad del usuario en el token
        $token = Seguridad::encriptarParams([
            'r2_key'     => $key,
            'usuario_id' => $usuarioId
        ], $minutosExpiracion);

        if (empty($token)) {
            return null;
        }
        // Construimos la URL amigable apuntando al endpoint de tu enrutador
        return "archivo/obtener/{$token}";
    }
}
