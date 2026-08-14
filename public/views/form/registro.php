<div class="container-registro-form">
    <h2 class="title-login">Registro</h2>
    <hr>
    <p>Aqui es donde puedes registrarte, elije un nombre de usuario y estable tu contraseña para poder iniciar session
        mas tarde pero lo mas importe es el correo ya que con el podras recuperar tu cuenta
        asegurate de que este activo para mas tarde verificarlo
    </p>
    <form class="form-registro" id="form-registro">

        <div class="reg-row-dual">

            <div class="reg-field-group">
                <div class="reg-container-svg">
                    <svg class="icono-outline">
                        <use href="#icono-usuario"></use>
                    </svg>
                </div>
                <input
                    type="text"
                    name="usuario"
                    id="usuario"
                    class="reg-input"
                    placeholder=" "
                    required />
                <label for="usuario" class="reg-label">Usuario</label>
            </div>

            <div class="reg-field-group">
                <div class="reg-container-svg">
                    <svg class="icono-outline">
                        <use href="#icono-candado"></use>
                    </svg>
                </div>
                <input
                    type="password"
                    name="contrasena"
                    id="contrasena"
                    class="reg-input"
                    placeholder=" "
                    required />
                <label for="contrasena" class="reg-label">Contraseña</label>
            </div>

        </div>

        <div class="reg-field-group reg-full-width">
            <div class="reg-container-svg">
                <svg class="icono-outline">
                    <use href="#icono-correo"></use>
                </svg>
            </div>
            <input
                type="email"
                name="correo"
                id="correo"
                class="reg-input"
                placeholder=" "
                required />
            <label for="correo" class="reg-label">Correo Electrónico</label>
        </div>
        <a target="_blank" href="terminos" class="enlace-terminos-condiciones">Has click aqui para Consultar los terminos y condiciones</a>
        <div class="div-input campo-checkbox">
            <input
                type="checkbox"
                name="terminos_id"
                id="terminos_id"
                value="<?= $idTermino ?? '' ?>"
                required />
            <label for="terminos">He leído y acepto los términos y condiciones</label>
        </div>
        <input type="hidden" name="csrf_token" value="<?= $token ?? '' ?>">

        <button class="btn-summit" type="submit">
            <svg class="icon-sistema-rellen">
                <use href="#icon-registro"></use>
            </svg>
            Enviar
        </button>

    </form>
    <br>
</div>
<script defer src="public/js/registro.js"></script>