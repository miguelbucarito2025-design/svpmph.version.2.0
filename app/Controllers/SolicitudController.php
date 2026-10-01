<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Helpers\Notificaciones;
use App\Libs\BuilderQuery;
use App\Models\CuotasModel;
use App\Models\SolicitudesModel;
use App\Models\TramitesModel;
use App\Traits\ManejoFechasTrait;
use App\Traits\MensageTrait;

class SolicitudController extends Controller
{
    use MensageTrait;
    use ManejoFechasTrait;



    public function guardar(): void
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();

        $tramiteId = $this->filtrarDatos([
            'id' => 'esDesencriptarId'
        ]);
        $datos['cuenta_id'] = $this->session->get('usuario_id');
        $datos['estado'] = 'pendiente';
        $datos['fecha_solicitud'] = date('Y-m-d H:i:s');

        $db = new BuilderQuery; //director de la transaccion
        $tramiteModel = new TramitesModel;
        $modelSolisitud = new SolicitudesModel;
        $cuotaModel = new CuotasModel;
        $notificaion = new Notificaciones;

        $tramites = $tramiteModel->obtenerPorId($tramiteId['id']);
        $datos['tramite_id'] = $tramiteId['id'];

        try {
            $db->beginTransaction();
            $datos['fecha_entrega_estimada'] = $this->modificarFecha(date('Y-m-d'), "+{$tramites['dias_entrega_estimados']} days");

            $modelSolisitud->save($datos);

            $datosCuotas['cuenta_id'] = $datos['cuenta_id'];
            $datosCuotas['cuota'] = 'Solicitud para: ' . $tramites['tramite'];
            $datosCuotas['monto'] =  $tramites['precio_usd'];
            $datosCuotas['status'] = 'Pendiente';
            $datosCuotas['corte'] = $this->modificarFecha(date('Y-m-d'), '+24 days') . ' ' . date('H:i:s');
            $datosCuotas['origen'] = 'solicitud';
            $datosCuotas['origen_id'] = $db->lastInsertId();

            $cuotaModel->save($datosCuotas);

            $mensaje = 'Su solicitud ha sido creada exitosamente. usted tiene 24 horas para confirmar la cancelación 
                del costo del tramite. Si en ese tiempo no lo cancela Soporte Técnico se pondra en contacto con usted
                para ayudarle si tuvo un inconveniente. De lo contrario si fue un error pudes deshacer esto entrando 
                en contacto con el Administrador Miguel Bucarito 
                ';

            $notificaion->crear(
                $datos['cuenta_id'],
                'Solicitud creada correctamente',
                $mensaje
            );

            $db->commit();

            $this->respuesta->json(true);
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
