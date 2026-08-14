<?php

declare(strict_types=1);

namespace App\Libs;

/**
 * Clase Response
 *
 * Módulo encargado de formatear, adjuntar cabeceras de seguridad y emitir
 * las salidas HTTP (Respuestas estructuradas en JSON o Redirecciones).
 *
 * @package App\Libs
 */
class Response
{
    /**
     * Emite una respuesta estructurada en formato JSON, establece el código de estado HTTP
     * correspondiente y finaliza la ejecución de PHP.
     *
     * Limpia cualquier búfer previo en memoria para prevenir corrupción en la salida JSON.
     *
     * @param mixed $data Payload de datos a retornar dentro del objeto de respuesta.
     * @param int $codigo Código de estado HTTP de la respuesta (ej. 200, 201, 400, 401, 403, 500).
     * @param string $mensaje Mensaje descriptivo. Si se envía vacío, se asigna automáticamente según el código HTTP.
     * @param array<string, string|array> $errores Listado de errores de validación opcionales para la respuesta.
     * @return void
     */
    public static function json(
        mixed $data = null,
        int $codigo = 200,
        string $mensaje = '',
        array $errores = []
    ): void {
        if (ob_get_length()) {
            ob_clean();
        }

        http_response_code($codigo);

        // Cabeceras HTTP de seguridad
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');

        $exito = $codigo >= 200 && $codigo < 300;

        // Asignación de mensaje estándar según el código de estado HTTP si el parámetro está vacío
        if (empty($mensaje)) {
            $mensaje = match ($codigo) {
                200 => 'Operación realizada con éxito',
                201 => 'Recurso creado con éxito',
                400 => 'Petición incorrecta o datos inválidos',
                401 => 'No autorizado o sesión expirada',
                403 => 'Acceso denegado',
                404 => 'Recurso no encontrado',
                500 => 'Error interno en el servidor',
                default => $exito ? 'Éxito' : 'Error'
            };
        }

        $respuesta = [
            'status'  => $exito ? 'success' : 'error',
            'code'    => $codigo,
            'message' => $mensaje,
            'data'    => $data
        ];

        if (!$exito && !empty($errores)) {
            $respuesta['errors'] = $errores;
        }

        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Efectúa una redirección HTTP enviando la cabecera Location y finalizando la ejecución del script.
     *
     * @param string $url Dirección de destino para la redirección.
     * @return void
     */
    public static function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
}
