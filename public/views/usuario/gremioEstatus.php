<article>
    <h1 class="title-pag">Estatus de Gremio</h1>
    <p class="text-pag">
        Resumen informativo de su estatus, datos de afiliación y condición de miembro.
    </p>

    <!-- Sección 1: Resumen de Afiliación y Botón para Abrir Modal -->
    <section class="section-usuario section-inf section-anuncio">
        <div class="header-estatus-wrapper">
            <h2 class="section-usuario-h2">
                <svg class="icon-sistema-rellen">
                    <use href="#icono-estatus" />
                </svg>
                Resumen de Afiliación
            </h2>

            <!-- Botón para lanzar el Modal de Inscripción/Edición -->

        </div>

        <div class="grid-form" id="gremio">
            <button type="button" class="success button-anuncio btn-abrir-registro max-width-250" id="btnAbrirModalRegistro">
                <svg class="icon-sistema-rellen">
                    <use href="#icon-registro" />
                </svg>
                Gestionar Afiliación
            </button>

            <!-- Ficha 1: Datos del Afiliado -->
            <div class="campo-grupo campo-full card-resumen-afiliado">
                <div class="header-afiliado">
                    <h3 id="usuario-nombre-completo" class="nombre-afiliado">Cargando usuario...</h3>
                    <span id="usuario-cedula" class="badge-cedula">C.I: ---</span>
                </div>
                <div class="body-afiliado-detalles">
                    <p><strong>Código de Registro:</strong> <span id="usuario-codigo" class="text-destacado">---</span></p>
                    <p><strong>Estado Institucional:</strong> <span id="usuario-estado" class="badg">---</span></p>
                </div>
            </div>

            <!-- Ficha 2: Mención -->
            <div class="campo-grupo campo-grupo-img">
                <span class="label-resumen">Mención Actual</span>
                <div class="img-prueva">
                    <img id="mencion-img" src="" alt="Mención">
                    <p class="text-muted small mb-1" id="mencion-nombre">Cargando...</p>
                </div>
            </div>

            <!-- Ficha 3: Promoción -->
            <div class="campo-grupo campo-grupo-img">
                <span class="label-resumen">Promoción Asignada</span>
                <div class="img-prueva">
                    <img id="promocion-img" src="" alt="Promoción">
                    <p class="text-muted small mb-1" id="promocion-nombre">Cargando...</p>
                </div>
            </div>

        </div>
        <hr />
        <button type="button" style="display: none;" class="cancelarRegistrocolor button-anuncio btn-abrir-registro " id="btnEliminar-gremio">
            <svg class="icon-sistema-rellen">
                <use href="#icono-cancelar" />
            </svg>
            Cancelar Afiliación
        </button>
    </section>

    <!-- Sección 2: Expediente Digital de Archivos -->
    <section class="section-usuario section-anuncio section-archivos">
        <h2 class="title-pag">Expediente Digital / Archivos Consignados</h2>

        <div class="container-archivos" id="contenedor-archivos">
            <!-- Renderizado dinámico vía JS -->
        </div>
    </section>

    <script src="<?= URL_BASE ?>public/js/<?= $pag ?? '' ?>.js" defer></script>
</article>