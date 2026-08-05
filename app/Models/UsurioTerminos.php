<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class UsurioTerminos  extends Model
{

    protected string $tabla = 'usuario_terminos';
    protected array $campos = [
        'cedula_id' => 'esCedula',
        'terminos_id' => 'esEntero',
        'fecha' => 'esFechaHora',
        'respuesta' => 'esTexto'

    ];
    protected array $camposMinimos = [
        'cedula_id',
        'terminos_id',
        'fecha',

    ];

    protected array $camposUnicos = [];
}
