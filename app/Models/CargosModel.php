<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class  CargosModel extends Model
{

    protected string $tabla = 'cargos';

    protected array $campos = [
        'id' => 'esEntero',
        'cargo' => 'esCadena',
        'institucion_id' => 'esEntero'
    ];
    protected array $camposMinimos = [
        'cargo',
        'institucion_id'
    ];

    protected array $camposUnicos = [];



    public function obtenerPorInstitucion(int $id): array| null
    {
        $sql = 'SELECT * FROM ' . $this->tabla . ' WHERE institucion_id=? ';
        return $this->db->select($sql, [$id], 'row');
    }
}
