<article>
    <div class="container-title">

        <h1 class="title-pag">Dashboard</h1>

        <p class="text-pag">
            <b>¡Sistema en Evolución Constante!</b>
            Estamos trabajando en nuevas funciones. Al permanecer activo en la plataforma SVPMPH,
            serás el primero en enterarte de las próximas actualizaciones de carnetización , trámites, Cursos y mucho más!.
        </p>
    </div>
    <?php
    if (empty($prueva)) { ?>
        <section class="section-usuario section-inf section-anuncio">

            <h2 class="section-usuario-h2">
                <svg width=" 24" height="24" stroke="#0d6efd">
                    <use href="#icono-info" />
                </svg>
                Información del sistema
            </h2>
            <hr />
            <p class="section-usuario-text">
                Estimado usuario se le informa que para continuar con el proceso de
                registro debe registrar sus datos personales. Para mas información consulte la seccion, <br><a target="_blank" href="terminos">3 de la Finalidad del Almacenamiento y Uso de Datos en los terminos y condiciones</a>

            </p>
            <hr />
            <div class="container-button-anuncion">
                <button type="button" class="success button-anuncio" onclick="redirec('perfil')">
                    <svg class="icono-outline">
                        <use href="#icono-exito" />
                    </svg>
                    Vamos
                </button>
            </div>
        </section>
    <?php

    }

    if (empty($prueva['cargo_id'])) {
    ?>
        <section class="section-usuario section-warning section-anuncio">
            <h2 class="section-usuario-h2">
                <svg width="24" height="24" stroke="#ffc107">
                    <use href="#icono-advertencia" />
                </svg>
                Información Adicional
            </h2>
            <hr />
            <p class="section-usuario-text">
                Como dato adicional debes indicar tus datos laborales (opcional) ya que como se a anunciado en los terminos y condiciones son nesesarios en algunos aspectos
                pero de no ser de su agrado o no poseer usted no esta obligado . Para mas informacion consulte la seccion, <br> <a target="_blank" href="terminos">3 de la Finalidad del Almacenamiento y Uso de Datos en los terminos y condiciones</a>
            </p>
            <hr />
            <div class="container-button-anuncion">
                <button type="button" class="success button-anuncio" onclick="redirec('laboral')">
                    <svg class="icono-outline">
                        <use href="#icono-exito" />
                    </svg>
                    Vamos
                </button>
            </div>
        </section>
    <?php

    }

    ?>
    <?php if ($_SESSION['usuario_rol'] == 1 && !empty($prueva)) { ?>
        <section class="dashboard-content text-pag">

            <div class="main-grid">

                <figure class="figure-main-dash">
                    <img src="<?= URL_BASE ?>public/multimedia/log/001.png" alt="logo-svpmph">
                </figure>
                <main class="info-container item-main-dash ">
                    <div class="card-info">
                        <h2 class="title-dash"> SVPMPH Oficial</h2>
                        <p>
                            <i>"Expertos en lo que hacemos y apasionados por Enseñar."</i>
                            El gremio busca reclutar y captar nuevo personal,
                            facilitando al personal activo el proceso de homologación y actualización propuesto en la gaceta oficial.
                            Ademas a partir de ahora reunira a todo el personal prehospitalario de todo el Pais que busque formar parte
                            de nuestro gremio.
                        </p>
                    </div>

                    <div class="card-info ">
                        <h3 class="title-dash">Novedades y Avisos</h3>
                        <p>
                            Certificate con nostros si no tienes un titulo o si buscas actualizarte ademas nuestra plataforma
                            te hacerca más al Ministerio de Salud del Pais ya que nuestras ofertas academicas son
                            abaladas por ellos ademas conoceras muchas personas importantes que son nuestros colegas
                            que pueden recomendarte en muchos trabajos o compañias que necesiten a personal tambien
                            pueden seleccionarte....
                        </p>
                    </div>
                </main>



            </div>

        </section>

    <?php } else if (!empty($prueva)) { ?>
        <section class="container-metric">


            <div class="metric-items morado" onclick="redirec('agremiados')">
                <svg class="icono-outline">
                    <use href="#icono-gremio" />
                </svg>Total en Gremio <?= ($total['gremio']['activos'] ?? 0) . '-' . ($total['gremio']['inactivos'] ?? 0) ?>
            </div>


            <div class="metric-items danger" onclick="redirec('pagos-admin')">
                <svg class="icono-outline">
                    <use href="#icono-dolar-rechazado" />
                </svg>Pagos Pendientes <?= ($total['cuotas']['Rechazadas'] ?? 0) + ($total['cuotas']['Pendiente'] ?? 0) ?>
            </div>
            <div class="metric-items success" onclick="redirec('pagos-admin')">
                <svg class="icono-outline">
                    <use href="#icono-dolar" />
                </svg> Pagos Verificados <?= $total['cuotas']['Pagado'] ?? 0 ?>
            </div>
            <div class="metric-items info" onclick="redirec('pagos-admin')">
                <svg class="icono-outline">
                    <use href="#icono-documentos" />
                </svg>Pagos En espera <?= $total['cuotas']['En Espera'] ?? 0  ?>
            </div>
        </section>

        <section class="dashboard-content text-pag">

            <div class="main-grid">

                <figure class="figure-main-dash">
                    <img src="<?= URL_BASE ?>public/multimedia/log/001.png" alt="logo-svpmph">
                </figure>
                <!-- ====================================================================
     SECCIÓN CENTRAL DEL DASHBOARD (VISTA ADMINISTRATIVA / DIRECTIVA)
     ==================================================================== -->
                <main class="info-container item-main-dash">

                    <!-- Tarjeta 1: Marco Directivo y Gestión Institucional -->
                    <div class="card-info">
                        <h2 class="title-dash">SVPMPH | Panel de Control Directivo</h2>
                        <p>
                            <i>"Expertos en lo que hacemos y apasionados por Enseñar."</i><br>
                            Plataforma centralizada para la supervisión, validación y control del personal prehospitalario a nivel nacional. Desde este módulo directivo se gestiona la captación de nuevos miembros, el proceso de homologación de credenciales y la auditoría de expedientes según los lineamientos de la Gaceta Oficial y el Ministerio del Poder Popular para la Salud.
                        </p>
                    </div>

                    <!-- Tarjeta 2: Notificaciones Administrativas y Estado Operativo -->
                    <div class="card-info">
                        <h3 class="title-dash">Novedades y Control Administrativo</h3>
                        <p>
                            Se recuerda a la coordinación directiva revisar de forma periódica las solicitudes en espera para la emisión de certificaciones y homologaciones de títulos. Las ofertas académicas respaldadas por el gremio requieren validación de firmas antes de su acreditación oficial. Asimismo, mantenga la auditoría sobre la verificación de cuotas reportadas para asegurar la solvencia y la actualización continua de la base de datos nacional del gremio.
                        </p>
                    </div>

                </main>



            </div>

        </section>

    <?php } ?>
</article>