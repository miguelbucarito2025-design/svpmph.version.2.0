<?php

declare(strict_types=1);

namespace App\Helpers;

use DateTime;

class Validar
{


    /**
     * Valida textos largos o párrafos permitiendo signos de puntuación comunes.
     */
    public static function esTexto(mixed $valor): mixed
    {
        $valorTexto = trim((string)$valor);

        // Permite letras (con acentos/ñ), números, espacios y signos de puntuación comunes
        if (!preg_match('/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\.\,\;\:\!\?\-\_\"\']+$/u', $valorTexto)) {
            return false;
        }

        return $valorTexto;
    }

    /**
     * verifica si un valor en alfanumerico 
     * 
     */
    public static function esAlfanumerico(mixed $valor): mixed
    {
        $valorTexto = trim((string)$valor);
        // Permite letras, números, puntos, guiones y espacios
        if (!preg_match('/^[a-zA-Z0-9\.\-\_\s]+$/', $valorTexto)) {
            return false;
        }
        return $valorTexto;
    }

    /**
     * Valida si un valor representa un booleano válido y devuelve su equivalente estricto (bool).
     *
     * @param mixed $valor Entrada a evaluar (bool, int, string)
     * @return bool|false Retorna bool (true/false) si es válido, o false si no es un booleano.
     */
    public static function esBooleano(mixed $valor): mixed
    {
        // filter_var con FILTER_VALIDATE_BOOLEAN procesa "1", "true", "on", "yes", 1, true (y sus variantes)
        // FILTER_NULL_ON_FAILURE hace que devuelva null si el valor no es un booleano válido
        $resultado = filter_var($valor, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($resultado === null) {
            return false; // Formato inválido para el bucle de validarCampos
        }

        return $resultado; // Devuelve true o false estricto
    }


    /**
     * Valida si un correo electrónico tiene formato correcto.
     */
    public static function esCorreo(string $email): bool
    {
        $email = trim($email);
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Valida nombres personales (Solo letras de cualquier idioma, tildes y espacios).
     * Devuelve el string formateado (Title Case) o false si es inválido.
     */
    public static function esCadena(string $string): string|bool
    {
        $string = trim($string);

        // Regex Unicode compatible con tildes, letras eñes y espacios
        if (preg_match("/^[\p{L} ]+$/u", $string) === 1) {
            return ucwords(mb_strtolower($string, 'UTF-8'));
        }

        return false;
    }

    /**
     * Valida Cédulas/DNI (solo números, longitud entre 7 y 9 dígitos).
     */
    public static function esCedula(string $cedula): bool
    {
        $cedula = trim($cedula);
        return preg_match("/^[0-9]{7,9}$/", $cedula) === 1;
    }

    /**
     * Valida números de teléfono (formatos: +584121234567, 0412-1234567, 04121234567).
     */
    public static function esTlf(string $telefono): bool
    {
        $telefono = trim($telefono);
        $limpio = str_replace([' ', '-'], '', $telefono);
        return preg_match("/^\+?[0-9]{7,15}$/", $limpio) === 1;
    }

    /**
     * Valida cadenas de texto según rango de longitud.
     */
    public static function esLongitudTexto(string $texto, int $min = 10, int $max = 255): bool
    {
        $texto = trim($texto);
        $longitud = mb_strlen($texto, 'UTF-8');
        return $longitud >= $min && $longitud <= $max;
    }

    /**
     * Escapa caracteres HTML para la SALIDA en vistas (XSS).
     */
    public static function escape(string $dato): string
    {
        return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Valida si un string cumple un formato de fecha real.
     */
    public static function esFecha(string $fecha, string $formato = 'Y-m-d'): string|bool
    {
        $fecha = trim($fecha);
        $d = DateTime::createFromFormat($formato, $fecha);

        if ($d && $d->format($formato) === $fecha) {
            return $fecha;
        }

        return false;
    }


    /**
     * Valida si una contraseña en texto plano cumple con las reglas de seguridad.
     *
     * @param mixed $valor La contraseña que envía el usuario
     * @return string|false Devuelve la contraseña sin modificar si pasa los filtros, o false si falla.
     */
    public static function esPassword(mixed $valor): mixed
    {
        // En contraseñas NO usamos trim() previo si los espacios iniciales/finales fueran intencionales,
        // pero aseguramos que sea cadena.
        $valorTexto = (string)$valor;

        // Regla 1: Longitud mínima (ejemplo: mínimo 8 caracteres, máximo 255)
        if (mb_strlen($valorTexto) < 8 || mb_strlen($valorTexto) > 255) {
            return false;
        }

        // Opcional (Regla estricta): Debe tener al menos una letra y un número
        // if (!preg_match('/[A-Za-z]/', $valorTexto) || !preg_match('/[0-9]/', $valorTexto)) {
        //     return false;
        // }

        return $valorTexto;
    }






    /**
     * Valida nombres de usuario (usernames).
     * Permite letras, números, guiones, guiones bajos y puntos. Sin espacios.
     *
     * @param mixed $valor Ejemplo: 'miguel_2026', 'admin.user', 'user-123'
     * @return string|false
     */
    public static function esNombreUsuario(mixed $valor): mixed
    {
        $valorTexto = trim((string)$valor);

        // Permite alfanuméricos, guion bajo, guion y punto. Entre 3 y 30 caracteres.
        if (!preg_match('/^[a-zA-Z0-9\._\-]{3,30}$/', $valorTexto)) {
            return false;
        }

        return $valorTexto;
    }




    /**
     * Valida rutas de archivos o directorios locales/relativos.
     *
     * @param mixed $valor Ejemplo: 'uploads/documentos/carnet_2026.pdf' o '/var/www/archivos/'
     * @return string|false
     */
    public static function esRutaArchivo(mixed $valor): mixed
    {
        $valorTexto = trim((string)$valor);

        if ($valorTexto === '') {
            return false;
        }

        // Permite letras, números, /, \, ., _, -, espacios y paréntesis
        if (!preg_match('/^[a-zA-Z0-9\/\_\\\\\.\-\s\(\)]+$/u', $valorTexto)) {
            return false;
        }

        return $valorTexto;
    }


    public static function esFechaHora(mixed $valor): mixed
    {
        return self::esFecha($valor, 'Y-m-d H:i:s');
    }
    /**
     * Valida y parsea números enteros dentro de un rango determinado.
     */
    public static function esEntero(mixed $valor, int $min = 0, int $max = PHP_INT_MAX): int|bool
    {
        $valorLimpio = trim((string)$valor);

        $opciones = [
            "options" => [
                "min_range" => $min,
                "max_range" => $max
            ]
        ];

        $resultado = filter_var($valorLimpio, FILTER_VALIDATE_INT, $opciones);

        return $resultado !== false ? $resultado : false;
    }

    /**
     * Valida y parsea números decimales (acepta comas como separadores).
     */
    public static function esDecimal(mixed $valor): float|bool
    {
        $valorLimpio = str_replace(',', '.', trim((string)$valor));

        $resultado = filter_var($valorLimpio, FILTER_VALIDATE_FLOAT);

        return $resultado !== false ? (float)$resultado : false;
    }

    /**
     * Valida la subida segura de un archivo ($_FILES).
     */
    public static function esArchivoValido(array $archivo, array $extensionesPermitidas, int $maxMegas = 2): array
    {
        if (!isset($archivo['error']) || $archivo['error'] !== UPLOAD_ERR_OK) {
            return ['valido' => false, 'mensaje' => 'Error al subir el archivo.'];
        }

        $maxBytes = $maxMegas * 1024 * 1024;
        if ($archivo['size'] > $maxBytes) {
            return ['valido' => false, 'mensaje' => "El archivo supera los $maxMegas MB."];
        }

        // Se usa finfo con namespace global
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
     * Valida múltiples campos de un arreglo asociativo contra las reglas de esta clase.
     * 
     * @param array $reglas Ej: ['nombre' => 'esCadena', 'correo' => 'esCorreo']
     * @param array|null $origenDatos Datos a validar (si es null toma $_POST por defecto)
     */
    public static function validarFormulario(array $reglas, ?array $origenDatos = null): array
    {
        $datosEntrada = $origenDatos ?? $_POST;
        $datosLimpios = [];
        $errores = [];

        foreach ($reglas as $campo => $metodoValidacion) {
            $valor = trim((string)($datosEntrada[$campo] ?? ''));
            $datosLimpios[$campo] = $valor;

            if (method_exists(self::class, $metodoValidacion)) {
                // Ejecutamos la validación
                $resultado = self::$metodoValidacion($valor);

                if ($resultado === false) {
                    $errores[] = "El campo '{$campo}' es inválido.";
                } else if (is_string($resultado) || is_int($resultado) || is_float($resultado)) {
                    // Si el método transformó el dato (como esCadena que hace ucwords), guardamos el dato formateado
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
