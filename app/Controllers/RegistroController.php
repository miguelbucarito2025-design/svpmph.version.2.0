<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Libs\BuilderQuery;
use App\Libs\Exceptions\AppException;
use App\Models\CuentasModel;
use App\Libs\Seguridad;
use App\Models\RolModel;
use App\Models\UsuarioTerminosModel;

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
    /**
     * Renderiza la vista del formulario de registro con los datos iniciales.
     *
     * @return void
     */
    public function registro(): void
    {
        $this->vista->render(
            'form/registro',
            [
                'ventana'   => 'registro',
                'token'     => $this->session->get('csrf_token'),
                'idTermino' => Seguridad::encriptarID(1)
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

            // 3. Obtener el nombre del rol para la sesión
            $rolData = $rolModel->obtenerPorId($datos['rol_id']);
            $nombreRol = $rolData['rol'] ?? 'Usuario';

            // Confirmar la transacción antes de iniciar sesión
            $db->commit();

            // 4. Asignación de variables globales de sesión utilizando el ID correcto ($cuentaId)
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
                $e->getMessage()
            );
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->respuesta->json(
                false,
                500,
                'Error interno al procesar el registro: ' . $e->getMessage()
            );
        }
    }
}
