<?php


declare(strict_types=1);

namespace App\Traits;

use App\Libs\Correo;
use App\Libs\Exceptions\AppException;

trait MensageTrait
{

    private Correo $correo;


    public function enviarToken(string $correo, int $token): void
    {

        $rutaPlantilla = 'public/mensages/token.html';
        $htmlPlantilla = file_get_contents($rutaPlantilla);

        $contenidoCorreo = str_replace('{{TOKEN}}', (string)$token, $htmlPlantilla);
        $envio = Correo::enviar($correo, 'Token de Verificacion', $contenidoCorreo);
        if (!$envio) {
            throw new AppException('No se pudo Enviar el Correo', 500);
        }
    }
}
