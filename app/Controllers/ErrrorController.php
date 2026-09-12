<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Helpers\R2Service;

class ErrrorController extends Controller
{
    // Directorio exacto de acuerdo a tu árbol de carpetas (app/Logs/)
    private string $dirLogs;

    public function __construct()
    {
        parent::__construct();
        $this->dirLogs = __DIR__ . '/../Logs/';
    }

    public function index(int $codigo, string $mensaje): void
    {
        $this->vista->render(
            'error/index',
            [
                'codigo' => $codigo,
                'mensaje' => $mensaje
            ],
            'error'
        );
    }

    public function verErrores(): void
    {
        $this->requerirAutenticacion();
        $r2Service = new R2Service();

        $urlPublica = $r2Service->obtenerUrlPublica((string)$this->session->get('foto_perfil'));

        $this->vista->render(
            'usuario/logs',
            [
                'token'         => $this->session->get('csrf_token'),
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'nombreRol'     => $this->session->get('nombre_rol'),
                'titlePag'      => 'Logs del Sistema',
                'pag'           => 'logs', // Carga public/js/logs.js
                'grup'          => 'administracion',
                'fotoUsuario'   => $urlPublica,
            ],
            'usuario'
        );
    }

    /**
     * Endpoint AJAX: Devuelve los logs de Aplicación y Base de Datos simultáneamente
     */
    public function obtenerLogs(): void
    {
        $this->requerirAutenticacion();

        $rutaApp = $this->dirLogs . 'log_app.log';
        $rutaDb  = $this->dirLogs . 'log_db.log';

        // Procesar Log de Aplicación
        $logApp = '--- Archivo log_app.log no existe ---';
        $pesoApp = '0 Bytes';
        if (file_exists($rutaApp)) {
            $bytes = filesize($rutaApp);
            $pesoApp = $bytes > 1024 ? round($bytes / 1024, 2) . ' KB' : $bytes . ' Bytes';
            $contenido = file_get_contents($rutaApp);
            $logApp = !empty($contenido) ? $contenido : '--- Sin errores de Aplicación ---';
        }

        // Procesar Log de Base de Datos
        $logDb = '--- Archivo log_db.log no existe ---';
        $pesoDb = '0 Bytes';
        if (file_exists($rutaDb)) {
            $bytes = filesize($rutaDb);
            $pesoDb = $bytes > 1024 ? round($bytes / 1024, 2) . ' KB' : $bytes . ' Bytes';
            $contenido = file_get_contents($rutaDb);
            $logDb = !empty($contenido) ? $contenido : '--- Sin errores de Base de Datos ---';
        }

        $this->respuesta->json([
            'status' => 'success',
            'app'    => ['log' => $logApp, 'peso' => $pesoApp],
            'db'     => ['log' => $logDb,  'peso' => $pesoDb]
        ]);
    }

    /**
     * Endpoint AJAX: Vacía el log seleccionado ('app', 'db' o 'ambos')
     */
    public function limpiarLogs(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();

        $datos = json_decode(file_get_contents('php://input'), true);
        $tipo = $datos['tipo'] ?? 'app';

        $archivosALimpiar = [];
        if ($tipo === 'db') {
            $archivosALimpiar[] = 'log_db.log';
        } elseif ($tipo === 'ambos') {
            $archivosALimpiar[] = 'log_app.log';
            $archivosALimpiar[] = 'log_db.log';
        } else {
            $archivosALimpiar[] = 'log_app.log';
        }

        foreach ($archivosALimpiar as $archivo) {
            $ruta = $this->dirLogs . $archivo;
            if (file_exists($ruta)) {
                file_put_contents($ruta, '');
            }
        }

        $this->respuesta->json([
            'status' => 'success',
            'exito'  => true,
            'mensaje' => 'Los registros seleccionados han sido vaciados.'
        ]);
    }
}
