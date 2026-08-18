<article>
    <h1 class="title-pag">Gestión de Cuenta</h1>
    <p class="text-pag">
        Actualización de tu información personal, correo electrónico y credenciales de acceso.
    </p>

    <!-- SECCIÓN 1: NOMBRE DE USUARIO -->
    <section class="section-usuario section-inf section-anuncio">
        <h2 class="section-usuario-h2">
            <svg class="icono-outline">
                <use href="#icono-usuario" />
            </svg>
            Nombre de Usuario
        </h2>
        <hr />
        <form action="cuenta/actualizar-usuario" method="post" class="form-usuario" id="formActualizarUsuario">
            <div class="grid-form">
                <div class="campo-grupo">
                    <label for="usuario">Nombre de usuario *</label>
                    <input
                        type="text"
                        name="usuario"
                        id="usuario"
                        value="<?= htmlspecialchars($cuenta['usuario'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required />
                </div>
                <input type="hidden" value="<?= $token ?? '' ?>" name="csrf_token">
            </div>
        </form>
        <hr />
        <div class="container-button-anuncion">
            <button type="submit" class="success button-anuncio" form="formActualizarUsuario">
                <svg class="icono-outline">
                    <use href="#icon-confirmar" />
                </svg>
                Actualizar Usuario
            </button>
        </div>
    </section>

    <!-- SECCIÓN 2: CORREO ELECTRÓNICO (VERIFICACIÓN EN 2 PASOS) -->
    <section class="section-usuario section-inf section-anuncio">
        <h2 class="section-usuario-h2">
            <svg class="icono-outline">
                <use href="#icono-correo" />
            </svg>
            Correo Electrónico
        </h2>
        <hr />

        <!-- Formulario para solicitar el código de cambio -->
        <form action="cuenta/solicitar-correo" method="post" class="form-usuario" id="formSolicitarCorreo">
            <div class="grid-form">
                <div class="campo-grupo">
                    <label for="correo_actual">Correo Actual</label>
                    <input
                        type="email"
                        id="correo_actual"
                        value="<?= htmlspecialchars($cuenta['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        disabled />
                </div>

                <div class="campo-grupo">
                    <label for="nuevo_correo">Nuevo Correo Electrónico *</label>
                    <input
                        type="email"
                        name="nuevo_correo"
                        id="nuevo_correo"
                        placeholder="ejemplo@correo.com"
                        required />
                </div>
                <input type="hidden" value="<?= $token ?? '' ?>" name="csrf_token">
            </div>
        </form>

        <?php if (!empty($cuenta['correo_pendiente'])): ?>
            <hr />
            <!-- Formulario condicional para confirmar el código si hay un correo pendiente -->
            <form action="cuenta/verificar-correo" method="post" class="form-usuario" id="formVerificarCorreo">
                <div class="grid-form">
                    <div class="campo-grupo">
                        <label for="codigo">Código enviado a: <strong><?= htmlspecialchars($cuenta['correo_pendiente'], ENT_QUOTES, 'UTF-8') ?></strong> *</label>
                        <input
                            type="text"
                            name="codigo"
                            id="codigo"
                            maxlength="6"
                            placeholder="Ingrese código de 6 dígitos"
                            required />
                    </div>
                    <input type="hidden" value="<?= $token ?? '' ?>" name="csrf_token">
                </div>
            </form>
        <?php endif; ?>

        <hr />
        <div class="container-button-anuncion">
            <?php if (!empty($cuenta['correo_pendiente'])): ?>
                <button type="submit" class="success button-anuncio" form="formVerificarCorreo">
                    <svg class="icono-outline">
                        <use href="#icon-confirmar" />
                    </svg>
                    Confirmar Código
                </button>
            <?php endif; ?>

            <button type="submit" class="button-anuncio amarillo" form="formSolicitarCorreo">
                <svg class="icono-outline">
                    <use href="#icon-confirmar" />
                </svg>
                Solicitar Código
            </button>
        </div>
    </section>

    <!-- SECCIÓN 3: CAMBIO DE CONTRASEÑA -->
    <section class="section-usuario section-inf section-anuncio">
        <h2 class="section-usuario-h2">
            <svg class="icono-outline">
                <use href="#icono-escudo" />
            </svg>
            Seguridad y Contraseña
        </h2>
        <hr />
        <form action="cuenta/cambiar-contrasena" method="post" class="form-usuario" id="formCambiarContrasena">
            <div class="grid-form">
                <div class="campo-grupo">
                    <label for="actual_contrasena">Contraseña Actual *</label>
                    <input
                        type="password"
                        name="actual_contrasena"
                        id="actual_contrasena"
                        required />
                </div>

                <div class="campo-grupo">
                    <label for="nueva_contrasena">Nueva Contraseña *</label>
                    <input
                        type="password"
                        name="nueva_contrasena"
                        id="nueva_contrasena"
                        required />
                </div>
                <input type="hidden" value="<?= $token ?? '' ?>" name="csrf_token">
            </div>
        </form>
        <hr />
        <div class="container-button-anuncion">
            <button type="submit" class="success button-anuncio" form="formCambiarContrasena">
                <svg class="icono-outline">
                    <use href="#icon-confirmar" />
                </svg>
                Cambiar Contraseña
            </button>
            <button type="reset" class="limpiar button-anuncio" form="formCambiarContrasena">
                <svg class="icono-outline">
                    <use href="#icon-limpiar" />
                </svg>
                Limpiar
            </button>
        </div>
    </section>
</article>

<script src="public/js/cuenta.js" defer></script>