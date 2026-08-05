<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class DestinarioModel  extends Model
{

    protected string $tabla = 'destinario';

    protected array $campos = [
        'id' => 'esEntero',
        'destinario' => 'esTexto',
        'oferta_id' => 'esEntero',
        'cedula_id' => 'esCedula',
        'datos' => 'esEntero'
    ];

    protected array $camposMinimos = [
        'destinario',
        'oferta_id',
        'cedula_id',
        'datos'
    ];
}
