inicializarFormulario("form-registro", "registro/guardar", (res) => {
  AlertApp.show(
    "Se ha enviado un token de verificacion a tu correo",
    "una vez dentro en el menu ve a Mi cuenta y luego presiona Cuenta de Usuario y en la sección de correo electronico  coloca el token para confirmar tu correo para que puedas recuperar tu cuenta si olvidas tu usuario o clave. ",
    "info",
    "dashboard",
  );
});
