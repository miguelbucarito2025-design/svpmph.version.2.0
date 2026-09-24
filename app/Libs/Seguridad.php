<?php

declare(strict_types=1);

namespace App\Libs;

/**
 * Clase encargada de la seguridad general, cifrado criptográfico simétrico,
 * empaquetado de parámetros y detección básica de peticiones maliciosas.
 */
class Seguridad
{
    /**
     * Clave secreta de cifrado obtenida del entorno.
     */
    private static string $key = '';

    /**
     * Algoritmo de cifrado estándar.
     */
    private static string $method = "aes-256-cbc";

    /**
     * Directorio en el sistema donde se almacenan las IPs baneadas.
     */
    private static function obtenerDirBaneos(): string
    {
        $dir = sys_get_temp_dir() . '/baneos_permanentes';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Inicializa la clave de cifrado desde las variables de entorno de forma consistente.
     * 
     * @return void
     */
    public static function init(): void
    {
        if (empty(self::$key)) {
            $clavePura = $_ENV['APP_KEY'] ?? $_ENV['DB_KEY'] ?? getenv('APP_KEY') ?: 'clave_secreta_por_defecto_32bytes_minimo';

            // Forzamos que la clave tenga exactamente 32 bytes usando hash_hkdf para AES-256
            self::$key = hash_hkdf('sha256', $clavePura, 32, 'aes_encryption_key');
        }
    }

    /**
     * Cifra un texto o identificador utilizando AES-256-CBC con IV aleatorio y firma HMAC-SHA256.
     * 
     * @param int|string $id Valor o cadena a cifrar.
     * @return string Cadena cifrada, firmada y codificada en formato URL-Safe Base64.
     */
    public static function encriptarID(int|string $id): string
    {
        self::init();

        // 1. Generar un Vector de Inicialización (IV) único e impredecible de 16 bytes
        $iv = random_bytes(16);

        // 2. Cifrar la información
        $cifradoRaw = openssl_encrypt((string)$id, self::$method, self::$key, OPENSSL_RAW_DATA, $iv);

        if ($cifradoRaw === false) {
            return '';
        }

        // 3. Crear firma de autenticidad HMAC sobre IV + Contenido Cifrado
        $hmac = hash_hmac('sha256', $iv . $cifradoRaw, self::$key, true);

        // 4. Empaquetar [HMAC (32 bytes)] + [IV (16 bytes)] + [Cifrado]
        $paquete = $hmac . $iv . $cifradoRaw;

        // 5. Retornar en Base64 URL-Safe sin caracteres especiales (+, /, =)
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($paquete));
    }

    /**
     * Desencripta una cadena, verificando previamente su firma de integridad HMAC y extrayendo el IV.
     * 
     * @param string $idEncriptado Cadena cifrada recibida.
     * @return string|null Texto desencriptado original o NULL si la firma es inválida / manipulada.
     */
    public static function desencriptarID(string $idEncriptado): ?string
    {
        self::init();

        if (empty($idEncriptado)) {
            return null;
        }

        // Restaurar formato Base64 estándar
        $data = str_replace(['-', '_'], ['+', '/'], $idEncriptado);
        $modulo = strlen($data) % 4;
        if ($modulo) {
            $data .= str_repeat('=', 4 - $modulo);
        }

        $bytes = base64_decode($data, true);

        // Validar tamaño mínimo: 32 bytes (HMAC) + 16 bytes (IV) = 48 bytes
        if ($bytes === false || strlen($bytes) < 48) {
            self::registrarFaltaManipulacion();
            return null;
        }

        // Extraer componentes
        $hmacRecibido = substr($bytes, 0, 32);
        $iv           = substr($bytes, 32, 16);
        $cifradoRaw   = substr($bytes, 48);

        // Recalcular HMAC para verificar integridad
        $hmacCalculado = hash_hmac('sha256', $iv . $cifradoRaw, self::$key, true);

        // Comparación en tiempo constante: Si no coincide, ALGUIEN ALTERÓ EL TOKEN
        if (!hash_equals($hmacCalculado, $hmacRecibido)) {
            self::registrarFaltaManipulacion();
            return null;
        }

        $desencriptado = openssl_decrypt($cifradoRaw, self::$method, self::$key, OPENSSL_RAW_DATA, $iv);

        if ($desencriptado === false) {
            self::registrarFaltaManipulacion();
            return null;
        }

        return $desencriptado;
    }

    /**
     * Empaqueta un arreglo asociativo, le añade expiración y genera un token corto en archivo local.
     * 
     * @param array $params Datos a ocultar (ej: ['cedula' => 821928192, 'seccion' => 1]).
     * @param int $minutosExpiracion Minutos de validez (0 para enlaces sin expiración).
     * @return string Token hexadecimal corto de 16 caracteres.
     */
    public static function encriptarParams(array $params, int $minutosExpiracion = 60): string
    {
        $expiraEn = $minutosExpiracion > 0 ? time() + ($minutosExpiracion * 60) : null;

        if ($expiraEn !== null) {
            $params['_exp'] = $expiraEn;
        }

        $json = json_encode($params);
        if ($json === false) {
            return '';
        }

        $cadenaCifrada = self::encriptarID($json);
        if (empty($cadenaCifrada)) {
            return '';
        }

        // Token aleatorio único de 16 caracteres
        $tokenCorto = bin2hex(random_bytes(8));

        // Guardar en el almacén de archivos local
        \App\Helpers\TokenStorageHelper::guardarToken($tokenCorto, $cadenaCifrada, $expiraEn);

        return $tokenCorto;
    }

    /**
     * Desencripta el token compartible consultando el almacén de archivos local de forma segura.
     * 
     * @param string $token Token corto hexadecimal de 16 caracteres o cadena cifrada directa.
     * @return array|null Datos deserializados o NULL si el token fue manipulado / expiró.
     */
    public static function desencriptarParams(string $token): ?array
    {
        if (empty($token)) {
            return null;
        }

        $json = null;

        // 1. Si el token tiene exactamente 16 caracteres hexadecimales, es un token de archivo
        if (strlen($token) === 16 && ctype_xdigit($token)) {
            $cadenaCifrada = \App\Helpers\TokenStorageHelper::obtenerToken($token);
            if ($cadenaCifrada !== null) {
                $json = self::desencriptarID($cadenaCifrada);
            } else {
                // Si mandó 16 hex pero NO EXISTE en el archivo, intentó adivinar un token
                self::registrarFaltaManipulacion();
                return null;
            }
        } else {
            // 2. Si es una cadena larga, es un token cifrado directo
            $json = self::desencriptarID($token);
        }

        if ($json === null) {
            return null;
        }

        $params = json_decode($json, true);
        if (!is_array($params)) {
            self::registrarFaltaManipulacion();
            return null;
        }

        // Validación de expiración interna en el JSON (Paso Normal, NO cuenta como falta)
        if (isset($params['_exp'])) {
            if (time() > $params['_exp']) {
                if (strlen($token) === 16) {
                    \App\Helpers\TokenStorageHelper::eliminarToken($token);
                }
                return null; // Expiración normal
            }
            unset($params['_exp']);
        }

        return $params;
    }

    /**
     * Registra un intento de alteración/fuzzing en la caché temporal de la IP.
     * Si la IP acumula 3 faltas por alteración de tokens, SE BANEA PARA SIEMPRE.
     * 
     * @return void
     */
    private static function registrarFaltaManipulacion(): void
    {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ipSegura = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $ip);
        $archivoFaltas = self::obtenerDirBaneos() . '/fuzzing_' . $ipSegura . '.json';

        $fp = @fopen($archivoFaltas, 'c+');
        if (!$fp) return;

        if (flock($fp, LOCK_EX)) {
            $contenido = stream_get_contents($fp);
            $datos = [
                'ip'              => $ip,
                'inicio'          => date('Y-m-d H:i:s'),
                'faltas'          => 0,
                'baneado'         => false,
                'ultima_anomalia' => date('Y-m-d H:i:s')
            ];

            if (!empty($contenido)) {
                $decodificado = json_decode($contenido, true);
                if (is_array($decodificado)) {
                    $datos = $decodificado;
                }
            }

            $datos['faltas']++;
            $datos['ultima_anomalia'] = date('Y-m-d H:i:s');

            // UMBRAL: Al 3er intento de alteración de IDs -> BANEO PERMANENTE
            if ($datos['faltas'] >= 3) {
                $datos['baneado'] = true;
                $datos['fecha_baneo'] = date('Y-m-d H:i:s');
            }

            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($datos, JSON_PRETTY_PRINT));
            fflush($fp);
            flock($fp, LOCK_UN);
        }

        fclose($fp);
    }

    /**
     * Verifica si la IP actual se encuentra en la lista de BANEADOS PERMANENTES.
     * 
     * @return bool
     */
    public static function ipBaneadaPorFuzzing(): bool
    {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ipSegura = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $ip);
        $archivoFaltas = self::obtenerDirBaneos() . '/fuzzing_' . $ipSegura . '.json';

        if (!file_exists($archivoFaltas)) {
            return false;
        }

        $contenido = @file_get_contents($archivoFaltas);
        if (!$contenido) return false;

        $datos = json_decode($contenido, true);

        // Retorna TRUE si la IP está marcada como baneada (Permanente hasta que tú la borres)
        return is_array($datos) && !empty($datos['baneado']);
    }

    /**
     * DESBLOQUEO MANUAL: Método para que tú elimines el baneo a una IP específica cuando te dé la gana.
     * 
     * @param string $ip Dirección IP a desbloquear (ej: '190.200.10.5').
     * @return bool True si se eliminó el baneo.
     */
    public static function desbloquearIp(string $ip): bool
    {
        $ipSegura = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $ip);
        $archivoFaltas = self::obtenerDirBaneos() . '/fuzzing_' . $ipSegura . '.json';

        if (file_exists($archivoFaltas)) {
            return @unlink($archivoFaltas);
        }

        return false;
    }

    /**
     * Obtiene la lista completa de IPs baneadas permanentemente para mostrar en tu Panel de Admin.
     * 
     * @return array Lista de IPs con detalles de baneo.
     */
    public static function obtenerListaIpsBaneadas(): array
    {
        $dir = self::obtenerDirBaneos();
        $archivos = glob($dir . '/fuzzing_*.json');
        $lista = [];

        foreach ($archivos as $archivo) {
            $contenido = @file_get_contents($archivo);
            if ($contenido) {
                $datos = json_decode($contenido, true);
                if (is_array($datos) && !empty($datos['baneado'])) {
                    $lista[] = $datos;
                }
            }
        }

        return $lista;
    }
}
