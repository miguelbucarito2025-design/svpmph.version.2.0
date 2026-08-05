<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class PagosModel  extends Model
{

    protected string $tabla = 'pagos';

    protected array $campos = [
        'id' => 'esEntero',
        'cuota_id' => 'esEntero',
        'metodo' => 'esTexto',
        'referencia' => 'esTexto',
        'monto' => 'esDecimal',
        'banco_origen_id' => 'esEntero',
        'destinario_id' => 'esEntero',
        'fecha' => 'esFecha',
        'status' => 'esCadena',
        'cedula_id' => 'esCedula'
    ];
    protected array $camposMinimos = [
        'cuota_id',
        'metodo',
        'monto',
        'banco_origen_id',
        'destinario_id',
        'fecha',
        'status',
        'cedula_id'

    ];

    protected array $camposUnicos = [];
}
