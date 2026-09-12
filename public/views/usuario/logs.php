<article>
    <div class="container-title">
        <h1 class="title-pag">Logs y Registros del Sistema</h1>
        <p class="text-pag">
            Monitoreo en tiempo real de fallas e incidencias de la plataforma SVPMPH
        </p>
    </div>
    <br>

    <section>
        <div class="toolbar-top-bar d-flex justify-content-between align-items-center">
            <div class="toolbar-metrics d-flex align-items-center gap-3">
                <span id="metricaGeneralLogs" class="metric-badge">Cargando métricas...</span>

                <!-- CONTROL DE FRECUENCIA CONFIGURABLE -->
                <div class="d-flex-control align-items-center gap-2">
                    <div class="container-select-control-time">
                        <label for="selectFrecuencia" class="small fw-bold text-muted mb-0">Frecuencia:</label>
                        <select id="selectFrecuencia" class="form-select form-select-sm" style="width: auto; font-size: 12px; cursor: pointer;">
                            <option value="0">Pausado (Manual)</option>
                            <option value="3">Cada 3 seg (Depuración)</option>
                            <option value="10" selected>Cada 10 seg (Normal)</option>
                            <option value="30">Cada 30 seg (Tráfico alto)</option>
                        </select>
                    </div>

                    <button type="button" id="btnRefrescarManual" class="button-anuncio small danger" title="Recargar registros ahora">
                        <svg class="icono-outline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;">
                            <path d="M23 4v6h-6"></path>
                            <path d="M1 20v-6h6"></path>
                            <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                        </svg>
                        Refrescar
                    </button>
                </div>
            </div>

            <button type="button" class="btn-toggle-panel " id="btnToggleHerramientas" aria-expanded="false">
                <svg class="icono-outline">
                    <use href="#icono-ajustes"></use>
                </svg>
                <span>Acciones de Limpieza</span>
                <svg class="icono-outline arrow-icon">
                    <use href="#icono-flecha-abajo"></use>
                </svg>
            </button>
        </div>

        <!-- PANEL DESPLEGABLE CON ACCIONES DIRECTAS -->
        <div class="toolbar-panel-content is-hidden" id="panelHerramientas">
            <div class="panel-row d-flex justify-content-end gap-2 align-items-center">
                <button type="button" class="button-anuncio" id="btnVaciarApp" style="background-color: #e67e22; color: #fff;">
                    Vaciar Log Aplicación
                </button>
                <button type="button" class="button-anuncio" id="btnVaciarDb" style="background-color: #2980b9; color: #fff;">
                    Vaciar Log BD
                </button>
                <button type="button" class="danger button-anuncio" id="btnVaciarAmbos" style="background-color: #dc3545; color: #fff;">
                    <svg class="icono-outline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    Vaciar Todos los Logs
                </button>
            </div>
        </div>
    </section>

    <div class="container-logs-card">
        <!-- BLOQUE 1: ERRORES DE APLICACIÓN (PHP / APP) -->
        <div class="seccion-log mt-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h4 style="font-size: 15px; font-weight: 600; color: #10b981; margin: 0;">
                    <span style="display:inline-block; width:10px; height:10px; background:#10b981; border-radius:50%; margin-right:6px;"></span>
                    Errores de Aplicación (log_app.log)
                </h4>
                <small id="pesoAppLog" class="byte-text">0 Bytes</small>
            </div>
            <div id="terminalApp" style="background: #f8f9fa; border-radius: 8px; padding: 12px; border: 1px solid #e2e8f0; max-height: 300px; overflow-y: auto;">
                <div class="text-muted small">Cargando registros de aplicación...</div>
            </div>
        </div>

        <!-- BLOQUE 2: ERRORES DE BASE DE DATOS (SQL / DB) -->
        <div class="seccion-log mt-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h4 style="font-size: 15px; font-weight: 600; color: #38bdf8; margin: 0;">
                    <span style="display:inline-block; width:10px; height:10px; background:#38bdf8; border-radius:50%; margin-right:6px;"></span>
                    Consultas y Errores de Base de Datos (log_db.log)
                </h4>
                <small id="pesoDbLog" class="byte-text">0 Bytes</small>
            </div>
            <div id="terminalDb" style="background: #f8f9fa; border-radius: 8px; padding: 12px; border: 1px solid #e2e8f0; max-height: 300px; overflow-y: auto;">
                <div class="text-muted small">Cargando registros de base de datos...</div>
            </div>
        </div>
    </div>
</article>

<script src="<?= URL_BASE ?>public/js/<?= $pag ?? '' ?>.js" defer></script>