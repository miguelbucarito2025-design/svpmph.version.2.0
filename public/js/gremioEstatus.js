/**
 * Renderiza el módulo de Estatus de Gremio y gestiona las modales de Afiliación y Archivos.
 *
 * @param {HTMLElement|string} contenedorOrSelector - Selector CSS o nodo DOM principal.
 */
function cargarEstatusGremio(contenedorOrSelector) {
  const contenedor =
    typeof contenedorOrSelector === "string"
      ? document.querySelector(contenedorOrSelector)
      : contenedorOrSelector;

  if (!contenedor) return;

  // 1. Cargar Datos del Estatus de Afiliación y Archivos
  busquedaRapida(null, "gremio/obtener-estatus", function (respuesta) {
    if (!respuesta || respuesta.status !== "success") return;

    let btnEliminarMostrar = document.getElementById("btnEliminar-gremio");
    if (btnEliminarMostrar) {
      btnEliminarMostrar.style.display =
        respuesta.data?.datos !== null ? "" : "none";
    }

    const info = respuesta.data?.datos || {};
    const listaArchivos = respuesta.data?.requisitos || [];

    // Pintar Datos Personales
    const txtNombreCompleto = contenedor.querySelector(
      "#usuario-nombre-completo",
    );
    const txtCedula = contenedor.querySelector("#usuario-cedula");
    const txtCodigo = contenedor.querySelector("#usuario-codigo");
    const txtEstado = contenedor.querySelector("#usuario-estado");

    if (txtNombreCompleto) {
      const nombre1 = (info.nombre || "").trim();
      const nombre2 = (info.s_nombre || "").trim();
      const apellido1 = (info.apellido || "").trim();
      const apellido2 = (info.s_apellido || "").trim();
      txtNombreCompleto.textContent =
        `${nombre1}${nombre2} ${apellido1}${apellido2}`.trim() || "Afiliado";
    }

    if (txtCedula) {
      const cedulaFormateada = info.id_cedula
        ? new Intl.NumberFormat("es-VE").format(info.id_cedula)
        : "---";
      txtCedula.textContent = `Cedula: V- ${cedulaFormateada}`;
    }

    if (txtCodigo) txtCodigo.textContent = info.codigo || "Sin Código";
    if (txtEstado)
      txtEstado.textContent =
        info.estado == 1 ? "Verificado" : " No Verificado";

    // Pintar Mención y Promoción
    const imgMencion = contenedor.querySelector("#mencion-img");
    const txtMencion = contenedor.querySelector("#mencion-nombre");
    const imgPromocion = contenedor.querySelector("#promocion-img");
    const txtPromocion = contenedor.querySelector("#promocion-nombre");

    if (imgMencion && txtMencion) {
      imgMencion.src = info.img_mencion || "public/multimedia/icon/error.jpg";
      imgMencion.alt = info.mencion || "Mención";
      txtMencion.textContent = info.mencion || "Sin mención asignada";
    }

    if (imgPromocion && txtPromocion) {
      imgPromocion.src = info.img || "public/multimedia/icon/error.jpg";
      imgPromocion.alt = info.promocion || "Promoción";
      txtPromocion.textContent = info.promocion || "Sin promoción asignada";
    }

    // Pintar Visor de Archivos (Restaurado)
    const contenedorArchivos = document.querySelector("#contenedor-archivos");
    if (contenedorArchivos) {
      if (!Array.isArray(listaArchivos) || listaArchivos.length === 0) {
        contenedorArchivos.innerHTML = `
          <div class="archivo-vacio-text">
              <p class="text-muted">No se encontraron archivos consignados en su expediente.</p>
          </div>`;
      } else {
        let htmlArchivos = "";
        listaArchivos.forEach((item) => {
          const esVerificado = Number(item.verificado) === 1;
          const tieneUrl = Boolean(item.url);

          const svgEstado = esVerificado
            ? `<svg class="icon-estado icon-verificado" title="Documento Verificado"><use href="#icon-confirmar" /></svg>`
            : `<svg class="icon-estado icon-pendiente" title="Pendiente de Verificación"><use href="#icon-pendiente" /></svg>`;

          htmlArchivos += `
            <div class="tarjeta-archivo ${esVerificado ? "estado-verificado" : "estado-pendiente"}" 
                 data-id="${item.id}" 
                 data-nombre="${item.archivo.trim()}" 
                 data-verificado="${item.verificado}"
                 data-tiene-url="${tieneUrl}">
                <div class="archivo-icono-wrapper">
                    <svg class="icon-documento"><use href="#icon-archivo" /></svg>
                    <span class="badge-verificacion">${svgEstado}</span>
                </div>
                <div class="archivo-info">
                    <span class="archivo-nombre" title="${item.archivo.trim()}">${item.archivo.trim()}</span>
                    <span class="archivo-estado-texto">${esVerificado ? "<br> Verificado" : "<br> En revisión"}</span>
                </div>
                <div class="archivo-accion">
                ${
                  tieneUrl
                    ? `<a href="${item.url}" target="_blank" rel="noopener noreferrer" class="btn-ver-archivo" title="Ver Documento">
                        <button type="button" class="success btn-ver-archivo max-width-250">
                            <svg class="icono-outline"><use href="#icon-confirmar" /></svg>
                            Ver Archivo
                        </button>
                       </a>`
                    : `Subir Archivo`
                }
                </div>
            </div>`;
        });
        contenedorArchivos.innerHTML = htmlArchivos;
      }
    }
  });
}

/**
 * Evento para Lanzar la Modal del Formulario de Registro / Selección de Gremio
 */
function abrirModalRegistroGremio(valoresIniciales = {}) {
  const formModal = crearFormularioRegistroGremio();

  AlertApp.show("", formModal, "", null, {
    claseExtra: "ventana-modal modal-registro-estilo",
    btnTexto: "Cerrar",
    btnIcono: svgCerrar,
    btnClase: "btn-ghost-danger",
    ocultarHeader: true,
  });

  // Cargar datos dinámicos de los select dentro de la modal
  busquedaRapida(null, "carreras/select", function (respuesta) {
    const selectMencion = formModal.querySelector("#mencion-id");
    const selectPromocion = formModal.querySelector("#promocion-id");

    const imgMencion = formModal.querySelector("#mencion-img");
    const txtNombreMencion = formModal.querySelector("#mencion-nombre");

    const imgPromocion = formModal.querySelector("#promocion-img");
    const txtNombrePromocion = formModal.querySelector("#promocion-nombre");

    const ulRequisitos = formModal.querySelector("#lista-requisitos");

    if (!selectMencion || !selectPromocion) return;

    const datos = respuesta?.data || {};
    const listaMenciones = Array.isArray(datos.mencion) ? datos.mencion : [];
    const listaPromociones = Array.isArray(datos.promocion)
      ? datos.promocion
      : [];

    // Llenar Selects
    let opcionesMencion = ``;
    listaMenciones.forEach((item) => {
      const selected = item.id == valoresIniciales.mencion_id ? "selected" : "";
      opcionesMencion += `<option value="${item.id}" ${selected}>${item.mencion || "Seleccione"}</option>`;
    });
    selectMencion.innerHTML = opcionesMencion;

    let opcionesPromocion = ``;
    listaPromociones.forEach((item) => {
      const selected =
        item.id == valoresIniciales.promocion_id ? "selected" : "";
      opcionesPromocion += `<option value="${item.id}" ${selected}>${item.promocion || "Seleccione"}</option>`;
    });
    selectPromocion.innerHTML = opcionesPromocion;

    // Cambios Mención
    selectMencion.addEventListener("change", function () {
      const m = listaMenciones.find((x) => x.id == selectMencion.value);
      if (m) {
        imgMencion.src = m.img || "";
        txtNombreMencion.textContent = m.mencion || "";

        // Renderizar requisitos
        const reqs = Array.isArray(m.requisitos) ? m.requisitos : [];
        if (reqs.length === 0) {
          ulRequisitos.innerHTML = `<li class="item-requisito-vacio">No hay requisitos consignados.</li>`;
        } else {
          ulRequisitos.innerHTML = reqs
            .map(
              (r, i) =>
                `<li class="item-requisito"><span class="num-requisito">${i + 1}.</span> <span>${r}</span></li>`,
            )
            .join("");
        }
      } else {
        imgMencion.src = "";
        txtNombreMencion.textContent = "";
        ulRequisitos.innerHTML = `<li class="text-muted">Seleccione una mención para ver los requisitos.</li>`;
      }
    });

    // Cambios Promoción
    selectPromocion.addEventListener("change", function () {
      const p = listaPromociones.find((x) => x.id == selectPromocion.value);
      if (p) {
        imgPromocion.src = p.img || "";
        txtNombrePromocion.textContent = p.promocion || "";
      } else {
        imgPromocion.src = "";
        txtNombrePromocion.textContent = "";
      }
    });
  });

  // Inicializar guardado mediante AJAX
  inicializarFormulario(
    "formRegistroGremioModal",
    "registro/guardar-Gremio",
    (res) => {
      AlertApp.show("Afiliación actualizada con éxito", "", "success");
      cargarEstatusGremio("#gremio");
    },
  );
}

/**
 * Genera el nodo HTML para el Formulario Modal de Selección de Gremio
 */
function crearFormularioRegistroGremio() {
  const div = document.createElement("div");
  div.className = "modal-gremio-wrapper";

  div.innerHTML = `
    <h2 class="info title-modal">
      <svg class="icono-outline"><use href="#icon-registro" /></svg>
      Formulario de Afiliación
    </h2>

    <hr class="divider-modal" />

    <form method="post" class="form-usuario grid-form" id="formRegistroGremioModal">
      
      <!-- Selector Mención -->
      <div class="campo-grupo campo-grupo-img">
        <label for="mencion-id">Mención *</label>
        <select name="mencion_id" id="mencion-id" required>
          <option value="">Cargando...</option>
        </select>
        <div class="img-prueva">
          <img id="mencion-img" src="" alt="">
          <p class="text-muted small mb-1" id="mencion-nombre"></p>
        </div>
      </div>

      <!-- Selector Promoción -->
      <div class="campo-grupo campo-grupo-img">
        <label for="promocion-id">Promoción *</label>
        <select name="promocion_id" id="promocion-id" required>
          <option value="">Cargando...</option>
        </select>
        <div class="img-prueva">
          <img id="promocion-img" src="" alt="">
          <p class="text-muted small mb-1" id="promocion-nombre"></p>
        </div>
      </div>

      <!-- Requisitos -->
      <div class="campo-grupo campo-full">
        <h3>Requisitos Requeridos</h3>
        <ul id="lista-requisitos" class="lista-requisitos">
          <li class="text-muted">Seleccione una mención para ver los requisitos.</li>
        </ul>
      </div>

    </form>

    <hr class="divider-modal" />

    <div class="container-button-anuncion modal-actions">
      <button type="submit" class="success button-anuncio" form="formRegistroGremioModal">
        <svg class="icono-outline"><use href="#icon-confirmar" /></svg>
        Guardar Afiliación
      </button>
      <button type="reset" class="limpiar button-anuncio" form="formRegistroGremioModal">
        <svg class="icono-outline"><use href="#icon-limpiar" /></svg>
        Limpiar
      </button>
    </div>
  `;

  return div;
}

// Carga Inicial del DOM
document.addEventListener("DOMContentLoaded", function () {
  cargarEstatusGremio("#gremio");

  // Botón para abrir el Modal de Registro
  const btnAbrir = document.querySelector("#btnAbrirModalRegistro");
  if (btnAbrir) {
    btnAbrir.addEventListener("click", function () {
      abrirModalRegistroGremio();
    });
  }

  // Botón de eliminar gremio (comprobación segura)
  const btnEliminarGremio = document.getElementById("btnEliminar-gremio");
  if (btnEliminarGremio) {
    btnEliminarGremio.addEventListener("click", function () {
      confirmarCancelacionGremio();
    });
  }

  // Delegación de eventos para la modal de archivos de expediente
  const contenedorArchivos = document.querySelector("#contenedor-archivos");
  if (contenedorArchivos) {
    contenedorArchivos.addEventListener("click", function (e) {
      if (e.target.closest(".btn-ver-archivo")) return;
      const tarjeta = e.target.closest(".tarjeta-archivo");
      if (tarjeta) {
        const tieneUrl = tarjeta.dataset.tieneUrl === "true";
        miFuncionPersonalizada(
          tarjeta.dataset.id,
          tarjeta.dataset.nombre || "Documento",
          tarjeta.dataset.verificado === "1",
          tieneUrl,
        );
      }
    });
  }
});

// Íconos SVG
const svgCerrar = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>`;
const svgEliminar = `<svg class="icono-outline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>`;
const svgSubir = `<svg class="icono-outline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>`;
const svgEditar = `<svg class="icono-outline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>`;

/**
 * Modal de Edición / Subida de Archivo Individual
 */
function miFuncionPersonalizada(id, nombre, verificado, tieneUrl) {
  let form = formularioArchivo(id, nombre, verificado, tieneUrl);

  AlertApp.show("", form, "", null, {
    claseExtra: "ventana-modal modal-archivo-estilo",
    btnTexto: "Cerrar",
    btnIcono: svgCerrar,
    btnClase: "btn-ghost-danger",
    ocultarHeader: true,
  });

  inicializarFormulario("formEditarArchivo", "archivo/actualizar", (res) => {
    AlertApp.show("Documento actualizado con éxito", "", "success");
    cargarEstatusGremio("#gremio");
  });

  const btnEliminar = form.querySelector("#btnEliminarArchivo");
  if (btnEliminar) {
    btnEliminar.addEventListener("click", function () {
      confirmarEliminacionArchivo(id);
    });
  }
}

/**
 * Construye el formulario HTML para la modal de archivo (Oculta botón Eliminar si tieneUrl es false)
 */
function formularioArchivo(id, nombreDocumento, esVerificado, tieneUrl) {
  const contenedor = document.createElement("div");
  contenedor.className = "modal-archivo-wrapper";

  const svgEstadoModal = esVerificado
    ? `<svg class="icon-estado icon-verificado"><use href="#icon-confirmar" /></svg>`
    : `<svg class="icon-estado icon-pendiente"><use href="#icon-pendiente" /></svg>`;

  const botonEliminarHtml = tieneUrl
    ? `<button type="button" class="danger button-anuncio btn-eliminar" id="btnEliminarArchivo">
        ${svgEliminar}
        Eliminar
       </button>`
    : "";

  contenedor.innerHTML = `
    <h2 class="h2-agregar info">
      ${svgEditar}
      ${tieneUrl ? "Reemplazar / Gestionar Archivo" : "Subir Archivo"}
    </h2>
    <div class="tarjeta-archivo tarjeta-modal-header ${esVerificado ? "estado-verificado" : "estado-pendiente"}">
      <div class="archivo-icono-wrapper">
          <svg class="icon-documento"><use href="#icon-archivo" /></svg>
          <span class="badge-verificacion">${svgEstadoModal}</span>
      </div>
      <div class="archivo-info">
          <span class="archivo-nombre">${nombreDocumento}</span>
          <span class="archivo-estado-texto">${esVerificado ? "Documento Verificado" : "En revisión / Sin cargar"}</span>
      </div>
    </div>

    <form method="post" class="form-usuario grid-form" id="formEditarArchivo" enctype="multipart/form-data">
      <input type="hidden" name="id" value="${id}">
      
      <div class="campo-grupo container-file-upload">
          <label for="input-archivo-edit" class="label-custom">
            ${tieneUrl ? "Reemplazar Documento Existente *" : "Adjuntar Documento *"}
          </label>
          
          <div class="drop-zone-preview">
            <input type="file" id="input-archivo-edit" name="archivo" class="input-preview" data-target="previewFlyerEdit" accept="image/*,application/pdf" required>
            
            <div class="preview-box" id="previewFlyerEdit">
              <span class="preview-placeholder">
                ${svgSubir}
                <p>Haga clic o arrastre aquí su archivo (PNG, JPG, PDF)</p>
              </span>
            </div>
          </div>
      </div>
    </form>

    <hr class="divider-modal" />

    <div class="container-button-anuncion modal-actions">
      <button type="submit" class="success button-anuncio btn-actualizar" form="formEditarArchivo">
        ${svgSubir}
        ${tieneUrl ? "Actualizar Documento" : "Subir Documento"}
      </button>

      ${botonEliminarHtml}
    </div>
  `;

  return contenedor;
}

/**
 * Diálogo de confirmación para cancelar la afiliación al gremio.
 */
function confirmarCancelacionGremio() {
  AlertApp.show(
    "¿Está seguro de realizar esta acción?",
    "No se puede deshacer esta acción. Tus archivos no serán eliminados, así que luego en el gestor de archivos puedes decidir conservarlos o eliminarlos.",
    "warning",
    function () {
      busquedaRapida(null, "gremio/eliminar-user", function (res) {
        if (res && res.status === "success") {
          AlertApp.show("Afiliación cancelada correctamente", "", "success");
          cargarEstatusGremio("#gremio");
        } else {
          AlertApp.show(
            "Error",
            res?.message || "No se pudo procesar la solicitud.",
            "error",
          );
        }
      });
    },
    {
      mostrarCancelar: true,
      btnTexto: "Sí, cancelar afiliación",
      btnClase: "btn-danger-red",
      btnCancelarTexto: "Volver",
      btnCancelarClase: "btn-cancel",
    },
  );
}

/**
 * Diálogo de confirmación para eliminar un archivo individual.
 */
function confirmarEliminacionArchivo(id) {
  AlertApp.show(
    "¿Está seguro de eliminar este documento?",
    "Esta acción removerá el archivo de su expediente institucional.",
    "warning",
    function () {
      busquedaRapida(
        id,
        "documentos/eliminar",
        function (res) {
          if (res && res.status === "success") {
            AlertApp.show("Eliminado correctamente", "", "success");
            cargarEstatusGremio("#gremio");
          } else {
            AlertApp.show(
              "Error",
              res?.message || "No se pudo eliminar el archivo.",
              "error",
            );
          }
        },
        { nombreParametro: "id" },
      );
    },
    {
      mostrarCancelar: true,
      btnTexto: "Sí, eliminar",
      btnClase: "btn-danger-red",
      btnCancelarTexto: "Cancelar",
      btnCancelarClase: "btn-cancel",
    },
  );
}
