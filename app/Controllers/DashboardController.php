<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Models\DatosModel;
use App\Helpers\R2Service;
use App\Models\ArchivosModel;
use App\Models\CuotasModel;
use App\Models\GremioModel;
use App\Traits\ManejoArchivosR2Trait;

class DashboardController extends Controller
{

    use ManejoArchivosR2Trait;

    public function index(): void
    {
        $this->requerirAutenticacion();

        $model = new DatosModel();
        $archivos = new ArchivosModel;
        $id = $this->session->get('usuario_id');
        $total['archivos'] = $archivos->select('count');
        $cuotas = new  CuotasModel;
        $total['cuotas'] = $cuotas->obtenerTotal($id, $this->session->get('usuario_rol'));
        $gremio = new GremioModel;
        $total['gremio'] = $gremio->obtenerTotalGremio();


        $datos = $model->datosPersonalesYLaborales(
            $this->session->get('usuario_id')
        );

        $foto = $datos['foto'] ?? 'perfiles/user.png';

        $urlPublica = $this->obtenerArchivo($foto);
        $this->session->set('foto_perfil', $foto);

        $this->vista->render(
            'usuario/dashborad',
            [
                'datos' => $datos,
                'prueva' => $datos,
                'total' => $total,
                'nombreUsuario' => $this->session->get('usuario_nombre'),
                'nombreRol' => $this->session->get('nombre_rol'),
                'titlePag' => 'Dashboard',
                'fotoUsuario' => $urlPublica
            ],
            'usuario'
        );
    }
}
