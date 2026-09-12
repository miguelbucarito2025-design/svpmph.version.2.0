<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="<?= URL_BASE ?>public/css/usuario.css?v=<?php echo time(); ?>" />
  <link rel="stylesheet" href="<?= URL_BASE ?>public/css/global.css?v=<?php echo time(); ?>" />
  <script defer src="<?= URL_BASE ?>public/js/global.js?v=<?php echo time(); ?>"></script>
  <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
  <base href="/<?= HOST ?>/">

  <title><?= $titlePag ?? '' ?> -SVPMPH</title>
</head>

<body>
  <?php
  require_once 'public/layaout/icon_svg.php';
  require_once 'public/layaout/alert.php'; ?>
  <header>
    <nav>
      <figure>
        <img id="foto-perfil"
          src="<?= $fotoUsuario ?? '' ?>" />
      </figure>
      <ul>
        <li>
          <svg class="icono-outline">
            <use href="#icono-usuario" />
          </svg> <?= $nombreUsuario ?? '' ?>
        </li>
        <li><b><?= $nombreRol ?? '' ?>-LTE</b></li>
      </ul>
    </nav>
    <button type="button" id="btn-menu" class="btn-menu" aria-expanded="false" aria-label="Menú principal">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line class="linea-1" x1="4" y1="6" x2="20" y2="6"></line>
        <line class="linea-2" x1="4" y1="12" x2="20" y2="12"></line>
        <line class="linea-3" x1="4" y1="18" x2="20" y2="18"></line>
      </svg>

    </button>
  </header>

  <div class="container-article-aside">
    <?php include_once $vista ?? 'public/views/usuario/dashborad.php' ?>
    <aside class="sidebar left" id="aside-menu">
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
            <li onclick="redirec('foto')" class="<?= (($pag ?? '')  === 'foto') ? 'linkactivo' : ''; ?>  ">
              <svg class="icono-outline">
                <use href="#icono-foto" />
              </svg>
              Foto de Perfil
            </li>
          </ul>

        </section>



        <section class="menu-grupo <?= ($seccionActiva === 'academico') ? 'abierto' : ''; ?>">

          <button type="button" class="grupo-titulo">
            <div class="link-content">
              <svg class="icono-outline">
                <use href="#icono-programas" />
              </svg>
              <span>Academico</span>
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
            <li onclick="redirec('facilitadores')" class="<?= (($pag ?? '')  === 'facilitador') ? 'linkactivo' : ''; ?>  ">
              <svg class="icono-outline">
                <use href="#icono-facilitadores" />
              </svg>
              Facilitadores
            </li>
          </ul>

        </section>

        <?php if ($_SESSION['usuario_rol'] === 5) {
        ?>
          <section class="menu-grupo <?= ($seccionActiva === 'administracion') ? 'abierto' : ''; ?>">


            <button type="button" class="grupo-titulo">
              <div class="link-content">
                <svg class="icono-outline">
                  <use href="#icono-configuracion" />
                </svg>
                <span>Administración</span>
                <span class="flecha">▾</span>
              </div>
            </button>

            <ul class="grupo-lista">
              <li onclick="redirec('programas')" class="<?= (($pag ?? '') === 'programas') ? 'linkactivo' : ''; ?>">
                <svg class="icono-outline">
                  <use href="#icono-programas" />
                </svg>
                Programas
              </li>

              <li onclick="redirec('asignaturas')" class="<?= (($pag ?? '') === 'asignaturas') ? 'linkactivo' : ''; ?>">
                <svg class="icono-outline">
                  <use href="#icono-asignaturas" />
                </svg>
                Asignaturas
              </li>

              <li onclick="redirec('nucleos')" class="<?= (($pag ?? '') === 'nucleos') ? 'linkactivo' : ''; ?>">
                <svg class="icono-outline">
                  <use href="#icono-nucleos" />
                </svg>
                Núcleos
              </li>


              <li onclick="redirec('ofertas')" class="<?= (($pag ?? '') === 'ofertas') ? 'linkactivo' : ''; ?>">
                <svg class="icono-outline">
                  <use href="#icono-ofertas" />
                </svg>
                Ofertas
              </li>

              <li onclick="redirec('roles')" class="<?= (($pag ?? '') === 'roles') ? 'linkactivo' : ''; ?>">
                <svg class="icono-outline">
                  <use href="#icono-usuario-llave" />
                </svg>
                Roles
              </li>

              <li onclick="redirec('usuarios')" class="<?= (($pag ?? '') === 'usuarios') ? 'linkactivo' : ''; ?>">
                <svg class="icono-outline">
                  <use href="#icono-usuario" />
                </svg>
                Usuarios
              </li>
              <li onclick="redirec('logs')" class="<?= (($pag ?? '') === 'logs') ? 'linkactivo' : ''; ?>">
                <svg class="icono-outline">
                  <use href="#icono-logs" />
                </svg>
                Logs
              </li>
            </ul>

          </section>

        <?php
        }
        ?>


        <!-- Enlace Suelto (Sin Grupo) -->
        <a href="logout">
          <div class="logout">
            <svg class="icono-outline">
              <use href="#icon-salir" />
            </svg>
            <span>Logout</span>
          </div>
        </a>


      </nav>
    </aside>
  </div>
  <script src="<?= URL_BASE ?>public/js/asideUsuario.js?v=<?php echo time(); ?>"></script>
</body>

</html>