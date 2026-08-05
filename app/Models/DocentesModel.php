<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class  DocentesModel extends Model
{

    protected string $tabla = 'docentes';

    protected array $campos = [
        'id' => 'esEntero',
        'cedula_id' => 'esCedula'
    ];
    protected array $camposMinimos = [
        'cedula_id'
    ];

    protected array $camposUnicos = [
        'cedula_id'
    ];
}
