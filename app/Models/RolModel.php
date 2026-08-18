<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class RolModel  extends Model
{

    protected string $tabla = 'rol';

    protected array $campos = [
        'id' => 'esEntero',
        'rol' => 'esCadena',
        'descripcion' => 'esTexto',
        'img' => 'esRutaArchivo'
    ];

    protected array $camposMinimos = [
        'rol'
    ];

    protected array $camposUnicos = [
        'rol',
        'id'
    ];

    /**
     * optiene el rol por el id
     *
     * @param integer $id
     * @return string devuelve solo el nombre
     */
    public function obtenerPorId(int $id): string
    {
        $sql = 'SELECT rol FROM rol WHERE id=?';
        return $this->db->select($sql, [$id], 'row')['rol'];
    }
}
