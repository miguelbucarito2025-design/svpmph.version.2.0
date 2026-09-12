<?php

namespace App\Traits;

use App\Helpers\R2Service;

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

        $time = time();
        $keyDestino = "{$prefijoCarpeta}/{$nombreCampo}_{$time}_" . uniqid() . ".{$archivoEstructura['extension']}";

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
     * Elimina una lista de keys de Cloudflare R2 (Usado para Rollback o limpieza)
     * 
     * @param array $keys Lista de strings con los path en R2
     */
    protected function eliminarArchivosR2(array $keys): void
    {
        if (empty($keys)) return;

        $r2Service = new R2Service();
        foreach ($keys as $key) {
            if (!empty($key) && is_string($key)) {
                $r2Service->eliminarArchivo($key);
            }
        }
    }

    protected function ObtenerArchivo(string $key): string
    {
        $r2Service = new R2Service();

        return $r2Service->obtenerUrlPublica($key);
    }
}
