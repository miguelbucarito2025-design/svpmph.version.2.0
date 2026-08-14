<?php

declare(strict_types=1);

namespace App\Libs;

use App\Libs\Exceptions\AppException;

class Vista
{
    private static string $dirVistas = 'public/views/';
    private static string $dirPlantillas = 'public/plantillas/';

    /**
     * @param string $pagina   Nombre de la vista (ej: "docente/buscar")
     * @param array  $datos    Variables para la vista y la plantilla
     * @param string $plantilla Nombre del archivo de plantilla (ej: "principal")
     */
    public static function render(string $pagina, array $datos = [], string $plantilla = 'index'): void
    {
        // 1. Construir la ruta de la vista de contenido
        $vista = self::$dirVistas . str_replace('.', '/', $pagina) . '.php';

        if (!file_exists($vista)) {
            self::error("La vista '{$pagina}' no existe en: {$vista}");
            return;
        }

        // 2. Extraer las variables del arreglo ($cedula, $seccion, $titulo, etc.)
        extract($datos, EXTR_SKIP);

        // 3. Verificar y cargar la plantilla directamente
        $archivoPlantilla = self::$dirPlantillas . $plantilla . '.php';

        if (!file_exists($archivoPlantilla)) {
            self::error("La plantilla '{$plantilla}' no existe en: {$archivoPlantilla}");
            return;
        }

        // Carga la plantilla (que tiene acceso a $vista y a todas las variables extraídas)
        require $archivoPlantilla;
    }

    private static function error(string $mensaje): void
    {
        http_response_code(500);
        echo "<div style='padding:15px; background:#f8d7da; color:#721c24; border:1px solid #f5c6cb;'>";
        echo "<strong>Error de Vista:</strong> " . htmlspecialchars($mensaje);
        echo "</div>";
    }
}
