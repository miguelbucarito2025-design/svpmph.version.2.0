<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Abstract\Controller;

class HomeController extends Controller
{


    public function index()
    {
        $this->vista->render('index/article_index', [], 'index');
    }
}
