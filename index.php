<?php

declare(strict_types=1);

// Configuración de la zona horaria oficial para el sistema
date_default_timezone_set('America/Caracas');

// Cabeceras HTTP para impedir el almacenamiento en caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Autocargador de dependencias y clases mediante Composer
require_once 'vendor/autoload.php';

use App\Controllers\AuthController;
use App\Controllers\CuentaController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Controllers\RegistroController;
use App\Controllers\TerminosController;
use App\Controllers\UsuariosController;
use App\Libs\Enrutador;
use App\Libs\ManejadorExcepciones;



ManejadorExcepciones::registrar();

Enrutador::get('/', [HomeController::class, 'index']);
Enrutador::get('home', [HomeController::class, 'index']);

Enrutador::get('login', [AuthController::class, 'login']);
Enrutador::get('logout', [AuthController::class, 'logout']);
Enrutador::get('logout', [AuthController::class, 'logout']);
Enrutador::post('login/auth', [AuthController::class, 'autenticar']);
Enrutador::get('recuperar', [AuthController::class, 'recuperarClave']);
Enrutador::post('token', [AuthController::class, 'enviarRecuperacion']);
Enrutador::post('cuenta/recuperada', [AuthController::class, 'actualizarUsuarioContrasena']);

Enrutador::get('recibido', [AuthController::class, 'vistaColocarCodigo']);
Enrutador::post('token/verificar', [AuthController::class, 'tokenRecibido']);

Enrutador::get('registro', [RegistroController::class, 'registro']);
Enrutador::post('registro/guardar', [RegistroController::class, 'guardarRegistro']);

Enrutador::get('terminos', [TerminosController::class, 'index']);

Enrutador::get('cuenta', [CuentaController::class, 'index'], [1]);
Enrutador::post('cuenta/actualizar-usuario', [CuentaController::class, 'actualizarUsuario'], [1]);
Enrutador::post('cuenta/solicitar-correo', [CuentaController::class, 'solicitarCodigoCorreo'], [1]);
Enrutador::post('cuenta/verificar-correo', [CuentaController::class, 'verificarCodigoCorreo'], [1]);
Enrutador::post('cuenta/cambiar-contrasena', [CuentaController::class, 'cambiarContrasena'], [1]);
Enrutador::post('foto/guardar', [CuentaController::class, 'cambiarFotoPerfil'], [1]);
Enrutador::get('foto', [CuentaController::class, 'fotoPerfil'], [1]);



Enrutador::get('perfil', [UsuariosController::class, 'perfil'], [1]);
Enrutador::get('perfil/', [UsuariosController::class, 'perfil'], [1]);
Enrutador::post('perfil/guardar', [UsuariosController::class, 'guardar'], [1]);
Enrutador::post('perfil/actualizar', [UsuariosController::class, 'actualizar'], [1]);
Enrutador::get('laboral', [UsuariosController::class, 'datosLaborales'], [1]);
Enrutador::post('buscar/cargos', [UsuariosController::class, 'cargos'], [1]);
Enrutador::post('laboral/guardar', [UsuariosController::class, 'guardarDatosLaborales'], [1]);
Enrutador::post('laboral/actualizar', [UsuariosController::class, 'actualizarDatosLaborales'], [1]);

Enrutador::get('dashboard', [DashboardController::class, 'index'], [1]);


Enrutador::despachar();
