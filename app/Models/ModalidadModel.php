<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class ModalidadModel  extends Model
{

    protected string $tabla = 'modalidad';

    protected array $campos = [
        'id' => 'esEntero',
        'modalidad' => 'esCadena'
    ];

    protected array $camposMinimos = [
        'modalidad'
    ];

    protected array $camposUnicos = [
        'modalidad'
    ];
}
