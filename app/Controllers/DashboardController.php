<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Models\DatosModel;

class DashboardController extends Controller
{


    public function index(): void
    {
        $this->requerirAutenticacion();

        $model = new DatosModel();

        $datos = $model->datosPersonalesYLaborales(
            $this->session->get('usuario_id')
        );

        $reglasUsuario = [
            'nombre',
            'apellido',
            'id_cedula',
            'tlf',
            'direccion',
            'edad'
        ];

        $reglasLaboral = [
            'institucion_id',
            'cargo_id'
        ];


        $datosFaltantes = !$this->faltanDatos($reglasUsuario, $datos);
        $datosLaboralesFaltantes  = !$this->faltanDatos($reglasLaboral, $datos);

        $this->vista->render(
            'usuario/dashborad',
            [
                'datosLaboralesFaltantes' => $datosLaboralesFaltantes,
                'datosFaltantes' => $datosFaltantes,
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'datos' => $datos,
                'nombreRol' => $this->session->get('nombre_rol'),
                'titlePag' => 'Dashboard'
            ],
            'usuario'
        );
    }
}
