<article>
    <h1 class="title-pag">Gestor de Expediente Digital</h1>
    <p class="text-pag">
        Consigne, revise y gestione la documentación institucional solicitada para su expediente.
    </p>

    <!-- Sección del Expediente Digital -->
    <section class="section-usuario section-inf section-anuncio">
        <div class="header-estatus-wrapper">
            <h2 class="section-usuario-h2">
                <svg class="icon-sistema-rellen">
                    <use href="#icon-archivo" />
                </svg>
                Archivos Consignados
            </h2>
        </div>
        <hr>
        <div class="container-archivos" id="contenedor-archivos">
            <!-- Renderizado dinámico vía JS -->
            <div class="archivo-vacio-text">
                <p class="text-muted">Cargando expediente digital...</p>
            </div>
        </div>
    </section>


    <script src="<?= URL_BASE ?>public/js/<?= $pag ?? 'documentos' ?>.js" defer></script>
</article>