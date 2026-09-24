<article>
    <h1 class="title-pag">Gestor de Expediente Digital</h1>
    <p class="text-pag">
        Consigne, revise y gestione la documentación institucional solicitada para su expediente.
    </p>
    <br>
    <div id="section-user" class="transition">

        <section>
            <input type="hidden" name="offset" id="offset" value="0">

            <!-- Barra Superior -->
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

            <!-- Panel de Filtros -->
            <div class="toolbar-panel-content is-hidden" id="panelHerramientas">
                <div class="panel-row">
                    <div class="container-search">
                        <div class="container-svg">
                            <svg class="icono-outline">
                                <use href="#icono-buscar"></use>
                            </svg>
                        </div>
                        <input type="search" name="buscar" id="intput-buscar" placeholder="Buscar por nombre, apellido o cédula..." />
                    </div>
                </div>

                <hr class="panel-divider">

                <div class="panel-row filters-row">
                    <div class="filter-group">
                        <label for="promocion_id">Promoción:</label>
                        <select name="promocion_id" id="promocion_id">
                            <option value="">-- Todas --</option>
                            <?php foreach ($promocion ?? [] as $n): ?>
                                <option value="<?= $n['id'] ?>"><?= $n['promocion'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="mencion_id">Mención:</label>
                        <select name="mencion_id" id="mencion_id">
                            <option value="">-- Todas --</option>
                            <?php foreach ($mencion ?? [] as $n): ?>
                                <option value="<?= $n['id'] ?>"><?= $n['mencion'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group limit-selector">
                        <label for="limit">Límite:</label>
                        <select name="limit" id="limit">
                            <option value="5">5 Registros</option>
                            <option value="10" selected>10 Registros</option>
                            <option value="20">20 Registros</option>
                            <option value="50">50 Registros</option>
                        </select>
                    </div>

                    <div class="filter-group limit-selector">
                        <label for="estado">Estado:</label>
                        <select name="estado" id="estado">
                            <option value="">-- seleccione --</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
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
                Archivos Consignados del Agremiado
            </h2>
        </div>
        <hr>

        <div class="items-archiv my-2">
            <button type="button" class="danger max-width-50 button-anuncio volver btn-eliminar" id="volver">
                <svg class="icono-outline">
                    <use href="#icono-volver" />
                </svg>
                Volver
            </button>

            <!-- Panel Flotante de Verificación -->
            <div id="panelLoteVerificacion" class="is-hidden" style="display: inline-flex; gap: 10px; align-items: center;">
                <span class="badge-cedula" id="textoContadorLote">Cambios pendientes: 0</span>
                <button type="button" id="btnProcesarLote" class="success button-anuncio">
                    <svg class="icono-outline">
                        <use href="#icon-confirmar" />
                    </svg>
                    Guardar Verificaciones
                </button>
            </div>
        </div>
        <hr>

        <div class="container-archivos" id="contenedor-archivos">
            <div class="archivo-vacio-text">
                <p class="text-muted">Haga doble clic sobre un agremiado para desplegar sus archivos.</p>
            </div>
        </div>
    </section>

    <script src="<?= URL_BASE ?>public/js/<?= $pag ?? 'agremiados' ?>.js" defer></script>
</article>