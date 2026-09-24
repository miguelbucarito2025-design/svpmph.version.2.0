<article>
    <h1 class="title-pag">Gremio</h1>
    <p class="text-pag">
        Modulo de registro del Gremio
    </p>
    <section class="section-usuario section-inf section-anuncio">
        <h2 class="section-usuario-h2">
            <svg class="icon-sistema-rellen">
                <use href="#icon-registro" />
            </svg>
            Formulario
        </h2>
        <hr />
        <form method="post" class="form-usuario" id="gremio">
            <div class="grid-form">

                <!-- Campo Mención -->
                <div class="campo-grupo campo-grupo-img">
                    <label for="mencion-id">Mención *</label>
                    <select name="mencion_id" id="mencion-id" required>
                        <option value="">Cargando...</option>
                    </select>
                    <div class="img-prueva">
                        <img id="mencion-img" src="" alt="">
                        <p class="text-muted small mb-1" id="mencion-nombre"></p>
                    </div>
                </div>

                <!-- Campo Promoción -->
                <div class="campo-grupo campo-grupo-img">
                    <label for="promocion-id">Promoción *</label>
                    <select name="promocion_id" id="promocion-id" required>
                        <option value="">Cargando...</option>
                    </select>
                    <div class="img-prueva">
                        <img id="promocion-img" src="" alt="">
                        <p class="text-muted small mb-1" id="promocion-nombre"></p>
                    </div>
                </div>

                <!-- Requisitos en Formato Lista -->
                <div class="campo-grupo campo-full">
                    <h2>Requisitos</h2>
                    <ul id="lista-requisitos" class="lista-requisitos">
                        <li class="text-muted">Seleccione una mención para ver los requisitos.</li>
                    </ul>
                </div>
            </div>
        </form>
        <hr />
        <div class="container-button-anuncion">
            <button type="submit" class="success button-anuncio" form="gremio">
                <svg class="icono-outline">
                    <use href="#icon-confirmar" />
                </svg>
                Vamos
            </button>
            <button type="reset" class="limpiar button-anuncio" form="gremio">
                <svg class="icono-outline">
                    <use href="#icon-limpiar" />
                </svg>
                Limpiar
            </button>
        </div>
    </section>


    <section>

        <div class="container-archivos" id="contenedor-archivos"></div>
    </section>



    <script src="<?= URL_BASE ?>public/js/<?= $pag ?? '' ?>.js" defer></script>
</article>