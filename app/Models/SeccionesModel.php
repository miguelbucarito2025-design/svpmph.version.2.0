<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class SeccionesModel  extends Model
{

    protected string $tabla = 'secciones';

    protected array $campos = [
        'id' => 'esEntero',
        'seccion' => 'esCadena',
        'oferta_id' => 'esEntero',
        'cantidad_max' => 'esEntero'
    ];

    protected array $camposMinimos = [
        'seccion',
        'oferta_id',
        'cantidad_max'
    ];

    protected array $camposUnicos = [
        'seccion',
        'oferta_id',
        'cantidad_max'
    ];
}
