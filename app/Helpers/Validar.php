<?php

declare(strict_types=1);

namespace App\Helpers;

use DateTime;
use App\Libs\Seguridad;

/**
 * Clase Validar
 *
 * Proporciona métodos estáticos para la sanitización, formateo
 * y validación de diversos tipos de datos en la aplicación.
 *
 * @package App\Helpers
 */
class Validar
{
    /**
     * Valida párrafos o textos largos permitiendo signos de puntuación comunes.
     *
     * @param mixed $valor Texto a evaluar.
     * @return string|null Devuelve el texto limpio o null si es inválido.
     */
    public static function esTexto(mixed $valor): ?string
    {
        if (!is_scalar($valor)) {
            return null;
        }

        $valorTexto = trim((string)$valor);

        // Uso de \p{L} para cubrir cualquier letra unicode (acentos, diéresis, ñ)
        if (!preg_match('/^[\p{L}0-9\s\.\,\;\:\!\?\#\-\_\"\']+$/u', $valorTexto)) {
            return null;
        }

        return $valorTexto;
    }

    /**
     * Verifica si un valor es alfanumérico permitiendo caracteres especiales básicos.
     *
     * @param mixed $valor Entrada a evaluar.
     * @return string|null
     */
    public static function esAlfanumerico(mixed $valor): ?string
    {
        if (!is_scalar($valor)) {
            return null;
        }

        $valorTexto = trim((string)$valor);

        if (!preg_match('/^[\p{L}0-9\.\-\_\s]+$/u', $valorTexto)) {
            return null;
        }

        return $valorTexto;
    }

    /**
     * Valida si un valor representa un booleano válido.
     *
     * @param mixed $valor Entrada a evaluar.
     * @return bool|null Retorna bool (true/false) si es válido, o null si el formato es inválido.
     */
    public static function esBooleano(mixed $valor): ?bool
    {
        $resultado = filter_var($valor, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $resultado; // Devuelve true, false o null (inválido)
    }

    /**
     * Valida si un correo electrónico tiene formato correcto.
     *
     * @param string $email Correo electrónico.
     * @return string|null Devuelve el correo o null si es inválido.
     */
    public static function esCorreo(string $email): ?string
    {
        $emailLimpio = trim($email);
        $esValido = filter_var($emailLimpio, FILTER_VALIDATE_EMAIL);

        return $esValido !== false ? $emailLimpio : null;
    }

    /**
     * Valida nombres personales y los convierte a formato Title Case.
     *
     * @param string $string Cadena de texto con el nombre.
     * @return string|null
     */
    public static function esCadena(string $string): ?string
    {
        $string = trim($string);

        if (preg_match("/^[\p{L} ]+$/u", $string) === 1) {
            return ucwords(mb_strtolower($string, 'UTF-8'));
        }

        return null;
    }

    /**
     * Valida documentos de identidad (solo dígitos entre 7 y 9 caracteres).
     *
     * @param string $cedula
     * @return string|null
     */
    public static function esCedula(string $cedula): ?string
    {
        $cedula = trim($cedula);

        return preg_match("/^[0-9]{7,9}$/", $cedula) === 1 ? $cedula : null;
    }

    /**
     * Valida números telefónicos estándar.
     *
     * @param string $telefono
     * @return string|null
     */
    public static function esTlf(string $telefono): ?string
    {
        $telefono = trim($telefono);
        $limpio = str_replace([' ', '-'], '', $telefono);

        return preg_match("/^\+?[0-9]{7,15}$/", $limpio) === 1 ? $limpio : null;
    }

    /**
     * Valida si la longitud de una cadena se encuentra dentro de un rango determinado.
     *
     * @param string $texto
     * @param int $min
     * @param int $max
     * @return string|null
     */
    public static function esLongitudTexto(string $texto, int $min = 10, int $max = 255): ?string
    {
        $texto = trim($texto);
        $longitud = mb_strlen($texto, 'UTF-8');

        return ($longitud >= $min && $longitud <= $max) ? $texto : null;
    }

    /**
     * Escapa caracteres especiales HTML para prevenir ataques XSS en las vistas.
     *
     * @param string $dato
     * @return string
     */
    public static function escape(string $dato): string
    {
        return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Valida la estructura y coherencia cronológica de una fecha.
     *
     * @param string $fecha
     * @param string $formato Formato esperado (Ej: 'Y-m-d').
     * @return string|null
     */
    public static function esFecha(string $fecha, string $formato = 'Y-m-d'): ?string
    {
        $fecha = trim($fecha);
        $d = DateTime::createFromFormat($formato, $fecha);

        if ($d && $d->format($formato) === $fecha) {
            return $fecha;
        }

        return null;
    }

    /**
     * Valida que una contraseña cumpla con los límites de longitud.
     *
     * @param mixed $valor
     * @return string|null
     */
    public static function esPassword(mixed $valor): ?string
    {
        if (!is_scalar($valor)) {
            return null;
        }

        $valorTexto = (string)$valor;
        $longitud = mb_strlen($valorTexto);

        if ($longitud < 8 || $longitud > 255) {
            return null;
        }

        return $valorTexto;
    }

    /**
     * Valida identificadores o nombres de usuario.
     *
     * @param mixed $valor
     * @return string|null
     */
    public static function esNombreUsuario(mixed $valor): ?string
    {
        if (!is_scalar($valor)) {
            return null;
        }

        $valorTexto = trim((string)$valor);

        if (!preg_match('/^[a-zA-Z0-9\._\-]{3,30}$/', $valorTexto)) {
            return null;
        }

        return $valorTexto;
    }

    /**
     * Valida la estructura de una fecha y hora completa.
     *
     * @param mixed $valor
     * @return string|null
     */
    public static function esFechaHora(mixed $valor): ?string
    {
        return is_string($valor) ? self::esFecha($valor, 'Y-m-d H:i:s') : null;
    }

    /**
     * Valida y filtra números enteros en un rango.
     *
     * @param mixed $valor
     * @param int $min
     * @param int $max
     * @return int|null
     */
    public static function esEntero(mixed $valor, int $min = 0, int $max = PHP_INT_MAX): ?int
    {
        if (!is_scalar($valor)) {
            return null;
        }

        $valorLimpio = trim((string)$valor);
        $opciones = [
            "options" => [
                "min_range" => $min,
                "max_range" => $max
            ]
        ];

        $resultado = filter_var($valorLimpio, FILTER_VALIDATE_INT, $opciones);

        return $resultado !== false ? $resultado : null;
    }


    public static function esDesencriptarId(string $id): ?int
    {
        if (empty($id)) {
            return null;
        }
        $id = Seguridad::desencriptarID($id);
        return (int)self::esEntero($id);
    }

    /**
     * Valida y parsea valores numéricos decimales.
     *
     * @param mixed $valor
     * @return float|null
     */
    public static function esDecimal(mixed $valor): ?float
    {
        if (!is_scalar($valor)) {
            return null;
        }

        $valorLimpio = str_replace(',', '.', trim((string)$valor));
        $resultado = filter_var($valorLimpio, FILTER_VALIDATE_FLOAT);

        return $resultado !== false ? (float)$resultado : null;
    }

    /**
     * Valida la integridad y seguridad de un archivo cargado ($_FILES).
     *
     * @param array $archivo
     * @param array $extensionesPermitidas
     * @param int $maxMegas
     * @return array{valido: bool, mensaje: string}
     */
    public static function esArchivoValido(array $archivo, array $extensionesPermitidas, int $maxMegas = 2): array
    {
        if (!isset($archivo['error']) || $archivo['error'] !== UPLOAD_ERR_OK) {
            return ['valido' => false, 'mensaje' => 'Error al subir el archivo.'];
        }

        $maxBytes = $maxMegas * 1024 * 1024;
        if ($archivo['size'] > $maxBytes) {
            return ['valido' => false, 'mensaje' => "El archivo supera los {$maxMegas} MB."];
        }

        // Uso de \finfo importado correctamente desde el espacio de nombres global
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $tipoMimeReal = $finfo->file($archivo['tmp_name']);

        $mimesAceptados = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'pdf'  => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'zip'  => 'application/zip'
        ];

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $extensionesPermitidas, true)) {
            return ['valido' => false, 'mensaje' => 'Extensión no permitida.'];
        }

        if (!isset($mimesAceptados[$extension]) || $mimesAceptados[$extension] !== $tipoMimeReal) {
            return ['valido' => false, 'mensaje' => 'El contenido real del archivo no coincide con su extensión.'];
        }

        return ['valido' => true, 'mensaje' => 'Archivo válido y seguro.'];
    }

    /**
     * Valida un conjunto de datos contra un mapa de reglas definidas en esta clase.
     *
     * @param array<string, string> $reglas Mapa de campos y métodos (Ej: ['correo' => 'esCorreo'])
     * @param array|null $origenDatos Datos a evaluar. Si es null.
     * @return array{datos: array, errores: array}
     */
    public static function validarFormulario(array $reglas, ?array $origenDatos = null): array
    {

        $datosEntrada = $origenDatos;
        $datosLimpios = [];
        $errores = [];

        foreach ($reglas as $campo => $metodoValidacion) {
            $valor = $datosEntrada[$campo] ?? null;

            if (method_exists(self::class, $metodoValidacion)) {
                $resultado = self::$metodoValidacion($valor);

                // Si el método retorna null, la validación falló
                if ($resultado === null) {
                    $errores[] = "El campo '{$campo}' es inválido.";
                } else {
                    $datosLimpios[$campo] = $resultado;
                }
            } else {
                $errores[] = "La regla de validación '{$metodoValidacion}' no existe.";
            }
        }

        return [
            'datos'   => $datosLimpios,
            'errores' => $errores
        ];
    }
}
