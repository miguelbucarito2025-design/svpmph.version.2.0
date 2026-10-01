<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Abstract\Model;

class NotificacionesModel extends Model
{
    protected string $tabla = 'notificaciones';

    protected array $campos = [
        'id' => 'esEntero',
        'cuenta_id' => 'esEntero',
        'titulo' => 'esTexto',
        'mensaje' => 'esTexto',
        'tipo' => 'esCadena',
        'leido' => 'esBooleano',
        'enviar_email' => 'esBooleano',
        'estado_email' => 'esCadena',
        'intentos_email' => 'esEntero', // Corregido de esBooleano a esEntero
        'fecha_creacion' => 'esFechaHora',
        'fecha_envio_email' => 'esFecha'
    ];

    protected array $camposMinimos = [
        'cuenta_id',
        'titulo',
        'mensaje',
        'tipo',
        'enviar_email',
        'fecha_creacion'
    ];

    public function pendientes(int $limite = 10)
    {


        $sql = 'SELECT * FROM ' . $this->tabla . ' WHERE fecha_envio_email=?';
        $result = $this->db->select($sql, [date('Y-m-d')], 'count');


        if ($result >= 90) {
            return [];
        }

        $sql = "SELECT n.*, c.correo as email, d.nombre
                FROM notificaciones n
                INNER JOIN datos d ON n.cuenta_id = d.cuenta_id 
                LEFT JOIN cuentas c ON c.id = n.cuenta_id
                WHERE n.enviar_email = 1 
                  AND n.estado_email = 'pendiente'
                  AND n.intentos_email < 3
                ORDER BY n.fecha_creacion ASC
                LIMIT ?";

        return $this->db->select($sql, [$limite]);
    }

    public function marcarEstadoEmail(int $idNotificacion, bool $exito): void
    {
        if ($exito) {
            $sql = "UPDATE notificaciones 
                    SET estado_email = 'enviado', fecha_envio_email = :fecha
                    WHERE id = :id";
            $this->db->consult($sql, [':id' => $idNotificacion, ':fecha' => date('Y-m-d')]);
        } else {
            $sql = "UPDATE notificaciones 
                    SET intentos_email = intentos_email + 1,
                        estado_email = IF(intentos_email >= 3, 'fallido', 'pendiente') 
                    WHERE id = :id";
            $this->db->consult($sql, [':id' => $idNotificacion]);
        }
    }
}
