<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class PensumModel  extends Model
{

    protected string $tabla = 'pensum';

    protected array $campos = [
        'id' => 'esEntero',
        'p_modalidad_id' => 'esEntero',
        'asignatura_id' => 'esEntero',
        'fecha_ini' => 'esFecha',
        'fecha_fin' => 'esFecha'
    ];

    protected array $camposMinimos = [
        'p_modalidad_id',
        'asignatura_id',

    ];

    protected array $camposUnicos = [
        'p_modalidad_id',
        'asignatura_id',
    ];
}
