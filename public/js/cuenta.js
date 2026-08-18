/**
 * Módulo de Gestión de Cuenta y Perfil
 *
 * Utiliza el helper global `inicializarFormulario` para procesar la actualización
 * de nombre de usuario, cambio de correo en 2 pasos y actualización de clave.
 *
 * @file public/js/cuenta.js
 */
document.addEventListener("DOMContentLoaded", () => {
  /**
   * Lista con los identificadores HTML de los formularios presentes en la vista de Cuenta.
   * @type {string[]}
   */
  const formulariosCuenta = [
    "formActualizarUsuario",
    "formSolicitarCorreo",
    "formVerificarCorreo",
    "formCambiarContrasena",
  ];

  // Iteramos e inicializamos cada formulario que exista en el DOM
  formulariosCuenta.forEach((idForm) => {
    const elementoForm = document.getElementById(idForm);

    if (elementoForm) {
      const ruta = elementoForm.action;

      inicializarFormulario(idForm, ruta, (respuesta) => {
        // Una vez guardado con éxito por el servidor, recargamos para reflejar cambios
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      });
    }
  });
});
