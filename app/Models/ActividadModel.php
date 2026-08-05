<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class ActividadModel  extends Model
{

    protected string $tabla = 'actividad';

    protected array $campos = [
        'id' => 'esEntero',
        'actividad' => 'esTexto',
        'tipo' => 'esEntero'
    ];

    protected array $camposMinimos = [
        'actividad',
        'tipo'
    ];

    protected array $camposUnicos = [
        'actividad',
        'tipo'
    ];
}
