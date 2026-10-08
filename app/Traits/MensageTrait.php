<?php

declare(strict_types=1);

namespace App\Traits;

use App\Libs\Correo;
use App\Libs\Exceptions\AppException;

trait MensageTrait
{
    public function enviarToken(string $correo, string $token): void
    {
        // Subimos 2 niveles desde app/Traits para llegar a public/
        $rutaPlantilla = 'public/mensages/token.html';

        if (!file_exists($rutaPlantilla)) {
            throw new AppException('No se encontró la plantilla del token.', 500);
        }

        $htmlPlantilla = file_get_contents($rutaPlantilla);
        $contenidoCorreo = str_replace('{{TOKEN}}', (string)$token, $htmlPlantilla);

        $envio = Correo::enviar($correo, 'Token de Verificacion', $contenidoCorreo);
        if (!$envio) {
            throw new AppException('No se pudo Enviar el Correo', 500);
        }
    }

    /**
     * Envía una notificación dinámica de trámites/solicitudes usando Resend
     */
    public function enviarNotificacionTramite(
        string $correoDestino,
        string $nombreUsuario,
        string $titulo,
        string $mensajeTexto,
        string $fecha
    ): bool {
        // Subimos 2 niveles desde app/Traits/ para llegar a la raíz /var/www/html/
        $rutaPlantilla = __DIR__ . '/../../public/mensages/notificacion.html';

        if (!file_exists($rutaPlantilla)) {
            // Si la plantilla no existe, lanzamos excepción para verlo en logs/consola
            throw new \Exception("La plantilla HTML no existe en la ruta: {$rutaPlantilla}");
        }

        $html = file_get_contents($rutaPlantilla);

        // Reemplazo de variables dinámicas dentro del HTML
        $reemplazos = [
            '{{TITULO}}'         => $titulo,
            '{{NOMBRE_USUARIO}}' => $nombreUsuario,
            '{{MENSAJE}}'        => nl2br(htmlspecialchars($mensajeTexto)),
            '{{FECHA}}'          => $fecha
        ];

        $htmlFinal = str_replace(array_keys($reemplazos), array_values($reemplazos), $html);

        // Disparo mediante tu clase Correo (API de Resend)
        return Correo::enviar($correoDestino, $titulo . ' - SVPMPH', $htmlFinal);
    }
}
