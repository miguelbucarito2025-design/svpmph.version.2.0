<article>
    <h1 class="title-pag">Datos personales</h1>
    <p class="text-pag">
        Gestión y actualización de tu información de identidad y contacto para la validación dentro del gremio.
    </p>
    <section class="section-usuario section-inf section-anuncio">
        <h2 class="section-usuario-h2">
            <svg class="icon-sistema-rellen">
                <use href="#icon-registro" />
            </svg>
            Formulario
        </h2>
        <hr />
        <form action="<?= $ruta ?? '' ?>" method="post" class="form-usuario" id="perfil">
            <div class="grid-form">

                <div class="campo-grupo">
                    <label for="nombre">Primer Nombre *</label>
                    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($datos['datos']['nombre'] ?? '') ?>" required>
                </div>

                <input type="hidden" value="<?= $token ?? '' ?>" name="csrf_token">

                <div class="campo-grupo">

                    <label for="s_nombre">Segundo Nombre </label>
                    <input type="text" id="s_nombre" name="s_nombre" value="<?= htmlspecialchars($datos['datos']['s_nombre'] ?? '') ?>">
                </div>

                <div class="campo-grupo">
                    <label for="apellido">Primer Apellido *</label>
                    <input type="text" id="apellido" name="apellido" value="<?= htmlspecialchars($datos['datos']['apellido'] ?? '') ?>" required>
                </div>

                <div class="campo-grupo">
                    <label for="s_apellido">Segundo Apellido</label>
                    <input type="text" id="s_apellido" name="s_apellido" value="<?= htmlspecialchars($datos['datos']['s_apellido'] ?? '') ?>">
                </div>

                <div class="campo-grupo">
                    <label for="id_cedula">Cédula de Identidad *</label>
                    <input type="text" id="id_cedula" name="id_cedula" value="<?= htmlspecialchars($datos['datos']['id_cedula'] ?? '') ?>" placeholder="Ej: V-12345678" required>
                </div>

                <div class="campo-grupo">
                    <label for="tlf">Teléfono de Contacto *</label>
                    <input type="tel" id="tlf" name="tlf" value="<?= htmlspecialchars($datos['datos']['tlf'] ?? '') ?>" placeholder="Ej: 0412-1234567" required>
                </div>

                <div class="campo-grupo">
                    <label for="edad">Fecha de nacimiento *</label>
                    <input type="date" id="edad" name="edad" value="<?= htmlspecialchars($datos['datos']['edad'] ?? '') ?>" required>
                </div>

                <div class="campo-grupo campo-full">
                    <label for="direccion">Dirección de Habitación *</label>
                    <textarea id="direccion" name="direccion" rows="3" required><?= htmlspecialchars($datos['datos']['direccion'] ?? '') ?></textarea>
                </div>

            </div>


        </form>
        <hr />
        <div class="container-button-anuncion">
            <button type="summit" class="success button-anuncio" form="perfil">
                <svg class="icono-outline">
                    <use href="#icon-confirmar" />
                </svg>
                Vamos
            </button>
            <button type="reset" class="limpiar button-anuncio" form="perfil">
                <svg class="icono-outline">
                    <use href="#icon-limpiar" />
                </svg>
                Limpiar
            </button>
        </div>
    </section>

    <script src="<?= URL_BASE ?>public/js/perfil.js" defer></script>
</article>