<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class  NucleoModel extends Model
{

    protected string $tabla = 'nucelo';

    protected array $campos = [
        'id' => 'esEntero',
        'nucleo' => 'esTexto',
        'descripcion' => 'esTexto',
        'direccion' => 'esTexto',
        'logo' => 'esRutaArchivo'
    ];
    protected array $camposMinimos = [
        'nucelo'
    ];

    protected array $camposUnicos = ['nucleo'];
}
