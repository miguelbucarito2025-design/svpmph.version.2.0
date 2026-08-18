<article>
    <h1 class="title-pag">Datos Laborales</h1>
    <p class="text-pag">
        Gestión y actualización de tu información laboral .
    </p>
    <section class="section-usuario section-inf section-anuncio">
        <h2 class="section-usuario-h2">
            <svg class="icon-sistema-rellen">
                <use href="#icono-trabajo" />

            </svg>
            Formulario
        </h2>
        <hr />
        <form action="<?= $ruta ?? '' ?>" method="post" class="form-usuario" id="perfilLaboral">
            <div class="grid-form">

                <div class="campo-grupo">
                    <label for="nombre">Institución *</label>
                    <select name="institucion_id" id="institucion_id">
                        <option value="<?= $datos['datos']['institucion_id'] ?? '' ?>"><?= !empty($datos['datos']['institucion']) ? 'Actual--> ' . $datos['datos']['institucion'] : '---Seleccione---'; ?></option>
                        <?php

                        if (empty($institucion)) {
                            $institucion = [];
                        }

                        foreach ($institucion as $i) {
                            if (($datos ?? '') == $i['id']) {
                                continue;
                            }

                        ?>
                            <option value="<?= $i['id'] ?>"><?= $i['institucion'] ?></option>
                        <?php

                        }

                        ?>
                    </select>

                </div>

                <input type="hidden" value="<?= $token ?? '' ?>" id="csrf_token" name="csrf_token">

                <div class="campo-grupo">

                    <label for="s_nombre">Cargo</label>
                    <select name="cargo_id" id="cargo_id">
                        <option value="<?= $datos['datos']['cargo_id'] ?? '' ?>"><?= !empty($datos['datos']['cargo']) ? 'Actual--> ' . $datos['datos']['cargo'] : '---Seleccione---'; ?></option>


                    </select>
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
            <button type="summit" class="success button-anuncio" form="perfilLaboral">
                <svg class="icono-outline">
                    <use href="#icon-confirmar" />
                </svg>
                Vamos
            </button>
            <button type="reset" class="limpiar button-anuncio" form="perfilLaboral">
                <svg class="icono-outline">
                    <use href="#icon-limpiar" />
                </svg>
                Limpiar
            </button>
        </div>
    </section>

</article>

<script src="public/js/laboral.js" defer></script>