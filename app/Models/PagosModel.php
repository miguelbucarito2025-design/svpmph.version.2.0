<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class PagosModel  extends Model
{

    protected string $tabla = 'pagos';

    protected array $campos = [
        'id' => 'esEntero',
        'metodo' => 'esTexto',
        'referencia' => 'esTexto',
        'monto' => 'esDecimal',
        'destinario_id' => 'esEntero',
        'fecha' => 'esFechaHora',
        'status' => 'esTexto',
        'tasa_id' => 'esEntero',
        'revisado_por' => 'esEntero',
        'cuenta_id' => 'esEntero',
        'img' => 'esRutaArchivo'

    ];

    protected array $camposMinimos = [
        'cuenta_id'
    ];

    protected array $camposUnicos = [];
}
