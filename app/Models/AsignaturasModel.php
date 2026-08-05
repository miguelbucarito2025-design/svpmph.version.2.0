<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class AsignaturasModel  extends Model
{

    protected string $tabla = 'asignaturas';

    protected array $campos = [
        'id' => 'esEntero',
        'asignatura' => 'esTexto',
        'codigo' => 'esTexto',
        'horas_teoricas' => 'esEntero',
        'horas_practicas' => 'esEntero',
        'programa_id' => 'esEntero',
        'unidades_credito' => 'esEntero'
    ];
    protected array $camposMinimos = [
        'asignatura',
        'codigo',
        'programa_id'

    ];

    protected array $camposUnicos = [
        'asignatura',
        'codigo',
        'programa_id'

    ];
}
