<?php

namespace App\Helpers;

/**
 * Gestor de caché basado en archivos individuales por IP para mitigación de ataques masivos.
 * Protege la Base de Datos y el servidor sin interferir con la lógica de desencriptación.
 */
class IpCacheHelper
{
    /**
     * Retorna y asegura el directorio para los archivos JSON de IPs.
     * 
     * @return string
     */
    private static function obtenerDirectorio(): string
    {
        $dir = __DIR__ . '/../../storage/data/ips';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Limpia la IP para generar un nombre de archivo seguro.
     * 
     * @param string $ip
     * @return string
     */
    private static function obtenerRutaIp(string $ip): string
    {
        $ipSegura = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $ip);
        return self::obtenerDirectorio() . '/' . $ipSegura . '.json';
    }

    /**
     * Lee los datos de una IP.
     * 
     * @param string $ip
     * @return array
     */
    public static function obtenerDatosIp(string $ip): array
    {
        $archivo = self::obtenerRutaIp($ip);

        if (!file_exists($archivo)) {
            return [
                'peticiones_minuto' => 0,
                'inicio_ventana'   => time(),
                'baneado'          => false,
            ];
        }

        $contenido = @file_get_contents($archivo);
        if (empty($contenido)) {
            return [
                'peticiones_minuto' => 0,
                'inicio_ventana'   => time(),
                'baneado'          => false,
            ];
        }

        $datos = json_decode($contenido, true);
        return is_array($datos) ? $datos : [];
    }

    /**
     * Guarda la información de la IP.
     * 
     * @param string $ip
     * @param array $datos
     * @return bool
     */
    public static function guardarDatosIp(string $ip, array $datos): bool
    {
        $archivo = self::obtenerRutaIp($ip);
        $json = json_encode($datos);
        return $json !== false && file_put_contents($archivo, $json, LOCK_EX) !== false;
    }

    /**
     * Revisa si la carga general del servidor supera un número límite de peticiones por segundo.
     * 
     * @param int $limiteMaximoPorSegundo Máximo de peticiones globales por segundo.
     * @return bool True si el servidor está sobrecargado.
     */
    public static function servidorSobrecargado(int $limiteMaximoPorSegundo = 150): bool
    {
        $archivoGlobal = self::obtenerDirectorio() . '/_carga_global.json';
        $ahora = time();

        $contenido = @file_get_contents($archivoGlobal);
        $global = $contenido ? json_decode($contenido, true) : [];

        $segundoActual = $global['segundo'] ?? $ahora;
        $totalPeticiones = $global['total'] ?? 0;

        if ($segundoActual !== $ahora) {
            $segundoActual = $ahora;
            $totalPeticiones = 0;
        }

        $totalPeticiones++;

        @file_put_contents($archivoGlobal, json_encode([
            'segundo' => $segundoActual,
            'total'   => $totalPeticiones
        ]), LOCK_EX);

        return $totalPeticiones > $limiteMaximoPorSegundo;
    }
}
