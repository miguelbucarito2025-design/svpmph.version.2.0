<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class EstuciantesModel extends Model
{

    protected string $tabla = 'estudiantes';

    protected array $campos = [
        'id' => 'esEntero',
        'cedula_id' => 'esCedula',
        'nucleo_id' => 'esEntero'
    ];


    protected array $camposMinimos = [
        'cedula_id',
        'nucleo_id'
    ];

    protected array $camposUnicos = [
        'cedula_id',
        'nucleo_id'
    ];
}
