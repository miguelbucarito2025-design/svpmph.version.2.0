<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Libs\Exceptions\AppException;
use App\Helpers\TasaBCV;
use App\Helpers\Validar;
use App\Libs\BuilderQuery;
use App\Models\CuentasModel;
use App\Models\CuotasModel;
use App\Models\DestinarioModel;
use App\Models\PagosModel;
use App\Traits\ManejoArchivosR2Trait;
use App\Models\TramitesModel;
use App\Helpers\Notificaciones;

class PagosController extends Controller
{

    use ManejoArchivosR2Trait;


    public function  index(): void
    {
        $this->requerirAutenticacion();

        $urlPublica = $this->obtenerArchivo($this->session->get('foto_perfil'));



        $this->vista->render(
            'usuario/cuotas',
            [
                'token'   => $this->session->get('csrf_token'),
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'nombreRol' => $this->session->get('nombre_rol'),
                'titlePag' => 'Cuotas',
                'pag' => 'cuotas',
                'grup' => 'pagos',
                'fotoUsuario' => $urlPublica


            ],
            'usuario'
        );
    }


    public function  admin(): void
    {
        $this->requerirAutenticacion();

        $urlPublica = $this->obtenerArchivo($this->session->get('foto_perfil'));



        $this->vista->render(
            'usuario/cuotasAdministrar',
            [
                'token'   => $this->session->get('csrf_token'),
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'nombreRol' => $this->session->get('nombre_rol'),
                'titlePag' => 'Pagos',
                'pag' => 'cuotasAdministrar',
                'grup' => 'pagos',
                'fotoUsuario' => $urlPublica


            ],
            'usuario'
        );
    }




    /**
     * Nombre del método: guardar
     * Descripción: Procesa, valida y persiste la solicitud de pago. Si el pago ya existe, 
     *              lo actualiza en la misma fila sin volver a generar vinculaciones.
     * Autor: Aprendiz de Backend
     * Fecha: 2026-10-04
     */
    public function guardar(): void
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();

        // 1. Sanitización de datos
        $datos = $this->filtrarDatos([
            'cuota_id'      => 'esDesencriptarId',
            'monto_bs'      => 'esDecimal',
            'destinario_id' => 'esDesencriptarId',
            'metodo'        => 'esTexto'
        ]);

        // 2. Consulta de la cuota (fuente de verdad)
        $cuotaModel = new CuotasModel();
        $cuota = $cuotaModel->obtenerPorId($datos['cuota_id']);

        if (empty($cuota)) {
            throw new AppException('La cuota a pagar no existe en el sistema', 400);
        }

        // 3. Tasa de cambio del día
        $pagos = new TasaBCV();
        $pagos->cargarDesdeBD(date('Y-m-d'));

        $datos['fecha']     = date('Y-m-d H:i:s');
        $datos['tasa_id']   = $pagos->getId();
        $datos['status']    = 'En Proceso';
        $datos['cuenta_id'] = $this->session->get('usuario_id');

        $imagenSubidaR2 = null;

        // 4. Lógica según el método de pago
        if ($datos['metodo'] === 'Efectivo') {

            $resultadoEfectivo = $pagos->procesarPagoEfectivoSeguro($cuota['monto'], $datos['monto_bs']);
            $datos['monto'] = $resultadoEfectivo['monto_recibido_bs'];
            $datos['referencia'] = null;
        } elseif ($datos['metodo'] === 'Divisas') {
            $datos['monto'] = $cuota['monto'];
            $datos['referencia'] = null;
        } else if ($datos['metodo'] === 'Pago_Mobil' || $datos['metodo'] === 'Pago Mobil' || $datos['metodo'] === 'Transferencia') {

            $pagoDigital = $this->getDatosEntrada();
            $datos['monto'] = $pagos->convertirUsdABs($cuota['monto']);

            $referenciaLimpia = Validar::esEntero($pagoDigital['referencia'] ?? null);
            if (strlen((string) $referenciaLimpia) !== 6) {
                throw new AppException('La referencia debe contener exactamente 6 dígitos', 400);
            }
            $datos['referencia'] = $referenciaLimpia;

            $reglasImagen = [['jpg', 'jpeg', 'png', 'webp'], 2];
            if (!empty($_FILES['img']['name'])) {
                $imgFiltrada = $this->filtrarArchivo('img', ...$reglasImagen);
                $resImg = $this->subirArchivoR2($imgFiltrada, 'pagos', 'capture');

                if (!$resImg['exito']) {
                    throw new AppException($resImg['error'] ?? 'Error al subir la imagen del comprobante', 400);
                }

                $datos['img'] = $resImg['key'];
                $imagenSubidaR2 = $resImg['key'];
            } else {
                // Si es un UPDATE y no subió imagen nueva, conservamos la imagen anterior si existía
                if (empty($cuota['pago_id'])) {
                    throw new AppException('El comprobante de pago es obligatorio para este método', 400);
                }
            }
        } else {
            throw new AppException('El método de pago seleccionado no está registrado', 400);
        }

        $cuotaId = $datos['cuota_id'];
        unset($datos['monto_bs']);

        // 5. Persistencia limpia
        $model = new PagosModel();
        $db = new BuilderQuery();

        try {
            $db->beginTransaction();

            if (!empty($cuota['pago_id'])) {

                // CASO A: EL PAGO YA EXISTE -> SOLO ACTUALIZAMOS LA FILA DE PAGOS
                $pagoId = $cuota['pago_id'];

                if (!$model->update($datos, ['id' => $pagoId])) {
                    throw new \Exception('No se pudo actualizar el registro del pago existente');
                }

                // Solo actualizamos el estatus de la cuota, sin tocar el pago_id que ya está vinculado
                $cuotaModel->update([
                    'status' => 'En Proceso'
                ], ['id' => $cuotaId]);
            } else {

                // CASO B: EL PAGO NO EXISTE -> INSERTAMOS NUEVO Y VINCULAMOS POR ÚNICA VEZ
                if (!$model->save($datos)) {
                    throw new \Exception('No se pudo registrar el nuevo pago');
                }

                $nuevoPagoId = $db->lastInsertId();

                if (!$nuevoPagoId) {
                    throw new \Exception('No se pudo obtener el ID del pago registrado');
                }

                // Vinculamos la cuota con el nuevo pago_id
                $cuotaModel->update([
                    'status'  => 'En Proceso',
                    'pago_id' => $nuevoPagoId
                ], ['id' => $cuotaId]);
            }

            // 6. Notificaciones
            $mensajeUsuario = 'Su solicitud fue guardada con éxito. Espere la confirmación de los encargados.';
            $notificaciones = new Notificaciones();
            $notificaciones->crear(
                $datos['cuenta_id'],
                'Pago guardado correctamente',
                $mensajeUsuario
            );

            $mensajeAutoridades = "El usuario {$cuota['nombre']} {$cuota['apellido']} V-{$cuota['id_cedula']} ha registrado una solicitud de pago.";
            $autoridadesModel = new CuentasModel();
            $autoridades = $autoridadesModel->obtenerAutoridadesSpeciales();

            foreach ($autoridades as $autoridad) {
                $notificaciones->crear(
                    $autoridad['id'],
                    'Solicitud de pago registrada',
                    $mensajeAutoridades
                );
            }

            $db->commit();

            $this->respuesta->json(
                $datos,
                201,
                'Solicitud de pago procesada con éxito.'
            );
        } catch (\Exception $e) {
            $db->rollBack();

            if (!empty($imagenSubidaR2)) {
                $this->eliminarArchivosR2([$imagenSubidaR2]);
            }

            throw new AppException('Error interno del servidor al procesar el pago: ' . $e->getMessage(), 500);
        }
    }





    /**
     * Endpoint para obtener las ofertas paginadas vía AJAX/JSON.
     */
    public function paginar(): void
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();



        $datos = $this->filtrarDatos([
            'limit' => 'esEntero',
            'offset' => 'esEntero',

        ]);


        if ($datos['offset'] < 0 || $datos['offset'] > 50) {
            $datos['offset'] = 0;
        }

        $model = new TramitesModel();
        $cuotas = $model->paginar($datos);
        $total = $model->select('count');


        $this->respuesta->json($cuotas, 200, "Tramites obtenidos con éxito", [], (int)$total);
    }












    public function solicitar(): void
    {
        $this->requerirAutenticacion();
        $urlPublica = $this->obtenerArchivo($this->session->get('foto_perfil'));



        $this->vista->render(
            'usuario/tramitesSolicitudes',
            [
                'token'   => $this->session->get('csrf_token'),
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'nombreRol' => $this->session->get('nombre_rol'),
                'titlePag' => 'Tramites y Solicitudes',
                'pag' => 'tramitesSolicitudes',
                'grup' => 'gremio',
                'fotoUsuario' => $urlPublica


            ],
            'usuario'
        );
    }


    public function buscar(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();
        $datos = $this->filtrarDatos([
            'limit' => 'esEntero',
            'offset' => 'esEntero',

        ]);
        if ($this->session->get('usuario_rol') >= 3) {
            $id =  $this->getDatosEntrada();
        }

        $cuenta = isset($id['cuenta_id'])  ? Validar::esDesencriptarId($id['cuenta_id']) : $this->session->get('usuario_id');



        $model = new CuotasModel();
        $cuotas = $model->traerPorUsuario($cuenta, $datos['limit'], $datos['offset']);
        $total = $model->totalCuotasUser($cuenta);


        $this->respuesta->json($cuotas, 200, "cuotas obtenidos con éxito", [], (int)$total);
    }


    public function listar(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();
        $datos = $this->filtrarDatos([
            'limit' => 'esEntero',
            'offset' => 'esEntero'
        ]);

        $filtros = $this->getDatosEntrada();
        $datos['buscar'] = Validar::esDesencriptarId($filtros['buscar']);
        $datos['status'] = Validar::esCadena($filtros['status'] ?? '') ?? null;

        $datos['id'] = $this->session->get('usuario_id');
        $datos['rol'] = $this->session->get('usuario_rol');
        $model = new CuotasModel();
        $cuotas = $model->paginar($datos);
        $total = count($cuotas);


        $this->respuesta->json($cuotas, 200, "Usuarios obtenidos con éxito", [], (int)$total);
    }



    public function destinariosBuscar(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();

        $datos = $this->filtrarDatos([
            'cuota_id' => 'esDesencriptarId'
        ]);



        $modelCuotas = new CuotasModel();

        $cuotas = $modelCuotas->obtenerPorId($datos['cuota_id']);
        $modelDestinario = new DestinarioModel;
        if ($cuotas['origen'] == 'Solicitud') {
            $result = $modelDestinario->traerPorOrigen(null);
        } else {
            $result = $modelDestinario->traerPorOrigen($cuotas['origen_id']);
        }

        $this->respuesta->json($result);
    }



    public function eliminar()
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();


        $cuotaId = $this->filtrarDatos(['id' => 'esDesencriptarId', 'pago_id' => 'esDesencriptarId']);
        $pagoId['id'] = $cuotaId['pago_id'];

        $cuotas = new CuotasModel;
        $pagos = new PagosModel;
        $db = new BuilderQuery;
        try {

            $db->beginTransaction();

            $cuotas->delete($cuotaId);
            $pagos->delete($pagoId);

            $db->commit();
            $this->respuesta->json(true);
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }


    public function actualizar()
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();


        $datos = $this->filtrarDatos([
            'id' => 'esDesencriptarId',
            'monto' => 'esDecimal',
            'corte' => 'esFecha',
            'status' => 'esCadena',
            'cuota' => 'esTexto',
            'cuenta_id' => 'esDesencriptarId'
        ]);


        $datos['corte'] .= ' ' . date('H:i:s');

        $condicion['id'] = $datos['id'];
        $cuenta = $datos['cuenta_id'];


        unset($datos['id']);
        unset($datos['cuenta_id']);

        $this->respuesta->json(true);
        $cuotas = new CuotasModel;

        $db = new BuilderQuery;
        $notificaciones = new Notificaciones;

        try {
            $db->beginTransaction();
            $cuotas->update($datos, $condicion);
            $mensaje = '
            Revisión Terminada:  
            Su pago ha sido Examinado y se actualizo el statuss
            Concepto: ' . $datos['cuota'] . '   
            Status:' . $datos['status'] . ' 
            Fecha de limite: ' . $datos['corte'] . '   
            ';

            $notificaciones->crear(
                $cuenta,
                'Información',
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
