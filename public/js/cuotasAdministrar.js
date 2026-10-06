/**
 * Módulo: Gestor de Expedientes y Cuotas - SVPMPH (Parte 1)
 * Descripción: Maneja la lista de usuarios, filtrado, paginación y la inicialización
 *              de ambas tablas dinámicas una sola vez en el DOM para evitar peticiones redundantes.
 * Autor: Gremio Dev & Aprendiz
 * Fecha: 2026-10-05
 */

// Memoria global de registros para la vista activa
let listaUsuariosMemoria = [];
let listaTramitesMemoria = [];

/* ==================================================================== */
/* 1. FUNCIONES AUXILIARES Y RENDERIZADO                                */
/* ==================================================================== */

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
  if (status === "Pendiente") {
    return `<div class="container-card-cuota-svg badge-warning">
              <span><svg class="icono-outline"><use href="#icono-no-verificado" /></svg> No Verificado</span>
            </div>`;
  } else if (status === "En Proceso") {
    return `<div class="container-card-cuota-svg badge-info">
              <span><svg class="icono-outline"><use href="#icono-buscar" /></svg> En Espera</span>
            </div>`;
  } else if (status === "Rechazado") {
    return `<div class="container-card-cuota-svg badge-danger">
              <span><svg class="icono-outline"><use href="#icono-cancelar" /></svg> ${status}</span>
            </div>`;
  } else {
    return `<div class="container-card-cuota-svg badge-success">
              <span><svg class="icono-outline"><use href="#icono-estatus" /></svg> Verificado</span>
            </div>`;
  }
}

/**
 * Renderiza las tarjetas de usuarios en la primera vista (#contenedorOfertasCards-2)
 * Alineando el badge SVG dentro del cuerpo del texto para no obstruir la foto.
 */
function renderizarTabla(datos) {
  const contenedorGrid = document.getElementById("contenedorOfertasCards-2");
  if (!contenedorGrid) return;

  contenedorGrid.innerHTML = "";

  if (!Array.isArray(datos) || datos.length === 0) {
    contenedorGrid.innerHTML = `
      <div class="empty-state text-center py-4 text-muted">
        No se encontraron Usuarios registrados.
      </div>`;
    return;
  }

  const fragmento = document.createDocumentFragment();

  datos.forEach((usuario) => {
    const card = document.createElement("div");
    card.className = "card-oferta card-rol card-agremiado-item";

    const imagen = usuario.foto || usuario.img;
    const nombreCompleto =
      `${usuario.nombre || ""} ${usuario.apellido || ""}`.trim();

    const cuotasEnProceso = Number(usuario.cuotas_en_proceso || 0);
    const cuotasPendientes = Number(usuario.cuotas_pendientes || 0);

    // Selección de la insignia SVG en versión Inline
    let badgeEstadoSVG = "";
    if (cuotasEnProceso > 0) {
      badgeEstadoSVG = `
        <div class="container-card-cuota-svg badge-info style-inline-badge">
          <span>
            <svg class="icono-outline"><use href="#icono-buscar" /></svg>
            En Espera (${cuotasEnProceso})
          </span>
        </div>`;
    } else if (cuotasPendientes > 0) {
      badgeEstadoSVG = `
        <div class="container-card-cuota-svg badge-danger style-inline-badge">
          <span>
            <svg class="icono-outline"><use href="#icono-no-verificado" /></svg>
            Sin Pagar (${cuotasPendientes})
          </span>
        </div>`;
    } else {
      badgeEstadoSVG = `
        <div class="container-card-cuota-svg badge-success style-inline-badge">
          <span>
            <svg class="icono-outline"><use href="#icono-estatus" /></svg>
            Al Día
          </span>
        </div>`;
    }

    card.innerHTML = `
      <div class="card-img">
        <img src="${escaparHTML(imagen)}" alt="Foto ${escaparHTML(nombreCompleto)}">
      </div>

      <div class="card-oferta-body">
        <div class="container-target-usuario">
          <div class="card-usuario-header flex-between align-center">
            <div class="card-usuario-main">
              <h4 class="card-usuario-nombre">${escaparHTML(nombreCompleto)}</h4>
              <span class="card-usuario-user">${escaparHTML(usuario.codigo_gremio || "SIN CÓDIGO")}</span>
            </div>
          </div>
          
          <div class="card-usuario-body margin-top-10">
            <p><strong>Cédula:</strong> V-${escaparHTML(usuario.id_cedula)}</p>
            <p><strong>Teléfono:</strong> ${escaparHTML(usuario.tlf || "N/A")}</p>
          </div>
          
          <div class="card-usuario-footer flex-between align-center margin-top-10">
            <small class="text-muted">Ingreso: ${escaparHTML(usuario.ingreso)}</small>
          </div>
            ${badgeEstadoSVG}

        </div>
      </div>
    `;

    // Clic en la tarjeta para pasar a las cuotas
    card.addEventListener("click", (e) => {
      e.preventDefault();
      seleccionarUsuarioParaPagos(usuario);
    });

    fragmento.appendChild(card);
  });

  contenedorGrid.appendChild(fragmento);
}
/**
 * PUENTE CONTROLADO: Pasa la ID al input #id-user y dispara UN SOLO evento 'input'
 */
function seleccionarUsuarioParaPagos(usuario) {
  const idCuenta = usuario.cuenta_id || usuario.id;
  if (!idCuenta) return;

  const containerUsuarios = document.getElementById("container-user-pagos");
  const containerHerramientas = document.getElementById(
    "container-herramienta-pagos",
  );
  const inputIdUser = document.getElementById("id-user");
  const inputCuotasOffset = document.getElementById("cuotas-offset");

  // A) Transición visual de contenedores principales
  if (containerUsuarios) containerUsuarios.style.display = "none";
  if (containerHerramientas) containerHerramientas.style.display = "block";

  const seccionExpediente = document.getElementById(
    "seccionExpedienteArchivos",
  );
  const seccionUser = document.getElementById("section-user");
  if (seccionExpediente) seccionExpediente.classList.add("is-hidden");
  if (seccionUser) seccionUser.classList.remove("is-hidden");

  // B) Si el usuario cambió, reseteamos offset y disparamos una sola petición
  if (inputIdUser) {
    if (inputCuotasOffset) inputCuotasOffset.value = 0;
    inputIdUser.value = idCuenta;
    inputIdUser.dispatchEvent(new Event("input", { bubbles: true }));
  }
}

/* ==================================================================== */
/* 2. INICIALIZACIÓN ÚNICA EN DOMCONTENTLOADED                          */
/* ==================================================================== */

document.addEventListener("DOMContentLoaded", () => {
  // TABLA 1: CONFIGURACIÓN DE USUARIOS (1 sola vez)
  configurarTablaDinamica(
    ["limit", "offset", "intput-buscar", "status_id"],
    "pagos/listar",
    (respuesta) => {
      listaUsuariosMemoria = respuesta?.data || [];
      const totalRegistros =
        typeof respuesta?.total === "number"
          ? respuesta.total
          : listaUsuariosMemoria.length;

      const inputOffset = document.getElementById("offset");
      const inputLimit = document.getElementById("limit");

      const offsetSQL = parseInt(inputOffset?.value) || 0;
      const limite = parseInt(inputLimit?.value) || 3;
      const paginaActual = Math.floor(offsetSQL / limite) + 1;

      renderizarTabla(listaUsuariosMemoria);
      actualizarPaginacionDos(totalRegistros, paginaActual, limite);
    },
    { tiempoDebounce: 300 },
  );

  // TABLA 2: CONFIGURACIÓN DE CUOTAS (1 sola vez, sin re-invocaciones)
  configurarTablaDinamica(
    ["cuotas-limit", "cuotas-offset", "id-user"],
    "cuotas/obtener/user/cuotas",
    (respuesta) => {
      listaTramitesMemoria = respuesta?.data || [];
      const totalRegistros =
        typeof respuesta?.total === "number"
          ? respuesta.total
          : listaTramitesMemoria.length;

      const inputOffset = document.getElementById("cuotas-offset");
      const inputLimit = document.getElementById("cuotas-limit");

      const offsetSQL = parseInt(inputOffset?.value) || 0;
      const limite = parseInt(inputLimit?.value) || 10;
      const paginaActual = Math.floor(offsetSQL / limite) + 1;

      renderizarTarjetasTramites(listaTramitesMemoria);
      actualizarPaginacionCuotas(totalRegistros, paginaActual, limite);
    },
    {
      tiempoDebounce: 300,
      ejecutarAlInicio: false, // <-- ¡CON ESTO NO SE DISPARA AL CARGAR LA PÁGINA!
    },
  );

  // CONTROL DEL PANEL DESPLEGABLE DE FILTROS
  const btnToggle = document.getElementById("btnToggleHerramientas");
  const panelTools = document.getElementById("panelHerramientas");

  if (btnToggle && panelTools) {
    btnToggle.addEventListener("click", (e) => {
      e.stopPropagation();
      const estaOculto = panelTools.classList.toggle("is-hidden");
      btnToggle.classList.toggle("active", !estaOculto);
      btnToggle.setAttribute("aria-expanded", !estaOculto);
    });

    panelTools.addEventListener("click", (e) => e.stopPropagation());

    document.addEventListener("click", () => {
      panelTools.classList.add("is-hidden");
      btnToggle.classList.remove("active");
      btnToggle.setAttribute("aria-expanded", "false");
    });
  }

  // Dentro de document.addEventListener("DOMContentLoaded", () => { ... })

  // Escuchador aislado para el selector de límite de la Tabla 2
  const selectCuotasLimit = document.getElementById("cuotas-limit");
  const inputCuotasOffset = document.getElementById("cuotas-offset");

  if (selectCuotasLimit && inputCuotasOffset) {
    selectCuotasLimit.addEventListener("change", () => {
      inputCuotasOffset.value = 0; // Resetea la página de cuotas a 1
      inputCuotasOffset.dispatchEvent(new Event("input", { bubbles: true }));
    });
  }

  // BOTÓN PARA REGRESAR A LA LISTA DE USUARIOS
  document
    .getElementById("btnVolverAUsuarios")
    ?.addEventListener("click", () => {
      document.getElementById("container-user-pagos").style.display = "block";
      document.getElementById("container-herramienta-pagos").style.display =
        "none";
    });
});

/**
 * Módulo: Gestor de Expedientes y Cuotas - SVPMPH (Parte 2)
 * Descripción: Maneja el renderizado de cuotas, apertura de formularios por índice
 *              de memoria, eventos reactivos de pago y controladores de paginación.
 * Autor: Gremio Dev & Aprendiz
 * Fecha: 2026-10-05
 */

/* ==================================================================== */
/* 3. RENDERIZADO DE CUOTAS Y DETALLE DE FORMULARIO                    */
/* ==================================================================== */

/**
 * Renderiza las tarjetas de trámites desde la memoria local
 */
function renderizarTarjetasTramites(datos) {
  const contenedorGrid = document.getElementById("contenedorOfertasCards");
  if (!contenedorGrid) return;

  if (!Array.isArray(datos) || datos.length === 0) {
    contenedorGrid.innerHTML = `
      <div class="empty-state text-center py-4 text-muted">
        No se encontraron trámites o cuotas registradas para este agremiado.
      </div>`;
    return;
  }

  contenedorGrid.innerHTML = "";

  datos.forEach((item, index) => {
    const nombreCompleto = `${item.cuota || ""}`.trim();
    const precioUsd = Number(item.monto_usd) || 0;
    const esActivo = estado(item.status);

    const card = document.createElement("div");
    card.className = "card-cuotas";
    card.setAttribute("data-pos", index);

    card.innerHTML = `
      ${esActivo}
      <div class="card-tramite-body">
        <h3>${escaparHTML(nombreCompleto || "Trámite")}</h3>
        <span class="badge">Fecha de Corte: ${item.corte ?? "N/A"}</span><br>
        <span class="badge">Concepto: ${item.origen ?? "N/A"}</span><br>
        <span class="badge-success">
          <b>REF. $${precioUsd.toFixed(2)} BCV</b>
        </span>
      </div>
    `;

    // Clic para seleccionar o abrir usando el índice numérico en memoria
    card.onclick = (e) => {
      e.preventDefault();
      e.stopPropagation();

      document
        .querySelectorAll("#contenedorOfertasCards .card-cuotas")
        .forEach((c) => {
          c.classList.remove("card-tramite-seleccionada");
        });

      card.classList.add("card-tramite-seleccionada");
      abrirDetalleTramitePorIndice(index);
    };

    contenedorGrid.appendChild(card);
  });
}

/**
 * Abre el detalle del trámite seleccionándolo directamente de la memoria por posición
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
 * Renderiza la ficha de auditoría y edición de cuotas según el JSON real.
 * Separa de forma estricta los datos históricos de pago (Solo Lectura)
 * de los datos contables de la cuota (Editables).
 */
function pintarTarjetaDetalle(datos) {
  const contenedorArchivos = document.querySelector("#contenedor-form");
  if (!contenedorArchivos) return;

  const tienePagoRegistrado = Boolean(
    datos.metodo && datos.metodo.trim() !== "" && datos.metodo !== "N/A",
  );

  const idCuentaUser = document.getElementById("id-user")?.value || "";

  // =========================================================================
  // CASO 1: LA CUOTA NO TIENE PAGO REGISTRADO (metodo === null)
  // =========================================================================
  if (!tienePagoRegistrado) {
    contenedorArchivos.innerHTML = `
      <div class="empty-state-pago" style="text-align: center; padding: 15px;margin: 40px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 8px;  ">
       
        <h3 style="color: #856404;margin: 10px; ">Solicitud sin Pago Registrado</h3>
        <p style="color: #856404; max-width: 550px; margin: 0 auto 20px auto; font-size: 0.95em;">
          El agremiado generó la solicitud para <b>"${escaparHTML(datos.cuota)}"</b> pero aún no ha adjuntado ni reportado ningún pago.
        </p>

      </div>

      <form method="post" class="form-usuario" id="solicitar-from">
      <input type="hidden" value="${datos.id}" name="id">

      <div class="grid-form">
        <div class="campo-grupo campo-full">
          <label>Nombre / Concepto de la Cuota*</label>
          <input type="text" name="cuota" value="${escaparHTML(datos.cuota || "")}" required placeholder="Ej. Solicitud para: certificación...">
        </div>

        <div class="campo-grupo">
          <label>Monto en $ USD*</label>
          <input type="number" step="0.01" name="monto" value="${datos.monto_usd ?? "0.00"}" required>
          ${datos.monto_bs_pagar ? `<small style="color: #666; font-size: 0.85em;">Equivalente Oficial BCV: <b>${datos.monto_bs_pagar} Bs</b></small>` : ""}
        </div>

        <div class="campo-grupo">
          <label>Fecha de Corte*</label>
          <input type="date" name="corte" value="${datos.corte ? datos.corte.split(" ")[0] : ""}" required>
        </div>

        <div class="campo-grupo">
          <label>Estatus de Verificación*</label>
          <select id="status-cuota" name="status" required>
            <option value="En Proceso" ${datos.status === "En Proceso" ? "selected" : ""}>En Proceso / Espera</option>
            <option value="Pagado" ${datos.status === "Pagado" || datos.status === "Aprobado" ? "selected" : ""}>Pagado / Aprobado</option>
            <option value="Pendiente" ${datos.status === "Pendiente" ? "selected" : ""}>Pendiente / No Verificado</option>
            <option value="Rechazado" ${datos.status === "Rechazado" ? "selected" : ""}>Rechazado</option>
          </select>
        </div>
      </div>
      <input type="hidden" name="cuenta_id" value="${idCuentaUser ?? ""}">

    </form>

    <!-- FORMULARIOS INVISIBLES DE ACCIÓN -->
    <form method="post" id="duplicar-cuota-from" style="display:none;">
      <input type="hidden" name="id" value="${datos.origen_id ?? ""}">
      <input type="hidden" name="cuenta_id" value="${idCuentaUser ?? ""}">
    </form>

    <form method="post" id="Eliminar-form" style="display:none;">
      <input type="hidden" name="id" value="${datos.id ?? ""}">
      <input type="hidden" name="pago_id" value="${datos.pago_id ?? ""}">
    </form>

    <!-- BOTONES PRINCIPALES DE ACCIÓN -->
    <div class="container-button-anuncion margin-top-15">
      <button type="submit" class="success button-anuncio" form="solicitar-from">
        <svg class="icono-outline"><use href="#icon-confirmar" /></svg>
        Guardar Cambios
      </button>

      <button type="submit" class="info button-anuncio" form="duplicar-cuota-from">
        <svg class="icono-outline"><use href="#icono-duplicar" /></svg>
        Duplicar Cuota
      </button>
    </div>

    <br><hr>

    <div class="container-button-anuncion margin-top-15">
      <button type="reset" class="limpiar button-anuncio" form="solicitar-from">
        <svg class="icono-outline"><use href="#icon-limpiar" /></svg>
        Restablecer
      </button>

      <button type="submit" class="limpiar button-anuncio btn-eliminar" form="Eliminar-form">
        <svg class="icono-outline"><use href="#icono-eliminar" /></svg>
        Eliminar Cuota
      </button> 
    </div>
    `;
    // HELPER REUTILIZABLE PARA CERRAR Y RECARGAR
    const recargarYVolver = () => {
      document
        .getElementById("seccionExpedienteArchivos")
        ?.classList.add("is-hidden");
      document.getElementById("section-user")?.classList.remove("is-hidden");
      document
        .getElementById("id-user")
        ?.dispatchEvent(new Event("input", { bubbles: true }));
    };
    // Escuchador AJAX directo para eliminar la solicitud vacía
    inicializarFormulario("Eliminar-form", "pagos/eliminar", () => {
      AlertApp.show("Solicitud eliminada con éxito", "", "success", () => {
        document
          .getElementById("seccionExpedienteArchivos")
          ?.classList.add("is-hidden");
        document.getElementById("section-user")?.classList.remove("is-hidden");
        document
          .getElementById("id-user")
          ?.dispatchEvent(new Event("input", { bubbles: true }));
      });
    });

    inicializarFormulario("duplicar-cuota-from", "solicitud/guardar", () => {
      AlertApp.show(
        "Cuota duplicada con éxito. Ya puede cambiarle el nombre y el precio.",
        "",
        "success",
        recargarYVolver,
      );
    });

    inicializarFormulario("solicitar-from", "pagos/verificar", () => {
      AlertApp.show("Actualizada con éxito", "", "success", recargarYVolver);
    });

    return; // CORTAMOS LA EJECUCIÓN: No permitimos ver ni editar nada más.
  }

  // =========================================================================
  // CASO 2: SÍ HAY PAGO REGISTRADO (Resumen de Pago + Formulario de Cuota)
  // =========================================================================
  const esPresencial =
    datos.metodo === "Efectivo" || datos.metodo === "Divisas";
  const tieneImagenGuardada = Boolean(datos.img);

  contenedorArchivos.innerHTML = `
    <!-- 1. RESUMEN ESTÁTICO DE LOS DATOS DE PAGO (SOLO LECTURA) -->
    <div class="card-resumen-pago" style="background: #f8f9fa; border-left: 4px solid #007bff; padding: 15px; margin: 40px; border-radius: 6px; box-shadow: 0 0 6px rgba(0, 0, 0, 0.22);">
      <h4 style="margin: 0 0 10px 0; color: #333; font-size: 0.9em; text-transform: uppercase; letter-spacing: 0.5px;">
        DATOS DE LA TRANSACCIÓN REPORTADA POR EL USUARIO
      </h4>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: center;">
        <div>
          <small class="text-muted" style="display:block;">Método de Pago:</small>
          <strong>${escaparHTML(datos.metodo)}</strong>
        </div>
        <div>
          <small class="text-muted" style="display:block;">Referencia:</small>
          <code>${escaparHTML(datos.referencia || "Presencial / Sin Ref")}</code>
        </div>
        <div>
          <small class="text-muted" style="display:block;">Monto Reportado en Bs:</small>
          <b style="color: #28a745; font-size: 1.05em;">${datos.monto_pago_bs ? Number(datos.monto_pago_bs).toFixed(2) : "0.00"} Bs</b>
        </div>
        <div>
          <small class="text-muted" style="display:block;">Destinatario:</small>
          <span>${escaparHTML(datos.destinario || "No asignado")}</span>
        </div>
        <div>
          <small class="text-muted" style="display:block;">Comprobante / Capture:</small>
          ${
            tieneImagenGuardada && !esPresencial
              ? `<a href="${datos.img}" target="_blank" class="color-info" style="font-weight: bold; text-decoration: underline;">
                   <svg class="icono-outline" style="width:14px; height:14px;"><use href="#icono-buscar" /></svg> Ver Capture Adjunto
                 </a>`
              : `<span class="badge badge-secondary">Pago Presencial</span>`
          }
        </div>
      </div>
    </div>

    <!-- 2. FORMULARIO DE EDICIÓN CONTABLE DE LA CUOTA (EDITABLE) -->
    <form method="post" class="form-usuario" id="solicitar-from">
      <input type="hidden" value="${datos.id}" name="id">
      <input type="hidden" name="cuenta_id" value="${idCuentaUser ?? ""}">

      <div class="grid-form">
        <div class="campo-grupo campo-full">
          <label>Nombre / Concepto de la Cuota*</label>
          <input type="text" name="cuota" value="${escaparHTML(datos.cuota || "")}" required placeholder="Ej. Solicitud para: certificación...">
        </div>

        <div class="campo-grupo">
          <label>Monto en $ USD*</label>
          <input type="number" step="0.01" name="monto" value="${datos.monto_usd ?? "0.00"}" required>
          ${datos.monto_bs_pagar ? `<small style="color: #666; font-size: 0.85em;">Equivalente Oficial BCV: <b>${datos.monto_bs_pagar} Bs</b></small>` : ""}
        </div>

        <div class="campo-grupo">
          <label>Fecha de Corte*</label>
          <input type="date" name="corte" value="${datos.corte ? datos.corte.split(" ")[0] : ""}" required>
        </div>

        <div class="campo-grupo">
          <label>Estatus de Verificación*</label>
          <select id="status-cuota" name="status" required>
            <option value="En Proceso" ${datos.status === "En Proceso" ? "selected" : ""}>En Proceso / Espera</option>
            <option value="Pagado" ${datos.status === "Pagado" || datos.status === "Aprobado" ? "selected" : ""}>Pagado / Aprobado</option>
            <option value="Pendiente" ${datos.status === "Pendiente" ? "selected" : ""}>Pendiente / No Verificado</option>
            <option value="Rechazado" ${datos.status === "Rechazado" ? "selected" : ""}>Rechazado</option>
          </select>
        </div>
      </div>
    </form>

    <!-- FORMULARIOS INVISIBLES DE ACCIÓN -->
    <form method="post" id="duplicar-cuota-from" style="display:none;">
      <input type="hidden" name="id" value="${datos.origen_id ?? ""}">
      <input type="hidden" name="cuenta_id" value="${idCuentaUser ?? ""}">
    </form>

    <form method="post" id="Eliminar-form" style="display:none;">
      <input type="hidden" name="id" value="${datos.id ?? ""}">
      <input type="hidden" name="pago_id" value="${datos.pago_id ?? ""}">
    </form>

    <!-- BOTONES PRINCIPALES DE ACCIÓN -->
    <div class="container-button-anuncion margin-top-15">
      <button type="submit" class="success button-anuncio" form="solicitar-from">
        <svg class="icono-outline"><use href="#icon-confirmar" /></svg>
        Guardar Cambios
      </button>

      <button type="submit" class="info button-anuncio" form="duplicar-cuota-from">
        <svg class="icono-outline"><use href="#icono-duplicar" /></svg>
        Duplicar Cuota
      </button>
    </div>

    <br><hr>

    <div class="container-button-anuncion margin-top-15">
      <button type="reset" class="limpiar button-anuncio" form="solicitar-from">
        <svg class="icono-outline"><use href="#icon-limpiar" /></svg>
        Restablecer
      </button>

      <button type="submit" class="limpiar button-anuncio btn-eliminar" form="Eliminar-form">
        <svg class="icono-outline"><use href="#icono-eliminar" /></svg>
        Eliminar Cuota
      </button> 
    </div>
  `;

  // HELPER REUTILIZABLE PARA CERRAR Y RECARGAR
  const recargarYVolver = () => {
    document
      .getElementById("seccionExpedienteArchivos")
      ?.classList.add("is-hidden");
    document.getElementById("section-user")?.classList.remove("is-hidden");
    document
      .getElementById("id-user")
      ?.dispatchEvent(new Event("input", { bubbles: true }));
  };

  inicializarFormulario("Eliminar-form", "pagos/eliminar", () => {
    AlertApp.show("Cuota eliminada con éxito", "", "success", recargarYVolver);
  });

  inicializarFormulario("duplicar-cuota-from", "solicitud/guardar", () => {
    AlertApp.show(
      "Cuota duplicada con éxito. Ya puede cambiarle el nombre y el precio.",
      "",
      "success",
      recargarYVolver,
    );
  });

  inicializarFormulario("solicitar-from", "pagos/verificar", () => {
    AlertApp.show("Actualizada con éxito", "", "success", recargarYVolver);
  });
}

// Botón para volver del formulario a la lista de cuotas
document.getElementById("volver")?.addEventListener("click", () => {
  const seccionExpediente = document.getElementById(
    "seccionExpedienteArchivos",
  );
  const seccionUser = document.getElementById("section-user");

  if (seccionExpediente) seccionExpediente.classList.add("is-hidden");
  if (seccionUser) seccionUser.classList.remove("is-hidden");
});

/* ==================================================================== */
/* 4. PAGINADORES AISLADOS PARA CADA VISTA                             */
/* ==================================================================== */

/**
 * Paginador para la primera vista (Usuarios)
 */
function actualizarPaginacionDos(
  totalRegistros = 0,
  paginaActual = 1,
  limitePorPagina = 3,
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
  const limite = Number(limitePorPagina) || 3;
  const totalPaginas = Math.ceil(total / limite) || 1;

  textoMetrica.textContent = `Mostrando ${Math.min(limite, total)} de ${total}`;

  const irAPagina = (destinoPagina) => {
    if (!inputOffset) return;
    const offsetSQL = (Number(destinoPagina) - 1) * limite;
    inputOffset.value = offsetSQL;
    inputOffset.dispatchEvent(new Event("input", { bubbles: true }));
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

/**
 * Paginador aislado y funcional para la segunda vista (Cuotas)
 * Modifica EXCLUSIVAMENTE #cuotas-offset y escucha #cuotas-limit
 */
function actualizarPaginacionCuotas(
  totalRegistros = 0,
  paginaActual = 1,
  limitePorPagina = 6,
) {
  const textoMetrica = document.getElementById("cuotas-textoMetricaPaginacion");
  const btnRetroceder = document.getElementById("cuotas-btnRetroceder");
  const btnAvanzar = document.getElementById("cuotas-btnAvanzar");
  const contenedorPaginas = document.getElementById(
    "cuotas-contenedorPaginasNumeradas",
  );

  // CONTROL EXCLUSIVO DE LA TABLA 2
  const inputCuotasOffset = document.getElementById("cuotas-offset");

  if (!textoMetrica || !inputCuotasOffset) return;

  const total = Number(totalRegistros) || 0;
  const pagActual = Number(paginaActual) || 1;
  const limite = Number(limitePorPagina) || 6;
  const totalPaginas = Math.ceil(total / limite) || 1;

  textoMetrica.textContent = `Mostrando ${Math.min(limite * pagActual, total)} de ${total}`;

  // Función interna para la Tabla 2
  const irAPaginaCuotas = (destinoPagina) => {
    const offsetSQL = (Number(destinoPagina) - 1) * limite;
    inputCuotasOffset.value = offsetSQL;

    // Disparamos el evento individualmente sobre el input de cuotas
    inputCuotasOffset.dispatchEvent(new Event("input", { bubbles: true }));
  };

  // Botón Retroceder (Cuotas)
  if (btnRetroceder) {
    btnRetroceder.style.display = pagActual <= 1 ? "none" : "inline-block";
    btnRetroceder.disabled = pagActual <= 1;
    btnRetroceder.onclick = (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (pagActual > 1) irAPaginaCuotas(pagActual - 1);
    };
  }

  // Botón Avanzar (Cuotas)
  if (btnAvanzar) {
    btnAvanzar.style.display =
      pagActual >= totalPaginas ? "none" : "inline-block";
    btnAvanzar.disabled = pagActual >= totalPaginas;
    btnAvanzar.onclick = (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (pagActual < totalPaginas) irAPaginaCuotas(pagActual + 1);
    };
  }

  // Números de Página (Cuotas)
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
          e.stopPropagation();
          irAPaginaCuotas(i);
        };
      }
      contenedorPaginas.appendChild(btn);
    }
  }
}
