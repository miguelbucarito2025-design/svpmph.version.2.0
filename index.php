<?php

declare(strict_types=1);
//zona horaria de venezula para la base de datos 
date_default_timezone_set('America/Caracas');


// Forzar al navegador y a los proxys intermedios a no almacenar nada en caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Fecha pasada en el tiempo

//el auto cargador de clases con composer
require_once 'vendor/autoload.php';

use App\Controllers\AuthController;
use App\Libs\Enrutador;
use App\Controllers\HomeController;
use App\Controllers\RegistroController;
use App\Libs\ManejadorExcepciones;
use App\Controllers\TerminosController;

// Sintaxis: Enrutador::METODO('ruta/accion', [Controlador::class, 'metodo'], ['ROLES_PERMITIDOS']);

ManejadorExcepciones::registrar();

Enrutador::get('/', [HomeController::class, 'index']);
Enrutador::get('home', [HomeController::class, 'index']);

Enrutador::get('login', [AuthController::class, 'login']);
Enrutador::get('logout', [AuthController::class, 'logout']);
Enrutador::post('login/auth', [AuthController::class, 'autenticar']);
Enrutador::get('recuperar', [AuthController::class, 'recuperarClave']);
Enrutador::post('token', [AuthController::class, 'enviarRecuperacion']);
Enrutador::get('resivido', [AuthController::class, 'resividoToken']);
Enrutador::post('token/verificar', [AuthController::class, 'tokenResivido']);

Enrutador::post('registro/guardar', [RegistroController::class, 'guardarRegistro']);
Enrutador::get('registro', [RegistroController::class, 'registro']);
Enrutador::get('terminos', [TerminosController::class, 'index']);

Enrutador::despachar();
