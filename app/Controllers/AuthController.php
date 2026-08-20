<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Libs\Exceptions\AppException;
use App\Models\CuentasModel;
use App\Traits\MensageTrait;

/**
 * Clase AuthController
 *
 * Controlador encargado de la autenticación de usuarios, despliegue del 
 * formulario de acceso, inicio/cierre de sesiones y recuperación pública de clave.
 *
 * @package App\Controllers
 */
class AuthController extends Controller
{
    use MensageTrait;

    /**
     * Renderiza la vista del formulario de inicio de sesión.
     *
     * @return void
     */
    public function login(): void
    {
        $this->vista->render(
            'form/login',
            [
                'ventana' => 'login',
                'token'   => $this->session->get('csrf_token')
            ],
            'form'
        );
    }

    /**
     * Procesa la petición de inicio de sesión mediante POST.
     *
     * @return void
     */
    public function autenticar(): void
    {
        $this->verificarCSRF();

        $datos = $this->filtrarDatos([
            'usuario'    => 'esNombreUsuario',
            'contrasena' => 'esPassword'
        ]);

        $modelo = new CuentasModel();
        $resultado = $modelo->obtenerPorUsuario($datos['usuario']);

        // Verificación de credenciales
        if (empty($resultado) || !password_verify($datos['contrasena'], $resultado['contrasena'])) {
            $this->respuesta->json(
                null,
                400,
                'Credenciales inválidas. Verifique su usuario y contraseña.'
            );
            return;
        }

        // Asignación de variables globales de sesión
        $this->session->set('nombre_rol', $resultado['rol']);
        $this->session->set('usuario_id', $resultado['id']);
        $this->session->set('usuario_rol', $resultado['rol_id']);
        $this->session->set('usuario_nombre', $resultado['usuario']);

        $this->respuesta->json(
            true,
            200,
            'Inicio de sesión exitoso'
        );
    }

    /**
     * Genera y envía un código de recuperación al correo del usuario.
     *
     * @return void
     */
    public function enviarRecuperacion(): void
    {
        $this->verificarCSRF();

        $datos = $this->filtrarDatos([
            'correo' => 'esCorreo'
        ]);

        $modelo = new CuentasModel();

        // Código de 6 dígitos formateado como string puro
        $token = (string) random_int(100000, 999999);
        $expiracion = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $modelo->insertarCodigo($datos['correo'], $token, $expiracion);

        // Envío manteniendo el tipo string (evita recorte de ceros a la izquierda)
        $this->enviarToken($datos['correo'], $token);

        $this->session->set('correo_de_verificacion', $datos['correo']);

        $this->respuesta->json(null, 200, 'Código de recuperación enviado exitosamente.');
    }


    /**
     * Verifica el token de 6 dígitos ingresado por el usuario para recuperar acceso.
     *
     * @return void
     */
    public function tokenRecibido(): void
    {
        $this->verificarCSRF();

        $datos = $this->filtrarDatos([
            'token'  => 'esTexto',
            'correo' => 'esCorreo'
        ]);

        $modelo = new CuentasModel();
        $tiempoActual = date('Y-m-d H:i:s');

        // Si el token es inválido o venció, el modelo dispara una AppException
        // que flota directo a tu Atrapador Global de Errores.
        $resultado = $modelo->validarToken($datos['correo'], $datos['token'], $tiempoActual);

        $this->session->set('nombre_rol', $resultado['rol']);
        $this->session->set('usuario_id', $resultado['id']);
        $this->session->set('usuario_rol', $resultado['rol_id']);
        $this->session->set('usuario_nombre', $resultado['usuario']);
        $this->session->set('correo_usuario', $datos['correo']);

        $this->respuesta->json(
            true,
            200,
            'Acceso concedido correctamente'
        );
    }

    /**
     * actualiza el usuario y la contraseña para q pueda acceder asu cuenta
     *
     * @return void
     */
    public function actualizarUsuarioContrasena(): void
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();

        $datos = $this->filtrarDatos([
            'usuario'  => 'esNombreUsuario',
            'contrasena' => 'esPassword',
            'contrasena_confirm' => 'esPassword'
        ]);
        if ($datos['contrasena'] !== $datos['contrasena_confirm']) {
            throw new AppException('Datos invalidos Verifique y vuelva a intentarlo', 400);
        }

        $modelo = new CuentasModel();
        $datos['contrasena'] = password_hash($datos['contrasena'], PASSWORD_BCRYPT);

        $correo = $this->session->get('correo_usuario');
        $resultado = $modelo->actualizarUsuarioContrasenaPorCorreo($correo, $datos['usuario'], $datos['contrasena']);
        if (!$resultado) {
            throw new AppException('No se pudo actualizar los nuevos datos', 500);
        }
        $this->session->set('usuario_nombre', $datos['usuario']);

        $this->respuesta->json(
            true,
            200,
            'Acceso concedido correctamente'
        );
    }

    /**
     * Renderiza la vista para solicitar la recuperación de clave.
     *
     * @return void
     */
    public function recuperarClave(): void
    {
        $this->vista->render(
            'form/recuperar',
            [
                'ventana' => 'recuperar',
                'token'   => $this->session->get('csrf_token')
            ],
            'form'
        );
    }

    /**
     * Renderiza la vista donde el usuario ingresa el código de 6 dígitos.
     *
     * @return void
     */
    public function vistaColocarCodigo(): void
    {
        $this->vista->render(
            'form/colocarCodigo',
            [
                'ventana' => 'recuperar',
                'token'   => $this->session->get('csrf_token'),
                'correo'  => $this->session->get('correo_de_verificacion')
            ],
            'form'
        );
    }

    /**
     * Destruye la sesión activa del usuario y redirige al inicio.
     *
     * @return void
     */
    public function logout(): void
    {
        $this->session->destroy();
        $this->respuesta->redirect('login');
    }
}
