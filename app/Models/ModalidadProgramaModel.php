<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class ModalidadProgramaModel  extends Model
{

    protected string $tabla = 'modalidad_programa';

    protected array $campos = [
        'id' => 'esEntero',
        'nombre' => 'esCadena',
        'oferta_id' => 'esEntero',
        'modalidad_id' => 'esEntero',
        'cantidad' => 'esEntero'
    ];
    protected array $camposMinimos = [
        'nombre',
        'oferta_id',
        'modalidad_id',
        'cantidad'
    ];

    protected array $camposUnicos = [
        'oferta_id',
        'modalidad_id',
    ];
}
