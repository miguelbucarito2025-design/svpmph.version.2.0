<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class RolModel  extends Model
{

    protected string $tabla = 'rol';

    protected array $campos = [
        'rol' => 'esCadena',
        'descripcion' => 'esTexto',
        'img' => 'esRutaArchivo'
    ];

    protected array $camposMinimos = [
        'rol'
    ];

    protected array $camposUnicos = [
        'rol'
    ];
}
