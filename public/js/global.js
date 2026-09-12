/**
 * Función global para realizar peticiones HTTP (AJAX) de forma flexible
 * @param {string} url - La ruta del archivo PHP (ej: 'controllers/usuario.php')
 * @param {string} metodo - 'GET', 'POST', 'PUT', 'DELETE'
 * @param {object|null} datos - El objeto con los datos que quieres enviar (para POST/PUT)
 * @returns {Promise<object>} - Devuelve la respuesta del servidor convertida en JSON
 */
async function realizarPeticion(url, metodo = "GET", datos = null) {
  const tokenCSRF = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute("content");
  const opciones = {
    method: metodo.toUpperCase(),
    headers: {
      "X-Requested-With": "XMLHttpRequest",
      "X-CSRF-TOKEN": tokenCSRF,
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

function AlertCargando() {
  const cargando = document.createElement("div");
  cargando.innerHTML = `<span id="loader" class="enviando-alerta"><svg class="icono-girando icono-girando-alerta"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" /></svg>Enviando por favor espere.....</span>`;
  AlertApp.show("", cargando, "loader", null, {
    btnTexto: "",
    btnClase: "none", // <--- Aplica el CSS transparente y letras rojas
  });
}

function TableCargando() {
  const tableAnimation = document.getElementById("tablaCuerpo");
  if (!tableAnimation) return;

  // OPCIÓN A: Inyección de cadena directa (Más limpia y rápida)
  tableAnimation.innerHTML = `
    <tr>
      <td colspan="10" class="td-cargando">
        <span id="loader" class="enviando-alerta">
          <svg class="icono-girando icono-girando-alerta" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
          </svg>
          
          Cargando datos, por favor espere...
        </span>
      </td>
    </tr>`;
}

/**
 * Configura un formulario de manera genérica para enviar sus datos al servidor
 * y reaccionar de forma limpia ante cualquier respuesta o error.
 *
 * @param {string} idFormulario - El valor del atributo id del formulario en el HTML.
 * @param {string} urlDestino - La ruta del controlador backend que procesará la petición.
 * @param {Function} [callbackExito] - Función opcional que se ejecuta al completarse con éxito (ej: redirecciones o cierres de modales).
 * @returns {void}
 */
// Variable fuera de la función para evitar envíos dobles por clics rápidos
let enviandoFormularioGlobal = false;

function inicializarFormulario(idFormulario, urlDestino, callbackExito = null) {
  const formulario = document.getElementById(idFormulario);

  if (!formulario) {
    console.error(
      `[Arquitectura SVPMPH]: El formulario con ID "${idFormulario}" no existe.`,
    );
    return;
  }

  // 1. Asignamos directamente la propiedad .onsubmit.
  // Esto SOBREESCRIBE cualquier función o evento que se haya pegado antes.
  formulario.onsubmit = async function (evento) {
    evento.preventDefault();

    // Bloqueo de peticiones si ya hay una en curso
    if (enviandoFormularioGlobal) return;
    enviandoFormularioGlobal = true;

    AlertCargando();

    const datosFormulario = new FormData(formulario);

    try {
      // 2. Disparar la petición AJAX única
      const respuesta = await realizarPeticion(
        urlDestino,
        "POST",
        datosFormulario,
      );

      if (respuesta.status === "success") {
        if (typeof AlertApp !== "undefined") {
          AlertApp.show(
            respuesta.title || "¡Éxito!",
            respuesta.message || "Guardado con éxito.",
            "success",
          );
        }

        formulario.reset();

        if (typeof callbackExito === "function") {
          callbackExito(respuesta);
        }
      } else {
        const mensajeAdvertencia =
          respuesta.message || "Verifique los datos ingresados.";
        if (typeof AlertApp !== "undefined") {
          AlertApp.show("Atención", mensajeAdvertencia, "warning");
        }
      }
    } catch (error) {
      const mensajeErrorServidor =
        error.message || "No se pudo completar la solicitud.";

      if (typeof AlertApp !== "undefined") {
        if (error.status === 409) {
          AlertApp.show("Registro Duplicado", mensajeErrorServidor, "warning");
        } else if (error.status === 400) {
          AlertApp.show(
            "Formato de Datos Invalidos",
            mensajeErrorServidor,
            "warning",
          );
        } else {
          AlertApp.show("Error de Sistema", mensajeErrorServidor, "error");
        }
      }
    } finally {
      // Liberamos el bloqueo al terminar (tanto en éxito como en fallo)
      enviandoFormularioGlobal = false;
    }
  };
}

/**
 * Componente AlertApp - Alertas y Modales Nativos con HTML5 <dialog>.
 * Manejo dinámico de botones de confirmación y cancelación.
 *
 * @author Miguel
 * @version 1.1.1
 */
const AlertApp = {
  dialog: document.getElementById("custom-alert"),
  icon: document.getElementById("alert-icon"),
  title: document.getElementById("alert-title"),
  body: document.getElementById("alert-body"),
  actions: document.getElementById("alert-actions"),

  /**
   * Inicializa escuchadores globales del elemento <dialog>.
   */
  init() {
    if (!this.dialog) return;

    this.dialog.addEventListener("cancel", () => {
      if (typeof this._onCancelCallback === "function") {
        this._onCancelCallback();
      }
    });
  },

  _onCancelCallback: null,

  /**
   * Muestra la alerta o modal dinámico.
   *
   * @param {string} titulo - Título de la cabecera.
   * @param {string|HTMLElement} contenido - Mensaje, String HTML o Nodo DOM.
   * @param {string} tipo - 'success', 'error', 'warning', 'info', 'none'.
   * @param {Function|string|null} accion - Callback de confirmación o URL.
   * @param {Object} opciones - Configuración avanzada del modal y botones.
   * @param {boolean} [opciones.mostrarCancelar=false] - Mostrar u ocultar botón cancelar.
   * @param {string} [opciones.btnTexto="Aceptar"] - Texto del botón de confirmación.
   * @param {string} [opciones.btnClase="btn-primary"] - Clase CSS para el botón de confirmación.
   * @param {string} [opciones.btnCancelarTexto="Cancelar"] - Texto del botón cancelar.
   * @param {string} [opciones.btnCancelarClase="btn-cancel"] - Clase CSS para el botón cancelar.
   * @param {Function|null} [opciones.onCancelar=null] - Callback al cancelar.
   */
  show(titulo, contenido, tipo = "info", accion = null, opciones = {}) {
    if (!this.dialog) return;

    const config = {
      btnTexto: opciones.btnTexto || "Aceptar",
      btnIcono: opciones.btnIcono || "",
      btnClase: opciones.btnClase || "btn-primary",
      ocultarHeader: opciones.ocultarHeader || false,
      claseExtra: opciones.claseExtra || "",
      mostrarCancelar: opciones.mostrarCancelar || false,
      btnCancelarTexto: opciones.btnCancelarTexto || "Cancelar",
      btnCancelarClase: opciones.btnCancelarClase || "btn-cancel",
      onCancelar: opciones.onCancelar || null,
    };

    this._onCancelCallback = config.onCancelar;

    // 1. Clases de la ventana principal
    this.dialog.className = `custom-alert ${tipo} ${config.claseExtra}`.trim();

    // 2. Cabecera (Título e Icono)
    if (config.ocultarHeader) {
      if (this.title) this.title.style.display = "none";
      if (this.icon) this.icon.style.display = "none";
    } else {
      if (this.title) {
        this.title.style.display = "block";
        this.title.textContent = titulo;
      }

      const iconos = {
        success: `<svg class="svg-icon successAlert" viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#16a34a" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>`,
        error: `<svg class="svg-icon error" viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#dc2626" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>`,
        warning: `<svg class="svg-icon warning" viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#d97706" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>`,
        info: `<svg class="svg-icon info" viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#0284c7" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>`,
      };

      if (this.icon) {
        this.icon.innerHTML = iconos[tipo] || "";
        this.icon.style.display =
          tipo === "none" || !iconos[tipo] ? "none" : "block";
      }
    }

    // 3. Contenido
    if (this.body) {
      this.body.innerHTML = "";
      if (contenido instanceof HTMLElement) {
        this.body.appendChild(contenido);
      } else {
        this.body.innerHTML = contenido;
      }
    }

    // 4. Construcción dinámica de la botonera
    if (this.actions) {
      this.actions.innerHTML = "";

      // Botón Cancelar (Secundario)
      if (config.mostrarCancelar) {
        const btnCancel = document.createElement("button");
        btnCancel.type = "button";
        btnCancel.className = `alert-btn ${config.btnCancelarClase}`.trim();
        btnCancel.textContent = config.btnCancelarTexto;

        btnCancel.addEventListener("click", () => {
          this.dialog.close();
          if (typeof config.onCancelar === "function") {
            config.onCancelar();
          }
        });

        this.actions.appendChild(btnCancel);
      }

      // Botón Aceptar (Principal)
      const btnConfirm = document.createElement("button");
      btnConfirm.type = "button";
      btnConfirm.className = `alert-btn ${config.btnClase}`.trim();
      btnConfirm.innerHTML =
        `${config.btnIcono} <span>${config.btnTexto}</span>`.trim();

      btnConfirm.addEventListener("click", () => {
        this.dialog.close();
        if (typeof accion === "function") {
          accion();
        } else if (typeof accion === "string" && accion.trim() !== "") {
          window.location.href = accion;
        }
      });

      this.actions.appendChild(btnConfirm);
    }

    // 5. Apertura del diálogo
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

  // 1. ACTIVAR ESTADO DE CARGA
  const elementoLoader = idLoader ? document.getElementById(idLoader) : null;
  if (elementoLoader) {
    elementoLoader.classList.remove("cargando-oculto");
    elementoLoader.classList.add("cargando-visible");
  }

  try {
    TableCargando();

    const respuesta = await realizarPeticion(urlDestino, "POST", datosAEnviar);

    if (respuesta.status === "success") {
      if (typeof callbackExito === "function") {
        // CORRECCIÓN AQUÍ: Pasamos el objeto de respuesta completo
        // para conservar la propiedad "total" de la paginación.
        callbackExito(respuesta);
      }
    } else {
      console.warn("[Consulta SVPMPH]:", respuesta.message || "Sin datos.");
      if (typeof callbackExito === "function")
        callbackExito({ data: [], total: 0 });
    }
  } catch (error) {
    console.error("[Error en Consulta]:", error);
    if (typeof AlertApp !== "undefined") {
      AlertApp.show(
        "Error de Red",
        error.message || "No se pudo consultar el servidor.",
        "error",
      );
    }
  } finally {
    // 2. DESACTIVAR ESTADO DE CARGA
    if (elementoLoader) {
      elementoLoader.classList.remove("cargando-visible");
      elementoLoader.classList.add("cargando-oculto");
    }
  }
}

/**
 * Realiza una búsqueda rápida hacia el servidor pasando directamente un valor.
 *
 * @param {string|number} valor Valor a buscar o enviar al servidor.
 * @param {string} urlDestino URL del endpoint a consultar.
 * @param {Function} callbackProcesar Función que procesará la respuesta del servidor.
 * @param {Object} [opciones={}] Opciones de configuración (nombreParametro, tiempoDebounce).
 */
function busquedaRapida(valor, urlDestino, callbackProcesar, opciones = {}) {
  // Configuraciones por defecto
  const tiempoDebounce = opciones.tiempoDebounce || 0; // 0 para ejecución inmediata por defecto
  const nombreParametro = opciones.nombreParametro || "buscar";

  // Formateamos el valor ingresado
  const valorLimpio = String(valor ?? "").trim();

  const payload = {};
  payload[nombreParametro] = valorLimpio;

  const ejecutarConsulta = () => {
    consultarServidor(urlDestino, payload, (datos) => {
      if (typeof callbackProcesar === "function") {
        callbackProcesar(datos, valorLimpio);
      }
    });
  };

  // Si se especifica un debounce se retarda la petición, de lo contrario dispara inmediatamente
  if (tiempoDebounce > 0) {
    if (window._temporizadorBusquedaRapida) {
      clearTimeout(window._temporizadorBusquedaRapida);
    }
    window._temporizadorBusquedaRapida = setTimeout(
      ejecutarConsulta,
      tiempoDebounce,
    );
  } else {
    ejecutarConsulta();
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

  if (!elemento) {
    console.error(
      `[Arquitectura SVPMPH]: El elemento ID "${idElemento}" no existe.`,
    );
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

      const payload = {};
      payload[nombreParametro] = valor;

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
/**
 * Vincula un grupo de controles a una consulta en tiempo real y ejecuta la carga inicial.
 *
 * @param {Array<string>} idsControles - Arreglo de IDs de los elementos HTML.
 * @param {string} urlDestino - Ruta del controlador PHP.
 * @param {Function} callbackProcesar - Callback que recibe los datos para actualizar la tabla.
 * @param {Object} [opciones={}] - Opciones adicionales (tiempoDebounce, ejecutarAlInicio).
 */
function configurarTablaDinamica(
  idsControles,
  urlDestino,
  callbackProcesar,
  opciones = {},
) {
  const controles = [];
  const tiempoDebounce = opciones.tiempoDebounce || 300;
  const ejecutarAlInicio = opciones.ejecutarAlInicio !== false; // Por defecto es true
  let temporizador = null;

  // 1. Validar y recolectar los elementos del DOM
  idsControles.forEach((id) => {
    const elemento = document.getElementById(id);
    if (!elemento) {
      console.warn(
        `[Arquitectura SVPMPH]: El control ID "${id}" no existe en la vista.`,
      );
    } else {
      controles.push(elemento);
    }
  });

  if (controles.length === 0) {
    console.error(
      `[Arquitectura SVPMPH]: No se encontraron controles válidos para la tabla.`,
    );
    return;
  }

  // 2. Función interna que captura el estado actual de TODOS los controles
  const ejecutarConsulta = () => {
    const payload = {};

    controles.forEach((el) => {
      // Usamos el 'name' o en su defecto el 'id'
      const clave = el.name || el.id;
      payload[clave] = el.value ? el.value.trim() : "";
    });

    // 3. Enviar la consulta al backend
    consultarServidor(urlDestino, payload, (respuesta) => {
      if (typeof callbackProcesar === "function") {
        // Garantizamos pasar la respuesta limpia o completa según el contrato de la API
        callbackProcesar(respuesta, payload);
      }
    });
  };

  // 4. Asignar los eventos de escucha a cada control
  controles.forEach((el) => {
    const tipoEvento =
      el.tagName === "SELECT" || el.type === "number" || el.type === "checkbox"
        ? "change"
        : "input";

    el.addEventListener(tipoEvento, function () {
      clearTimeout(temporizador);

      temporizador = setTimeout(() => {
        ejecutarConsulta();
      }, tiempoDebounce);
    });
  });

  // 5. CORRECCIÓN CLAVE: Disparar la primera consulta automáticamente al inicializar
  if (ejecutarAlInicio) {
    ejecutarConsulta();
  }
}
