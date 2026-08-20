<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Models\DatosModel;
use App\Helpers\R2Service;

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
            'edad',
            'foto'
        ];

        $reglasLaboral = [
            'institucion_id',
            'cargo_id'
        ];
        $r2Service = new R2Service();
        $foto = $datos['foto'] ?? 'perfiles/user.png';
        $urlPublica = $r2Service->obtenerUrlPublica($foto);

        $this->session->set('foto_perfil', $foto);

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
                'titlePag' => 'Dashboard',
                'fotoUsuario' => $urlPublica
            ],
            'usuario'
        );
    }
}
