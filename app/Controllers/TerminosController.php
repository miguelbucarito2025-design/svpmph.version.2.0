<?php


declare(strict_types=1);


namespace App\Controllers;

use App\Controllers\Abstract\Controller;
use App\Models\TerminosModel;

class TerminosController extends Controller
{


    /**
     * muestra los termnios y condiciones en una vista para los usuarios 
     * q se van a registrar por primera vez
     *
     * @return void
     */
    public function index(): void
    {
        $model = new TerminosModel;
        $result = $model->selecRolTermminostId(1);
        $this->vista->render('form/login', ['contenido' => $result['contenido']], 'terminos');
    }
}
