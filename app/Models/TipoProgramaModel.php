<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class TipoProgramaModel  extends Model
{

    protected string $tabla = 'tipo_programa';

    protected array $campos = [
        'id' => 'esEntero',
        'tipo' => 'esCadena'
    ];

    protected array $camposMinimos = [
        'tipo'
    ];

    protected array $camposUnicos = [
        'tipo'
    ];
}
