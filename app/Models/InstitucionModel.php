<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class InstitucionModel  extends Model
{

    protected string $tabla = 'institucion';

    protected array $campos = [
        'id' => 'esEntero',
        'institucion' => 'esCadena'
    ];
    protected array $camposMinimos = [
        'institucion'
    ];

    protected array $camposUnicos = [
        'institucion'
    ];
}
