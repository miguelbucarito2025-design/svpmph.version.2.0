<article>
    <h1 class="title-pag">Cuotas</h1>
    <p class="text-pag">
        Espacio dedicado para que usted administre sus pagos de las cuotas generadas por los cargos a su cuenta
    </p>
    <br>
    <div id="section-user" class="transition">

        <section>
            <input type="hidden" name="offset" id="offset" value="0">



            <!-- Panel de Filtros -->
            <div class="toolbar-panel-content " id="panelHerramientas">
                <div class="toolbar-metrics">
                    <span id="textoMetricaPaginacion" class="metric-badge">Mostrando 0 de 0</span>
                </div>



                <hr class="panel-divider">

                <div class="panel-row filters-row">


                    <div class="filter-group limit-selector">
                        <label for="limit">Límite:</label>
                        <select name="limit" id="limit">
                            <option value="5">5 Registros</option>
                            <option value="10" selected>10 Registros</option>
                            <option value="20">20 Registros</option>
                            <option value="50">50 Registros</option>
                        </select>
                    </div>


                </div>
            </div>
        </section>

        <!-- Grid de Agremiados -->
        <div id="contenedorOfertasCards" class="ofertas-cards-grid">
            <div class="empty-state">Cargando agremiados...</div>
        </div>

        <!-- Paginador -->
        <div class="toolbar-top-bar items-center my-3">
            <button type="button" id="btnRetroceder" class="table-navigator" disabled>Retroceder</button>
            <div id="contenedorPaginasNumeradas" style="display: inline-flex; gap: 4px;"></div>
            <button type="button" id="btnAvanzar" class="table-navigator">Avanzar</button>
        </div>

    </div>

    <!-- Sección Expediente Digital -->
    <section class="section-usuario section-inf section-anuncio is-hidden" id="seccionExpedienteArchivos">
        <div class="header-estatus-wrapper">
            <h2 class="section-usuario-h2">
                <svg class="icon-sistema-rellen">
                    <use href="#icon-archivo" />
                </svg>
                Confirmacion de solicitud del Agremiado
            </h2>
            <hr>

            <p>

                <b>
                    Le informamos que el Costo de la solicitud esta fijada a la tasa diaria del BCV, sin importar que hizo la
                    solicitud hace 1 semana el monto se calculara con respecto a la taza BCV publicada ese dia. para mas información
                    consulte los terminos y condiciones. </b><a class="color-info" href="https://www.bcv.org.ve/" target="_blank">visite el sitio oficial en este enlace aqui!</a>

            </p>
        </div>

        <div class="items-archiv my-2">
            <button type="button" class="danger max-width-50 button-anuncio volver btn-eliminar" id="volver">
                <svg class="icono-outline">
                    <use href="#icono-volver" />
                </svg>
                Volver
            </button>

        </div>
        <hr>

        <div id="contenedor-form"></div>


    </section>




    <script src="<?= URL_BASE ?>public/js/<?= $pag ?? 'agremiados' ?>.js" defer></script>
</article>