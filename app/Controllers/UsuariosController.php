<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Libs\Exceptions\AppException;
use App\Libs\Seguridad;
use App\Models\CargosModel;
use App\Models\DatosModel;
use App\Traits\ManejoFechasTrait;
use App\Models\DatosLaboralesModel;
use App\Models\InstitucionModel;
use App\Helpers\R2Service;

class UsuariosController extends Controller
{
    use ManejoFechasTrait;

    /**
     * renderiza el perfil del usuario
     *
     * @return void
     */
    public function perfil(): void
    {
        $this->requerirAutenticacion();

        $model = new DatosModel();

        $datos = $model->datosPersonales(
            $this->session->get('usuario_id')
        );
        $r2Service = new R2Service();
        $urlPublica = $r2Service->obtenerUrlPublica($this->session->get('foto_perfil'));

        $ruta = empty($datos) ? 'perfil/guardar' : 'perfil/actualizar';

        $this->vista->render(
            'usuario/perfil',
            [
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'datos' => $datos,
                'nombreRol' => $this->session->get('nombre_rol'),
                'titlePag' => 'Datos Personales',
                'grup' => 'perfil',
                'pag' => 'datosPersonales',
                'token' => $this->session->get('csrf_token'),
                'ruta' => $ruta,
                'fotoUsuario' => $urlPublica


            ],
            'usuario'
        );
    }



    public function guardar(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();
        $datos = $this->filtrarDatos(
            [
                'nombre' => 'esCadena',
                's_nombre' => 'esCadena',
                'apellido' => 'esCadena',
                's_apellido' => 'esCadena',
                'id_cedula' => 'esCedula',
                'tlf' => 'esTlf',
                'edad' => 'esFecha',
                'direccion' => 'esTexto'

            ]
        );

        $edad = $this->obtenerEdad($datos['edad']);
        if ($edad < 17 || $edad > 80) {
            throw new AppException('Usted no esta en el rango de edad Permitido');
        }

        $datos['ingreso'] =  date('Y-m-d H:i:s');
        $datos['cuenta_id'] = $this->session->get('usuario_id');
        $model = new DatosModel();


        $result = $model->save($datos);
        if (!$result) {
            throw new AppException('Error inesperado al guardar el dato');
        }
        $this->respuesta->json(
            null,
            201,
            'Datos guardados Correctamente',
            []
        );
    }


    public function actualizar(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();
        $datos = $this->filtrarDatos(
            [
                'nombre' => 'esCadena',
                's_nombre' => 'esCadena',
                'apellido' => 'esCadena',
                's_apellido' => 'esCadena',
                'id_cedula' => 'esCedula',
                'tlf' => 'esTlf',
                'edad' => 'esFecha',
                'direccion' => 'esTexto'

            ]
        );


        $edad = $this->obtenerEdad($datos['edad']);
        if ($edad < 17 || $edad > 80) {
            throw new AppException('Usted no esta en el rango de edad Permitido');
        }

        $model = new DatosModel();

        $result = $model->update($datos, ['cuenta_id' => $this->session->get('usuario_id')]);
        if (!$result) {
            throw new AppException('Error inesperado al actualizar los datos');
        }
        $this->respuesta->json(
            null,
            201,
            'Datos Actualizados Correctamente',
            []
        );
    }



    /**
     * Carga la vista de datos laborales del usuario.
     * Cifra directamente la fila única de datos laborales y el catálogo de instituciones.
     * 
     * @return void
     */
    public function datosLaborales(): void
    {
        $this->requerirAutenticacion();

        $model = new DatosLaboralesModel();
        $id = $this->session->get('usuario_id');

        $datos = $model->datosLaborales($id);

        if (!empty($datos)) {
            if (isset($datos['institucion_id'])) {
                $datos['institucion_id'] = Seguridad::encriptarID($datos['institucion_id']);
            }

            if (isset($datos['cargo_id'])) {
                $datos['cargo_id'] = Seguridad::encriptarID($datos['cargo_id']);
            }
        }

        $modelInstucion = new InstitucionModel();
        $institucion = $modelInstucion->selectNoId($id);

        if (!empty($institucion) && is_array($institucion)) {
            foreach ($institucion as &$i) {
                if (isset($i['id'])) {
                    $i['id'] = Seguridad::encriptarID($i['id']);
                }
            }
            unset($i);
        }
        $r2Service = new R2Service();
        $urlPublica = $r2Service->obtenerUrlPublica($this->session->get('foto_perfil'));

        $ruta = empty($datos) ? 'laboral/guardar' : 'laboral/actualizar';

        $this->vista->render(
            'usuario/datosLaborales',
            [
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'datos'         => $datos,
                'nombreRol'     => $this->session->get('nombre_rol'),
                'titlePag'      => 'Datos Laborales',
                'grup'          => 'perfil',
                'pag'           => 'datosLaborales',
                'token'         => $this->session->get('csrf_token'),
                'ruta'          => $ruta,
                'institucion'   => $institucion,
                'fotoUsuario' => $urlPublica


            ],
            'usuario'
        );
    }


    /**
     * Obtiene la lista de cargos asociados a una institución.
     * 
     * Este método espera recibir un 'institucion_id' encriptado.
     * Devuelve siempre un arreglo de filas (lista) con los IDs de cada cargo re-encriptados.
     *
     * @return void Envía la respuesta en formato JSON.
     */
    public function cargos()
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();

        $datos = $this->filtrarDatos([
            'institucion_id' => 'esDesencriptarId'
        ]);


        $model = new CargosModel();
        $result = $model->obtenerPorInstitucion($datos['institucion_id']);

        if (empty($result)) {
            $this->respuesta->json([], 200, 'No se encontraron cargos para la institución especificada.');
            return;
        }

        if (isset($result['id'])) {
            $result['id'] = Seguridad::encriptarID($result['id']);
        } else {
            foreach ($result as &$r) {
                if (isset($r['id'])) {
                    $r['id'] = Seguridad::encriptarID($r['id']);
                }
            }
            unset($r);
        }
        $this->respuesta->json($result, 200, 'Cargos obtenidos exitosamente.');
    }


    public function guardarDatosLaborales(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();
        $datos = $this->filtrarDatos([
            'institucion_id' => 'esDesencriptarId',
            'cargo_id' => 'esDesencriptarId'
        ]);

        $datos['cuenta_id'] = $this->session->get('usuario_id');
        $model = new DatosLaboralesModel();
        $result = $model->save($datos);
        if (!$result) {
            throw new AppException("error al guardar los datos laborales");
        }

        $this->respuesta->json(
            null,
            201,
            'Guardado con exito',
            []
        );
    }


    public function actualizarDatosLaborales(): void
    {
        $this->requerirAutenticacion();
        $this->verificarCSRF();
        $datos = $this->filtrarDatos([
            'institucion_id' => 'esDesencriptarId',
            'cargo_id' => 'esDesencriptarId'
        ]);

        $condicion['cuenta_id'] = $this->session->get('usuario_id');

        $model = new DatosLaboralesModel();
        $result = $model->update($datos, $condicion);
        if (!$result) {
            throw new AppException("error al guardar los datos laborales");
        }

        $this->respuesta->json(
            null,
            200,
            'Actualizado con exito',
            []
        );
    }
}
