<article>
    <div class="container-title">
        <h1 class="title-pag">Secciones Académicas</h1>
        <p class="text-pag">
            Administra los grupos, aulas y capacidades máximas por oferta académica
        </p>
    </div>
    <br>

    <section>
        <!-- Input oculto para mantener el despliegue del Offset SQL en la paginación -->
        <input type="hidden" name="offset" id="offset" value="0" readonly>

        <div class="toolbar-top-bar">
            <div class="toolbar-metrics">
                <span id="textoMetricaPaginacion" class="metric-badge">Mostrando 0 de 0</span>
            </div>

            <button type="button" class="btn-toggle-panel" id="btnToggleHerramientas" aria-expanded="false">
                <svg class="icono-outline">
                    <use href="#icono-ajustes"></use>
                </svg>
                <span>Filtros y Acciones</span>
                <svg class="icono-outline arrow-icon">
                    <use href="#icono-flecha-abajo"></use>
                </svg>
            </button>
        </div>

        <!-- PANEL DESPLEGABLE CON HERRAMIENTAS Y FILTROS -->
        <div class="toolbar-panel-content is-hidden" id="panelHerramientas">

            <!-- Bloque 1: Búsqueda y Acciones Directas -->
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
                        placeholder="Buscar por sección, programa o cohorte..."
                        id="intput-buscar" />
                </div>
            </div>

            <hr class="panel-divider">

            <!-- Bloque 2: Filtros de Paginación -->
            <div class="panel-row filters-row">
                <div class="filter-group limit-selector">
                    <label for="limit">Límite:</label>
                    <select name="limit" id="limit">
                        <option value="3">-- 3 Registros --</option>
                        <option value="6" selected>-- 6 Registros --</option>
                        <option value="12">-- 12 Registros --</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="nucleo">Núcleo</label>
                    <select name="nucleo_id" id="nucleo" required>

                        <?php

                        foreach ($nucleos ?? [] as $n) {
                            echo '<option value="' . $n['id'] . '">' . $n['nucleo'] . '</option>';
                        }  ?>
                    </select>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTENEDOR DE TARJETAS DINÁMICAS -->
    <div id="contenedorSeccionesCards" class="ofertas-cards-grid">
        <div class="empty-state text-center py-4 text-muted">Cargando Secciones...</div>
    </div>

    <!-- NAVEGACIÓN PAGINADA INFERIOR -->
    <div class="toolbar-top-bar items-center mt-3">
        <button type="button" id="btnRetroceder" class="table-navigator" disabled>Retroceder</button>
        <div id="contenedorPaginasNumeradas" style="display: inline-flex; gap: 4px;"></div>
        <button type="button" id="btnAvanzar" class="table-navigator">Avanzar</button>
    </div>
</article>

<script src="<?= URL_BASE ?>public/js/<?= $pag ?? '' ?>.js" defer></script>