<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php'; // Ajusta la ruta a tu autoload/config

use App\Traits\MensageTrait;
use App\Libs\ManejadorExcepciones;
use App\Helpers\EnvLoader;
use App\Helpers\Notificaciones;
use App\Libs\Session;

EnvLoader::load(__DIR__ . '/../app/Config/.env');
ManejadorExcepciones::registrar();
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
$_SERVER['HTTP_REFERER']          = 'http://localhost/cron';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

// 2. SIMULACIÓN DE SESIÓN (EJECUTADA ANTES DE CUALQUIER OUTPUT)
$session = new Session();
$session->start();

$_SESSION['usuario_id']        = 19;
$_SESSION['usuario_rol']       = 5; // Rol autorizado (ej: Admin)
$_SESSION['ultimo_acceso']     = time();
$_SESSION['_user_fingerprint'] = hash('sha256', $_SERVER['HTTP_USER_AGENT']);

class ProcesadorColaCorreos
{
    use MensageTrait;
    private Notificaciones $notificacionService;

    public function __construct()
    {
        $this->notificacionService = new Notificaciones();
    }

    public function ejecutar(): void
    {
        // 1. Tomamos un lote controlado de 10 correos para no exceder límites
        $cola = $this->notificacionService->obtenerColaPendienteEmail(10);

        if (empty($cola)) {
            exit("Sin correos pendientes en la cola.\n");
        }

        foreach ($cola as $notificacion) {
            try {
                // 2. Intentamos enviar vía Resend usando el Trait
                $enviado = $this->enviarNotificacionTramite(
                    $notificacion['email'],
                    $notificacion['nombre'],
                    $notificacion['titulo'],
                    $notificacion['mensaje'],
                    $notificacion['fecha_creacion']
                );

                // 3. Marcamos el resultado en BD
                $this->notificacionService->marcarEstadoEmail((int)$notificacion['id'], $enviado);
            } catch (\Throwable $e) {
                // Si falla cURL o Resend, se registra el fallo para reintentar
                $this->notificacionService->marcarEstadoEmail((int)$notificacion['id'], false);
            }


            sleep(2);
        }
    }
}

$worker = new ProcesadorColaCorreos();
$worker->ejecutar();
