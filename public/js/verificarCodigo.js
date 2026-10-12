// Icono SVG para el botón de cierre del modal
const svgCerrar = `
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <line x1="18" y1="6" x2="6" y2="18"></line>
    <line x1="6" y1="6" x2="18" y2="18"></line>
  </svg>
`;

// 1. Inicializamos el primer formulario del token (respetando tu arquitectura)
inicializarFormulario("form-token", "token-verificar", (res) => {
  // Creamos el contenedor del formulario de nueva contraseña
  const formUsuario = document.createElement("div");
  formUsuario.className = "modal-ofertas-container"; // O tu clase base de modales
  formUsuario.innerHTML = `
    <h2 class="title-login success">Verificado</h2>
    <hr>
    <form method="post" class="form-usuario" id="form-newUserPass">
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
            <input type="password" name="contrasena_confirm" id="contrasena-nueva" placeholder=" " required />
            <label for="contrasena-nueva">Confirmar Contraseña</label>
        </div>
    </form>

    <hr />

    <div class="container-button-anuncion">
      <button type="submit" class="success button-anuncio" form="form-newUserPass">
        <svg class="icono-outline"><use href="#icono-login"></use></svg>
        Actualizar
      </button>
    </div>
    
  `;

  // 2. Mostramos el Modal con el componente creado (igual que en carreras.js)
  AlertApp.show("", formUsuario, "", null, {
    claseExtra: "ventana-modal modal-large",
    btnTexto: "Cerrar",
    btnIcono: svgCerrar,
    btnClase: "btn-ghost-danger",
    ocultarHeader: true,
  });

  // 3. Validaciones visuales auxiliares dentro del contenedor
  const pass = formUsuario.querySelector("#contrasena");
  const passNew = formUsuario.querySelector("#contrasena-nueva");

  if (passNew) {
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
  }

  // 4. Inicializamos el segundo formulario usando exactamente la misma función global
  inicializarFormulario(
    "form-newUserPass",
    "cuenta/recuperada",
    (respuesta) => {
      AlertApp.show(
        "Éxito",
        "La contraseña ha sido actualizada con éxito.",
        "success",
        "dashboard",
      );
    },
  );
});
