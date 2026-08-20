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
     * Valida si una cadena representa una ruta relativa de archivo o Key de almacenamiento válida y segura.
     *
     * Documentación de reglas de validación:
     * 1. Comprueba que la cadena no esté vacía ni supere el límite de almacenamiento (255 caracteres).
     * 2. Bloquea cualquier intento de Directory Traversal ('..') y barras invertidas de Windows ('\').
     * 3. Garantiza que la ruta no comience ni termine con barra inclinada '/'.
     * 4. Valida mediante expresión regular que solo contenga caracteres seguros (a-z, A-Z, 0-9, _, -, /, .)
     *    y que posea una extensión de archivo al final.
     *
     * @param string $ruta Cadena a evaluar (ej: 'perfiles/usuario_16_17240000.jpg').
     * @return bool Retorna true si la ruta es segura y válida; false si es sospechosa o inválida.
     */
    public static function esRutaArchivo(string $ruta): bool
    {
        $ruta = trim($ruta);

        // 1. Longitud básica (las claves de BD no deben estar vacías ni saturar el campo VARCHAR)
        if (empty($ruta) || strlen($ruta) > 255) {
            return false;
        }

        // 2. Control de seguridad contra saltos de directorio o rutas absolutas del SO
        if (str_contains($ruta, '..') || str_contains($ruta, '\\')) {
            return false;
        }

        // 3. Expresión regular para rutas relativas limpias de almacenamiento (ej: carpeta/subcarpeta/archivo.ext)
        // Estructura: [nombre_carpeta/]*nombre_archivo.extension
        $patron = '/^[a-zA-Z0-9_\-]+(?:\/[a-zA-Z0-9_\-]+)*\.[a-zA-Z0-9]{2,5}$/';

        return preg_match($patron, $ruta) === 1;
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
     * Valida la integridad, tamaño y seguridad de un archivo cargado ($_FILES).
     *
     * Inspecciona los bytes mágicos del archivo mediante Fileinfo para evitar la suplantación
     * de extensiones (spoofing) y garantiza que el tipo MIME coincida con la extensión.
     *
     * @param array $archivo Arreglo proveniente de $_FILES['input_name'].
     * @param array $extensionesPermitidas Lista de extensiones en minúscula (ej: ['jpg', 'png', 'pdf']).
     * @param int $maxMegas Límite máximo permitido en Megabytes (Por defecto: 2 MB).
     * @return array{valido: bool, mensaje: string, mime?: string, extension?: string}
     */
    public static function esArchivoValido(array $archivo, array $extensionesPermitidas, int $maxMegas = 2): array
    {
        // 1. Verificación de la estructura básica del arreglo $_FILES
        if (!isset($archivo['error'], $archivo['tmp_name'], $archivo['name'], $archivo['size'])) {
            return ['valido' => false, 'mensaje' => 'La estructura del archivo cargado no es válida.'];
        }

        // 2. Control detallado de errores de carga nativos de PHP
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            $mensajesError = [
                UPLOAD_ERR_INI_SIZE   => 'El archivo excede el tamaño máximo permitido por el servidor.',
                UPLOAD_ERR_FORM_SIZE  => 'El archivo excede el límite especificado en el formulario.',
                UPLOAD_ERR_PARTIAL    => 'El archivo solo se cargó parcialmente. Intente de nuevo.',
                UPLOAD_ERR_NO_FILE     => 'No se seleccionó ningún archivo para subir.',
                UPLOAD_ERR_NO_TMP_DIR => 'Error interno: Falta la carpeta temporal en el servidor.',
                UPLOAD_ERR_CANT_WRITE => 'Error interno: No se pudo escribir el archivo en el disco.',
                UPLOAD_ERR_EXTENSION  => 'Una extensión del servidor detuvo la subida del archivo.'
            ];

            $mensaje = $mensajesError[$archivo['error']] ?? 'Error desconocido al procesar la subida del archivo.';
            return ['valido' => false, 'mensaje' => $mensaje];
        }

        // 3. Validación del tamaño máximo especificado
        $maxBytes = $maxMegas * 1024 * 1024;
        if ($archivo['size'] > $maxBytes) {
            return ['valido' => false, 'mensaje' => "El archivo supera el límite permitido de {$maxMegas} MB."];
        }

        // 4. Verificación de seguridad de carga HTTP POST
        if (!is_uploaded_file($archivo['tmp_name'])) {
            return ['valido' => false, 'mensaje' => 'El archivo no fue cargado mediante un origen HTTP POST válido.'];
        }

        // 5. Lectura del tipo MIME real desde los bytes mágicos (vía raíz global \finfo)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $tipoMimeReal = $finfo->file($archivo['tmp_name']);

        // Mapa estricto de extensiones vs MIME types aceptados por la aplicación
        $mimesAceptados = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'webp' => 'image/webp',
            'pdf'  => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'zip'  => 'application/zip'
        ];

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        // 6. Validación de la extensión contra la lista blanca entregada por parámetro
        if (!in_array($extension, $extensionesPermitidas, true)) {
            return ['valido' => false, 'mensaje' => "La extensión '.{$extension}' no está permitida."];
        }

        // 7. Verificación de coherencia entre extensión y contenido real del archivo
        if (!isset($mimesAceptados[$extension]) || $mimesAceptados[$extension] !== $tipoMimeReal) {
            return [
                'valido' => false,
                'mensaje' => 'El contenido real del archivo no coincide con su extensión o es un formato manipulado.'
            ];
        }

        // 8. Retorno exitoso enriquecido con metadatos para el servicio R2
        return [
            'valido'    => true,
            'mensaje'   => 'Archivo válido y seguro.',
            'mime'      => $tipoMimeReal,
            'extension' => $extension
        ];
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
