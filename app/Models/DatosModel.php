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




    public function datosPersonales(int $idCuenta)
    {

        $sql = 'SELECT 
                nombre,
                apellido,
                s_nombre,
                s_apellido,
                id_cedula,
                tlf,
                direccion,
                edad
                FROM
                datos
                WHERE cuenta_id=?
     ';

        return $this->db->select($sql, [$idCuenta], 'row');
    }

    public function datosPersonalesYLaborales(int $id)
    {



        $sql = 'SELECT 
                d.nombre,
                d.apellido,
                d.s_nombre,
                d.s_apellido,
                d.id_cedula,
                d.tlf,
                d.direccion,
                d.edad,
                d.foto,
                l.institucion_id,
                l.cargo_id
                FROM
                datos d LEFT JOIN datos_laborales l
                ON d.cuenta_id=l.cuenta_id
                WHERE d.cuenta_id=?
     ';

        return $this->db->select($sql, [$id], 'row');
    }





    public function guardarFoto(int $idCuenta, string $key): ?string
    {
        try {

            $sql = 'SELECT foto,cuenta_id FROM datos WHERE cuenta_id=?';
            $this->db->beginTransaction();
            $result = $this->db->select($sql, [$idCuenta], 'row');
            if (empty($result['cuenta_id'])) {
                $this->db->rollBack();
                return null;
            }


            $actualizar = $this->update(['foto' => $key], ['cuenta_id' => $idCuenta]);
            if (!$actualizar) {
                $this->db->rollBack();
                return null;
            }

            $this->db->commit();
            return $result['foto'];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
