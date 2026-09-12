<?php

include 'vendor/autoload.php';

use App\Models\ProgramasModel;
use App\Helpers\EnvLoader;



EnvLoader::load('app/Config/.env');


$model = new ProgramasModel;

$model->update(['estado' => true], ['id' => 23]);
if ($model) {
    echo 'bello papi';
}
