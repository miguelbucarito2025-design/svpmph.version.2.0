/**
 * Módulo para la gestión de foto de perfil con previsualización temporal dedicada.
 * Documentación:
 * 1. Muestra un borrador local en #vista-previa al elegir un archivo en #inputFoto.
 * 2. Mantiene intacta la imagen #foto-perfil hasta recibir confirmación del backend.
 */
document.addEventListener("DOMContentLoaded", () => {
  const inputFoto = document.getElementById("inputFoto");
  const imgVistaPrevia = document.getElementById("vista-previa");
  const contenedorPreview = document.getElementById(
    "contenedor-previsualizacion",
  );
  const imgFotoPerfilActual = document.getElementById("foto-perfil");
  const formFoto = document.getElementById("formCambiarFoto");

  // ----------------------------------------------------
  // PASO 1: Previsualización local en el input
  // ----------------------------------------------------
  if (inputFoto && imgVistaPrevia) {
    inputFoto.addEventListener("change", (e) => {
      const archivo = e.target.files[0];

      if (archivo) {
        // Validación rápida de tipo de archivo en el cliente
        if (!archivo.type.startsWith("image/")) {
          alert("Por favor, selecciona un archivo de imagen válido.");
          inputFoto.value = "";
          if (contenedorPreview) contenedorPreview.style.display = "none";
          return;
        }

        // Generamos el enlace temporal en RAM y se lo asignamos SOLO a la previsualización
        const urlTemporal = URL.createObjectURL(archivo);
        imgVistaPrevia.src = urlTemporal;

        // Hacemos visible el recuadro de previsualización
        if (contenedorPreview) {
          contenedorPreview.style.display = "block";
        }
      } else {
        // Si el usuario canceló la selección de archivo, ocultamos la previsualización
        if (contenedorPreview) contenedorPreview.style.display = "none";
        imgVistaPrevia.src = "";
      }
    });
  }

  let imgUsuario = document.getElementById("foto-perfil").src;
  imgMuestra = document.getElementById("foto-perfil-actual").src = imgUsuario;

  // ----------------------------------------------------
  // PASO 2: Envío AJAX y actualización del avatar real
  // ----------------------------------------------------
  if (formFoto) {
    formFoto.addEventListener("submit", async (e) => {
      e.preventDefault();

      const btnSubir = document.querySelector(
        'button[form="formCambiarFoto"][type="submit"]',
      );
      let divMensaje = document.getElementById("mensaje-respuesta-foto");

      if (!divMensaje) {
        divMensaje = document.createElement("div");
        divMensaje.id = "mensaje-respuesta-foto";
        formFoto.insertAdjacentElement("afterend", divMensaje);
      }

      const formData = new FormData(formFoto);

      if (btnSubir) btnSubir.disabled = true;
      divMensaje.innerHTML = "";

      try {
        loader();

        const respuesta = await fetch(formFoto.getAttribute("action"), {
          method: "POST",
          body: formData,
        });

        const resultado = await respuesta.json();

        // Evaluamos según la estructura que devuelve tu clase Response::json
        if (respuesta.ok && resultado.code === 200 && resultado.data) {
          AlertApp.show(resultado.message, "", "success");

          // ¡AQUÍ SÍ cambiamos la foto de perfil principal con la URL devuelta por R2!
          if (imgFotoPerfilActual && resultado.data.url) {
            imgFotoPerfilActual.src = resultado.data.url;
          }
          loader();

          let imgUsuario = document.getElementById("foto-perfil").src;
          let imgMuestra = (document.getElementById("foto-perfil-actual").src =
            imgUsuario);
          // Limpiamos el formulario y ocultamos el cuadro de previsualización
          formFoto.reset();
          if (contenedorPreview) contenedorPreview.style.display = "none";
          imgVistaPrevia.src = "";
        } else {
          loader();
          if (resultado.code === 400) {
            AlertApp.show(
              "Advertencia",
              resultado.message,
              "warning",
              "perfil",
            );
          } else {
            AlertApp.show("Error del Servidor", resultado.message, "error");
          }
        }
      } catch (error) {
        console.error("Error en la petición AJAX:", error);
        AlertApp.show("Error de Conexion", "", "error");
      } finally {
        if (btnSubir) btnSubir.disabled = false;
      }
    });
  }
});
