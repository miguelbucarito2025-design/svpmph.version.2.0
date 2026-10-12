<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <?php
  include_once 'public/layaout/head.php';

  ?>
  <link rel="stylesheet" href="<?= URL_BASE ?>public/css/usuario.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="<?= URL_BASE ?>public/css/global.css?v=<?= time() ?>" />
  <script defer src="<?= URL_BASE ?>public/js/global.js?v=<?= time() ?>"></script>
  <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
  <base href="<?= URL_BASE ?>">

  <title><?= $titlePag ?? '' ?>-SVPMPH</title>
</head>

<body>
  <?php

  require_once 'public/layaout/icon_svg.php';
  require_once 'public/layaout/alert.php';
  ?>

  <header>
    <nav>
      <figure>
        <img id="foto-perfil" src="<?= $fotoUsuario ?? '' ?>" />
      </figure>
      <ul>
        <li>
          <svg class="icono-outline">
            <use href="#icono-usuario" />
          </svg>
          <?= ucfirst($nombreUsuario ?? '') ?>
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
    <?php include_once $vista ?? 'public/views/usuario/dashborad.php'; ?>

    <aside class="sidebar left" id="aside-menu">
      <?php $seccionActiva = $grup ?? 'Dashboard'; ?>

      <nav class="sidebar-nav">

        <!-- ========================================== -->
        <!-- BLOQUE COMÚN: DASHBOARD Y MI CUENTA       -->
        <!-- ========================================== -->
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

          <ul class="grupo-lista">
            <li onclick="redirec('perfil')" class="<?= (($pag ?? '') === 'datosPersonales') ? 'linkactivo' : ''; ?>">
              <svg class="icono-outline">
                <use href="#icono-usuario" />
              </svg>Datos Personales
            </li>
            <?php if (!empty($prueva) || $_SESSION['usuario_rol'] !== 1): ?>

              <li onclick="redirec('laboral')" class="<?= (($pag ?? '') === 'datosLaborales') ? 'linkactivo' : ''; ?>">
                <svg class="icono-outline">
                  <use href="#icono-trabajo" />
                </svg>Datos Laborales
              </li>
            <?php endif; ?>

            <li onclick="redirec('cuenta')" class="<?= (($pag ?? '') === 'cuenta') ? 'linkactivo' : ''; ?>">
              <svg class="icono-outline">
                <use href="#icono-configuracion" />
              </svg>Cuenta de Usuario
            </li>
            <?php if (!empty($prueva)  || $_SESSION['usuario_rol'] !== 1): ?>
              <li onclick="redirec('foto')" class="<?= (($pag ?? '') === 'foto') ? 'linkactivo' : ''; ?>">
                <svg class="icono-outline">
                  <use href="#icono-foto" />
                </svg>Foto de Perfil
              </li>
            <?php endif; ?>
          </ul>

        </section>


        <!-- ========================================== -->
        <!-- BLOQUE INDEPENDIENTE: ROL 1 (AGREMIADO)    -->
        <!-- ========================================== -->
        <?php if ($_SESSION['usuario_rol'] == 1 and ! empty($prueva)): ?>

          <a href="gremio/estatus" class="menu-link <?= (($pag ?? '') === 'gremioEstatus') ? 'linkactivo' : ''; ?>">
            <svg class="menu-icon">
              <use href="#icono-estatus" />
            </svg>
            <span>Estatus del Gremio</span>
          </a>

          <a href="tramites-solicitar" class="menu-link <?= (($pag ?? '') === 'tramitesSolicitudes') ? 'linkactivo' : ''; ?>">
            <svg class="menu-icon">
              <use href="#icono-tramites" />
            </svg>
            <span>Trámites y Solicitudes</span>
          </a>

          <a href="cuotas" class="menu-link <?= ($seccionActiva === 'cuotas') ? 'linkactivo' : ''; ?>">
            <svg class="menu-icon">
              <use href="#icono-cuotas" />
            </svg>
            <span>Pagos</span>
          </a>
          <a href="archivos" class="menu-link <?= ($seccionActiva === 'documentos') ? 'linkactivo' : ''; ?>">
            <svg class="menu-icon">
              <use href="#icono-documentos" />
            </svg>
            <span>Documentos</span>
          </a>

        <?php endif; ?>


        <!-- ========================================== -->
        <!-- BLOQUE INDEPENDIENTE: ROL >= 3 (GESTIÓN)   -->
        <!-- ========================================== -->
        <?php if ($_SESSION['usuario_rol'] >= 3): ?>

          <section class="menu-grupo <?= ($seccionActiva === 'gremio') ? 'abierto' : ''; ?>">
            <button type="button" class="grupo-titulo">
              <div class="link-content">
                <svg class="icono-outline">
                  <use href="#icono-gremio" />
                </svg>
                <span>Gremio</span>
                <span class="flecha">▾</span>
              </div>
            </button>

            <ul class="grupo-lista">
              <li onclick="redirec('agremiados')" class="<?= (($pag ?? '') === 'agremiados') ? 'linkactivo' : ''; ?>">
                <svg class="icon-sistema-rellen">
                  <use href="#icono-agremiados" />
                </svg>Agremiados
              </li>

              <?php if ($_SESSION['usuario_rol'] > 3): ?>
                <li onclick="redirec('tramites')" class="<?= (($pag ?? '') === 'tramites') ? 'linkactivo' : ''; ?>">
                  <svg class="icon-sistema-rellen">
                    <use href="#icono-tramites" />
                  </svg>Trámites
                </li>
                <li onclick="redirec('promocion')" class="<?= (($pag ?? '') === 'promociones') ? 'linkactivo' : ''; ?>">
                  <svg class="icono-outline">
                    <use href="#icono-promociones" />
                  </svg>Promociones
                </li>
                <li onclick="redirec('carreras')" class="<?= (($pag ?? '') === 'carreras') ? 'linkactivo' : ''; ?>">
                  <svg class="icono-outline">
                    <use href="#icono-carreras-menciones" />
                  </svg>Carreras y Grados
                </li>
              <?php endif; ?>
            </ul>
          </section>

          <a href="pagos-admin" class="menu-link <?= ($seccionActiva === 'cuotas') ? 'linkactivo' : ''; ?>">
            <svg class="menu-icon">
              <use href="#icono-cuotas-pagos" />
            </svg>
            <span>Administrar Pagos</span>
          </a>

        <?php endif; ?>


        <!-- ========================================== -->
        <!-- BLOQUE INDEPENDIENTE: ROL 5 (ADMIN/ACADEM) -->
        <!-- ========================================== -->
        <?php if ($_SESSION['usuario_rol'] == 5): ?>

          <section class="menu-grupo <?= ($seccionActiva === 'academico') ? 'abierto' : ''; ?>">
            <button type="button" class="grupo-titulo">
              <div class="link-content">
                <svg class="icono-outline">
                  <use href="#icono-programas" />
                </svg>
                <span>Académico</span>
                <span class="flecha">▾</span>
              </div>
            </button>

            <ul class="grupo-lista">
              <li onclick="redirec('secciones')" class="<?= (($pag ?? '') === 'secciones') ? 'linkactivo' : ''; ?>">
                <svg class="icono-outline">
                  <use href="#icono-secciones-academicas" />
                </svg>Gestionar Secciones
              </li>
              <li onclick="redirec('facilitadores')" class="<?= (($pag ?? '') === 'facilitador') ? 'linkactivo' : ''; ?>">
                <svg class="icono-outline">
                  <use href="#icono-facilitadores" />
                </svg>Facilitadores
              </li>
            </ul>
          </section>

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
                </svg>Programas
              </li>
              <li onclick="redirec('asignaturas')" class="<?= (($pag ?? '') === 'asignaturas') ? 'linkactivo' : ''; ?>">
                <svg class="icono-outline">
                  <use href="#icono-asignaturas" />
                </svg>Asignaturas
              </li>
              <li onclick="redirec('nucleos')" class="<?= (($pag ?? '') === 'nucleos') ? 'linkactivo' : ''; ?>">
                <svg class="icono-outline">
                  <use href="#icono-nucleos" />
                </svg>Núcleos
              </li>
              <li onclick="redirec('ofertas')" class="<?= (($pag ?? '') === 'ofertas') ? 'linkactivo' : ''; ?>">
                <svg class="icono-outline">
                  <use href="#icono-ofertas" />
                </svg>Ofertas
              </li>
              <li onclick="redirec('roles')" class="<?= (($pag ?? '') === 'roles') ? 'linkactivo' : ''; ?>">
                <svg class="icono-outline">
                  <use href="#icono-usuario-llave" />
                </svg>Roles
              </li>
              <li onclick="redirec('usuarios')" class="<?= (($pag ?? '') === 'usuarios') ? 'linkactivo' : ''; ?>">
                <svg class="icono-outline">
                  <use href="#icono-usuario" />
                </svg>Usuarios
              </li>
              <li onclick="redirec('logs')" class="<?= (($pag ?? '') === 'logs') ? 'linkactivo' : ''; ?>">
                <svg class="icono-outline">
                  <use href="#icono-logs" />
                </svg>Logs
              </li>
            </ul>
          </section>

          <a href="archivos" class="menu-link <?= ($seccionActiva === 'documentos') ? 'linkactivo' : ''; ?>">
            <svg class="menu-icon">
              <use href="#icono-documentos" />
            </svg>
            <span>Documentos</span>
          </a>

        <?php endif; ?>


        <!-- ========================================== -->
        <!-- BLOQUE COMÚN: LOGOUT                       -->
        <!-- ========================================== -->
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

  <script src="<?= URL_BASE ?>public/js/asideUsuario.js?v=<?= time() ?>"></script>
</body>

</html>