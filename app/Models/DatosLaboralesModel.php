<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class DatosLaboralesModel  extends Model
{

    protected string $tabla = 'datos_laborales';

    protected array $campos = [
        'cedula_id' => 'esCedula',
        'institucion_id' => 'esEntero',
        'cargo_id' => 'esEntero'
    ];
    protected array $camposMinimos = [
        'cedula_id',
        'institucion_id',
        'cargo_id'
    ];

    protected array $camposUnicos = ['cedula_id'];
}
