<article>
    <div class="container-title">

        <h1 class="title-pag">Dashboard</h1>
        <p class="text-pag">
            Monitoreo en tiempo real, métricas clave y gestión centralizada en un
            solo lugar.
        </p>
    </div>
    <?php



    if (($datosFaltantes ?? false) !== true) {
    ?>
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

    if (($datosLaboralesFaltantes ?? false) !== true) {
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
                <button type="button" class="success button-anuncio" onclick="redirec('perfil/laboral')">
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
</article>