<article>
    <h1 class="title-pag">Foto de Pefil</h1>
    <p class="text-pag">
        Actualizacion de la imagen del perfil
    </p>


    <section class="section-usuario section-inf section-anuncio">
        <h2 class="section-usuario-h2">
            <svg class="icono-outline">
                <use href="#icono-foto" />
            </svg>
            Foto de Perfil
        </h2>
        <hr />

        <!-- 1. FOTO DE PERFIL ACTUAL (Solo cambia cuando PHP responde 200 OK) -->
        <div class="contaniner-foto-perfil-muestras">
            <div class="muestra-div-img">
                <p class="text-muted small mb-1">Vista previa de la imagen actual :</p>
                <img id="foto-perfil-actual" src="/assets/img/avatar-default.png" alt="Foto Actual" class="rounded-circle img-thumbnail">

            </div>
            <!-- 2. CONTENEDOR Y ETIQUETA EXCLUSIVA PARA PREVISUALIZAR -->
            <div id="contenedor-previsualizacion" class="muestra-div-img" style="display: none;">
                <p class="text-muted small mb-1">Vista previa de la nueva imagen:</p>
                <img id="vista-previa" src="" alt="Previsualización">
            </div>
        </div>
        <!-- FORMULARIO DE CARGA -->
        <form id="formCambiarFoto" class="form-usuario" action="foto/guardar" method="POST" enctype="multipart/form-data">
            <input type="hidden" value="<?= $token ?? '' ?>" id="csrf_token" name="csrf_token">

            <div class="grid-form">

                <div class="campo-grupo">
                    <label for="inputFoto" class="form-label">Seleccionar Nueva Imagen</label>
                    <input type="file" id="inputFoto" name="foto" accept="image/png, image/jpeg, image/webp" class="form-control">
                    <span id="loader" class="cargando-oculto ">
                        <svg class="icono-girando" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                        </svg>
                        Buscando...
                    </span>
                </div>


        </form>

        <hr />

        <div class="container-button-anuncion">
            <button type="submit" class="success button-anuncio" form="formCambiarFoto">
                <svg class="icono-outline">
                    <use href="#icon-confirmar" />
                </svg>
                Subir Foto
            </button>
            <button type="reset" class="limpiar button-anuncio" form="formCambiarFoto">
                <svg class="icono-outline">
                    <use href="#icon-limpiar" />
                </svg>
                Limpiar
            </button>
        </div>
        </div>

    </section>
    <script src="<?= URL_BASE ?>public/js/foto.js" defer></script>
</article>