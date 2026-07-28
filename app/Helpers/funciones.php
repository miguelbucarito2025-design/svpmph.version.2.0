<?php

function buscar_usuario($conn, $tabla)
{
    $sql = "SELECT * FROM $tabla";
    $pdo = $conn->prepare($sql);
    $resul = $pdo->execute();
    return $resul = $pdo;
}


function isertarPago($cedula, $db)
{

    if (cantidad($db->getConnet(), "pago_confirmacion where cedula_id={$cedula}") < 1) {
        $conn = $db->getConnet();
        $sql = "INSERT INTO pago_confirmacion(cedula_id, pago_1, pago_2, pago_3, pago_4, pago_5, pago_6, pago_7, pago_8, pago_9, pago_10, pago_11, pago_12) VALUES  (?,'','','','','','','','','','','','')";
        $pdo = $conn->prepare($sql);
        $pdo->execute([$cedula]);
        return 0;
    } else {
        return 1;
    }
}
function validarImagen($file)
{
    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    return in_array($file['type'], $tiposPermitidos);
}

function buscar($conn, $tabla)
{
    $sql = "SELECT * FROM $tabla";
    $pdo = $conn->prepare($sql);
    $resul = $pdo->execute();
    return $resul = $pdo;
}

function eliminarArchivo(string $ruta)
{
    if (file_exists($ruta)) {
        if (unlink($ruta)) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}


function sql($conn, $consulta)
{
    $sql = $consulta;
    $pdo = $conn->prepare($sql);
    $resul = $pdo->execute();
    return $resul = $pdo;
}
function sql_2($conn, $consulta)
{
    $sql = $consulta;
    $pdo = $conn->prepare($sql);
    $pdo->execute();
    return $pdo->fetch(PDO::FETCH_ASSOC);
}
function sql_3($conn, $consulta)
{
    $sql = $consulta;
    $pdo = $conn->prepare($sql);
    $pdo->execute();
    return $pdo->fetchAll(PDO::FETCH_ASSOC);
}
function value_string(string $variable)
{
    // Elimina espacios al inicio y al final
    $variable = trim($variable);

    // Elimina etiquetas HTML y PHP
    $variable = strip_tags($variable);

    // Convierte caracteres especiales en entidades HTML
    $variable = htmlspecialchars($variable, ENT_QUOTES, 'UTF-8');

    // Opcional: elimina caracteres no imprimibles
    $variable = preg_replace('/[\x00-\x1F\x7F]/u', '', $variable);

    return ucwords(strtolower($variable));
}

function value_int(int $variable)
{
    $variable_filtrada = is_int($variable) ? filter_var($variable, FILTER_SANITIZE_NUMBER_INT) : false;

    return $variable_filtrada;
}

function value_email(string $variable)
{
    $variable_filtrada = filter_var($variable, FILTER_SANITIZE_EMAIL);
    return $variable_filtrada;
}


function calcularEdad($fechaNacimiento)
{
    // Crear un objeto DateTime con la fecha de nacimiento
    $fechaNac = new DateTime($fechaNacimiento);

    // Crear un objeto DateTime con la fecha actual
    $hoy = new DateTime();

    // Calcular la diferencia entre ambas fechas
    $edad = $hoy->diff($fechaNac);

    // Retornar la edad en años
    return $edad->y;
}

function quitarAcentos($cadena)
{
    $acentos = array(
        'á' => 'a',
        'é' => 'e',
        'í' => 'i',
        'ó' => 'o',
        'ú' => 'u',
        'Á' => 'A',
        'É' => 'E',
        'Í' => 'I',
        'Ó' => 'O',
        'Ú' => 'U',
        'ñ' => 'n',
        'Ñ' => 'N'
    );
    return strtr($cadena, $acentos);
}

function decimal($valor, $limite)
{
    // Verifica si el valor es numérico
    if (!is_numeric($valor)) {
        return 'Valor inválido';
    }

    // Convierte a float y redondea a 2 decimales
    return number_format((float)$valor, $limite, '.', '');
}

function cantidad($connet, $sql)
{

    $resul = buscar($connet, $sql);
    return $resul->rowCount();
}


function fetchAll($connet, $sql)
{

    $buscar = buscar($connet, $sql);
    $resultado = $buscar->fetchAll(PDO::FETCH_ASSOC);
    return  $resultado;
}



function fetch($connet, $sql)
{

    $buscar = buscar($connet, $sql);
    $resultado = $buscar->fetch(PDO::FETCH_ASSOC);
    return  $resultado;
}


function resul_pagos($var)
{

    if ($var == 1) {
        echo '../multimedia/icon/success.png';
    } else if ($var == 2) {
        echo '../multimedia/icon/question.png';
    } else if ($var == 3) {
        echo '../multimedia/icon/error.png';
    } else {
        echo '';
    }
}

function resulPagos($libroMenor, $libroMayor)
{

    if ($libroMenor > 0 and $libroMayor > 1) {
        return '<div class="question table_icon"></div>';
    } else if ($libroMenor > 0 and $libroMayor == 1) {
        return '<div class="succes table_icon"> </div>';
    } else if ($libroMenor == 0  and $libroMayor > 0) {
        return '<div class="error table_icon"></div>';
    } else {
        return '';
    }
}

function value_sql(PDO $pdo, string $consulta): string|false
{
    $consulta = trim($consulta);

    // Validación: no vacía
    if ($consulta === '') {
        return false;
    }

    // Validación: debe comenzar con una palabra SQL válida (CRUD)
    if (!preg_match('/^(SELECT|INSERT|UPDATE|DELETE)\s/i', $consulta)) {
        return false;
    }

    // Validación: debe terminar con punto y coma
    if (substr($consulta, -1) !== ';') {
        return false;
    }

    // Validación sintáctica con prepare()
    try {
        $stmt = $pdo->prepare($consulta);
    } catch (Throwable $e) {
        return false;
    }

    return $consulta;
}
function registrarError($mensaje)
{
    $fecha = date("Y-m-d H:i:s");
    $log = "[$fecha] ERROR: $mensaje" . PHP_EOL; // PHP_EOL es un salto de línea
    file_put_contents("errores_sistema.txt", $log, FILE_APPEND);
}
