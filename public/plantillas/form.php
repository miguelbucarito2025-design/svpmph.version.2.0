<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="public/css/form.css?v=<?php echo time(); ?>" />
  <link rel="stylesheet" href="public/css/global.css?v=<?php echo time(); ?>" />
  <script defer src="public/js/global.js?v=<?php echo time(); ?>"></script>

  <base href="/svpmph.version.2.0/">

  <title>Document</title>
</head>

<body>
  <?php
  require_once 'public/layaout/icon_svg.php';
  require_once 'public/layaout/alert.php';
  ?>
  <header class="header-form-container">
    <nav class="form-container-menu">
      <ul>
        <li class="item <?= (($ventana ?? '') === 'login') ? 'activo' : '' ?>" onclick="redirec('login')">
          <svg class="icono-outline">
            <use href="#icono-login"></use>
          </svg>
          <span>

            Login
          </span>
        </li>
        <li class="item <?= (($ventana ?? '') === 'registro') ? 'activo' : '' ?>" onclick="redirec('registro')">
          <svg class="icon-sistema-rellen">
            <use href="#icon-registro"></use>
          </svg>
          <span>
            Registro
          </span>

        </li>
        <li class="item <?= (($ventana ?? '') === 'recuperar') ? 'activo' : '' ?>" onclick="redirec('recuperar')">
          <svg class="icono-outline">
            <use href="#icono-recuperar-cuenta"></use>
          </svg> <span>
            Recuperacion
          </span>
        </li>
        <li class="volver" onclick="redirec('home/')">
          <svg class="icon-sistema-rellen">
            <use href="#icon-home"></use>
          </svg>
          <span>
            Volver
          </span>
        </li>
      </ul>
    </nav>
  </header>

  <?php

  require_once $vista ?? '../views/form/login.php';

  ?>



</body>

</html>