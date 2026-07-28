<?php

declare(sytict_types=1);


namespace App\helpers;

use DateTime;


class Validar
{
    /**
     * Valida si un correo electrónico tiene formato correcto y dominio válido.
     */
    public static function esCorreo(string $email): bool
    {
        $email = trim($email);
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Valida nombres personales (Solo letras, tildes y espacios).
     * Evita números y caracteres especiales.
     */
    public static function esCadena(string $string): string|bool
    {
        $string = trim($string);

        // 1. Validamos
        if (preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/", $string) === 1) {
            // 2. Transformamos y devolvemos el texto
            return ucwords(mb_strtolower($string, 'UTF-8'));
        }

        return false;
    }

    /**
     * Valida Cédulas/DNI (solo números, longitud entre 7 y 10 dígitos).
     * Ajusta los números {7,10} según tu país.
     */
    public static function esCedula(string $cedula): bool
    {
        $cedula = trim($cedula);
        return preg_match("/^[0-9]{7,9}$/", $cedula) === 1;
    }

    /**
     * Valida números de teléfono.
     * Soporta formatos: +584121234567, 0412-1234567, 04121234567.
     */
    public static function esTlf(string $telefono): bool
    {
        $telefono = trim($telefono);
        // Permite opcionalmente el +, números, guiones y espacios
        return preg_match("/^\+?[0-9]{7,15}$/", str_replace([' ', '-'], '', $telefono)) === 1;
    }

    /**
     * Valida cadenas largas (Direcciones, descripciones).
     * Verifica que no esté vacía y tenga una longitud mínima/máxima razonable.
     */
    public static function esDireccion(string $texto, int $min = 10, int $max = 255): bool
    {
        $texto = trim($texto);
        $longitud = mb_strlen($texto);
        return $longitud >= $min && $longitud <= $max;
    }

    /**
     * Limpia un string de posibles ataques XSS .
     */
    public static function sanear(string $dato): string
    {
        return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
    }


    public static function esFechaValida(string $fecha, string $formato = 'Y-m-d'): string|bool
    {
        $fecha = trim($fecha);

        // Creamos el objeto DateTime a partir del formato esperado
        $d = DateTime::createFromFormat($formato, $fecha);

        // Verificamos:
        // 1. Que el objeto se haya creado bien.
        // 2. Que al volver a formatear el objeto coincida con el string original (evita 31 de febrero).
        if ($d && $d->format($formato) === $fecha) {
            return $fecha; // Retornamos la fecha limpia
        }

        return false;
    }
    public static function esEntero(mixed $valor, int $min = 0, int $max = PHP_INT_MAX): int|bool
    {
        $valor = trim((string)$valor);

        // FILTER_VALIDATE_INT es la forma más segura de PHP
        $opciones = ["options" => ["min_range" => $min, "max_range" => $max]];
        $resultado = filter_var($valor, FILTER_VALIDATE_INT, $opciones);

        return $resultado !== false ? $resultado : false;
    }

    public static function esDecimal(mixed $valor): float|bool
    {
        // Reemplazamos coma por punto para estandarizar
        $valor = str_replace(',', '.', trim((string)$valor));

        if (filter_var($valor, FILTER_VALIDATE_FLOAT) !== false) {
            return (float)$valor;
        }

        return false;
    }
    /**
     * Valida un archivo subido por $_FILES.
     * * @param array $archivo El elemento de $_FILES['nombre']
     * @param array $extensionesPermitidas Ej: ['jpg', 'png', 'pdf']
     * @param int $maxMegas Tamaño máximo en Megabytes
     * @return array [bool 'valido', string 'mensaje']
     */
    public static function esArchivoValido(array $archivo, array $extensionesPermitidas, int $maxMegas = 2): array
    {
        // 1. Verificar si hubo error en la subida
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return ['valido' => false, 'mensaje' => 'Error al subir el archivo.'];
        }

        // 2. Validar tamaño (Convertimos MB a Bytes)
        $maxBytes = $maxMegas * 1024 * 1024;
        if ($archivo['size'] > $maxBytes) {
            return ['valido' => false, 'mensaje' => "El archivo supera los $maxMegas MB."];
        }

        // 3. VALIDACIÓN DE SEGURIDAD: Tipo MIME real
        // No confíes en $archivo['type'], es fácil de falsificar.
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $tipoMimeReal = $finfo->file($archivo['tmp_name']);

        // Mapeo de extensiones comunes a sus tipos MIME reales
        $mimesAceptados = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'pdf'  => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'zip'  => 'application/zip'
        ];

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        // 4. Verificar si la extensión está permitida y coincide con su contenido real
        if (!in_array($extension, $extensionesPermitidas)) {
            return ['valido' => false, 'mensaje' => 'Extensión no permitida.'];
        }

        if (!isset($mimesAceptados[$extension]) || $mimesAceptados[$extension] !== $tipoMimeReal) {
            return ['valido' => false, 'mensaje' => 'El contenido del archivo no coincide con su extensión.'];
        }

        return ['valido' => true, 'mensaje' => 'Archivo seguro.'];
    }

    /**
     * Sanitiza y valida múltiples campos de forma automática.
     * * @param array $reglas Arreglo asociativo con el campo y su regla de validación.
     * Ej: ['nombre' => 'esCadena', 'correo' => 'esCorreo']
     * @return array Devuelve un array asociativo con dos llaves: 
     * 'datos' (los valores limpios con trim) y 'errores' (lista de fallos encontrados).
     */
    public static function validarFormulario(array $reglas): array
    {
        $datosLimpios = [];
        $errores = [];

        foreach ($reglas as $campo => $metodoValidacion) {
            // 1. Aplicamos el trim automático manejando si el campo no existe en $_POST
            $valor = trim($_POST[$campo] ?? '');
            $datosLimpios[$campo] = $valor;

            // 2. Evaluamos dinámicamente el método de validación de esta misma clase
            if (method_exists(self::class, $metodoValidacion)) {
                if (!self::$metodoValidacion($valor)) {
                    // Si falla, agregamos un mensaje genérico o personalizado usando el nombre del campo
                    $errores[] = "El campo {$campo} es inválido.";
                }
            } else {
                $errores[] = "Regla de validación '{$metodoValidacion}' no existe.";
            }
        }

        return [
            'datos'   => $datosLimpios,
            'errores' => $errores
        ];
    }
}
