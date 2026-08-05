<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class CuotasModel  extends Model
{

    protected string $tabla = 'cuotas';

    protected array $campos = [
        'inscripcion_id' => 'esEntero',
        'cuota' => 'esEntero',
        'pago_id' => 'esEntero',
        'monto' => 'esDecimal',
        'status' => 'esCadena'
    ];
    protected array $camposMinimos = [
        'inscripcion_id',
        'cuota',
        'pago_id',
        'monto',
        'status'
    ];

    protected array $camposUnicos = [];
}
