<div class="container-registro-form">
    <h2 class="title-login">Recuperacion</h2>
    <hr>
    <p>Bienvenido aqui podras recuperar el acceso a tu cuenta si haz olvidado tu usuario o contraseña coloca tu correo electronico para continuar con la verificacion y el envio del token,
        o pulsa en el siguiente link para ir a colocar el token de una vez. <br>
        <a href="resivido" class="enlace-terminos-condiciones">Has click aqui para Verificar el token de confirmacion</a>

    </p>
    <form method="post" class="form-registro" id="form-recuperacion">


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
            <label for="correo" class="reg-label">Correo Electrónico de recuperacion</label>
        </div>

        <input type="hidden" name="csrf_token" value="<?= $token ?? '' ?>">

        <button class="btn-summit" type="submit">
            <svg class="icono-outline">
                <use href="#icono-correo"></use>
            </svg>
            Enviar Token
        </button>

    </form>
    <br>


</div>
<script src="public/js/recuperar.js" defer></script>