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
  const idsControles = ["limit", "offset"];
  const urlPaginacion = "cuotas/obtener/user/cuotas";

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
    const nombreCompleto = `${item.cuota || ""}`.trim();
    const precioUsd = Number(item.monto_usd) || 0;
    const precioBs = item.monto_bs || "0,00";
    const esActivo = estado(item.status);

    const card = document.createElement("div");
    card.className = "card-cuotas";
    card.setAttribute("data-pos", index);

    card.innerHTML = `
      
          ${esActivo}
          
       

      <div class="card-tramite-body">
        <h3 >${escaparHTML(nombreCompleto || "Trámite")}</h3>
        <span class="badge">
          Fecha de Corte: ${item.corte ?? "N/A"}
        </span><br> <span class="badge">
          Concepto: ${item.origen ?? "N/A"}
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
        .querySelectorAll("#contenedorOfertasCards .card-cuotas")
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
}

/**
 * Pinta los datos del trámite seleccionado usando el objeto capturado en memoria,
 * gestiona la vista previa de imágenes, el autorelleno de datos guardados y la reactividad.
 *
 * @param {Object} datos Objeto con los detalles de la cuota/trámite
 */
function pintarTarjetaDetalle(datos) {
  const contenedorArchivos = document.querySelector("#contenedor-form");
  if (!contenedorArchivos) return;

  // Evaluamos si el método guardado amerita ocultar campos de entrada digital
  const esPresencial =
    datos.metodo === "Efectivo" || datos.metodo === "Divisas";
  const tieneImagenGuardada = Boolean(datos.img);

  contenedorArchivos.innerHTML = `
    <form method="post" class="form-usuario" id="solicitar-from">
      <input type="hidden" value="${datos.id}" name="cuota_id">

      <div class="grid-form">
        <div class="campo-grupo">
          <label>Método de Pago*</label>
          <select id="metodo-pago" name="metodo">
            <option value="">Seleccione un método...</option>
            <option value="Pago Mobil" ${datos.metodo === "Pago Mobil" || datos.metodo === "Pago_Mobil" ? "selected" : ""}>Pago Móvil</option>
            <option value="Efectivo" ${datos.metodo === "Efectivo" ? "selected" : ""}>Efectivo Bs</option>
            <option value="Divisas" ${datos.metodo === "Divisas" ? "selected" : ""}>Divisas $</option>
            <option value="Transferencia" ${datos.metodo === "Transferencia" ? "selected" : ""}>Transferencia</option>
          </select>
        </div>

        <div class="campo-grupo" id="grupo-referencia" style="${esPresencial ? "display:none;" : ""}">
          <label>Referencia (últimos 6 números)*</label>
          <input type="number" id="input-referencia" name="referencia" value="${datos.referencia ?? ""}" ${esPresencial ? "" : "required"}>
        </div>

        <div class="campo-grupo">
          <label>Monto *</label>
          <input type="number" name="monto_bs" step="any" value="${datos.monto_pago_bs ?? datos.monto_bs_pagar}" required>
          ${datos.monto_bs_pagar ? `<small style="color: #666; font-size: 0.85em;">Monto Actual a Pagar: <b>${datos.monto_bs_pagar} Bs</b></small>` : ""}
        </div>

        <div class="campo-grupo campo-full">
          <label>Destinatario*</label>
          <select id="destinario_id" name="destinario_id" required>
            <option value="${datos.destinario_id ?? ""}">${datos.destinario ?? "Seleccione un destinatario..."}</option>
          </select>
          <!-- Contenedor donde se mostrarán los datos bancarios del destinatario -->
          <div id="info-destinatario" class="info-bancaria-box" style="margin-top: 8px; font-weight: bold;"></div>
        </div>

        <div class="campo-grupo campo-full" id="grupo-imagen" style="${esPresencial ? "display:none;" : ""}">
          <label>Capture ${tieneImagenGuardada ? "(Opcional si no desea cambiarlo)" : "*"}</label>
          <!-- Si ya hay imagen guardada, no exigimos 'required' obligatoriamente -->
          <input type="file" ${tieneImagenGuardada || esPresencial ? "" : "required"} name="img" id="input-imagen" class="input-preview" accept="image/*">
          
          <!-- Vista previa de la imagen cargada o previamente guardada -->
          <div style="margin-top: 10px;" class="contenedor-preview-unit">
            <img id="preview-flyer" src="${datos.img ?? ""}" alt="Vista previa de imagen" style="${tieneImagenGuardada ? "display:block;" : "display:none;"} object-fit:cover; border-radius:6px; margin: 0 auto; max-width: 200px;">
          </div>
        </div>
      </div>
    </form>

    <div class="container-button-anuncion">
      <button type="submit" class="success button-anuncio" form="solicitar-from">
        <svg class="icono-outline">
          <use href="#icon-confirmar" />
        </svg>
        Confirmar Solicitud
      </button>
      <button type="reset" class="limpiar button-anuncio" form="solicitar-from">
        <svg class="icono-outline">
          <use href="#icon-limpiar" />
        </svg>
        Limpiar
      </button>
    </div>
  `;

  // Inicialización de evento AJAX del formulario
  inicializarFormulario("solicitar-from", "pagos/guardar/user", (respuesta) => {
    AlertApp.show(
      "Transacción Exitosa. Espere a la confirmación de los encargados.",
      "",
      "success",
      () => {
        const seccionUser = document.getElementById(
          "seccionExpedienteArchivos",
        );
        if (seccionUser) seccionUser.classList.add("is-hidden");

        const seccionCuotas = document.getElementById("section-user");
        if (seccionCuotas) seccionCuotas.classList.remove("is-hidden");

        if (typeof recargarListaAgremiados === "function") {
          recargarListaAgremiados();
        }
      },
    );
  });

  // 1. CAPTURA DE ELEMENTOS DEL DOM
  const selectMetodoPago = document.querySelector("#metodo-pago");
  const grupoReferencia = document.querySelector("#grupo-referencia");
  const inputReferencia = document.querySelector("#input-referencia");

  const grupoImagen = document.querySelector("#grupo-imagen");
  const inputImagen = document.querySelector("#input-imagen");
  const imgPreview = document.querySelector("#preview-flyer");

  const selectDestinatario = document.querySelector("#destinario_id");
  const infoDestinatarioDiv = document.querySelector("#info-destinatario");

  let listaDestinatariosCache = [];

  // 2. LÓGICA DE MOSTRAR/OCULTAR REFERENCIA Y CAPTURE SEGÚN MÉTODO DE PAGO
  if (selectMetodoPago && grupoReferencia && grupoImagen) {
    selectMetodoPago.addEventListener("change", () => {
      const valorMetodo = selectMetodoPago.value;

      if (valorMetodo === "Efectivo" || valorMetodo === "Divisas") {
        grupoReferencia.style.display = "none";
        inputReferencia.removeAttribute("required");
        inputReferencia.value = "";

        grupoImagen.style.display = "none";
        inputImagen.removeAttribute("required");
        inputImagen.value = "";

        imgPreview.src = "";
        imgPreview.style.display = "none";
      } else {
        grupoReferencia.style.display = "block";
        inputReferencia.setAttribute("required", "required");

        grupoImagen.style.display = "block";
        if (!tieneImagenGuardada) {
          inputImagen.setAttribute("required", "required");
        }
      }
    });
  }

  // 3. VISTA PREVIA DINÁMICA DE LA IMAGEN EN FILE INPUT
  if (inputImagen && imgPreview) {
    inputImagen.addEventListener("change", (evento) => {
      const archivo = evento.target.files[0];

      if (archivo) {
        const lector = new FileReader();
        lector.onload = function (e) {
          imgPreview.src = e.target.result;
          imgPreview.style.display = "block";
        };
        lector.readAsDataURL(archivo);
      } else {
        if (!tieneImagenGuardada) {
          imgPreview.src = "";
          imgPreview.style.display = "none";
        }
      }
    });
  }

  // 4. MUESTRA DE DATOS DEL DESTINATARIO SELECCIONADO
  if (selectDestinatario) {
    selectDestinatario.addEventListener("change", (e) => {
      const idSeleccionado = e.target.value;
      const destinatarioEncontrado = listaDestinatariosCache.find(
        (item) => item.id === idSeleccionado,
      );

      if (destinatarioEncontrado && destinatarioEncontrado.datos) {
        infoDestinatarioDiv.innerHTML = `Datos de Pago: <span style="color: #007bff;">${destinatarioEncontrado.datos}</span>`;
      } else {
        infoDestinatarioDiv.innerHTML = "";
      }
    });
  }

  // 5. CONSULTA Y POBLADO DE DESTINATARIOS CON AUTOSELECCIÓN
  busquedaRapida(
    datos.id,
    "destinario/buscar",
    function (respuesta) {
      if (!selectDestinatario || !respuesta.data) return;

      listaDestinatariosCache = respuesta.data;

      respuesta.data.forEach((item) => {
        // Verificamos si el destinatario de la iteración coincide con el guardado en BD
        const esElSeleccionado = item.id === datos.destinario_id;

        selectDestinatario.innerHTML += `<option value="${item.id}" ${esElSeleccionado ? "selected" : ""}>${item.destinario}</option>`;

        // Si es el seleccionado previamente, mostramos sus datos bancarios de una vez
        if (esElSeleccionado && item.datos) {
          infoDestinatarioDiv.innerHTML = `Datos de Pago: <span style="color: #007bff;">${item.datos}</span>`;
        }
      });
    },
    {
      nombreParametro: "cuota_id",
    },
  );
}
/**
 * Deseleccionar tarjetas si se hace clic afuera
 */
document.addEventListener("click", (e) => {
  if (!e.target.closest(".card-cuotas")) {
    document
      .querySelectorAll("#contenedorOfertasCards .card-cuotas")
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

function estado(status) {
  let result;
  if (status === "Pendiente") {
    result = `<div class="container-card-cuota-svg  badge-danger">
                 
             <span >
              <svg class="icono-outline">
                  <use href="#icono-no-verificado" />
                </svg>  No Verificado </span>
      </div> `;
  } else if (status === "En Proceso") {
    result = `<div class="container-card-cuota-svg  badge-info">
                 
             <span > <svg class="icono-outline">
                  <use href="#icono-buscar" />
                </svg> En Espera </span>
      </div>`;
  } else {
    result = `<div class="container-card-cuota-svg  badge-success">
                 
             <span > <svg class="icono-outline">
                  <use href="#icono-estatus" />
                </svg> Verificado </span>
      </div>`;
  }

  return result;
}
