/**
 * Módulo para la gestión de Trámites y Solicitudes - SVPMPH
 * Control de Interacción: Acceso por índice numérico en memoria local
 * Autor: Gremio Dev
 * Fecha: 2026-09-29
 */

// Variable global en memoria para almacenar los trámites de la página actual
let listaTramitesMemoria = [];
let usuarioExpedienteAbiertoId = null;

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
 * Función auxiliar para recargar la lista activando la tabla dinámica
 */
function recargarListaAgremiados() {
  const inputOffset = document.getElementById("offset");
  if (inputOffset) {
    inputOffset.dispatchEvent(new Event("input"));
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const idsControles = ["limit", "intput-buscar", "offset"];
  const urlPaginacion = "tramites/solicitar/disponibles";

  configurarTablaDinamica(
    idsControles,
    urlPaginacion,
    (respuesta) => {
      // 1. Guardamos la lista en memoria
      listaTramitesMemoria = respuesta?.data || [];

      const totalRegistros =
        typeof respuesta?.total === "number"
          ? respuesta.total
          : listaTramitesMemoria.length;

      const inputOffset = document.getElementById("offset");
      const inputLimit = document.getElementById("limit");

      const offsetSQL = parseInt(inputOffset?.value) || 0;
      const limite = parseInt(inputLimit?.value) || 10;
      const paginaActual = Math.floor(offsetSQL / limite) + 1;

      // 2. Renderizamos usando la memoria
      renderizarTarjetasTramites(listaTramitesMemoria);
      actualizarPaginacion(totalRegistros, paginaActual, limite);
    },
    { tiempoDebounce: 300 },
  );

  // Evento para el botón Volver
  const btnVolver = document.getElementById("volver");
  if (btnVolver) {
    btnVolver.addEventListener("click", () => {
      const seccionExpediente = document.getElementById(
        "seccionExpedienteArchivos",
      );
      const seccionUser = document.getElementById("section-user");

      if (seccionExpediente) seccionExpediente.classList.add("is-hidden");
      if (seccionUser) seccionUser.classList.remove("is-hidden");

      usuarioExpedienteAbiertoId = null;
    });
  }
});

/**
 * Renderiza las tarjetas de trámites asignando un índice numérico limpio (0, 1, 2...)
 */
function renderizarTarjetasTramites(datos) {
  const contenedorGrid = document.getElementById("contenedorOfertasCards");
  if (!contenedorGrid) return;

  if (!Array.isArray(datos) || datos.length === 0) {
    contenedorGrid.innerHTML = `
      <div class="empty-state text-center py-4 text-muted">
        No se encontraron trámites registrados.
      </div>`;
    return;
  }

  contenedorGrid.innerHTML = "";

  datos.forEach((item, index) => {
    const nombreCompleto = `${item.tramite || item.nombre || ""}`.trim();
    const avatarImg = item.img || "/public/multimedia/icon/error.jpg";
    const precioUsd = Number(item.precio_usd) || 0;
    const precioBs = item.precio_bs || "0,00";

    const card = document.createElement("div");
    card.className = "card-tramites";
    card.setAttribute("data-pos", index);

    card.innerHTML = `
      <div class="container-card-tramite-img">
        <img 
          src="${escaparHTML(avatarImg)}" 
          alt="Foto de ${escaparHTML(nombreCompleto)}" 
          loading="lazy"
          onerror="this.onerror=null; this.src='/public/multimedia/icon/error.jpg';"
        >
      </div>

      <div class="card-tramite-body">
        <h3 class="card-title-tramite">${escaparHTML(nombreCompleto || "Trámite")}</h3>
       
        <span class="badge">
          Precio: ${precioUsd <= 0 ? "Gratis" : precioBs + " Bs."}
        </span><br>

        <span class="badge-success">
          <b>REF. $${precioUsd.toFixed(2)} BCV</b>
        </span>
      </div>
    `;

    // Clic para seleccionar o abrir usando el índice numérico
    card.onclick = (e) => {
      e.preventDefault();
      e.stopPropagation();

      document
        .querySelectorAll("#contenedorOfertasCards .card-tramites")
        .forEach((c) => c.classList.remove("card-tramite-seleccionada"));

      card.classList.add("card-tramite-seleccionada");
      abrirDetalleTramitePorIndice(index);
    };

    contenedorGrid.appendChild(card);
  });
}

/**
 * Obtiene el trámite de la memoria global por índice y muestra el detalle
 * ¡Cero peticiones HTTP adicionales al backend!
 */
function abrirDetalleTramitePorIndice(posicion) {
  const tramiteSeleccionado = listaTramitesMemoria[posicion];

  if (!tramiteSeleccionado) {
    console.error("No se encontraron los datos en la posición:", posicion);
    return;
  }

  const seccionExpediente = document.getElementById(
    "seccionExpedienteArchivos",
  );
  const seccionUser = document.getElementById("section-user");

  if (!seccionExpediente || !seccionUser) return;

  seccionUser.classList.add("is-hidden");
  seccionExpediente.classList.remove("is-hidden");

  seccionExpediente.scrollIntoView({
    behavior: "smooth",
    block: "nearest",
  });

  pintarTarjetaDetalle(tramiteSeleccionado);

  inicializarFormulario("solicitar", "solicitud/guardar", (res) => {
    AlertApp.show("Solicitud Creada Exitosamente", "", "success", () => {
      const seccionExpediente = document.getElementById(
        "seccionExpedienteArchivos",
      );
      const seccionUser = document.getElementById("section-user");

      if (seccionExpediente) seccionExpediente.classList.add("is-hidden");
      if (seccionUser) seccionUser.classList.remove("is-hidden");

      usuarioExpedienteAbiertoId = null;
    });
  });
}

/**
 * Pinta los datos del trámite seleccionado usando el objeto capturado en memoria
 */
function pintarTarjetaDetalle(tramite) {
  const contenedorArchivos = document.querySelector("#contenedor-archivos");
  if (!contenedorArchivos) return;

  contenedorArchivos.innerHTML = `
  <div class="container-solicitud" id="contenedor-archivos">

      <div class="container-card-tramite-img">
        <img 
          src="${escaparHTML(tramite.img)}" 
          alt="Foto de ${escaparHTML(tramite.tramite)}" 
          loading="lazy"
          onerror="this.onerror=null; this.src='/public/multimedia/icon/error.jpg';"
        >
      </div>

      <div class="card-tramite-body">
        <h3 class="card-title-tramite">${escaparHTML(tramite.tramite || "Trámite")}</h3>
        
        <hr>
       <p> ${escaparHTML(tramite.descripcion)}
       </p>
        <span class="badge badge-svg color-info">
        <svg class="icono-outline"><use href="#icono-dias-espera" /> </svg>Dias de espera estimados para la entrega: ${tramite.dias_entrega_estimados} Dias
        </span>
        <span class="badge-success">
          <b>REF. $${tramite.precio_usd} BCV</b>
        </span>
       <br>

         <span class="badge">
          Precio: ${tramite.precio_usd <= 0 ? "Gratis" : tramite.precio_bs + " Bs."}
        </span>
      </div>  
  </div>
  <br>
  <hr>
        <form method="post" class="form-usuario" id="solicitar">
        <input type="hidden" value="${tramite.id}" name="id">
        </form>
 <div class="container-button-anuncion">
            <button type="summit" class="success button-anuncio" form="solicitar">
                <svg class="icono-outline">
                    <use href="#icon-confirmar" />
                </svg>
                Confirmar Solicitud
            </button>
          
        </div>
    `;
}

/**
 * Deseleccionar tarjetas si se hace clic afuera
 */
document.addEventListener("click", (e) => {
  if (!e.target.closest(".card-tramites")) {
    document
      .querySelectorAll("#contenedorOfertasCards .card-tramites")
      .forEach((card) => {
        card.classList.remove("card-tramite-seleccionada");
      });
  }
});

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

  textoMetrica.textContent = `Total Mostrados ${total}`;

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
