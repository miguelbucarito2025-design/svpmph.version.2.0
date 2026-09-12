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

use App\Controllers\AsignaturasController;
use App\Controllers\AuthController;
use App\Controllers\CuentaController;
use App\Controllers\DashboardController;
use App\Controllers\ErrrorController;
use App\Controllers\FacilitadorController;
use App\Controllers\HomeController;
use App\Controllers\NucleoController;
use App\Controllers\OfertasController;
use App\Controllers\ProgramasController;
use App\Controllers\RegistroController;
use App\Controllers\RolesController;
use App\Controllers\TerminosController;
use App\Controllers\UsuariosController;
use App\Libs\Enrutador;
use App\Libs\ManejadorExcepciones;
use App\Helpers\EnvLoader;


EnvLoader::load('app/Config/.env');


ManejadorExcepciones::registrar();

Enrutador::get('/', [HomeController::class, 'index']);
Enrutador::get('home', [HomeController::class, 'index']);

Enrutador::get('login', [AuthController::class, 'login']);
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
Enrutador::post('terminos/vincular', [TerminosController::class, 'vincular'], [1]);


Enrutador::get('cuenta', [CuentaController::class, 'index'], [1, 2, 3, 4, 5]);
Enrutador::post('cuenta/actualizar-usuario', [CuentaController::class, 'actualizarUsuario'], [1, 2, 3, 4, 5]);
Enrutador::post('cuenta/solicitar-correo', [CuentaController::class, 'solicitarCodigoCorreo'], [1, 2, 3, 4, 5]);
Enrutador::post('cuenta/verificar-correo', [CuentaController::class, 'verificarCodigoCorreo'], [1, 2, 3, 4, 5]);
Enrutador::post('cuenta/cambiar-contrasena', [CuentaController::class, 'cambiarContrasena'], [1, 2, 3, 4, 5]);
Enrutador::post('foto/guardar', [CuentaController::class, 'cambiarFotoPerfil'], [1, 2, 3, 4, 5]);
Enrutador::get('foto', [CuentaController::class, 'fotoPerfil'], [1, 2, 3, 4, 5]);
Enrutador::post('cuenta/cambiar', [CuentaController::class, 'actualizarDatos'], [5]);
Enrutador::post('usuario/eliminar', [CuentaController::class, 'eliminarMasivo'], [5]);

Enrutador::post('programas/guardar', [ProgramasController::class, 'guardar'], [5]);
Enrutador::post('programas/paginar', [ProgramasController::class, 'buscar'], [5]);
Enrutador::post('programas/actualizar', [ProgramasController::class, 'actualizar'], [5]);
Enrutador::post('programas/eliminacionMultiple', [ProgramasController::class, 'eliminarMasivo'], [5]);

Enrutador::get('programas', [ProgramasController::class, 'index'], [5]);

Enrutador::get('nucleos', [NucleoController::class, 'index'], [5]);
Enrutador::post('nucleos/paginar', [NucleoController::class, 'buscar'], [5]);
Enrutador::post('nucleos/guardar', [NucleoController::class, 'guardar'], [5]);
Enrutador::post('nucleos/actualizar', [NucleoController::class, 'actualizar'], [5]);
Enrutador::post('nucleos/eliminacionMultiple', [NucleoController::class, 'eliminarMasivo'], [5]);
Enrutador::post('nucleo/buscar', [NucleoController::class, 'traerTodos'], [5]);

Enrutador::get('asignaturas', [AsignaturasController::class, 'index'], [5]);
Enrutador::post('asignaturas/paginar', [AsignaturasController::class, 'buscar'], [5]);
Enrutador::post('asignaturas/guardar', [AsignaturasController::class, 'guardar'], [5]);
Enrutador::post('asignaturas/actualizar', [AsignaturasController::class, 'actualizar'], [5]);
Enrutador::post('asignatura/eliminacionMultiple', [AsignaturasController::class, 'eliminarMasivo'], [5]);

Enrutador::get('ofertas', [OfertasController::class, 'index'], [5]);
Enrutador::post('ofertas/paginar', [OfertasController::class, 'buscar'], [5]);
Enrutador::post('ofertas/guardar', [OfertasController::class, 'guardar'], [5]);
Enrutador::post('ofertas/actualizar', [OfertasController::class, 'actualizar'], [5]);
Enrutador::post('ofertas/eliminacionMultiple', [OfertasController::class, 'eliminarMasivo'], [5]);
Enrutador::post('oferta/buscar', [OfertasController::class, 'select'], [5]);

Enrutador::get('roles', [RolesController::class, 'index'], [5]);
Enrutador::post('roles/paginar', [RolesController::class, 'buscar'], [5]);
Enrutador::post('roles/guardar', [RolesController::class, 'guardar'], [5]);
Enrutador::post('roles/actualizar', [RolesController::class, 'actualizar'], [5]);
Enrutador::post('roles/eliminacionMultiple', [RolesController::class, 'eliminarMasivo'], [5]);
Enrutador::post('rol/cambiar', [RolesController::class, 'cambiar'], [5]);


Enrutador::get('perfil', [UsuariosController::class, 'perfil'], [1, 2, 3, 4, 5]);
Enrutador::get('perfil/', [UsuariosController::class, 'perfil'], [1, 2, 3, 4, 5]);
Enrutador::get('laboral', [UsuariosController::class, 'datosLaborales'], [1, 2, 3, 4, 5]);
Enrutador::get('usuarios', [UsuariosController::class, 'index'], [5]);
Enrutador::post('perfil/guardar', [UsuariosController::class, 'guardar'], [1, 2, 3, 4, 5]);
Enrutador::post('perfil/actualizar', [UsuariosController::class, 'actualizar'], [1, 2, 3, 4, 5]);
Enrutador::post('buscar/cargos', [UsuariosController::class, 'cargos'], [1, 2, 3, 4, 5]);
Enrutador::post('laboral/guardar', [UsuariosController::class, 'guardarDatosLaborales'], [1, 2, 3, 4, 5]);
Enrutador::post('laboral/actualizar', [UsuariosController::class, 'actualizarDatosLaborales'], [1, 2, 3, 4, 5]);
Enrutador::post('usuarios/paginar', [UsuariosController::class, 'paginar'], [5]);

Enrutador::get('dashboard', [DashboardController::class, 'index'], [1, 2, 3, 4, 5]);

Enrutador::get('facilitadores', [FacilitadorController::class, 'index'], [5, 4]);
Enrutador::post('facilitador/paginar', [FacilitadorController::class, 'paginar'], [5, 4]);
Enrutador::post('facilitador/guardar', [FacilitadorController::class, 'save'], [5, 4]);
Enrutador::post('facilitador/actualizar', [FacilitadorController::class, 'update'], [5, 4]);
Enrutador::post('facilitador/opciones', [FacilitadorController::class, 'traerPorUsuario'], [5, 4]);
Enrutador::post('facilitador/eliminar', [FacilitadorController::class, 'delete'], [5, 4]);

Enrutador::get('logs', [ErrrorController::class, 'verErrores'], [5]);
Enrutador::get('errror/obtenerLogs', [ErrrorController::class, 'obtenerLogs'], [5]);
Enrutador::post('errror/limpiarLogs', [ErrrorController::class, 'limpiarLogs'], [5]);

Enrutador::despachar();
