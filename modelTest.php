<?php

declare(strict_types=1);



require_once 'vendor/autoload.php';



use App\Libs\ManejadorExcepciones;
use App\Helpers\EnvLoader;
use App\Models\NotificacionesModel;

// Carga de variables de entorno
EnvLoader::load('app/Config/.env');

// Manejo global de excepciones sin capturar
//ManejadorExcepciones::registrar();


$mensaje = new NotificacionesModel;
$datos = [
    'cuenta_id' => 19,
    'titulo' => 'cualquiera',
    'mensaje' => 'qloq',
    'tipo' => 'Solicitud',
    'enviar_email' => true,
    'fecha_creacion' => date('Y-m-d')
];


$mensaje->save($datos);
