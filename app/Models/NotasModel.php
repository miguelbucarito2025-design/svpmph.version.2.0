<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class NotasModel  extends Model
{

    protected string $tabla = 'notas';

    protected array $campos = [
        'evaluacion_id' => 'esEntero',
        'nota' => 'esEntero',
        'estudiante_id' => 'esEntero'
    ];

    protected array $camposMinimos = [
        'id' => 'esEntero',
        'evaluacion_id',
        'nota',
        'estudiante_id'
    ];

    protected array $camposUnicos = [
        'evaluacion_id',
        'nota',
        'estudiante_id'
    ];
}
