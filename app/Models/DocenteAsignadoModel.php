<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class DocenteAsignadoModel  extends Model
{

    protected string $tabla = 'docente_asignado';

    protected array $campos = [
        'id' => 'esEntero',
        'docente_id' => 'esEntero',
        'seccion_id' => 'esEntero',
        'asignatura_id' => 'esEntero'
    ];

    protected array $camposMinimos = [
        'docente_id',
        'seccion_id',
        'asignatura_id'
    ];

    protected array $camposUnicos = [
        'docente_id',
        'seccion_id',
        'asignatura_id'
    ];
}
