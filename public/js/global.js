/**
 * Función global para realizar peticiones HTTP (AJAX) de forma flexible
 * @param {string} url - La ruta del archivo PHP (ej: 'controllers/usuario.php')
 * @param {string} metodo - 'GET', 'POST', 'PUT', 'DELETE'
 * @param {object|null} datos - El objeto con los datos que quieres enviar (para POST/PUT)
 * @returns {Promise<object>} - Devuelve la respuesta del servidor convertida en JSON
 */
async function realizarPeticion(url, metodo = "GET", datos = null) {
  const opciones = {
    method: metodo.toUpperCase(),
    headers: {
      "X-Requested-With": "XMLHttpRequest",
      // 💡 Quitamos el 'Content-Type: application/json' para que actúe como un formulario normal
    },
  };

  if ((opciones.method === "POST" || opciones.method === "PUT") && datos) {
    // Si ya es un FormData (por ejemplo, si envías un formulario HTML entero), lo dejamos igual
    if (datos instanceof FormData) {
      opciones.body = datos;
    } else {
      // 💡 Si es un objeto común de JS, lo convertimos automáticamente a formato de formulario ($_POST)
      const formularioVirtual = new FormData();
      for (const llave in datos) {
        formularioVirtual.append(llave, datos[llave]);
      }
      opciones.body = formularioVirtual;
    }
  }

  try {
    const respuesta = await fetch(url, opciones);

    // 💡 Si el estatus HTTP no es un éxito (200-299)
    if (!respuesta.ok) {
      try {
        const errorJson = await respuesta.json();

        // 💡 Creamos un objeto de error personalizado para JS
        const miError = new Error(errorJson.message || "Error en el proceso.");
        miError.status = respuesta.status; // Guardamos el 400, 403, 500, etc.

        throw miError; // Lo mandamos al formulario
      } catch (jsonError) {
        // Por si el servidor escupe un error fatal HTML (Sintaxis PHP rota)
        if (jsonError instanceof SyntaxError) {
          const errorCritico = new Error(
            `Error crítico en el backend (Código ${respuesta.status})`,
          );
          errorCritico.status = respuesta.status;
          throw errorCritico;
        }
        throw jsonError; // Propaga el error estructurado si ya se armó arriba
      }
    }

    // Si todo salió excelente (HTTP 200), parseamos y devolvemos la data limpia
    return await respuesta.json();
  } catch (error) {
    // Registramos en la consola de CodeLink para depuración interna
    console.error("Error en la petición AJAX:", error);
    // Re-lanzamos el error para que llegue vivo al catch de formEnv()
    throw error;
  }
}

/**
 * @file formHandler.js
 * @description Módulo encargado de interceptar el envío de formularios HTML,
 * empaquetarlos mediante FormData y procesar la respuesta asíncrona del servidor.
 * @author Arquitectura SVPMPH
 * @version 1.0.0
 */

/**
 * Configura un formulario de manera genérica para enviar sus datos al servidor
 * y reaccionar de forma limpia ante cualquier respuesta o error.
 *
 * @param {string} idFormulario - El valor del atributo id del formulario en el HTML.
 * @param {string} urlDestino - La ruta del controlador backend que procesará la petición.
 * @param {Function} [callbackExito] - Función opcional que se ejecuta al completarse con éxito (ej: redirecciones o cierres de modales).
 * @returns {void}
 */
function inicializarFormulario(idFormulario, urlDestino, callbackExito = null) {
  // 1. Buscamos la estructura del formulario en el documento
  const formulario = document.getElementById(idFormulario);

  if (!formulario) {
    console.error(
      `[Arquitectura SVPMPH]: El formulario con ID "${idFormulario} no existe en el DOM.`,
    );
    return;
  }

  // 2. Escuchamos el evento de envío (submit)
  formulario.addEventListener("submit", async function (evento) {
    // Detenemos el comportamiento nativo para evitar recargas bruscas de página
    evento.preventDefault();

    // Empaquetamos todos los campos del formulario (incluyendo archivos y token CSRF oculto)
    const datosFormulario = new FormData(formulario);

    try {
      // 3. Invocamos nuestra función global de red
      const respuesta = await realizarPeticion(
        urlDestino,
        "POST",
        datosFormulario,
      );

      // 4. Evaluamos el contrato de éxito dictado por el backend PHP
      if (respuesta.status === "success") {
        const titulo = respuesta.title || "¡Operación Exitosa!";
        const mensaje =
          respuesta.message || "Los datos se procesaron correctamente.";

        // Mostramos la alerta estandarizada (asumiendo que usas tu sistema AlertApp)
        if (typeof AlertApp !== "undefined") {
          AlertApp.show(titulo, mensaje, "success");
        } else {
          alert(mensaje);
        }

        // Limpiamos los campos del formulario para dejar el taller limpio
        formulario.reset();

        // Si se definió una acción extra de éxito, la disparamos
        if (typeof callbackExito === "function") {
          callbackExito(respuesta);
        }
      } else {
        // Si el backend respondió con un estado lógico alternativo (ej: validación fallida controlada)
        const mensajeAdvertencia =
          respuesta.message || "Verifique los datos ingresados.";

        if (typeof AlertApp !== "undefined") {
          AlertApp.show("Atención", mensajeAdvertencia, "warning");
        } else {
          alert("Atención: " + mensajeAdvertencia);
        }
      }
    } catch (error) {
      // 5. Atrapamos errores de red o códigos HTTP críticos (400, 401, 429, 500)
      console.error("[Error Crítico en Petición]:", error);

      const mensajeErrorServidor =
        error.message || "No se pudo establecer comunicación con el servidor.";

      if (typeof AlertApp !== "undefined") {
        // Evaluamos el código HTTP que viene atrapado en el objeto de error para refinar la alerta
        if (error.status === 409) {
          AlertApp.show("Registro Duplicado", mensajeErrorServidor, "warning");
        } else if (error.status === 401) {
          AlertApp.show("Acceso Denegado", mensajeErrorServidor, "warning");
        } else if (error.status === 403) {
          AlertApp.show("Usuario Bloqueado", mensajeErrorServidor, "warning");
        } else if (error.status === 429) {
          AlertApp.show(
            "Límite Excedido",
            "Demasiados intentos. Intente más tarde.",
            "warning",
          );
        } else {
          AlertApp.show("Error de Sistema", mensajeErrorServidor, "error");
        }
      } else {
        alert("Error: " + mensajeErrorServidor);
      }
    }
  });
}

const AlertApp = {
  dialog: document.getElementById("custom-alert"),
  icon: document.getElementById("alert-icon"),
  title: document.getElementById("alert-title"),
  body: document.getElementById("alert-body"),
  actions: document.getElementById("alert-actions"),
  btnClose: document.getElementById("alert-btn-close"),

  init() {
    if (this.btnClose) {
      this.btnClose.addEventListener("click", () => this.dialog.close());
    }
  },

  /**
   * Muestra la alerta ultra flexible.
   * @param {string} titulo - El título de la cabecera.
   * @param {string|HTMLElement} contenido - Texto plano, HTML en string o un Elemento del DOM real.
   * @param {string} tipo - 'success', 'error', 'info', 'warning', 'custom'.
   */
  show(titulo, contenido, tipo = "info") {
    if (!this.dialog) return;

    // 1. Configurar tipo y título
    this.dialog.className = "custom-alert " + tipo;
    this.title.textContent = titulo;

    // 2. Diccionario de iconos vectoriales (SVG) limpios y profesionales
    const iconos = {
      success: `
        <svg class="svg-icon success" viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
          <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>`,
      error: `
        <svg class="svg-icon error" viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="15" y1="9" x2="9" y2="15"></line>
          <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>`,
      warning: `
        <svg class="svg-icon warning" viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
          line x1="12" y1="9" x2="12" y2="13"></line>
          <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>`,
      info: `
        <svg class="svg-icon info" viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#0284c7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="16" x2="12" y2="12"></line>
          <line x1="12" y1="8" x2="12.01" y2="8"></line>
        </svg>`,
    };
    // Inyectamos el SVG directamente en el contenedor del icono
    this.icon.innerHTML = iconos[tipo] || iconos.info;
    this.icon.style.display = tipo === "none" ? "none" : "block";

    // 3. Limpiar el cuerpo e inyectar el contenido dinámico
    this.body.innerHTML = "";
    if (contenido instanceof HTMLElement) {
      this.body.appendChild(contenido);
    } else {
      this.body.innerHTML = contenido;
    }

    // 4. Mostrar el diálogo de forma nativa
    this.dialog.showModal();
  },
};

AlertApp.init();

function redirec(url) {
  window.location.href = url;
}
