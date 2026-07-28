<?php

declare(sytict_types=1);

require_once 'vendor/autoload.php';


use App\Helpers\EnvLoader;
use App\Libs\Exceptions\AppException;
use App\Models\UserModel;

EnvLoader::load('app/Config/.env');

$pdo = new UserModel();




















/* header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include_once 'autoload.php';

envLoader::load('.env');
Seguridad::init();



session_start();
$_SESSION['empleado'] = $_SESSION['empleado'] ?? 'invitado';

Seguridad::detectorDeBots();

if (!isset($_POST['method'])) {
    $tokent = bin2hex(random_bytes(32));
    $_SESSION['token'] = $tokent;
}


$url =   $_GET['action'] ?? 'ini/';
$parametros = Seguridad::setParams($url, '/');
$views = $parametros[0] ?? 'ini/';
$param = $parametros[1] ?? '';

$controller = new serverAppController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_POST['method'])) {
        Seguridad::responderError("Paquete vacío", "Error de Recepción", 400);
    }

    $metodoDesencriptado = (string) trim(Seguridad::desencriptarID($_POST['method']));
    $parametrosAction = Seguridad::setParams($metodoDesencriptado, '/');

    $tokenValidar  = $parametrosAction[0] ?? '';
    $model   = $parametrosAction[1] ?? null;
    $accionMetodo  = $parametrosAction[2] ?? null;
    $tokenSession  = (string) ($_SESSION['token'] ?? '');


    if (hash_equals($tokenSession, $tokenValidar)) {
        try {
            $datosRetorno = $controller->action($model, $accionMetodo);

            Seguridad::responderExito($_SESSION['title']  ?? '', $datosRetorno, $_SESSION['message'] ?? 'Acción Exitosa');
            $_SESSION['title'] = '';
            $_SESSION['message'] = '';
        } catch (RegistroDuplicadoException $e) {

            Seguridad::responderError($e->getMessage(), "Registro Duplicado", 400);
        } catch (Throwable $e) {

            Seguridad::responderError("Error interno: " . $e->getMessage(), "Error de Sistema", 500);
        }
    } else {
        Seguridad::responderError("Token de seguridad inválido o expirado (Ataque CSRF evitado).", "Acceso Denegado", 403);
    }
} else {

    return $controller->render($views, $param);
}
 */