<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class DatosLaboralesModel  extends Model
{

    protected string $tabla = 'datos_laborales';

    protected array $campos = [
        'id' => 'esEntero',
        'cuenta_id' => 'esEntero',
        'institucion_id' => 'esEntero',
        'cargo_id' => 'esEntero'
    ];
    protected array $camposMinimos = [
        'cuenta_id',
        'institucion_id',
        'cargo_id'
    ];

    protected array $camposUnicos = ['cuenta_id'];

    public function datosLaborales(int $id): array|null
    {
        $sql = 'SELECT 
        i.institucion,
        d.institucion_id,
        c.cargo,
        d.cargo_id
        FROM datos_laborales d LEFT JOIN institucion i ON d.institucion_id=i.id 
        LEFT JOIN cargos c ON d.cargo_id=c.id WHERE d.cuenta_id=?';
        return $this->db->select($sql, [$id], 'row');
    }
}
