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
 * formulario de acceso e inicio/cierre de sesiones en el sistema.
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
                'token' => $this->session->get('csrf_token')
            ],
            'form'
        );
    }

    /**
     * Procesa la petición de inicio de sesión mediante POST.
     * 
     * Valida la protección CSRF, sanitiza la entrada, verifica las credenciales
     * contra la base de datos y establece las variables globales de sesión.
     *
     * @return void
     */
    public function autenticar(): void
    {
        // NO se llama a requerirAutenticacion() porque es una ruta pública de acceso.
        $this->verificarCSRF();

        $datos = $this->filtrarDatos([
            'usuario'    => 'esNombreUsuario',
            'contrasena' => 'esPassword'
        ]);

        $modelo = new CuentasModel();
        $resultado = $modelo->obtenerPorUsuario($datos['usuario']);

        // Verificación de existencia del usuario y hash de contraseña
        if (empty($resultado) || !password_verify($datos['contrasena'], $resultado['contrasena'])) {
            $this->respuesta->json(
                null,
                400,
                'Credenciales inválidas. Verifique su usuario y contraseña.'
            );
            return;
        }

        // Asignación de claves estandarizadas de sesión
        $this->session->set('usuario_datos', $resultado);
        $this->session->set('usuario_id', $resultado['id']);
        $this->session->set('usuario_rol', $resultado['rol_id']);
        $this->session->set('usuario_nombre', $resultado['usuario']);

        $this->respuesta->json(
            true,
            200,
            'Inicio de sesión exitoso'
        );
    }


    public function enviarRecuperacion(): void
    {
        $this->verificarCSRF();

        $correo = $this->filtrarDatos([
            'correo' => 'esCorreo'
        ]);

        $modelo = new CuentasModel();
        $token = random_int(100000, 999999);
        $fecha = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $modelo->insertarCodigo($correo['correo'], $token, $fecha);

        $this->enviarToken($correo['correo'], $token);

        $this->session->set('correo_de_verificacion', $correo['correo']);

        $this->respuesta->json(null, 200, 'token enviado');
    }



    /**
     * verifica el token del usuario
     *
     * @return void
     */
    public function tokenResivido(): void
    {
        $this->verificarCSRF();

        $datos = $this->filtrarDatos([
            'token'    => 'esEntero',
            'correo' => 'esCorreo'
        ]);

        $modelo = new CuentasModel();
        $tiempoActual = date('Y-m-d H:i:s');
        $resultado = $modelo->validarToken($datos['correo'], $datos['token'], $tiempoActual);


        // Asignación de claves estandarizadas de sesión
        $this->session->set('usuario_datos', $resultado);
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
     * renderiza la vista para recuperar la clave
     *
     * @return void
     */
    public function recuperarClave(): void
    {
        $this->vista->render(
            'form/recuperar',
            [
                'ventana' => 'recuperar',
                'token' => $this->session->get('csrf_token')
            ],
            'form'
        );
    }


    public function resividoToken(): void
    {
        $this->vista->render(
            'form/colocarCodigo',
            [
                'ventana' => 'recuperar',
                'token' => $this->session->get('csrf_token'),
                'correo' => $this->session->get('correo_de_verificacion')
            ],
            'form'
        );
    }


    /**
     * Destruye la session
     *
     * @return void
     */
    public function logout(): void
    {
        $this->session->destroy();
        $this->respuesta->redirect('http://localhost/svpmph.version.2.0/');
    }
}
