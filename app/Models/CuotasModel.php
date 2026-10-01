<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class CuotasModel  extends Model
{

    protected string $tabla = 'cuotas';

    protected array $campos = [
        'id' => 'esEntero',
        'cuenta_id' => 'esEntero',
        'cuota' => 'esTexto',
        'pago_id' => 'esEntero',
        'monto' => 'esDecimal',
        'status' => 'esCadena',
        'corte' => 'esFechaHora',
        'origen' => 'esCadena',
        'origen_id' => 'esEntero'
    ];
    protected array $camposMinimos = [
        'cuenta_id',
        'cuota',
        'monto',
        'status',
        'corte',
        'origen',
        'origen_id'
    ];

    protected array $camposUnicos = [];
}
