<article>
    <h1 class="title-pag">Gestor de Expediente y Cuotas</h1>
    <p class="text-pag">
        Administre la lista de agremiados y gestione la verificación de los pagos de sus cuotas registradas.
    </p>
    <br>

    <!-- ================================================================= -->
    <!-- BLOQUE 1: LISTADO PRINCIPAL DE AGREMIADOS/USUARIOS               -->
    <!-- ================================================================= -->
    <div id="container-user-pagos">
        <section>
            <!-- Inputs de Control para la Tabla 1 (Agremiados) -->
            <input type="hidden" name="offset" id="offset" value="0" readonly>

            <div class="toolbar-top-bar">
                <div class="toolbar-metrics">
                    <span id="textoMetricaPaginacion" class="metric-badge">Mostrando 0 de 0</span>
                </div>

                <button type="button" class="btn-toggle-panel" id="btnToggleHerramientas" aria-expanded="false">
                    <svg class="icono-outline">
                        <use href="#icono-ajustes"></use>
                    </svg>
                    <span>Filtros y Búsqueda</span>
                    <svg class="icono-outline arrow-icon">
                        <use href="#icono-flecha-abajo"></use>
                    </svg>
                </button>
            </div>

            <!-- PANEL DESPLEGABLE DE FILTROS (VISTA 1) -->
            <div class="toolbar-panel-content is-hidden" id="panelHerramientas">
                <div class="panel-row">
                    <div class="container-search">
                        <div class="container-svg">
                            <svg class="icono-outline">
                                <use href="#icono-buscar"></use>
                            </svg>
                        </div>
                        <input
                            type="search"
                            name="buscar"
                            placeholder="Buscar por programa, núcleo, nombre o cédula..."
                            id="intput-buscar" />
                    </div>
                </div>

                <hr class="panel-divider">

                <div class="panel-row filters-row">
                    <div class="filter-group limit-selector">
                        <label for="limit">Límite:</label>
                        <select name="limit" id="limit">
                            <option value="3">-- 3 Registros --</option>
                            <option value="6" selected>-- 6 Registros --</option>
                            <option value="12">-- 12 Registros --</option>
                            <option value="50">-- 50 Registros --</option>
                        </select>
                    </div>

                    <div class="filter-group limit-selector">
                        <label for="rol_id">Estatus:</label>
                        <select name="status" id="status_id">
                            <option value="">-- Todos --</option>
                            <option value="En Proceso">En Proceso / Espera</option>
                            <option value="Pagado">Pagado / Aprobado</option>
                            <option value="Pendiente">Pendiente / No Verificado</option>
                            <option value="Rechazado">Rechazado</option>

                        </select>
                    </div>
                </div>
            </div>
        </section>

        <!-- Grid donde se pintan los Usuarios/Agremiados -->
        <div id="contenedorOfertasCards-2" class="ofertas-cards-grid margin-top-20">
            <div class="empty-state">Cargando Usuarios...</div>
        </div>

        <!-- Paginador Vista 1 -->
        <div class="toolbar-top-bar items-center margin-top-15">
            <button type="button" id="btnRetroceder" class="table-navigator" disabled>Retroceder</button>
            <div id="contenedorPaginasNumeradas" style="display: inline-flex; gap: 4px;"></div>
            <button type="button" id="btnAvanzar" class="table-navigator">Avanzar</button>
        </div>
    </div>


    <!-- ================================================================= -->
    <!-- BLOQUE 2: VISTA DE CUOTAS Y FORMULARIO (OCULTO POR DEFECTO)      -->
    <!-- ================================================================= -->
    <div id="container-herramienta-pagos" style="display: none;">

        <!-- NIVEL A: GRID DE CUOTAS DEL USUARIO -->
        <div id="section-user" class="transition">
            <section>
                <!-- Inputs de Control para la Tabla 2 (Cuotas) -->
                <input type="hidden" name="offset" id="cuotas-offset" value="0" readonly>

                <div class="toolbar-panel-content">
                    <span id="cuotas-textoMetricaPaginacion" class="metric-badge">Mostrando 0 de 0</span>
                    <hr class="panel-divider">
                    <div class="toolbar-metrics max-wight  flex-between align-center button-table  continer-item-table">
                        <button type="button" class="danger max-width-50  button-anuncio volver btn-eliminar" id="btnVolverAUsuarios">
                            <svg class="icono-outline">
                                <use href="#icono-volver"></use>
                            </svg>
                            Volver
                        </button>

                        <div class="continer-item-table margin-top-10">
                            <div class="filter-group limit-selector margin-bottom-10">
                                <label for="cuotas-limit">Límite:</label>
                                <select name="limit" id="cuotas-limit">
                                    <option value="3">-- 3 Registros --</option>
                                    <option value="6" selected>-- 6 Registros --</option>
                                    <option value="12">-- 12 Registros --</option>
                                    <option value="24">-- 24 Registros --</option>
                                    <option value="48">-- 48 Registros --</option>
                                    <option value="96">-- 96 Registros --</option>
                                </select>
                            </div>

                        </div>

                        <!-- EL INPUT CLAVE DONDE SE INYECTA EL ID DEL USUARIO SELECCIONADO -->
                        <div class="container-search is-hidden">
                            <input
                                type="search"
                                name="cuenta_id"
                                placeholder="ID Usuario"
                                id="id-user" />
                        </div>
                    </div>
                </div>
            </section>

            <!-- Grid donde se pintan las Cuotas/Trámites del agremiado -->
            <div id="contenedorOfertasCards" class="ofertas-cards-grid margin-top-15">
                <div class="empty-state">Cargando solicitudes del agremiado...</div>
            </div>

            <!-- Paginador Vista 2 -->
            <div class="toolbar-top-bar items-center my-3">
                <button type="button" id="cuotas-btnRetroceder" class="table-navigator" disabled>Retroceder</button>
                <div id="cuotas-contenedorPaginasNumeradas" style="display: inline-flex; gap: 4px;"></div>
                <button type="button" id="cuotas-btnAvanzar" class="table-navigator">Avanzar</button>
            </div>
        </div>

        <!-- NIVEL B: FORMULARIO INTERACTIVO DE CONFIRMACIÓN / AUDITORÍA DE PAGO -->
        <section class="section-usuario section-inf section-anuncio is-hidden" id="seccionExpedienteArchivos">
            <div class="header-estatus-wrapper">
                <h2 class="section-usuario-h2">
                    <svg class="icon-sistema-rellen">
                        <use href="#icon-archivo" />
                    </svg>
                    Confirmación de solicitud del Agremiado
                </h2>
                <hr>
                <p>
                    <b>
                        Le informamos que el Costo de la solicitud está fijada a la tasa diaria del BCV.
                        El monto se calculará con respecto a la tasa BCV publicada ese día. Para más información consulte los términos y condiciones.
                    </b>
                    <a class="color-info" href="https://www.bcv.org.ve/" target="_blank">¡visite el sitio oficial en este enlace aquí!</a>
                </p>
            </div>

            <div class="items-archiv my-2">
                <button type="button" class="danger max-width-50  button-anuncio volver btn-eliminar" id="volver">
                    <svg class="icono-outline">
                        <use href="#icono-volver" />
                    </svg>
                    Volver
                </button>
            </div>
            <hr>

            <!-- Contenedor inyectado dinámicamente con pintarTarjetaDetalle() -->
            <div id="contenedor-form"></div>
        </section>

    </div>

    <script src="<?= URL_BASE ?>public/js/<?= $pag ?? 'agremiados' ?>.js" defer></script>
</article>