<?php

declare(strict_types=1);

/**
 * Script Simulador de Peticiones HTTP para Depurar el Enrutador, Tokens y APIs con Xdebug.
 * Ejecución desde consola: php debug_api.php
 */

date_default_timezone_set('America/Caracas');

// Activar búfer de salida para evitar errores de "headers already sent"
ob_start();

require_once __DIR__ . '/vendor/autoload.php';

use App\Helpers\EnvLoader;
use App\Helpers\TokenStorageHelper;
use App\Libs\Enrutador;
use App\Libs\Seguridad;
use App\Libs\Session;
use App\Models\ArchivosModel;

// 1. Cargar variables de entorno
EnvLoader::load(__DIR__ . '/app/Config/.env');

// ============================================================================
// SIMULACIÓN DE CABECERAS Y ENTORNO HTTP REAL
// ============================================================================
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME']    = '/index.php';
$_SERVER['HTTP_HOST']      = 'localhost';
$_SERVER['REMOTE_ADDR']    = '127.0.0.1';

// Simular Navegador Real
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36';

// Simular Cabeceras de Origen y AJAX
$_SERVER['HTTP_ORIGIN']           = 'http://localhost';
$_SERVER['HTTP_REFERER']          = 'http://localhost/archivos';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

// 2. SIMULACIÓN DE SESIÓN (EJECUTADA ANTES DE CUALQUIER OUTPUT)
$session = new Session();
$session->start();

$_SESSION['usuario_id']        = 19;
$_SESSION['usuario_rol']       = 5; // Rol autorizado (ej: Admin)
$_SESSION['ultimo_acceso']     = time();
$_SESSION['_user_fingerprint'] = hash('sha256', $_SERVER['HTTP_USER_AGENT']);

$tokenCsrf = $session->asegurarTokenCSRF();

// ============================================================================
// EXTRAER DATOS REALES DESDE EL MODELO DE ARCHIVOS
// ============================================================================
$model = new ArchivosModel();
$archivos = $model->traerTodos();

if (empty($archivos) || !is_array($archivos)) {
    ob_clean();
    die("[!] ERROR: No se encontraron archivos registrados en la base de datos para realizar la prueba.\n");
}

// Tomamos el primer archivo real registrado en la BD
$archivoPrueba = $archivos[0];
$r2KeyReal     = $archivoPrueba['url'];

if (empty($r2KeyReal)) {
    ob_clean();
    die("[!] ERROR: El registro recuperado de la BD no contiene una columna de clave/ruta válida.\n");
}

// Generamos el token de prueba con la KEY REAL de R2
$tokenPrueba = Seguridad::encriptarParams([
    'r2_key'     => $r2KeyReal,
    'usuario_id' => $_SESSION['usuario_id']
], 15);

// Asignamos la URI simulada con el token dinámico
$_SERVER['REQUEST_URI'] = '/archivos/ver/' . $tokenPrueba;

// Limpiamos la salida del búfer antes de mostrar los logs de consola
ob_clean();

echo "[DEBUG] Archivo seleccionado de la BD: ID {$archivoPrueba['id']} | Key: {$r2KeyReal}\n";
echo "[DEBUG] Token de prueba generado: {$tokenPrueba}\n";
echo "[DEBUG] Evaluando URI: {$_SERVER['REQUEST_URI']}\n\n";

// ============================================================================
// REGISTRO DE RUTAS EN EL ENRUTADOR
// ============================================================================

// Registrar rutas asociadas
Enrutador::get('archivos/ver', [\App\Controllers\ArchivosController::class, 'obtener'], [1, 2, 3, 4, 5]);

foreach ($archivos as $a) {
    if (!empty($a['url'])) {
        Enrutador::get($a['url'], [\App\Controllers\ArchivosController::class, 'index'], [1, 2, 3, 4, 5]);
    }
}

// ============================================================================
// EJECUCIÓN CON XDEBUG
// ============================================================================
try {
    echo "[DEBUG] Despachando ruta con el token...\n";
    Enrutador::despachar();

    echo "\n[DEBUG] Petición procesada correctamente.\n";
} catch (\Throwable $e) {
    echo "\n[!] EXCEPCIÓN DETECTADA:\n";
    echo "Código: " . $e->getCode() . "\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . " (Línea " . $e->getLine() . ")\n";
}

// Limpieza manual de tokens expirados de prueba
TokenStorageHelper::purgarTokensExpirados();
