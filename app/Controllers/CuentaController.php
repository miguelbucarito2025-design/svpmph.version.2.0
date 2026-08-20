<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Libs\Exceptions\AppException;
use App\Models\CuentasModel;
use App\Traits\MensageTrait;
use App\Helpers\R2Service;
use App\Models\DatosModel;

/**
 * Clase CuentaController
 *
 * Controlador encargado de la administración del perfil del usuario autenticado
 * (actualización de nombre de usuario, cambio de correo en 2 pasos y cambio de contraseña).
 *
 * @package App\Controllers
 */
class CuentaController extends Controller
{
    use MensageTrait;

    /**
     * Renderiza la vista principal del perfil con los datos actuales del usuario.
     *
     * @return void
     */
    public function index(): void
    {
        $this->requerirAutenticacion();

        $usuarioId = (int) $this->session->get('usuario_id');
        $modelo = new CuentasModel();
        $datosCuenta = $modelo->obtenerPorId($usuarioId);

        if (empty($datosCuenta)) {
            throw new AppException('No se encontraron los datos de la cuenta.', 404);
        }
        $r2Service = new R2Service();
        $urlPublica = $r2Service->obtenerUrlPublica($this->session->get('foto_perfil'));

        // Retiramos la contraseña del arreglo por seguridad antes de pasar a la vista
        unset($datosCuenta['contrasena']);

        $this->vista->render(
            'usuario/cuenta',
            [
                'ventana' => 'cuenta',
                'token'   => $this->session->get('csrf_token'),
                'cuenta'  => $datosCuenta,
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'nombreRol' => $this->session->get('nombre_rol'),
                'titlePag' => 'Cuenta',
                'pag' => 'cuenta',
                'grup' => 'perfil',
                'fotoUsuario' => $urlPublica

            ],
            'usuario'
        );
    }
    public function fotoPerfil(): void
    {
        $this->requerirAutenticacion();

        $r2Service = new R2Service();
        $urlPublica = $r2Service->obtenerUrlPublica($this->session->get('foto_perfil'));


        $this->vista->render(
            'usuario/fotoPerfil',
            [
                'token'   => $this->session->get('csrf_token'),
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'nombreRol' => $this->session->get('nombre_rol'),
                'titlePag' => 'Foto Perfil',
                'pag' => 'foto',
                'grup' => 'perfil',
                'fotoUsuario' => $urlPublica,

            ],
            'usuario'
        );
    }
    /**
     * Procesa la actualización del nombre de usuario mediante AJAX.
     *
     * @return void
     */
    public function actualizarUsuario(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();

        $usuarioId = (int) $this->session->get('usuario_id');

        $datos = $this->filtrarDatos([
            'usuario' => 'esNombreUsuario'
        ]);

        $modelo = new CuentasModel();

        // Validamos que el nombre no esté registrado por OTRA cuenta
        if ($modelo->existeUsuarioEnOtroId($datos['usuario'], $usuarioId)) {
            throw new AppException('El nombre de usuario ya está en uso por otra cuenta.', 400);
        }

        $exito = $modelo->actualizarNombreUsuario($usuarioId, $datos['usuario']);

        if (!$exito) {
            throw new AppException('No se pudo actualizar el nombre de usuario.', 500);
        }

        // Actualizamos la variable global de sesión
        $this->session->set('usuario_nombre', $datos['usuario']);

        $this->respuesta->json(
            true,
            200,
            'Nombre de usuario actualizado con éxito.'
        );
    }

    /**
     * Inicia el flujo de cambio de correo guardando el 'correo_pendiente'
     * y enviando el código de 6 dígitos al nuevo correo.
     *
     * @return void
     */
    public function solicitarCodigoCorreo(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();

        $usuarioId = (int) $this->session->get('usuario_id');

        $datos = $this->filtrarDatos([
            'nuevo_correo' => 'esCorreo'
        ]);

        $modelo = new CuentasModel();

        // Generamos el token de 6 dígitos formateado como string puro
        $token = (string) random_int(100000, 999999);
        $expiracion = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $guardado = $modelo->guardarTokenVerificacion(
            $usuarioId,
            $datos['nuevo_correo'],
            $token,
            $expiracion
        );

        if (!$guardado) {
            throw new AppException('No se pudo registrar la solicitud de cambio de correo.', 500);
        }

        // Envío del código al correo nuevo ingresado
        $this->enviarToken($datos['nuevo_correo'], $token);

        $this->respuesta->json(
            true,
            200,
            'Código de verificación enviado al nuevo correo electrónico.'
        );
    }

    /**
     * Valida el código de 6 dígitos e impacta el cambio de correo definitivo.
     *
     * @return void
     */
    public function verificarCodigoCorreo(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();

        $usuarioId = (int) $this->session->get('usuario_id');

        $datos = $this->filtrarDatos([
            'codigo' => 'esTexto'
        ]);

        $modelo = new CuentasModel();
        $cuenta = $modelo->obtenerPorId($usuarioId);

        if (empty($cuenta) || empty($cuenta['correo_pendiente'])) {
            throw new AppException('No existe ninguna solicitud de cambio de correo pendiente.', 400);
        }

        $tiempoActual = date('Y-m-d H:i:s');

        // La verificación de expiración e intentos (máximo 3) la resuelve la lógica del modelo
        $modelo->validarTokenPorId($usuarioId, $datos['codigo'], $tiempoActual);

        // Promovemos correo_pendiente a correo principal y limpiamos tokens en BD
        $exito = $modelo->confirmarNuevoCorreo($usuarioId, $cuenta['correo_pendiente']);

        if (!$exito) {
            throw new AppException('Error al actualizar el correo electrónico.', 500);
        }

        // =========================================================================
        // PASO FALTANTE: Actualizar la variable de sesión con el nuevo correo
        // =========================================================================
        $this->session->set('usuario_correo', $cuenta['correo_pendiente']);

        $this->respuesta->json(
            true,
            200,
            'Correo electrónico actualizado exitosamente.'
        );
    }
    /**
     * Procesa el cambio de contraseña comprobando la contraseña actual.
     *
     * @return void
     */
    public function cambiarContrasena(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();

        $usuarioId = (int) $this->session->get('usuario_id');

        $datos = $this->filtrarDatos([
            'actual_contrasena' => 'esPassword',
            'nueva_contrasena'  => 'esPassword'
        ]);

        $modelo = new CuentasModel();
        $cuenta = $modelo->obtenerPorId($usuarioId);

        if (empty($cuenta) || !password_verify($datos['actual_contrasena'], $cuenta['contrasena'])) {
            throw new AppException('La contraseña actual es incorrecta.', 400);
        }

        $nuevoHash = password_hash($datos['nueva_contrasena'], PASSWORD_BCRYPT);
        $exito = $modelo->actualizarContrasena($usuarioId, $nuevoHash);

        if (!$exito) {
            throw new AppException('No se pudo actualizar la contraseña.', 500);
        }

        $this->respuesta->json(
            true,
            200,
            'Contraseña actualizada con éxito.'
        );
    }
    /**
     * Procesa la carga asíncrona de la foto de perfil del usuario.
     * Actualiza el registro en la base de datos, refresca la sesión y retorna la URL pública.
     * 
     * Ruta esperada: POST /cuenta/foto-perfil
     *
     * @return void Emite respuesta JSON directa.
     */
    public function cambiarFotoPerfil(): void
    {
        // 1. Capturar y validar el archivo recibido
        $archivo = $this->filtrarArchivo('foto', ['jpg', 'jpeg', 'png', 'webp'], 2);

        if (empty($archivo['valido'])) {
            $this->respuesta->json(
                null,
                400,
                $archivo['error'] ?? 'El archivo seleccionado no es válido.',
                ['foto' => 'Archivo no permitido o excede el tamaño']
            );
            return;
        }
        $this->verificarCSRF();
        $this->requerirAutenticacion();

        $usuarioId  = $this->session->get('usuario_id');
        $extension  = $archivo['extension'];

        $keyDestino = "perfiles/usuario_{$usuarioId}_" . time() . ".{$extension}";

        $r2Service = new R2Service();
        $resultado = $r2Service->subirArchivo($archivo['tmp_name'], $keyDestino, $archivo['mime']);

        if (!$resultado['exito']) {
            $this->respuesta->json(
                null,
                500,
                $resultado['error'] ?? 'Error al almacenar el archivo en la nube.',
                ['storage' => 'Fallo al conectar con Cloudflare R2']
            );
            return;
        }



        $model = new DatosModel();
        $actualizado = $model->guardarFoto($usuarioId, $keyDestino);


        if (!$actualizado) {
            $r2Service->eliminarArchivo($keyDestino);
            $this->respuesta->json(
                null,
                400,
                'No se pudo guardar la imagen ya que Usted no se ha registrado, debe registrar sus datos personales antes para continuar con esta accion.'
            );
            return;
        }

        $r2Service->eliminarArchivo($actualizado);
        $urlPublica = $r2Service->obtenerUrlPublica($keyDestino);

        $this->session->set('foto_perfil', $keyDestino);

        $this->respuesta->json(
            [
                'url' => $urlPublica
            ],
            200,
            'Foto subida correctamente'
        );
    }
}
