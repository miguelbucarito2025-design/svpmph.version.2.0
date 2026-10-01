<?php

namespace App\Helpers;

use App\Libs\Exceptions\AppException;
use App\Models\NotificacionesModel;

/**
 * Servicio: NotificacionService
 * Descripción: Crea notificaciones para usuarios y gestiona la cola diferida 
 *              para el envío progresivo de correos electrónicos.
 * Autor: Gremio Dev
 * Fecha: 2026-09-29
 */
class Notificaciones
{
    private NotificacionesModel  $model;

    public function __construct()
    {
        $this->model = new NotificacionesModel;
    }

    /**
     * Registra una nueva notificación en la Base de Datos.
     * 
     * @param int $idUsuario ID del agremiado a notificar.
     * @param string $titulo Título de la notificación.
     * @param string $mensaje Contenido o cuerpo del mensaje.
     * @param string $tipo Tipo de evento ('solicitud', 'pago', etc.)
     * @param bool $enviarEmail Define si debe enviarse copia por correo.
     * @return bool
     */
    public function crear(
        int $idUsuario,
        string $titulo,
        string $mensaje,
        string $tipo = 'solicitud',
        bool $enviarEmail = true
    ): bool {

        if (empty($mensaje)) {
            throw new AppException('Mensaje Invalido o Vacio', 400);
        }


        return $this->model->save([
            'cuenta_id'   => $idUsuario,
            'titulo'       => $titulo,
            'mensaje'      => $mensaje,
            'tipo'         => $tipo,
            'enviar_email' => $enviarEmail ? 1 : 0,
            'fecha_creacion' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Obtiene un lote controlado de notificaciones pendientes de envío por correo.
     * 
     * @param int $limite Cantidad máxima de correos a procesar por tanda (ej. 10 u 20).
     * @return array
     */
    public function obtenerColaPendienteEmail(int $limite = 10): array
    {
        return $this->model->pendientes($limite);
    }

    /**
     * Actualiza el estado de la cola tras intentar enviar el correo.
     * 
     * @param int $idNotificacion
     * @param bool $exito
     * @return void
     */
    public function marcarEstadoEmail(int $idNotificacion, bool $exito): void
    {
        $this->model->marcarEstadoEmail($idNotificacion, $exito);
    }
}
