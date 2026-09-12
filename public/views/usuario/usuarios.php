<article>
    <div class="container-title">
        <h1 class="title-pag">Usuarios</h1>
        <p class="text-pag">
            Crea y administra los usuarios para el uso responsable del sistema.
        </p>
    </div>
    <br>
    <section class="">
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

        <!-- PANEL DESPLEGABLE (Oculto por defecto) -->
        <div class="toolbar-panel-content is-hidden" id="panelHerramientas">

            <!-- Bloque 1: Búsqueda y Acciones -->
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
                        placeholder="Buscar por programa o núcleo..."
                        id="intput-buscar" />
                </div>
                <div class="panel-action-buttons">

                    <button type="button" class="danger" id="btnEliminarMasivo">
                        <svg class="icono-outline">
                            <use href="#icono-eliminar"></use>
                        </svg>
                        <span>Eliminar</span>
                    </button>
                </div>
            </div>

            <hr class="panel-divider">

            <!-- Bloque 2: Filtros de Selección y Paginación -->
            <div class="panel-row filters-row">

                <div class="filter-group limit-selector">
                    <label for="limit">Límite:</label>
                    <select name="limit" id="limit">
                        <option value="3">-- 3 Registros --</option>
                        <option value="6">-- 6 Registros --</option>
                        <option value="12">-- 12 Registros --</option>
                        <option value="50">-- 50 Registros --</option>
                    </select>
                </div>
                <div class="filter-group limit-selector">
                    <label>Rol:</label>
                    <select name="rol_id" id="rol_id">
                        <option value="">-- Select --</option>
                        <?php foreach (($roles ?? []) as $rol) : ?>

                            <option value="<?= $rol['id']  ?>"><?= $rol['rol'] ?></option>


                        <?php endforeach; ?>
                    </select>
                </div>
                <label for="checkSeleccionarTodos" class="checkbox-container">
                    <input type="checkbox" id="checkSeleccionarTodos" class="form-check-input">
                    <span>Seleccionar todas</span>
                </label>
            </div>

        </div>
    </section>
    <div id="contenedorOfertasCards" class="ofertas-cards-grid">
        <div class="empty-state">Cargando Usuarios...</div>
    </div>
    <div class="toolbar-top-bar items-center">
        <button type="button" id="btnRetroceder" class="table-navigator" disabled>Retroceder</button>
        <div id="contenedorPaginasNumeradas" style="display: inline-flex; gap: 4px;"></div>
        <button type="button" id="btnAvanzar" class="table-navigator">Avanzar</button>
    </div>


</article>

<script src="<?= URL_BASE ?>public/js/<?= $pag ?? '' ?>.js" defer></script>