<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class ProgramaModel  extends Model
{

    protected string $tabla = 'programas';

    protected array $campos = [
        'id' => 'esEntero',
        'programa' => 'esTexto',
        'descripcion' => 'esTexto',
        'contenido' => 'esTexto',
        'requisitos' => 'esTexto',
        'logo' => 'esRutaArchivo',
        'duracion' => 'esTexto',
        'tipo_programa' => 'esEntero',
        'flyer' => 'esRutaArchivo',
        'estado' => 'esBooleano',
        'certificado' => 'esRutaArchivo'

    ];

    protected array $camposMinimos = [
        'programa',
        'descripcion',
        'tipo_programa',

    ];

    protected array $camposUnicos = [
        'programa'
    ];
}
