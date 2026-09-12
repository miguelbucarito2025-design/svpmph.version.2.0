/**
 * Documentación: Script de Control UI/UX para Ofertas Académicas
 * Maneja el renderizado dinámico en Cards, Modales de creación/edición,
 * previsualización de archivos y eventos de paginación/filtros.
 */

const svgCerrar = `
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <line x1="18" y1="6" x2="6" y2="18"></line>
    <line x1="6" y1="6" x2="18" y2="18"></line>
  </svg>
`;

const svgEditar = `
  <svg class="icono-outline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
  </svg>
`;

/**
 * Obtiene las opciones de selects precargadas desde la vista principal DOM.
 * @param {string} idSelect ID del elemento SELECT del DOM.
 * @returns {string} Opciones HTML para inyectar.
 */
function obtenerOpcionesSelect(idSelect) {
  const selectDOM = document.getElementById(idSelect);
  return selectDOM && selectDOM.innerHTML.trim() !== ""
    ? selectDOM.innerHTML
    : '<option value="">-- Seleccione --</option>';
}

/**
 * Genera el formulario a 2 Columnas para AGREGAR OFERTA
 */
function crearFormularioAgregarOferta() {
  const contenedor = document.createElement("div");
  contenedor.className = "modal-ofertas-container";

  contenedor.innerHTML = `
    <h2 class="h2-agregar success">
      <svg class="icono-outline"><use href="#icono-agregar"></use></svg>
      Crear Oferta Académica
    </h2>

    <form method="post" class="form-usuario" id="formAgregarOferta" enctype="multipart/form-data">
      <div class="grid-form-2col">
        
        <!-- COLUMNA 1: Relaciones y Fechas -->
        <div class="columna-form">
          <div class="campo-grupo">
            <label>Programa Académico *</label>
            <select name="programa_id" id="selectPrograma" required>
              ${obtenerOpcionesSelect("programa")}
            </select>
          </div>

          <div class="campo-grupo">
            <label>Núcleo Sede *</label>
            <select name="nucleo_id" id="selectNucleo" required>
              ${obtenerOpcionesSelect("nucleo")}
            </select>
          </div>

          <div class="campo-grupo">
            <label>Fecha de Inicio *</label>
            <input type="date" name="fecha_ini" required>
          </div>

          <div class="campo-grupo">
            <label>Fecha de Finalización *</label>
            <input type="date" name="fecha_fin" required>
          </div>

          <div class="campo-grupo">
            <label>Límite de Cuotas</label>
            <input type="number" name="cuotas" min="1" value="1" required>
          </div>
        </div>

        <!-- COLUMNA 2: Costos, Modalidad y Flyer -->
        <div class="columna-form">
          <div class="campo-grupo">
            <label>Costo Inscripción ($) *</label>
            <input type="number" step="0.01" name="costo_inscripcion" placeholder="0.00" required>
          </div>

          <div class="campo-grupo">
            <label>Costo Total ($) *</label>
            <input type="number" step="0.01" name="costo_total" placeholder="0.00" required>
          </div>

          <div class="campo-grupo">
            <label>Modalidad de Pago *</label>
            <select name="modo_cuotas" id="selectModalidad" required>
              ${obtenerOpcionesSelect("modos")}
            </select>
          </div>

          <div class="campo-grupo">
            <label>Flyer Promocional (Imagen)</label>
            <input type="file" name="flyer" class="input-preview" data-target="previewFlyerAdd" accept="image/*">
          </div>
        </div>

      </div>
    </form>

    <div class="contenedor-preview-unit">
      <img id="previewFlyerAdd" src="" alt="Vista previa del flyer" style="display:none; object-fit:cover; border-radius:6px; margin: 0 auto;">
    </div>

    <hr />

    <div class="container-button-anuncion">
      <button type="submit" class="success button-anuncio" form="formAgregarOferta">
        <svg class="icono-outline"><use href="#icono-agregar"></use></svg>
        Guardar Oferta
      </button>
      <button type="reset" class="limpiar button-anuncio" form="formAgregarOferta">
        <svg class="icono-outline"><use href="#icon-limpiar" /></svg>
        Limpiar
      </button>
    </div>
  `;

  vincularPrevisualizacionArchivos(contenedor);
  return contenedor;
}

/**
 * Genera el formulario a 2 Columnas para EDITAR OFERTA
 */
function crearFormularioEditarOferta(datos) {
  const contenedor = document.createElement("div");
  contenedor.className = "modal-ofertas-container";

  const flyerUrl = datos.flyer_url || datos.flyer || "";

  contenedor.innerHTML = `
    <h2 class="info">
      ${svgEditar}
      Editar Oferta Académica
    </h2>

    <form method="post" class="form-usuario" id="formEditarOferta" enctype="multipart/form-data">
      <input type="hidden" name="id" value="${datos.id}">

      <div class="grid-form-2col">
        
        <!-- COLUMNA 1 -->
        <div class="columna-form">
          <div class="campo-grupo">
            <label>Programa Académico *</label>
            <select name="programa_id" id="selectProgramaEdit" required>
<option value="${datos.programa_id}">${datos.programa}</option>

              ${obtenerOpcionesSelect("programa")}
            </select>
          </div>

          <div class="campo-grupo">
            <label>Núcleo Sede *</label>
            <select name="nucleo_id" id="selectNucleoEdit" required>
<option value="${datos.nucleo_id}">${datos.nucleo}</option>

              ${obtenerOpcionesSelect("nucleo")}
            </select>
          </div>

          <div class="campo-grupo">
            <label>Fecha de Inicio *</label>
            <input type="date" name="fecha_ini" value="${datos.fecha_ini || ""}" required>
          </div>

          <div class="campo-grupo">
            <label>Fecha de Finalización *</label>
            <input type="date" name="fecha_fin" value="${datos.fecha_fin || ""}" required>
          </div>

          <div class="campo-grupo">
            <label>Límite de Cuotas</label>
            <input type="number" name="cuotas" min="1" value="${datos.cuotas || 1}" required>
          </div>
        </div>

        <!-- COLUMNA 2 -->
        <div class="columna-form">
          <div class="campo-grupo">
            <label>Costo Inscripción ($) *</label>
            <input type="number" step="0.01" name="costo_inscripcion" value="${datos.costo_inscripcion || ""}" required>
          </div>

          <div class="campo-grupo">
            <label>Costo Total ($) *</label>
            <input type="number" step="0.01" name="costo_total" value="${datos.costo_total || ""}" required>
          </div>

          <div class="campo-grupo">
            <label>Modalidad de Pago *</label>
            <select name="modo_cuotas" id="selectModalidadEdit" required>
<option value="${datos.modo_cuotas}">${datos.modalidad}</option>

              ${obtenerOpcionesSelect("modos")}
            </select>
          </div>
  <div class="campo-grupo">
            <label>Estado *</label>
            <select name="estado" required>
<option value="${datos.estado}">${datos.estado == 1 ? "Activo " : "Desactivado"}</option>
<option value="${datos.estado == 1 ? 0 : 1}">${datos.estado == 1 ? "Desactivado" : "Activo "}</option>
            </select>
          </div>
          <div class="campo-grupo">
            <label>Cambiar Flyer (Opcional)</label>
            <input type="file" name="flyer" class="input-preview" data-target="previewFlyerEdit" accept="image/*">
          </div>
        </div>

      </div>
    </form>

    <div class="contenedor-preview-unit">
      <img id="previewFlyerEdit" src="${flyerUrl}" alt="Flyer Oferta" style="${flyerUrl ? "display:block;" : "display:none;"} object-fit:cover; border-radius:6px; margin: 0 auto;">
    </div>

    <hr />

    <div class="container-button-anuncion">
      <button type="submit" class="success button-anuncio" form="formEditarOferta">
        Actualizar Oferta
      </button>
    </div>
  `;

  vincularPrevisualizacionArchivos(contenedor);

  // Auto-selección síncrona/asíncrona segura
  setTimeout(() => {
    const selProg = contenedor.querySelector("#selectProgramaEdit");
    const selNuc = contenedor.querySelector("#selectNucleoEdit");
    const selMod = contenedor.querySelector("#selectModalidadEdit");

    if (selProg && datos.programa_id) selProg.value = datos.programa_id;
    if (selNuc && datos.nucleo_id) selNuc.value = datos.nucleo_id;
    if (selMod && datos.modo_cuotas) selMod.value = datos.modo_cuotas;
  }, 50);

  return contenedor;
}

/**
 * Listener dinámico para previsualizar imágenes subidas
 */
function vincularPrevisualizacionArchivos(nodoPadre) {
  const inputsFile = nodoPadre.querySelectorAll(".input-preview");

  inputsFile.forEach((input) => {
    input.addEventListener("change", (e) => {
      const archivo = e.target.files[0];
      const targetId = input.getAttribute("data-target");
      const imgTarget = nodoPadre.querySelector(`#${targetId}`);

      if (imgTarget && archivo) {
        imgTarget.src = URL.createObjectURL(archivo);
        imgTarget.style.display = "block";
      }
    });
  });
}

/**
 * RENDERIZADO EN TARJETAS (CARDS GRID)
 */
function renderizarTabla(datos, paginaActual = 1, limitePorPagina = 9) {
  const contenedorGrid = document.getElementById("contenedorOfertasCards");
  if (!contenedorGrid) return;

  contenedorGrid.innerHTML = "";

  if (!Array.isArray(datos) || datos.length === 0) {
    contenedorGrid.innerHTML = `
      <div class="empty-state text-center py-4 text-muted">
        No se encontraron ofertas académicas registradas.
      </div>`;
    return;
  }

  const fragmento = document.createDocumentFragment();

  datos.forEach((oferta) => {
    const card = document.createElement("div");
    card.className = "card-oferta";

    const imagenFlyer =
      oferta.flyer_url || oferta.flyer || "public/img/default-flyer.jpg";
    const badgeEstado =
      oferta.estado == 1
        ? `<span class="badge badge-success">Activa</span>`
        : `<span class="badge badge-danger">Inactiva</span>`;

    card.innerHTML = `
      <div class="card-oferta-header">
        <input type="checkbox" class="form-check-input check-oferta" data-id="${oferta.id}">
        ${badgeEstado}
        <button type="button" class="btn-editar-card btn-editar" title="Editar Oferta">
          ${svgEditar}
        </button>
      </div>

      <div class="card-oferta-img">
        <img src="${escaparHTML(imagenFlyer)}" alt="Flyer ${escaparHTML(oferta.programa || "Programa")}">
      </div>

      <div class="card-oferta-body">
        <h3 class="card-title">${escaparHTML(oferta.programa || "Programa N/A")}</h3>
        <p class="card-subtitle"><strong>Núcleo:</strong> ${escaparHTML(oferta.nucleo || "Sede N/A")}</p>
        
        <div class="card-details">
          <div><span>Inicio:</span> ${escaparHTML(oferta.fecha_ini || "N/A")}</div>
          <div><span>Fin:</span> ${escaparHTML(oferta.fecha_fin || "N/A")}</div>
          <div><span>Inscripción:</span> $${escaparHTML(oferta.costo_inscripcion || "0.00")}</div>
          <div><span>Costo Total:</span> $${escaparHTML(oferta.costo_total || "0.00")}</div>
          <div><span>Cuotas:</span> ${escaparHTML(oferta.cuotas || "1")}</div>
        </div>
      </div>
    `;

    const btnEditar = card.querySelector(".btn-editar");
    btnEditar.addEventListener("click", () => {
      abrirModalEdicion(oferta);
    });

    fragmento.appendChild(card);
  });

  contenedorGrid.appendChild(fragmento);
  vincularEventosFilas();
}

/**
 * Inicialización y Controladores de Eventos
 */
document.addEventListener("DOMContentLoaded", () => {
  // Incluimos todos los controles (buscador, offset, limit y selects de filtrado)
  const idsControles = [
    "limit",
    "intput-buscar",
    "offset",
    "programa",
    "nucleo",
    "modos",
    "estado",
  ];
  const url = "ofertas/paginar";

  configurarTablaDinamica(
    idsControles,
    url,
    (respuesta) => {
      const listaOfertas = respuesta?.data || [];
      const totalRegistros =
        typeof respuesta?.total === "number"
          ? respuesta.total
          : listaOfertas.length;

      const inputOffset = document.getElementById("offset");
      const inputLimit = document.getElementById("limit");

      const offsetSQL = parseInt(inputOffset?.value) || 0;
      const limite = parseInt(inputLimit?.value) || 9;
      const paginaActual = Math.floor(offsetSQL / limite) + 1;

      renderizarTabla(listaOfertas, paginaActual, limite);
      actualizarPaginacion(totalRegistros, paginaActual, limite);
    },
    { tiempoDebounce: 300 },
  );

  const btnAgregar = document.getElementById("btnAgregarOferta");
  if (btnAgregar) {
    btnAgregar.addEventListener("click", () => {
      const formNodo = crearFormularioAgregarOferta();

      AlertApp.show("", formNodo, "", null, {
        claseExtra: "ventana-modal modal-large",
        btnTexto: "Cerrar",
        btnIcono: svgCerrar,
        btnClase: "btn-ghost-danger",
        ocultarHeader: true,
      });

      inicializarFormulario("formAgregarOferta", "ofertas/guardar", (res) => {
        AlertApp.show("Oferta guardada con éxito", "", "success");
        refrescarTabla(true);
      });
    });
  }

  const btnEliminarMasivo = document.getElementById("btnEliminarMasivo");
  if (btnEliminarMasivo) {
    btnEliminarMasivo.addEventListener("click", eliminarOfertasSeleccionadas);
  }
});

function abrirModalEdicion(oferta) {
  const formEditar = crearFormularioEditarOferta(oferta);

  AlertApp.show("", formEditar, "", null, {
    claseExtra: "ventana-modal modal-large",
    btnTexto: "Cerrar",
    btnIcono: svgCerrar,
    btnClase: "btn-ghost-danger",
    ocultarHeader: true,
  });

  inicializarFormulario("formEditarOferta", "ofertas/actualizar", (res) => {
    AlertApp.show("Oferta actualizada con éxito", "", "success");
    refrescarTabla(false);
  });
}

function refrescarTabla(resetearPagina = true) {
  const inputOffset = document.getElementById("offset");
  if (resetearPagina && inputOffset) {
    inputOffset.value = 0;
  }
  const control = document.getElementById("intput-buscar") || inputOffset;
  if (control) {
    control.dispatchEvent(new Event("input"));
  }
}

function actualizarPaginacion(
  totalRegistros = 0,
  paginaActual = 1,
  limitePorPagina = 9,
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
  const limite = Number(limitePorPagina) || 9;
  const totalPaginas = Math.ceil(total / limite) || 1;

  textoMetrica.textContent = `Mostrando ${Math.min(limite, total)} de ${total}`;

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

function vincularEventosFilas() {
  const checkTodos = document.getElementById("checkSeleccionarTodos");
  const cards = document.querySelectorAll(
    "#contenedorOfertasCards .card-oferta",
  );

  cards.forEach((card) => {
    const checkbox = card.querySelector(".check-oferta");
    if (!checkbox) return;

    card.onclick = (e) => {
      if (
        e.target.tagName === "INPUT" ||
        e.target.closest("button") ||
        e.target.closest("a")
      )
        return;
      checkbox.checked = !checkbox.checked;
      card.classList.toggle("card-seleccionada", checkbox.checked);
    };

    checkbox.onchange = () => {
      card.classList.toggle("card-seleccionada", checkbox.checked);
    };
  });

  if (checkTodos) {
    checkTodos.checked = false;
    checkTodos.onclick = () => {
      const checks = document.querySelectorAll(".check-oferta");
      checks.forEach((cb) => {
        cb.checked = checkTodos.checked;
        const cardPadre = cb.closest(".card-oferta");
        if (cardPadre)
          cardPadre.classList.toggle("card-seleccionada", cb.checked);
      });
    };
  }
}

function eliminarOfertasSeleccionadas() {
  const checksMarcados = document.querySelectorAll(".check-oferta:checked");
  const idsParaEliminar = Array.from(checksMarcados).map((cb) =>
    cb.getAttribute("data-id"),
  );

  if (idsParaEliminar.length === 0) {
    AlertApp.show(
      "Atención",
      "Debe seleccionar al menos una oferta para eliminar.",
      "warning",
    );
    return;
  }

  AlertApp.show(
    "Atención",
    "¿Seguro que deseas eliminar las ofertas seleccionadas? Esta acción no se puede deshacer.",
    "warning",
    () => {
      const payload = JSON.stringify({ ids: idsParaEliminar });
      const tokenCSRF =
        document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute("content") || "";

      fetch("ofertas/eliminacionMultiple", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": tokenCSRF,
        },
        body: payload,
      })
        .then((respuesta) => respuesta.json())
        .then((res) => {
          if (res.status === "success" || res.exito) {
            AlertApp.show(
              "Eliminado",
              "Las ofertas seleccionadas han sido removidas.",
              "success",
            );
            refrescarTabla(false);
          } else {
            AlertApp.show(
              "Error",
              res.mensaje || "No se pudo completar la eliminación.",
              "error",
            );
          }
        })
        .catch((err) => {
          console.error("Error al eliminar ofertas:", err);
          AlertApp.show("Error", "Ocurrió una falla en el servidor.", "error");
        });
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
 * Control del desplegable de herramientas con animaciones y autoplegado
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
    if (!panelTools.classList.contains("is-hidden")) {
      cerrarPanel();
    }
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && !panelTools.classList.contains("is-hidden")) {
      cerrarPanel();
    }
  });
});
