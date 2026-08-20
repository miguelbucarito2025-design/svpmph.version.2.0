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
    //console.error("Error en la petición AJAX:", error);
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
      //console.error("[Error Crítico en Petición]:", error);
      AlertApp.show("Error del Sistema", error, "error");

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
/**
 * Componente AlertApp - Alertas y Modales Nativos con HTML5 <dialog>.
 */
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
   * Muestra la alerta o modal dinámico.
   *
   * @param {string} titulo - Título de la cabecera.
   * @param {string|HTMLElement} contenido - Mensaje, String HTML o Nodo DOM.
   * @param {string} tipo - 'success', 'error', 'warning', 'info', 'none'.
   * @param {Function|string|null} accion - Callback al confirmar o URL de redirección.
   * @param {Object} opciones - Configuración avanzada del modal y botón.
   */
  show(titulo, contenido, tipo = "info", accion = null, opciones = {}) {
    if (!this.dialog) return;

    // Configuración con valores por defecto
    const config = {
      btnTexto: opciones.btnTexto || "Aceptar",
      btnIcono: opciones.btnIcono || "", // String con etiqueta SVG o <use>
      btnClase: opciones.btnClase || "", // Ej: 'btn-ghost-danger'
      ocultarHeader: opciones.ocultarHeader || false,
      claseExtra: opciones.claseExtra || "", // Ej: 'modal-formulario-expandido'
    };

    // 1. Limpieza y asignación de clases al diálogo principal
    this.dialog.className = `custom-alert ${tipo} ${config.claseExtra}`.trim();

    // 2. Manejo de la cabecera (Título e Icono principal)
    if (config.ocultarHeader) {
      this.title.style.display = "none";
      this.icon.style.display = "none";
    } else {
      this.title.style.display = "block";
      this.title.textContent = titulo;

      const iconos = {
        success: `<svg class="svg-icon successAlert" viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#16a34a" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>`,
        error: `<svg class="svg-icon error" viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#dc2626" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>`,
        warning: `<svg class="svg-icon warning" viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#d97706" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>`,
        info: `<svg class="svg-icon info" viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#0284c7" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>`,
      };

      this.icon.innerHTML = iconos[tipo] || "";
      this.icon.style.display =
        tipo === "none" || !iconos[tipo] ? "none" : "block";
    }

    // 3. Inyección de contenido en el cuerpo del modal
    this.body.innerHTML = "";
    if (contenido instanceof HTMLElement) {
      this.body.appendChild(contenido);
    } else {
      this.body.innerHTML = contenido;
    }

    // 4. Reemplazo del Botón con Estilos e Iconos Personalizados
    const nuevoBoton = this.btnClose.cloneNode(true);

    // Aplicamos las clases base + la clase personalizada (ej: btn-ghost-danger)
    nuevoBoton.className = `alert-btn ${config.btnClase}`.trim();

    // Insertamos el SVG opcional junto al texto
    nuevoBoton.innerHTML =
      `${config.btnIcono} <span>${config.btnTexto}</span>`.trim();

    this.actions.replaceChild(nuevoBoton, this.btnClose);
    this.btnClose = nuevoBoton;

    // 5. Escuchador de clic para cerrar o ejecutar la acción
    this.btnClose.addEventListener("click", () => {
      this.dialog.close();
      if (typeof accion === "function") {
        accion();
      } else if (typeof accion === "string" && accion.trim() !== "") {
        window.location.href = accion;
      }
    });

    // 6. Desplegar diálogo nativo
    this.dialog.showModal();
  },
};

AlertApp.init();

function redirec(url) {
  window.location.href = url;
}

/**
 * 
 * 
 * // Icono SVG de una 'X' de cerrar
const svgCerrar = `
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <line x1="18" y1="6" x2="6" y2="18"></line>
    <line x1="6" y1="6" x2="18" y2="18"></line>
  </svg>
`;

AlertApp.show(
  "",                // Título (vacío porque usaremos ocultarHeader: true)
  formUsuario,       // Tu elemento HTML con el formulario
  "none",            // Sin tipo de icono predeterminado
  null,              // Sin redirección previa
  {
    btnTexto: "Cerrar",
    btnIcono: svgCerrar,               // <--- Le pasamos el SVG de la 'X'
    btnClase: "btn-ghost-danger",      // <--- Aplica el CSS transparente y letras rojas
    ocultarHeader: true,               // <--- Oculta el icono y título superior del modal
    claseExtra: "modal-formulario"     // <--- Clase CSS para ajustar el tamaño del modal
  }
);
 * 
 * 
 * 
 * // Si le pasas una cadena de texto como 4to parámetro, JS lo entenderá como una URL
AlertApp.show(
    "Expediente Incompleto", 
    "Debes completar tus datos antes de subir una foto.", 
    "warning", 
    "/cuenta/completar-datos" // <--- Redirige automáticamente aquí
);
 *
// Si le pasas una función como 4to parámetro, se ejecutará al pulsar Aceptar
AlertApp.show(
    "Sesión Expirada", 
    "Tu sesión ha caducado por inactividad.", 
    "info", 
    function() {
        console.log("Limpiando datos locales...");
        window.location.href = "/login";
    }
); 
 * // 1. Creas el elemento en memoria
const contenedor = document.createElement("div");
contenedor.innerHTML = `
  <p>Ingresa tu clave actual para confirmar:</p>
  <input type="password" id="clave-confirm" class="form-control mb-2">
`;

// 2. Lo envías como 2do parámetro a la alerta
AlertApp.show("Confirmación Requerida", contenedor, "warning", () => {
    const clave = document.getElementById("clave-confirm").value;
    console.log("Clave ingresada:", clave);
});
 * 
 */

/**
 * Realiza una consulta silenciosa al servidor PHP controlando el estado visual de carga.
 *
 * @param {string} urlDestino - Ruta del controlador PHP.
 * @param {FormData|Object|null} datos - Parámetros a enviar a PHP.
 * @param {Function} callbackExito - Función que recibe la respuesta (respuesta.data).
 * @param {string|null} [idLoader=null] - ID del elemento HTML que contiene el indicador visual de carga.
 */
async function consultarServidor(
  urlDestino,
  datos = null,
  callbackExito = null,
  idLoader = null,
) {
  let datosAEnviar = datos;

  if (datos && !(datos instanceof FormData)) {
    datosAEnviar = new FormData();
    Object.keys(datos).forEach((key) => {
      datosAEnviar.append(key, datos[key]);
    });
  }

  // 1. ACTIVAR ESTADO DE CARGA (Si se proporcionó un ID de loader)
  const elementoLoader = idLoader ? document.getElementById(idLoader) : null;
  if (elementoLoader) {
    elementoLoader.classList.remove("cargando-oculto");
    elementoLoader.classList.add("cargando-visible");
  }

  try {
    const respuesta = await realizarPeticion(urlDestino, "POST", datosAEnviar);

    if (respuesta.status === "success") {
      if (typeof callbackExito === "function") {
        callbackExito(respuesta.data || respuesta.datos || respuesta);
      }
    } else {
      console.warn("[Consulta SVPMPH]:", respuesta.message || "Sin datos.");
      if (typeof callbackExito === "function") callbackExito([]);
    }
  } catch (error) {
    loader();

    console.error("[Error en Consulta]:", error);
    if (typeof AlertApp !== "undefined") {
      AlertApp.show(
        "Error de Red",
        error.message || "No se pudo consultar el servidor.",
        "error",
      );
    }
  } finally {
    // 2. DESACTIVAR ESTADO DE CARGA (Garantizado aunque haya error)
    if (elementoLoader) {
      elementoLoader.classList.remove("cargando-visible");
      elementoLoader.classList.add("cargando-oculto");
    }
  }
}

/**
 * Vincula un input o select a una consulta en tiempo real con control de rebote (debounce).
 *
 * @param {string} idElemento - ID del input o select que dispara la búsqueda.
 * @param {string} urlDestino - Ruta del controlador PHP.
 * @param {Function} callbackProcesar - Callback que recibe (datos, valorIngresado) para actualizar la UI.
 * @param {Object} [opciones={}] - Opciones adicionales (tiempoDebounce, nombreParametro, evento).
 */
function configurarBusquedaTiempoReal(
  idElemento,
  urlDestino,
  callbackProcesar,
  opciones = {},
) {
  const elemento = document.getElementById(idElemento);
  const token = document.getElementById("csrf_token");
  const elementoLoader = document.getElementById("loader");

  if (!elemento) {
    console.error(
      `[Arquitectura SVPMPH]: El elemento ID "${idElemento}" no existe.`,
    );
    return;
  }

  if (!token) {
    console.error(`[Arquitectura SVPMPH]: El token   no existe.`);
    return;
  }

  // Configuraciones por defecto
  const tiempoDebounce = opciones.tiempoDebounce || 300;
  const nombreParametro =
    opciones.nombreParametro || elemento.name || "busqueda";
  const tipoEvento =
    opciones.evento || (elemento.tagName === "SELECT" ? "change" : "input");

  let temporizador = null;

  elemento.addEventListener(tipoEvento, function () {
    clearTimeout(temporizador);
    loader();

    temporizador = setTimeout(() => {
      const valor = elemento.value.trim();
      const valorToken = token.value.trim();

      const payload = {};
      payload[nombreParametro] = valor;
      payload["csrf_token"] = valorToken;

      // Invocamos la consulta silenciosa pasándole el callback
      consultarServidor(urlDestino, payload, (datos) => {
        loader();

        if (typeof callbackProcesar === "function") {
          callbackProcesar(datos, valor);
        }
      });
    }, tiempoDebounce);
  });
}

function loader() {
  const prueva = document.getElementById("loader");

  prueva.classList.toggle("cargando-visible");
}
