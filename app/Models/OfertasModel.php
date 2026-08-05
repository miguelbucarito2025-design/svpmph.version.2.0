<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class OfertasModel extends Model
{

    protected string $tabla = 'ofertas';

    protected array $campos = [
        'nucleo_id' => 'esEntero',
        'programa_id' => 'esEntero',
        'fecha_ini' => 'esFechaHora',
        'fecha_fin' => 'esFechaHora',
        'costo_inscripcion' => 'esDecimal',
        'costo_total' => 'esDecimal',
        'cuotas' => 'esEntero',
        'estado' => 'esBooleano'

    ];

    protected array $camposMinimos = [
        'nucleo_id',
        'programa_id',
        'fecha_ini',
        'fecha_fin',
        'costo_inscripcion',
        'costo_total',
        'cuotas',
        'estado'
    ];

    protected array $camposUnicos = [];
}
