<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class EvaluacionesModel  extends Model
{

    protected string $tabla = 'evaluaciones';

    protected array $campos = [
        'id' => 'esEntero',
        'docente_id' => 'esEntero',
        'actividad_id' => 'esEntero',
        'valor' => 'esDecimal',
        'fecha' => 'esFechaHora',
        'asignatura_id' => 'esEntero',
    ];


    protected array $camposMinimos = [
        'docente_id',
        'actividad_id',
        'valor',
        'fecha',
        'asignatura_id'
    ];
}
