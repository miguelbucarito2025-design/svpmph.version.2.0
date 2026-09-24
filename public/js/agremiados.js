/**
 * Módulo para la gestión de Agremiados y Verificación Masiva de Expediente Digital
 * Control de Interacción: Clic = Seleccionar / Doble Clic = Abrir o Cerrar Carpeta
 */

let usuarioExpedienteAbiertoId = null;

// Colas temporales para la verificación en lote
const loteVerificar = new Set();
const lotePendiente = new Set();
let temporizadorEnvioAut = null;

/**
 * Cliente HTTP Asíncrono Reutilizable
 */
async function realizarPeticion(
  url,
  metodo = "GET",
  datos = null,
  enviarComoJson = false,
) {
  const tokenCSRF =
    document
      .querySelector('meta[name="csrf-token"]')
      ?.getAttribute("content") || "";

  const opciones = {
    method: metodo.toUpperCase(),
    headers: {
      "X-Requested-With": "XMLHttpRequest",
      "X-CSRF-TOKEN": tokenCSRF,
    },
  };

  if (
    (opciones.method === "POST" ||
      opciones.method === "PUT" ||
      opciones.method === "DELETE") &&
    datos
  ) {
    if (enviarComoJson) {
      opciones.headers["Content-Type"] = "application/json";
      opciones.body = JSON.stringify(datos);
    } else if (datos instanceof FormData) {
      opciones.body = datos;
    } else {
      const formularioVirtual = new FormData();
      for (const llave in datos) {
        formularioVirtual.append(llave, datos[llave]);
      }
      opciones.body = formularioVirtual;
    }
  }

  try {
    const respuesta = await fetch(url, opciones);

    if (!respuesta.ok) {
      try {
        const errorJson = await respuesta.json();
        const miError = new Error(
          errorJson.message || errorJson.mensaje || "Error en el proceso.",
        );
        miError.status = respuesta.status;
        throw miError;
      } catch (jsonError) {
        if (jsonError instanceof SyntaxError) {
          const errorCritico = new Error(
            `Error crítico en el backend (Código ${respuesta.status})`,
          );
          errorCritico.status = respuesta.status;
          throw errorCritico;
        }
        throw jsonError;
      }
    }

    return await respuesta.json();
  } catch (error) {
    throw error;
  }
}

/**
 * Función auxiliar para recargar la lista de agremiados activando la tabla dinámica
 */
function recargarListaAgremiados() {
  const inputOffset = document.getElementById("offset");
  if (inputOffset) {
    inputOffset.dispatchEvent(new Event("input"));
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const idsControles = [
    "limit",
    "intput-buscar",
    "offset",
    "promocion_id",
    "mencion_id",
    "estado",
  ];

  const urlPaginacion = "gremio/obtener/agremiados";

  configurarTablaDinamica(
    idsControles,
    urlPaginacion,
    (respuesta) => {
      const listaAgremiados = respuesta?.data || [];
      const totalRegistros =
        typeof respuesta?.total === "number"
          ? respuesta.total
          : listaAgremiados.length;

      const inputOffset = document.getElementById("offset");
      const inputLimit = document.getElementById("limit");

      const offsetSQL = parseInt(inputOffset?.value) || 0;
      const limite = parseInt(inputLimit?.value) || 10;
      const paginaActual = Math.floor(offsetSQL / limite) + 1;

      renderizarTarjetasAgremiados(listaAgremiados);
      actualizarPaginacion(totalRegistros, paginaActual, limite);
    },
    { tiempoDebounce: 300 },
  );

  // Botón manual de guardar lote
  const btnProcesarLote = document.getElementById("btnProcesarLote");
  if (btnProcesarLote) {
    btnProcesarLote.addEventListener("click", () => enviarLoteVerificacion());
  }

  // Evento para el botón Volver (Cierra expediente y refresca lista)
  const btnVolver = document.getElementById("volver");
  if (btnVolver) {
    btnVolver.addEventListener("click", async () => {
      if (loteVerificar.size > 0 || lotePendiente.size > 0) {
        await enviarLoteVerificacion();
      }

      const seccionExpediente = document.getElementById(
        "seccionExpedienteArchivos",
      );
      const seccionUser = document.getElementById("section-user");

      if (seccionExpediente) seccionExpediente.classList.add("is-hidden");
      if (seccionUser) seccionUser.classList.remove("is-hidden");

      usuarioExpedienteAbiertoId = null;

      // Actualizar los datos de los agremiados para reflejar cambios en 'estado'
      recargarListaAgremiados();
    });
  }
});

/**
 * Renderiza o actualiza las tarjetas de agremiados de forma reactiva (sin recargar imágenes existentes)
 */
function renderizarTarjetasAgremiados(datos) {
  const contenedorGrid = document.getElementById("contenedorOfertasCards");
  if (!contenedorGrid) return;

  if (!Array.isArray(datos) || datos.length === 0) {
    contenedorGrid.innerHTML = `
      <div class="empty-state text-center py-4 text-muted">
        No se encontraron agremiados registrados.
      </div>`;
    return;
  }

  // Limpiamos el mensaje de "no hay datos" si existía
  const emptyState = contenedorGrid.querySelector(".empty-state");
  if (emptyState) emptyState.remove();

  // Guardamos un mapa de las tarjetas que ya están renderizadas físicamente en el DOM
  const tarjetasExistentes = new Map();
  contenedorGrid.querySelectorAll(".card-agremiado").forEach((card) => {
    const id = card.getAttribute("data-id");
    if (id) tarjetasExistentes.set(id, card);
  });

  const idsProcesados = new Set();

  datos.forEach((item) => {
    const idCuenta = String(item.id_cuenta);
    idsProcesados.add(idCuenta);

    const nombreCompleto = `${item.nombre || ""} ${item.apellido || ""}`.trim();
    const cedula = item.id_cedula
      ? `V-${new Intl.NumberFormat("es-VE").format(item.id_cedula)}`
      : "Sin Cédula";
    const esActivo = item.estado == 1;

    // CASO A: La tarjeta YA EXISTE en pantalla -> Solo actualizamos el estado/badge sin tocar el <img>
    if (tarjetasExistentes.has(idCuenta)) {
      const cardExistente = tarjetasExistentes.get(idCuenta);

      // Actualizamos solo el texto e icono del badge de estado
      const badgeEstado = cardExistente.querySelector(".badge-estado");
      if (badgeEstado) {
        badgeEstado.className = `badge badge-estado ${esActivo ? "badge-success" : "badge-warning"}`;
        badgeEstado.textContent = esActivo ? "Activo" : "No Activo";
      }

      // Actualizamos texto de datos por si cambiaron
      const elNombre = cardExistente.querySelector(".card-title");
      if (elNombre) elNombre.textContent = nombreCompleto || "Agremiado";

      return; // Fin del ciclo para este elemento, ¡cero petición HTTP de imagen!
    }

    // CASO B: La tarjeta NO existe -> La creamos de cero
    const card = document.createElement("div");
    card.className = "card-agremiado";
    card.setAttribute("data-id", idCuenta);

    const avatarImg =
      item.foto_url && item.foto_url.trim() !== ""
        ? item.foto_url
        : "/public/assets/img/avatar-default.png";

    card.innerHTML = `
      <div class="container-card-agremiado-img">
        <img 
          src="${escaparHTML(avatarImg)}" 
          alt="Foto de ${escaparHTML(nombreCompleto)}" 
          loading="lazy"
          onerror="this.onerror=null; this.src='/public/assets/img/avatar-default.png';"
        >
      </div>

      <div class="card-agremiado-body">
        <h3 class="card-title">${escaparHTML(nombreCompleto || "Agremiado")}</h3>
        <span class="badge-cedula">${cedula}</span>
        <br>
        <span class="badge ">${item.codigo || "S/C"}</span>
        <br>
        <span class="badge badge-estado ${esActivo ? "badge-success" : "badge-danger"}">
          ${
            esActivo
              ? `<svg class="icono-outline">
                  <use href="#icono-estatus" />
                </svg> Verificado`
              : ` <svg class="icono-outline">
                  <use href="#icono-no-verificado" />
                </svg>  No Verificado `
          }
          
        </span>
      </div>
    `;

    contenedorGrid.appendChild(card);
  });

  // CASO C: Eliminamos del DOM las tarjetas que ya no vengan en la paginación/filtro actual
  tarjetasExistentes.forEach((card, id) => {
    if (!idsProcesados.has(id)) {
      card.remove();
    }
  });

  vincularEventosCarpetas();
}

/**
 * Manejo de eventos en las tarjetas de agremiados
 */
function vincularEventosCarpetas() {
  const cards = document.querySelectorAll(
    "#contenedorOfertasCards .card-agremiado",
  );
  const seccionExpediente = document.getElementById(
    "seccionExpedienteArchivos",
  );
  const seccionUser = document.getElementById("section-user");

  cards.forEach((card) => {
    const agremiadoId = card.getAttribute("data-id");

    card.onclick = (e) => {
      e.stopPropagation();
      cards.forEach((c) => c.classList.remove("card-gremio-seleccionada"));
      card.classList.add("card-gremio-seleccionada");
    };

    card.ondblclick = async (e) => {
      e.preventDefault();
      e.stopPropagation();

      if (!agremiadoId || !seccionExpediente || !seccionUser) return;

      // Cierre de carpeta si ya está abierta la del mismo usuario
      if (
        usuarioExpedienteAbiertoId === agremiadoId &&
        !seccionExpediente.classList.contains("is-hidden")
      ) {
        if (loteVerificar.size > 0 || lotePendiente.size > 0) {
          await enviarLoteVerificacion();
        }
        seccionExpediente.classList.add("is-hidden");
        seccionUser.classList.remove("is-hidden");
        usuarioExpedienteAbiertoId = null;

        // Recargar la lista de agremiados
        recargarListaAgremiados();
        return;
      }

      // Apertura de carpeta
      seccionUser.classList.add("is-hidden");
      seccionExpediente.classList.remove("is-hidden");
      usuarioExpedienteAbiertoId = agremiadoId;

      seccionExpediente.scrollIntoView({
        behavior: "smooth",
        block: "nearest",
      });

      await cargarArchivosAgremiado(agremiadoId);
    };
  });
}

/**
 * Deseleccionar tarjetas si se hace clic afuera
 */
document.addEventListener("click", (e) => {
  if (!e.target.closest(".card-agremiado")) {
    document
      .querySelectorAll("#contenedorOfertasCards .card-agremiado")
      .forEach((card) => {
        card.classList.remove("card-gremio-seleccionada");
      });
  }
});

/**
 * Carga de Expediente Digital
 */
async function cargarArchivosAgremiado(agremiadoId) {
  const contenedorArchivos = document.querySelector("#contenedor-archivos");
  if (!contenedorArchivos) return;

  // Auto-guardar lote pendiente si existe
  if (loteVerificar.size > 0 || lotePendiente.size > 0) {
    await enviarLoteVerificacion();
  }

  loteVerificar.clear();
  lotePendiente.clear();
  actualizarPanelLote();

  contenedorArchivos.innerHTML = `
    <div class="archivo-vacio-text">
        <p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Cargando expediente digital...</p>
    </div>`;

  try {
    const respuesta = await realizarPeticion(`archivo/user/obtener`, "POST", {
      id: agremiadoId,
    });

    if (!respuesta || respuesta.status !== "success") {
      contenedorArchivos.innerHTML = `
        <div class="archivo-vacio-text">
            <p class="text-muted">No se pudo obtener el expediente digital.</p>
        </div>`;
      return;
    }

    const listaArchivos = respuesta.data || [];

    if (!Array.isArray(listaArchivos) || listaArchivos.length === 0) {
      contenedorArchivos.innerHTML = `
        <div class="archivo-vacio-text">
            <p class="text-muted">El agremiado no posee documentos consignados.</p>
        </div>`;
      return;
    }

    let htmlArchivos = ``;
    listaArchivos.forEach((item) => {
      const esVerificado = Number(item.verificado) === 1;
      const tieneUrl = Boolean(item.url);

      const svgEstado = esVerificado
        ? `<svg class="icon-estado icon-verificado" title="Documento Verificado"><use href="#icon-confirmar" /></svg>`
        : `<svg class="icon-estado icon-pendiente" title="Pendiente de Verificación"><use href="#icon-pendiente" /></svg>`;

      htmlArchivos += `
        <div class="tarjeta-archivo ${
          esVerificado ? "estado-verificado" : "estado-pendiente"
        }" 
             data-id="${item.id}" 
             data-verificado-original="${item.verificado}">
            <div class="archivo-icono-wrapper">
                <svg class="icon-documento"><use href="#icon-archivo" /></svg>
                <span class="badge-verificacion">${svgEstado}</span>
            </div>
            <div class="archivo-info">
                <span class="archivo-nombre" title="${item.archivo.trim()}">${item.archivo.trim()}</span>
                <span class="archivo-estado-texto">${
                  esVerificado ? "Verificado" : "En revisión"
                }</span>
            </div>
            <div class="archivo-accion">
            ${
              tieneUrl
                ? `<a href="${
                    item.url
                  }" target="_blank" rel="noopener noreferrer" class="btn-ver-archivo" title="Ver Documento" onclick="event.stopPropagation();">
                     <button type="button" class="success btn-ver-archivo max-width-250">
                        <svg class="icono-outline"><use href="#icon-confirmar" /></svg>
                        Ver Archivo
                     </button>
                   </a>`
                : `<span class="badge-subir">Sin Cargar</span>`
            }
            </div>
        </div>`;
    });

    contenedorArchivos.innerHTML = htmlArchivos;
    vincularEventosSombreadoLote();
  } catch (error) {
    contenedorArchivos.innerHTML = `
      <div class="archivo-vacio-text">
          <p class="text-muted">Error de conexión al obtener el expediente.</p>
      </div>`;
  }
}

/**
 * Evento de sombreado y clasificación de archivos
 */
function vincularEventosSombreadoLote() {
  const tarjetas = document.querySelectorAll(
    "#contenedor-archivos .tarjeta-archivo",
  );

  tarjetas.forEach((tarjeta) => {
    tarjeta.onclick = (e) => {
      if (e.target.closest(".btn-ver-archivo")) return;

      const id = tarjeta.dataset.id;
      const estadoOriginal = Number(tarjeta.dataset.verificadoOriginal);

      if (estadoOriginal === 0) {
        if (loteVerificar.has(id)) {
          loteVerificar.delete(id);
          tarjeta.classList.remove("archivo-sombreado-seleccionado");
        } else {
          loteVerificar.add(id);
          tarjeta.classList.add("archivo-sombreado-seleccionado");
        }
      } else {
        if (lotePendiente.has(id)) {
          lotePendiente.delete(id);
          tarjeta.classList.remove("archivo-sombreado-pendiente");
        } else {
          lotePendiente.add(id);
          tarjeta.classList.add("archivo-sombreado-pendiente");
        }
      }

      actualizarPanelLote();
      reiniciarTimerEnvioAutomatico();
    };
  });
}

function actualizarPanelLote() {
  const panel = document.getElementById("panelLoteVerificacion");
  const spanText = document.getElementById("textoContadorLote");
  const totalCambios = loteVerificar.size + lotePendiente.size;

  if (!panel) return;

  if (totalCambios > 0) {
    panel.classList.remove("is-hidden");
    if (spanText) {
      spanText.textContent = `Cambios pendientes: ${totalCambios} (${loteVerificar.size} a verificado, ${lotePendiente.size} a revisión)`;
    }
  } else {
    panel.classList.add("is-hidden");
  }
}

function reiniciarTimerEnvioAutomatico() {
  if (temporizadorEnvioAut) clearTimeout(temporizadorEnvioAut);

  if (loteVerificar.size > 0 || lotePendiente.size > 0) {
    temporizadorEnvioAut = setTimeout(() => {
      enviarLoteVerificacion();
    }, 7000);
  }
}

/**
 * Envía la cola de verificación en una sola transacción, notifica al usuario y refresca la lista
 */
async function enviarLoteVerificacion() {
  if (temporizadorEnvioAut) clearTimeout(temporizadorEnvioAut);

  if (loteVerificar.size === 0 && lotePendiente.size === 0) return;

  const cantidadVerificados = loteVerificar.size;
  const cantidadPendientes = lotePendiente.size;

  const datosBatch = {
    cuenta_id: usuarioExpedienteAbiertoId,
    verificar_ids: Array.from(loteVerificar),
    pendiente_ids: Array.from(lotePendiente),
  };

  loteVerificar.clear();
  lotePendiente.clear();
  actualizarPanelLote();

  try {
    const respuesta = await realizarPeticion(
      `archivo/verificar/Lote`,
      "POST",
      datosBatch,
      true,
    );

    if (respuesta && respuesta.status === "success") {
      // Notificación explícita al usuario de que el lote fue guardado
      AlertApp.show(
        "Expediente Actualizado",
        `Se han procesado los documentos exitosamente (${cantidadVerificados} aprobados, ${cantidadPendientes} marcados en revisión).`,
        "success",
      );

      if (usuarioExpedienteAbiertoId) {
        await cargarArchivosAgremiado(usuarioExpedienteAbiertoId);
      }
      // Actualizar también la lista general por si cambió el 'estado' del gremio
      recargarListaAgremiados();
    } else {
      AlertApp.show(
        "Error de Guardado",
        respuesta?.message || "No se pudieron actualizar los documentos.",
        "error",
      );
    }
  } catch (err) {
    console.error("Error al procesar el lote de verificación:", err);
    AlertApp.show(
      "Error de Conexión",
      "Ocurrió un fallo al intentar guardar la verificación masiva.",
      "error",
    );
  }
}

/**
 * Paginador
 */
function actualizarPaginacion(
  totalRegistros = 0,
  paginaActual = 1,
  limitePorPagina = 10,
) {
  const textoMetrica = document.getElementById("textoMetricaPaginacion");
  const btnRetroceder = document.getElementById("btnRetroceder");
  const btnAvanzar = document.getElementById("btnAvanzar");
  const contenedorPaginas = document.getElementById(
    "contenedorPaginasNumeradas",
  );
  const inputOffset = document.getElementById("offset");

  if (!textoMetrica) return;

  const total = Number(totalRegistros) || 0;
  const pagActual = Number(paginaActual) || 1;
  const limite = Number(limitePorPagina) || 10;
  const totalPaginas = Math.ceil(total / limite) || 1;

  textoMetrica.textContent = `Total Guardados ${total}`;

  const irAPagina = (destinoPagina) => {
    if (!inputOffset) return;
    const offsetSQL = (Number(destinoPagina) - 1) * limite;
    inputOffset.value = offsetSQL;
    inputOffset.dispatchEvent(new Event("input"));
  };

  if (btnRetroceder) {
    btnRetroceder.style.display = pagActual <= 1 ? "none" : "";
    btnRetroceder.disabled = pagActual <= 1;
    btnRetroceder.onclick = (e) => {
      e.preventDefault();
      irAPagina(pagActual - 1);
    };
  }

  if (btnAvanzar) {
    btnAvanzar.style.display = pagActual >= totalPaginas ? "none" : "";
    btnAvanzar.disabled = pagActual >= totalPaginas;
    btnAvanzar.onclick = (e) => {
      e.preventDefault();
      irAPagina(pagActual + 1);
    };
  }

  if (contenedorPaginas) {
    contenedorPaginas.innerHTML = "";
    for (let i = 1; i <= totalPaginas; i++) {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = `pag ${i === pagActual ? "pagActual" : ""}`;
      btn.textContent = i;
      if (i !== pagActual) {
        btn.onclick = (e) => {
          e.preventDefault();
          irAPagina(i);
        };
      }
      contenedorPaginas.appendChild(btn);
    }
  }
}

function escaparHTML(cadena) {
  if (!cadena) return "";
  return String(cadena)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

/**
 * Panel de Filtros
 */
document.addEventListener("DOMContentLoaded", () => {
  const btnToggle = document.getElementById("btnToggleHerramientas");
  const panelTools = document.getElementById("panelHerramientas");

  if (!btnToggle || !panelTools) return;

  const cerrarPanel = () => {
    panelTools.classList.add("is-hidden");
    btnToggle.classList.remove("active");
    btnToggle.setAttribute("aria-expanded", "false");
  };

  btnToggle.addEventListener("click", (e) => {
    e.stopPropagation();
    const estaOculto = panelTools.classList.toggle("is-hidden");
    btnToggle.classList.toggle("active", !estaOculto);
    btnToggle.setAttribute("aria-expanded", !estaOculto);
  });

  panelTools.addEventListener("click", (e) => {
    e.stopPropagation();
  });

  document.addEventListener("click", () => {
    if (!panelTools.classList.contains("is-hidden")) cerrarPanel();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && !panelTools.classList.contains("is-hidden"))
      cerrarPanel();
  });
});
