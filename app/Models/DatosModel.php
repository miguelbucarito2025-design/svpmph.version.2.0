<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class DatosModel  extends Model
{

    protected string $tabla = 'datos';

    protected array $campos = [
        'id' => 'esEntero',
        'nombre' => 'esCadena',
        'apellido' => 'esCadena',
        's_nombre' => 'esCadena',
        's_apellido' => 'esCadena',
        'id_cedula' => 'esCedula',
        'tlf' => 'esTlf',
        'direccion' => 'esTexto',
        'edad' => 'esFecha',
        'foto' => 'esRutaArchivo',
        'ingreso' => 'esFechaHora',
        'cuenta_id' => 'esEntero'
    ];
    protected array $camposMinimos = [
        'nombre',
        'apellido',
        'id_cedula',
        'ingreso',
        'cuenta_id'
    ];

    protected array $camposUnicos = [
        'id_cedula',
        'tlf',
        'cuenta_id'
    ];
}
