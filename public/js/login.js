// Vinculamos el formulario de login al controlador correspondiente
inicializarFormulario("form-login", "login/auth", (res) => {
  // Esta función extra se ejecuta SÓLO si el login fue exitoso
  setTimeout(() => {
    window.location.href = "dashboard";
  }, 1500);
});
