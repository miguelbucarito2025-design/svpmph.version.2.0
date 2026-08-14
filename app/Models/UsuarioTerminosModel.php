<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class UsuarioTerminosModel  extends Model
{

    protected string $tabla = 'usuario_terminos';

    protected array $campos = [
        'id' => 'esEntero',
        'cuenta_id' => 'esEntero',
        'terminos_id' => 'esEntero',
        'fecha' => 'esFechaHora',
        'respuesta' => 'esTexto'

    ];
    protected array $camposMinimos = [
        'cuenta_id',
        'terminos_id',
        'fecha',

    ];

    protected array $camposUnicos = [];
}
