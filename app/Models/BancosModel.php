<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class BancosModel  extends Model
{

    protected string $tabla = 'bancos';

    protected array $campos = [
        'id' => 'esEntero',
        'banco' => 'esTexto'
    ];
    protected array $camposMinimos = [
        'banco'
    ];

    protected array $camposUnicos = ['banco'];
}
