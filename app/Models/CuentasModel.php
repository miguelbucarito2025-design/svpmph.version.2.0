<?php


declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;
use App\Libs\Exceptions\DatabaseException;
use App\Libs\Exceptions\AppException;

class CuentasModel  extends Model
{

    protected string $tabla = 'cuentas';

    protected array $campos = [
        'usuario' => 'esNombreUsuario',
        'contrasena' => 'esPassword',
        'estado' => 'esEntero',
        'codigo' => 'esTexto',
        'fecha_codigo' => 'esFechaHora',
        'rol_id' => 'esEntero',
        'correo' => 'esCorreo'

    ];

    protected array $camposMinimos = [
        'usuario',
        'contrasena',
        'estado',
        'correo'
    ];

    protected array $camposUnicos = [
        'usuario',
        'correo'
    ];



    /**
     * funcion para guardar una Cuenta de usuario
     *
     * @param array{
     * usuario:string ,
     * contrasena:string ,
     * estado: int,
     * correo:string 
     * } $datos
     * 
     * @return boolean si todo fue correcto  retorna 'true'
     */
    public function save(array $datos): bool
    {
        try {

            $datosValidados = $this->validarCampos($datos, true);
            $datosValidados['contrasena'] = password_hash($datosValidados['contrasena'], PASSWORD_BCRYPT);
            $datosUnicos = array_intersect_key($datosValidados, array_flip($this->camposUnicos));

            return $this->db->insert(
                $this->tabla,
                $datosValidados,
                $datosUnicos
            );
        } catch (DatabaseException $e) {
            throw new AppException("Error al guardar el registro: " . $e->getMessage(), 500);
        }
    }
}
