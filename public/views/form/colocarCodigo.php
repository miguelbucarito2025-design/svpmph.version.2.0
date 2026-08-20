<div class="container-registro-form">
    <h2 class="title-login">Recuperacion</h2>
    <hr>
    <p>Coloque el token que le ha llegado a su cuenta
        y si le pide el correo a donde le llego el token
        coloquelo tambien y haga click en Verificar. <br>

        <a href="recuperar" class="enlace-terminos-condiciones">Has click aqui para Volver a enviar el token de validacion</a>

    </p>
    <form method="post" class="form-registro" id="form-token">


        <div class="reg-field-group reg-full-width">
            <div class="reg-container-svg">
                <svg class="icono-outline">
                    <use href="#icono-correo"></use>
                </svg>
            </div>
            <input
                <?= !empty($correo) ? 'readonly' : '' ?>
                type="email"
                name="correo"
                id="correo"
                class="reg-input"
                placeholder=""
                required
                value="<?= $correo ?? '' ?>" />
            <label for="correo" class="reg-label">Correo Electrónico de recuperacion</label>
        </div>



        <input type="hidden" name="csrf_token" value="<?= $token ?? '' ?>">

        <div class="reg-row-dual">

            <div class="reg-field-group">
                <div class="reg-container-svg">
                    <svg class="icono-outline">
                        <use href="#icono-candado"></use>
                    </svg>
                </div>
                <input
                    type="text"
                    name="token"
                    id="key"
                    class="reg-input"
                    placeholder=" "
                    required />
                <label for="usuario" class="reg-label">Key</label>
            </div>


            <button type="submit" class="button-token">
                <svg class="icono-outline">
                    <use href="#icono-recuperar-cuenta"></use>
                </svg>
                Verificar
            </button>

        </div>
    </form>
    <br>

</div>
<script src="<?= URL_BASE ?>public/js/verificarCodigo.js" defer></script>