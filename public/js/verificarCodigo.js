const tokenCsrfActual =
  document.querySelector('input[name="csrf_token"]')?.value || "";

// Icono SVG para el botón de cierre del modal
const svgCerrar = `
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <line x1="18" y1="6" x2="6" y2="18"></line>
    <line x1="6" y1="6" x2="18" y2="18"></line>
  </svg>
`;

const formUsuario = document.createElement("div");
formUsuario.innerHTML = `
    <h2 class="title-login">Verificado</h2>
    <hr>
    <form method="post" id="form-newUserPass">
        <p>Coloque el usuario y la nueva contraseña para completar la recuperación.<br><br></p>
        
        <div class="div-input campo-icon">
            <div class="container-svg">
                <svg class="icono-outline"><use href="#icono-usuario"></use></svg>
            </div>
            <input type="text" name="usuario" id="usuario" placeholder=" " required />
            <label for="usuario">Usuario</label>
        </div>

        <div class="div-input campo-icon">
            <div class="container-svg">
                <svg class="icono-outline"><use href="#icono-candado"></use></svg>
            </div>
            <input type="password" name="contrasena" id="contrasena" placeholder=" " required />
            <label for="contrasena">Nueva Contraseña</label>
        </div>

        <div class="div-input campo-icon">
            <div class="container-svg">
                <svg class="icono-outline"><use href="#icono-candado"></use></svg>
            </div>
            <!-- 1. CORREGIDO: name="contrasena_confirm" y label for="contrasena-nueva" -->
            <input type="password" name="contrasena_confirm" id="contrasena-nueva" placeholder=" " required />
            <label for="contrasena-nueva">Confirmar Contraseña</label>
        </div>

        <input type="hidden" name="csrf_token" value="${tokenCsrfActual}">

        <button class="btn-summit" type="submit">
            <svg class="icono-outline"><use href="#icono-login"></use></svg>
            Actualizar
        </button>
    </form>
`;

inicializarFormulario("form-token", "token/verificar", (res) => {
  // 1. Mostrar Modal con el botón rojo transparente y la X de cierre
  AlertApp.show("", formUsuario, "none", null, {
    btnTexto: "Cerrar",
    btnIcono: svgCerrar,
    btnClase: "btn-ghost-danger",
    ocultarHeader: true,
    claseExtra: "modal-formulario-expandido",
  });

  const pass = document.getElementById("contrasena");
  const passNew = document.getElementById("contrasena-nueva");
  const formNewUser = document.getElementById("form-newUserPass");

  // 2. Validación visual al salir del SEGUNDO campo de contraseña
  passNew.addEventListener("blur", () => {
    const v1 = pass.value.trim();
    const v2 = passNew.value.trim();

    if (v1 !== "" && v2 !== "" && v1 !== v2) {
      AlertApp.show(
        "Contraseñas no coinciden",
        "Las contraseñas no son iguales. Por favor, verifique e intente de nuevo.",
        "warning",
      );
    }
  });

  // 3. Barrera de seguridad: Evita el envío por AJAX si no coinciden
  formNewUser.addEventListener("submit", (e) => {
    if (pass.value !== passNew.value) {
      e.preventDefault();
      e.stopImmediatePropagation(); // Cancela la ejecución de inicializarFormulario
      AlertApp.show(
        "Atención",
        "No puede continuar porque las contraseñas no coinciden.",
        "warning",
      );
    }
  });

  // 4. Inicialización del procesamiento AJAX hacia PHP
  inicializarFormulario("form-newUserPass", "cuenta/recuperada", () => {
    AlertApp.show(
      "Éxito",
      "La contraseña ha sido actualizada con éxito.",
      "success",
      "dashboard",
    );
  });
});
