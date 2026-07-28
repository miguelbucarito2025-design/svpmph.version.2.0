<?php

declare(sytict_types=1);


class view
{
    /**
     * El especialista en renderizar vistas, se encarga de cargar la plantilla
     * y pasarle los datos necesarios para mostrar la información en el HTML
     * @param string $content Ejemplo: 'usuarios/perfil'
     * @param array $datos Datos que quieres mostrar en el HTML
     */
    public static function render($content, $datos = [])
    {
        $plantilla = 'public/plantilla.php';
        $content = 'public/views/' . $content . '.php';

        if (file_exists($plantilla)) {
            empty($datos) ?: extract($datos);
            require_once $plantilla;
        } else {
            $content = 'public/views/404.php';
            require_once $plantilla;
        }
    }
}
