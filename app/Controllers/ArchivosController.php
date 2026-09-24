<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Libs\Exceptions\AppException;
use App\Helpers\R2Service;
use App\Helpers\Validar;
use App\Models\ArchivosModel;
use App\Traits\CifrarTrait;
use App\Traits\ManejoArchivosR2Trait;

class ArchivosController extends Controller
{

    use ManejoArchivosR2Trait;
    use CifrarTrait;

    public function  index(): void
    {
        $this->requerirAutenticacion();
        $urlPublica = $this->obtenerArchivo($this->session->get('foto_perfil'));



        $this->vista->render(
            'usuario/documentos',
            [
                'token'   => $this->session->get('csrf_token'),
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'nombreRol' => $this->session->get('nombre_rol'),
                'titlePag' => 'Archivos',
                'pag' => 'documentos',
                'grup' => 'gremio',
                'fotoUsuario' => $urlPublica,


            ],
            'usuario'
        );
    }






    /**
     * Procesa la actualización del archivo adjunto en la base de datos y Cloudflare R2.
     */
    public function actualizar(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();

        $datosEntrada = $this->filtrarDatos([
            'id' => 'esDesencriptarId',
        ]);

        $id = $datosEntrada['id'];
        $condicion = ['id' => $id];

        $model = new ArchivosModel();
        $fileActual = $model->traerPorId($id, false);

        if (!$fileActual) {
            $this->respuesta->json(null, 404, 'El Archivo no existe.');
            return;
        }

        $datosActualizar = [];
        $archivosNuevosSubidos = [];
        $archivosViejosAEliminar = [];
        $reglasImagen = [['jpg', 'jpeg', 'png', 'webp', 'pdf'], 2];

        $campoInput = isset($_FILES['img']) ? 'img' : 'archivo';

        if (!empty($_FILES[$campoInput]['name'])) {
            $imgFiltrada = $this->filtrarArchivo($campoInput, ...$reglasImagen);

            $resImg = $this->subirArchivoR2($imgFiltrada, 'documento', $campoInput);

            if (!$resImg['exito']) {
                $this->respuesta->json(null, 400, $resImg['error']);
                return;
            }

            $datosActualizar['url'] = $resImg['key'];
            $archivosNuevosSubidos[] = $resImg['key'];

            $keyVieja = $fileActual['url'] ?? $fileActual['archivo'] ?? null;
            if (!empty($keyVieja)) {
                $archivosViejosAEliminar[] = $keyVieja;
            }
        }

        if (empty($datosActualizar)) {
            $this->respuesta->json(null, 400, 'No se proporcionó ningún archivo para actualizar.');
            return;
        }

        try {
            if (!$model->update($datosActualizar, $condicion)) {
                $this->eliminarArchivosR2($archivosNuevosSubidos);

                throw new AppException('Error interno al actualizar en la base de datos', 500);
            }
        } catch (\Exception $e) {
            $this->eliminarArchivosR2($archivosNuevosSubidos);
            $this->respuesta->json(null, 500, 'Error interno del servidor: ' . $e->getMessage());
            return;
        }


        $this->respuesta->json([], 200, 'Archivo actualizado correctamente.');
    }


    public function traerArchivos(): void
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();
        $datos = $this->filtrarDatos(['id' => 'esDesencriptarId']);

        $model = new ArchivosModel();
        $result = $model->traerPorUsuario($datos['id']);
        $this->respuesta->json($result);
    }


    public function eliminarArchivo(): void
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();
        $datos = $this->filtrarDatos(['id' => 'esDesencriptarId']);


        if (empty($datos)) {
            throw new AppException('No se proporsiono el id', 400);
        }

        $model = new ArchivosModel;
        $result = $model->traerPorId($datos['id'], false);
        $r2Service = new R2Service();

        $imgEliminar = $r2Service->eliminarArchivo($result['url']);

        if (!$imgEliminar) {
            throw new AppException('No se pudo eliminar El recurso ', 500);
        }

        $registrosEliminados = $model->update(['url' => null, 'verificado' => false], $datos);

        if ($registrosEliminados) {
            $this->respuesta->json([
                'exito' => true,
                'mensaje' => "Se elimino correctamente."
            ], 200, '');
        } else {
            $this->respuesta->json([
                'exito' => false,
                'mensaje' => 'No se pudo eliminar .'
            ], 409, '');
        }
    }


    public function listar(): void
    {
        $this->verificarCSRF();
        $this->requerirAutenticacion();

        $datos = $this->session->get('usuario_id');
        $model = new ArchivosModel();
        $result = $model->traerPorUsuario($datos);
        $this->respuesta->json($result);
    }


    public function verificarLote(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();
        $ids = $this->getDatosEntrada();

        $verificar =  $ids['verificar_ids'] ?? [];
        $noVerificar = $ids["pendiente_ids"] ?? [];
        $cuenta_id = Validar::esDesencriptarId($ids['cuenta_id']);
        $idsVerificarLimpios = [];
        $idsNoVerificarLimpios = [];

        foreach ($verificar as $v) {
            $id = Validar::esDesencriptarId($v);
            if ($id == null) {
                throw new AppException('Id invalido : ' . $v, 400);
            }

            $idsVerificarLimpios[] = $id;
        }


        foreach ($noVerificar as $n) {
            $id = Validar::esDesencriptarId($n);
            if ($id === null) {
                throw new AppException('Id invalido : ' . $n, 400);
            }
            $idsNoVerificarLimpios[] = $id;
        }

        $model = new ArchivosModel;


        if (!empty($idsVerificarLimpios)) {
            $model->verificar($idsVerificarLimpios, $cuenta_id);
        }
        if (!empty($idsNoVerificarLimpios)) {
            $model->verificar($idsNoVerificarLimpios, $cuenta_id, 0);
        }


        $this->respuesta->json(true);
    }




    /**
     * Endpoint GET: /archivo/obtener/{token}
     * 
     * Recibe los parámetros desencriptados automáticamente por el Enrutador,
     * valida la pertenencia contra la sesión actual y sirve el flujo del archivo.
     *
     * @param array $parametros Arreglo asociativo desencriptado por el Enrutador.
     * @return void
     * @throws AppException Si la petición carece de permisos o el recurso no existe.
     */
    public function obtener(array $parametros = []): void
    {
        // 1. PASO CRUCIAL: Extraer los datos de la sesión PRIMERO
        $usuarioSesionId = $this->session->get('usuario_id');
        $rolUsuario      = $this->session->get('usuario_rol');

        // 2. AHORA SÍ: Liberar el bloqueo de archivo de la sesión para peticiones concurrentes
        session_write_close();

        // 3. Validar que la desencriptación del Enrutador haya retornado un paquete válido
        if (empty($parametros) || !isset($parametros['r2_key'])) {
            throw new AppException('Recurso no válido o firma alterada.', 403);
        }

        // 4. Control de pertenencia: Verificar que el recurso le pertenezca al usuario en sesión
        $usuarioPropietario = $parametros['usuario_id'] ?? null;

        // Permitir el acceso si el archivo le pertenece al usuario o si tiene rol administrativo (1)
        if ($usuarioPropietario !== $usuarioSesionId && (int)$rolUsuario !== 1) {
            throw new AppException('Acceso denegado: No tienes permisos para ver este archivo.', 403);
        }

        $r2Key = $parametros['r2_key'];

        // 5. Prevenir inyecciones de ruta o accesos inválidos
        if (empty($r2Key) || str_contains($r2Key, '..')) {
            throw new AppException('Ruta de recurso inválida.', 403);
        }

        // 6. Solicitar los datos binarios del archivo al almacenamiento privado en R2
        $r2Service = new R2Service();
        $archivo = $r2Service->obtenerObjeto($r2Key);

        if (!$archivo) {
            throw new AppException('El archivo solicitado no existe o no se encuentra disponible.', 404);
        }

        // 7. Limpiar cualquier búfer de salida previo solo si NO estamos en consola CLI
        if (ob_get_level() && php_sapi_name() !== 'cli') {
            ob_end_clean();
        }

        if (!headers_sent()) {
            header("Content-Type: {$archivo['ContentType']}");
            header("Content-Length: {$archivo['ContentLength']}");
            header("Cache-Control: private, no-transform, max-age=3600");
        }

        echo (string) $archivo['Body'];

        if (php_sapi_name() !== 'cli') {
            exit;
        }
    }
}
