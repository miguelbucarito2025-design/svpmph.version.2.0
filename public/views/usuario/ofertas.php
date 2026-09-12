<article>
    <div class="container-title">
        <h1 class="title-pag">Ofertas Académicas</h1>
        <p class="text-pag">
            Creación y control de ofertas académicas. Gestiona y administra conectando los Programas con los diferentes Núcleos.
        </p>
    </div>
    <br>
    <section class="">
        <input type="hidden" name="offset" id="offset" value="0">

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
                    <button type="button" class="success" id="btnAgregarOferta">
                        <svg class="icono-outline">
                            <use href="#icono-agregar"></use>
                        </svg>
                        <span>Agregar Oferta</span>
                    </button>

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
                <div class="filter-group">
                    <label for="programa">Programas</label>
                    <select name="programa_id" id="programa" required>
                        <option value="" class="option-none">-- Seleccione --</option>

                        <?php foreach ($programas ?? [] as $n) {
                            echo '<option value="' . $n['id'] . '">' . $n['programa'] . '</option>';
                        } ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="nucleo">Núcleo</label>
                    <select name="nucleo_id" id="nucleo" required>
                        <option value="" class="option-none">-- Seleccione --</option>
                        <?php foreach ($nucleos ?? [] as $n) {
                            echo '<option value="' . $n['id'] . '">' . $n['nucleo'] . '</option>';
                        } ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="nucleo">Modalidad</label>
                    <select name="modalidad_id" id="modos" required>
                        <option value="" class="option-none">-- Seleccione --</option>
                        <?php foreach ($modalidades ?? [] as $n) {
                            echo '<option value="' . $n['id'] . '">' . $n['modalidad'] . '</option>';
                        } ?>
                    </select>
                </div>
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
                    <label>Estado:</label>
                    <select name="estado" id="estado">
                        <option value="1">-- Activo --</option>
                        <option value="0">-- Desactivado --</option>
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
        <div class="empty-state">Cargando ofertas académicas...</div>
    </div>
    <div class="toolbar-top-bar items-center">
        <button type="button" id="btnRetroceder" class="table-navigator" disabled>Retroceder</button>
        <div id="contenedorPaginasNumeradas" style="display: inline-flex; gap: 4px;"></div>
        <button type="button" id="btnAvanzar" class="table-navigator">Avanzar</button>
    </div>


</article>

<script src="<?= URL_BASE ?>public/js/<?= $pag ?? '' ?>.js" defer></script>