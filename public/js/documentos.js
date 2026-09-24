/**
 * Módulo para la gestión del Expediente Digital / Documentos Consignados
 */

// Íconos SVG Auxiliares
const svgCerrar = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>`;
const svgEliminar = `<svg class="icono-outline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>`;
const svgSubir = `<svg class="icono-outline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>`;
const svgEditar = `<svg class="icono-outline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>`;

// Carga Inicial del DOM
document.addEventListener("DOMContentLoaded", function () {
  cargarExpedienteDigital();

  // Delegación de eventos para las tarjetas del expediente
  const contenedorArchivos = document.querySelector("#contenedor-archivos");
  if (contenedorArchivos) {
    contenedorArchivos.addEventListener("click", function (e) {
      // Si hizo clic en el botón de ver archivo, dejamos que el enlace <a> actúe libremente
      if (e.target.closest(".btn-ver-archivo")) return;

      const tarjeta = e.target.closest(".tarjeta-archivo");
      if (tarjeta) {
        const tieneUrl = tarjeta.dataset.tieneUrl === "true";
        abrirModalGestionArchivo(
          tarjeta.dataset.id,
          tarjeta.dataset.nombre || "Documento",
          tarjeta.dataset.verificado === "1",
          tieneUrl,
        );
      }
    });
  }
});

/**
 * Carga y renderiza la lista de archivos del expediente digital.
 */
function cargarExpedienteDigital() {
  const contenedorArchivos = document.querySelector("#contenedor-archivos");
  if (!contenedorArchivos) return;

  busquedaRapida(null, "documentos/listar", function (respuesta) {
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
            <p class="text-muted">No se encontraron archivos consignados en su expediente.</p>
        </div>`;
      return;
    }

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
                <span class="archivo-estado-texto">${esVerificado ? "Verificado" : "En revisión"}</span>
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
                : `<span class="badge-subir">Subir Archivo</span>`
            }
            </div>
        </div>`;
    });

    contenedorArchivos.innerHTML = htmlArchivos;
  });
}

/**
 * Abre la modal de edición y subida de archivo individual.
 */
function abrirModalGestionArchivo(id, nombre, verificado, tieneUrl) {
  const formModal = crearFormularioSubidaArchivo(
    id,
    nombre,
    verificado,
    tieneUrl,
  );

  AlertApp.show("", formModal, "", null, {
    claseExtra: "ventana-modal modal-archivo-estilo",
    btnTexto: "Cerrar",
    btnIcono: svgCerrar,
    btnClase: "btn-ghost-danger",
    ocultarHeader: true,
  });

  // Inicializar el envío del formulario vía AJAX
  inicializarFormulario("formEditarArchivo", "archivo/actualizar", (res) => {
    AlertApp.show("Documento actualizado con éxito", "", "success");
    cargarExpedienteDigital();
  });

  // Evento para el botón de eliminar solo si existe en el DOM (si tieneUrl era true)
  const btnEliminar = formModal.querySelector("#btnEliminarArchivo");
  if (btnEliminar) {
    btnEliminar.addEventListener("click", function () {
      confirmarEliminacionArchivo(id);
    });
  }
}

/**
 * Construye el nodo DOM del formulario para la modal de subida de archivo.
 */
function crearFormularioSubidaArchivo(
  id,
  nombreDocumento,
  esVerificado,
  tieneUrl,
) {
  const contenedor = document.createElement("div");
  contenedor.className = "modal-archivo-wrapper";

  const svgEstadoModal = esVerificado
    ? `<svg class="icon-estado icon-verificado"><use href="#icon-confirmar" /></svg>`
    : `<svg class="icon-estado icon-pendiente"><use href="#icon-pendiente" /></svg>`;

  // Si no hay URL, no se renderiza el botón de eliminar
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
 * Muestra el diálogo de confirmación para eliminar un documento del expediente.
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
            cargarExpedienteDigital();
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
