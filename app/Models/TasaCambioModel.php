<?php

declare(strict_types=1);


namespace App\Models;

use App\Models\Abstract\Model;


class TasaCambioModel extends Model
{

    protected string $tabla = 'tasas_cambio';

    protected array $campos = [
        'id' => 'esEntero',
        'moneda' => 'esCadena',
        'fecha_oficial' => 'esFecha',
        'tasa_bs' => 'esDecimal',
        'creado' => 'esFechaHora'
    ];

    protected array $camposMinimos = [
        'moneda',
        'fecha_oficial',
        'tasa_bs',
        'creado'
    ];

    protected array $camposUnicos = ['fecha_oficial'];


    public function obtenerActual(string $actual): ?array
    {
        $sql = 'SELECT * FROM ' . $this->tabla . ' WHERE creado=? ';
        $result = $this->db->select($sql, [$actual], 'row');
        return $result;
    }


    public function insert(string $tasaBs, string $fecha): bool
    {
        $datos['moneda'] = 'USD';
        $datos['tasa_bs'] = $tasaBs;
        $datos['fecha_oficial'] = $fecha;
        $datos['creado'] = date('Y-m-d H:i:s');

        $datos = $this->validarCampos($datos, true);

        $values = array_values($datos);

        $sql = "INSERT IGNORE INTO tasas_cambio (moneda, tasa_bs, fecha_oficial,creado) 
                VALUES (?,?,?,?)";

        $this->db->consult($sql, $values);

        return true;
    }

    public function ultimaTasaRegistrada()
    {
        $sql = 'SELECT id,tasa_bs, fecha_oficial 
                FROM ' . $this->tabla . '
                ORDER BY fecha_oficial DESC 
                LIMIT 1';
        $result = $this->db->select($sql, [], 'row');
        return $result;
    }
}
