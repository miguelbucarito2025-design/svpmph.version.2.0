<?php



declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Libs\BuilderQuery;
use App\Libs\Exceptions\AppException;
use App\Models\CuentasModel;
use App\Libs\Seguridad;
use App\Models\UsuarioTerminosModel;

class RegistroController extends Controller
{



    public function registro(): void
    {
        $this->vista->render(
            'form/registro',
            [
                'ventana' => 'registro',
                'token' => $this->session->get('csrf_token'),
                'idTermino' => Seguridad::encriptarID(1)
            ],
            'form'
        );
    }



    /**
     * Procesa la solicitud HTTP para registrar una nueva cuenta en el sistema.
     *
     * @return void
     */
    public function guardarRegistro(): void
    {
        $this->verificarCSRF();

        $datos = $this->filtrarDatos([
            'usuario'     => 'esNombreUsuario',
            'contrasena' => 'esPassword',
            'correo' => 'esCorreo',
            'terminos_id' => 'esDesencriptarId'
        ]);

        $datos['estado'] = 1;
        $datos['rol_id'] = 1;

        $terminosIdTemp = $datos['terminos_id'];
        unset($datos['terminos_id']);

        $db = new BuilderQuery();
        $terminos = new UsuarioTerminosModel();
        $model = new CuentasModel();

        try {
            $db->beginTransaction();

            $guardadoExitosoCuenta = $model->save($datos);

            $cuentaId = $db->lastInsertId();

            $datosTerminos = [
                'cuenta_id' => $cuentaId,
                'terminos_id' => $terminosIdTemp,
                'fecha' => $this->getCreatedAt(),
                'respuesta' => 'Acepto los terminos y condiciones'
            ];

            $guardadoExitosoTerminos = $terminos->save($datosTerminos);

            if (!$guardadoExitosoCuenta || !$guardadoExitosoTerminos) {
                throw new AppException('No se pudo crear la cuenta debido a un error interno', 500);
            }

            $db->commit();

            $this->respuesta->json(
                true,
                201,
                'Cuenta creada con éxito'
            );
        } catch (\Throwable $e) {
            $db->rollBack();


            $this->respuesta->json(
                false,
                500,
                'Error al guardar la cuenta'
            );
        }
    }
}
