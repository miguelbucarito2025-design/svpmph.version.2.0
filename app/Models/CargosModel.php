<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class  CargosModel extends Model
{

    protected string $tabla = 'cargos';

    protected array $campos = [
        'cargo' => 'esCadena',
        'institucion_id' => 'esEntero'
    ];
    protected array $camposMinimos = [
        'cargo',
        'institucion_id'
    ];

    protected array $camposUnicos = [];
}
