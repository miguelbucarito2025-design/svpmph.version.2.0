<?php

declare(strict_types=1);

date_default_timezone_set('America/Caracas');

// Cabeceras HTTP globales para evitar almacenamiento en caché de la interfaz
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Activar búfer de salida para proteger las cabeceras ante cualquier excepción
ob_start();

// ============================================================================
// 1. SIMULACIÓN DE ENTORNO HTTP Y SESIÓN PARA EL CRON
// ============================================================================
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME']    = '/index.php';
$_SERVER['HTTP_HOST']      = 'localhost';
$_SERVER['REMOTE_ADDR']    = '127.0.0.1';

// Carga única del autoloader y entorno
require_once __DIR__ . '/../vendor/autoload.php';

use App\Traits\MensageTrait;
use App\Libs\ManejadorExcepciones;
use App\Helpers\EnvLoader;
use App\Helpers\Notificaciones;
use App\Libs\Session;
use App\Helpers\LogAnalyzer;

EnvLoader::load(__DIR__ . '/../app/Config/.env');
ManejadorExcepciones::registrar();

// Simulación de Navegador y Cabeceras
$_SERVER['HTTP_USER_AGENT']       = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36';
$_SERVER['HTTP_ORIGIN']           = URL_BASE;
$_SERVER['HTTP_REFERER']          = URL_BASE . 'cron';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

$session = new Session();
$session->start();

$_SESSION['usuario_id']        = 19;
$_SESSION['usuario_rol']       = 5;
$_SESSION['ultimo_acceso']     = time();
$_SESSION['_user_fingerprint'] = hash('sha256', $_SERVER['HTTP_USER_AGENT']);

// ============================================================================
// 2. CLASE WORKER PARA PROCESAR LA COLA DE CORREOS GENERALES DE LA PLATAFORMA
// ============================================================================
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
        $cola = $this->notificacionService->obtenerColaPendienteEmail(10);

        if (empty($cola)) {
            return;
        }

        foreach ($cola as $notificacion) {
            try {
                $enviado = $this->enviarNotificacionTramite(
                    $notificacion['email'],
                    $notificacion['nombre'],
                    $notificacion['titulo'],
                    $notificacion['mensaje'],
                    $notificacion['fecha_creacion']
                );

                $this->notificacionService->marcarEstadoEmail((int)$notificacion['id'], $enviado);
            } catch (\Throwable $e) {
                $this->notificacionService->marcarEstadoEmail((int)$notificacion['id'], false);
            }

            sleep(2);
        }
    }
}

// ============================================================================
// 3. CLASE PARA DISPARAR LA ALERTA INMEDIATA DE LA IA AL CORREO DEL ADMIN
// ============================================================================
class NotificadorIAInmediato
{
    use MensageTrait;

    public function enviarAlerta(string $asunto, string $mensajeDiagnostico): bool
    {
        $emailAdmin = getenv('ADMIN_EMAIL') ?: 'admin@svpmph.org';
        $nombreAdmin = 'Administrador SVPMPH';

        try {
            return $this->enviarNotificacionTramite(
                $emailAdmin,
                $nombreAdmin,
                $asunto,
                $mensajeDiagnostico,
                date('Y-m-d H:i:s')
            );
        } catch (\Throwable $e) {
            return false;
        }
    }
}

// ============================================================================
// 4. EJECUCIÓN TOTAL DEL CICLO DE MANTENIMIENTO AUTÓNOMO
// ============================================================================

try {
    // A. Análisis inteligente de logs con la IA usando las rutas correctas
    $analyzer = new LogAnalyzer('app/Logs/', 'storage/');
    $analyzer->ejecutarAnalisisCompleto();

    $pendientes = $analyzer->obtenerReportesPendientesDeNotificacion();
    $notificadorIA = new NotificadorIAInmediato();

    // B. Despachar alertas de seguridad de la IA directamente al correo
    if (!empty($pendientes['seguridad'])) {
        foreach ($pendientes['seguridad'] as $alerta) {
            $asunto = "🚨 Alerta de Seguridad (IA): Actividad Sospechosa";
            $cuerpoMensaje = "Diagnóstico técnico emitido por Gemma:\n\n" . $alerta['analisis'];

            $enviado = $notificadorIA->enviarAlerta($asunto, $cuerpoMensaje);

            if ($enviado) {
                $analyzer->marcarComoNotificado('seguridad', (int)$alerta['indice']);
            }
        }
    }

    // C. Despachar errores críticos de Base de Datos de la IA al correo
    if (!empty($pendientes['base_datos'])) {
        foreach ($pendientes['base_datos'] as $errorDb) {
            $asunto = "⚠️ Error Crítico en Base de Datos (IA)";
            $cuerpoMensaje = "Se ha detectado la siguiente incidencia:\n\n" .
                "--- Error Original ---\n{$errorDb['error']}\n\n" .
                "--- Análisis Experto de la IA ---\n{$errorDb['analisis']}";

            $enviado = $notificadorIA->enviarAlerta($asunto, $cuerpoMensaje);

            if ($enviado) {
                $analyzer->marcarComoNotificado('base_datos', (int)$errorDb['indice']);
            }
        }
    }

    // D. Procesar la cola general de correos de la plataforma
    $worker = new ProcesadorColaCorreos();
    $worker->ejecutar();

    echo "[CRON] Ciclo de mantenimiento completado con éxito a las " . date('Y-m-d H:i:s') . "\n";
} catch (\Throwable $e) {
    error_log("Error crítico en cron procesar_correos.php: " . $e->getMessage());
}

// Cierre limpio del búfer de salida
ob_end_flush();
