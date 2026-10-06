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


    public function traerPorOrigen(?int $oferta)
    {

        $sql = 'SELECT id,destinario,datos FROM ' . $this->tabla . '  WHERE ';
        $value = [];
        if ($oferta !== NULL) {
            $sql .= 'oferta_id=? ';
            $value[] = $oferta;
        } else {
            $sql .= 'oferta_id IS NULL';
        }
        $result = $this->db->select($sql, $value);
        if (empty($result)) {
            return [];
        }


        return $this->cifrarDatos($result, ['id']);
    }
}
