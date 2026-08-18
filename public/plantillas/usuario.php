<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="public/css/usuario.css" />
  <link rel="stylesheet" href="public/css/global.css" />
  <script defer src="public/js/global.js?v=<?php echo time(); ?>"></script>

  <base href="/svpmph.version.2.0/">

  <title><?= $titlePag ?? '' ?> -SVPMPH</title>
</head>

<body>
  <?php
  require_once 'public/layaout/icon_svg.php';
  require_once 'public/layaout/alert.php'; ?>
  <header>
    <nav>
      <figure>
        <img
          src="public/multimedia/ichigo-dos-espadas-bleach-thousand-year-blood-war_3840x2160_xtrafondos.com.jpg" />
      </figure>
      <ul>
        <li>
          <svg class="icono-outline">
            <use href="#icono-usuario" />
          </svg> <?= $nombreUsuario ?? '' ?>
        </li>
        <li><b><?= $nombreRol ?? '' ?></b></li>
      </ul>
    </nav>
  </header>

  <div class="container-article-aside">
    <?php include_once $vista ?? 'public/views/usuario/dashborad.php' ?>
    <aside class="sidebar">
      <?php
      // Ejemplo: Tu controlador te dice en qué sección estás parado
      $seccionActiva = $grup ?? 'Dashboard';
      ?>

      <nav class="sidebar-nav">

        <!-- Enlace Suelto (Sin Grupo) -->
        <a href="dashboard" class="menu-link <?= ($seccionActiva === 'Dashboard') ? 'linkactivo' : ''; ?>">
          <svg class="menu-icon">
            <use href="#icono-dashboard" />
          </svg>
          <span>Dashboard</span>
        </a>


        <section class="menu-grupo <?= ($seccionActiva === 'perfil') ? 'abierto' : ''; ?>">

          <button type="button" class="grupo-titulo">
            <div class="link-content">
              <svg class="menu-icon">
                <use href="#icono-usuario" />
              </svg>
              <span>Mi Cuenta</span>
              <span class="flecha">▾</span>
            </div>
          </button>

          <!-- Lista simple de enlaces (Sin anidaciones raras) -->
          <ul class="grupo-lista">
            <li onclick="redirec('perfil')" class=" <?= (($pag ?? '')  === 'datosPersonales') ? 'linkactivo' : ''; ?> ">
              <svg class="icono-outline">
                <use href="#icono-usuario" />
              </svg>Datos Personales
            </li>
            <li onclick="redirec('laboral')" class="<?= (($pag ?? '')  === 'datosLaborales') ? 'linkactivo' : ''; ?>  ">
              <svg class="icono-outline">
                <use href="#icono-trabajo" />
              </svg>
              Datos Laborales
            </li>
            <li onclick="redirec('cuenta')" class="<?= (($pag ?? '')  === 'cuenta') ? 'linkactivo' : ''; ?>  ">
              <svg class="icono-outline">
                <use href="#icono-configuracion" />
              </svg>
              Cuenta de Usuario
            </li>
          </ul>

        </section>


      </nav>
    </aside>
  </div>
  <script src="public/js/asideUsuario.js"></script>
</body>

</html>