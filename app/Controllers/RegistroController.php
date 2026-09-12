<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Libs\BuilderQuery;
use App\Libs\Exceptions\AppException;
use App\Models\CuentasModel;
use App\Libs\Seguridad;
use App\Models\RolModel;
use App\Models\TerminosModel;
use App\Models\UsuarioTerminosModel;
use App\Traits\MensageTrait;

/**
 * Clase RegistroController
 *
 * Controlador encargado de la visualización del formulario de registro
 * y del procesamiento de alta de nuevas cuentas en el sistema.
 *
 * @package App\Controllers
 */
class RegistroController extends Controller
{

    use MensageTrait;

    /**
     * Renderiza la vista del formulario de registro con los datos iniciales.
     *
     * @return void
     */
    public function registro(): void
    {


        $model = new TerminosModel();
        $idTermino = $model->seleccionarPorRol(1);
        $this->vista->render(
            'form/registro',
            [
                'ventana'   => 'registro',
                'token'     => $this->session->get('csrf_token'),
                'idTermino' => Seguridad::encriptarID($idTermino['id'])
            ],
            'form'
        );
    }

    /**
     * Procesa la solicitud HTTP POST para registrar una nueva cuenta y sus términos.
     *
     * @return void
     */
    public function guardarRegistro(): void
    {
        $this->verificarCSRF();

        $datos = $this->filtrarDatos([
            'usuario'     => 'esNombreUsuario',
            'contrasena'  => 'esPassword',
            'correo'      => 'esCorreo',
            'terminos_id' => 'esDesencriptarId'
        ]);

        $terminosIdTemp = (int) $datos['terminos_id'];
        unset($datos['terminos_id']);

        // Valores asignados por defecto para el registro público
        $datos['estado'] = 1;
        $datos['rol_id'] = 1;
        $datos['correo_pendiente'] = $datos['correo'];
        // Hasheo obligatorio de la contraseña antes de guardar
        $datos['contrasena'] = password_hash($datos['contrasena'], PASSWORD_BCRYPT);

        $db = new BuilderQuery();
        $terminosModel = new UsuarioTerminosModel();
        $cuentasModel = new CuentasModel();
        $rolModel = new RolModel();

        try {
            $db->beginTransaction();

            // 1. Guardar la cuenta de usuario
            $guardadoExitosoCuenta = $cuentasModel->save($datos);
            if (!$guardadoExitosoCuenta) {
                throw new AppException('No se pudo procesar la creación de la cuenta', 400);
            }

            // Capturamos de forma precisa el ID generado para la cuenta
            $cuentaId = (int) $db->lastInsertId();

            // 2. Vincular la aceptación de términos
            $datosTerminos = [
                'cuenta_id'   => $cuentaId,
                'terminos_id' => $terminosIdTemp,
                'fecha'       => $this->getCreatedAt(),
                'respuesta'   => 'Acepto los terminos y condiciones'
            ];

            $guardadoExitosoTerminos = $terminosModel->save($datosTerminos);
            if (!$guardadoExitosoTerminos) {
                throw new AppException('No se pudo registrar la aceptación de términos', 500);
            }

            $rolData = $rolModel->obtenerPorId($datos['rol_id']);
            $nombreRol = $rolData['rol'] ?? 'Usuario';


            $token = (string) random_int(100000, 999999);
            $expiracion = date('Y-m-d H:i:s', strtotime('+30 minutes'));

            $cuentasModel->insertarCodigo($datos['correo'], $token, $expiracion);

            $this->enviarToken($datos['correo'], $token);
            $db->commit();

            $this->session->set('nombre_rol', $nombreRol);
            $this->session->set('usuario_id', $cuentaId);
            $this->session->set('usuario_rol', $datos['rol_id']);
            $this->session->set('usuario_nombre', $datos['usuario']);

            $this->respuesta->json(
                true,
                201,
                'Cuenta creada con éxito'
            );
        } catch (AppException $e) {
            $db->rollBack();
            $this->respuesta->json(
                false,
                $e->getCode() > 0 ? $e->getCode() : 400,
                ($e->getCode() == 409) ? 'Usuario o Correo Duplicado' : '',
                []
            );
        } catch (\Throwable $e) {
            $db->rollBack();
            throw new  AppException('Error Interno : ' . $e->getMessage(), 500);
        }
    }
}
