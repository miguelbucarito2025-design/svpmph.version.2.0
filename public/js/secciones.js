/**
 * Script de Control UI/UX para Secciones Académicas
 * Respetando 100% las clases CSS nativas y la arquitectura del proyecto
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

async function eliminarSeccionIndividual(idSeccion) {
  return await realizarPeticion(
    "seccion/eliminar",
    "POST",
    { id: idSeccion },
    true,
  );
}
/**
 * Genera el Formulario a 2 Columnas para AGREGAR SECCIÓN ACADÉMICA
 * @param {Object} [ofertaPreseleccionada] Datos de la oferta si se abrió desde la tarjeta
 */
function crearFormularioAgregarSeccion(ofertaPreseleccionada = null) {
  const contenedor = document.createElement("div");
  contenedor.className = "modal-ofertas-container";

  const ofertaId = ofertaPreseleccionada?.oferta_id || "";
  const ofertaNombre =
    ofertaPreseleccionada?.programa || ofertaPreseleccionada?.oferta || "";

  contenedor.innerHTML = `
    <h2 class="h2-agregar success">
      <svg class="icono-outline"><use href="#icono-agregar"></use></svg>
      Crear Sección ${ofertaNombre ? `para "${escaparHTML(ofertaNombre)}"` : "Académica"}
    </h2>
    <form method="post" class="form-usuario" id="formAgregarSeccion">
      <!-- ID de la Oferta Académica a la que se asociará -->
      <input type="hidden" name="oferta_id" value="${ofertaId}">

      <div class="grid-form">
          <div class="campo-grupo">
            <label>Identificador / Nombre de Sección *</label>
            <input type="text" name="seccion" placeholder="Ej: Sección A, Cohorte I" required>
          </div>
       
      
          <div class="campo-grupo">
            <label>Cupo / Cantidad Máxima *</label>
            <input type="number" name="cantidad_max" min="1" value="30" required>
          </div>
          
           <div class="campo-grupo container-textarea">
          <label>Enlace Grupo de WhatsApp (Opcional)</label>
          <textarea name="grupo_whatsapp">https://chat.whatsapp.com/</textarea>
        </div>
      </div>
    </form>
    <hr />
    <div class="container-button-anuncion">
      <button type="submit" class="success button-anuncio" form="formAgregarSeccion">
        <svg class="icono-outline"><use href="#icono-agregar"></use></svg>
        Guardar Sección
      </button>
      <button type="reset" class="limpiar button-anuncio" form="formAgregarSeccion">
        <svg class="icono-outline"><use href="#icon-limpiar" /></svg>
        Limpiar
      </button>
    </div>
  `;

  return contenedor;
}

/**
 * Genera el Formulario a 2 Columnas para EDITAR SECCIÓN ACADÉMICA
 * @param {Object} datos Objeto de la oferta que contiene el array de secciones
 */
function crearFormularioEditarSeccion(datos) {
  const contenedor = document.createElement("div");
  contenedor.className = "modal-ofertas-container";

  // Asegurarnos de que el array de secciones existe
  const listaSecciones = Array.isArray(datos.secciones) ? datos.secciones : [];

  let opcionesSeccion =
    '<option value="">-- Seleccione una sección --</option>';
  listaSecciones.forEach((sec) => {
    opcionesSeccion += `<option value="${sec.id}">${escaparHTML(sec.seccion)}</option>`;
  });

  // Convertimos el array a JSON seguro para guardarlo en un atributo del DOM
  const jsonSecciones = JSON.stringify(listaSecciones);

  contenedor.innerHTML = `
    <h2 class="info">
      ${svgEditar}
      Editar Sección Académica
    </h2>
    <form method="post" class="form-usuario" id="formEditarSeccion">
      <!-- Campo Oculto con la Oferta ID -->
      <input type="hidden" name="oferta_id" value="${datos.oferta_id || ""}">

      <div class="grid-form">
          <div class="campo-grupo">
            <label>Nombre de Sección *</label>
            <select name="id" id="selectSeccionEditar" data-secciones='${jsonSecciones}' required>
              ${opcionesSeccion}
            </select>
          </div>
          <div class="campo-grupo">
            <label>Nombre *</label>
            <input type="text" name="seccion" id="nombreSeccion"  value="" required>
          </div>
          <div class="campo-grupo">
            <label>Cupo / Cantidad Máxima *</label>
            <input type="number" name="cantidad_max" id="inputCantidadMax" min="1" value="" required>
          </div>
         
          <div class="campo-grupo container-textarea">
            <label>Enlace Grupo de WhatsApp (Opcional)</label>
            <textarea name="grupo_whatsapp" id="inputGrupoWsp"></textarea>
          </div>
        
      </div>
    </form>
    <hr />
    <div class="container-button-anuncion">
      <button type="submit" class="success button-anuncio" form="formEditarSeccion">
        Actualizar Sección
      </button>
        <button type="button" id="btn-delete" class="danger button-anuncio" form="formEditarSeccion">
        Actualizar Sección
      </button>
    </div>
  `;

  // Lógica lógica y directa para autocompletar sin servidor
  setTimeout(() => {
    const selectElem = contenedor.querySelector("#selectSeccionEditar");
    const inputCantidad = contenedor.querySelector("#inputCantidadMax");
    const inputWsp = contenedor.querySelector("#inputGrupoWsp");
    const nombreSeccion = contenedor.querySelector("#nombreSeccion");
    const btnDelete = document.getElementById("btn-delete");

    if (btnDelete) {
      // 💡 1. Agregamos 'async' aquí para poder usar 'await' adentro sin problemas
      btnDelete.addEventListener("click", async () => {
        try {
          let id = selectElem.value;

          if (!id) {
            AlertApp.show(
              "Atención",
              "Por favor seleccione una sección para eliminar.",
              "warning",
            );
            return;
          }

          // 💡 2. Esperamos la respuesta de la función que creamos
          const fetchDelete = await eliminarSeccionIndividual(id);

          if (
            fetchDelete &&
            (fetchDelete.status === "success" || fetchDelete.exito)
          ) {
            refrescarTabla(false);

            AlertApp.show("Éxito", "Eliminado con Éxito", "success");
            // Aquí podrías cerrar el modal o refrescar si lo deseas
          } else {
            AlertApp.show(
              "Error",
              fetchDelete.message || "No se pudo eliminar la sección",
              "error",
            );
          }
        } catch (error) {
          // 💡 3. Capturamos el error real propagado por la petición
          console.error("Error en la eliminación:", error);
          AlertApp.show(
            "Error",
            error || "Ocurrió una falla en el servidor.",
            "error",
          );
        }
      });
    }

    if (selectElem) {
      selectElem.addEventListener("change", function () {
        const idSeleccionado = this.value;

        // Recuperamos el array de secciones desde el atributo data
        const seccionesData = JSON.parse(
          this.getAttribute("data-secciones") || "[]",
        );

        // Buscamos la sección que coincida con el ID seleccionado
        const seccionEncontrada = seccionesData.find(
          (s) => s.id === idSeleccionado,
        );

        if (seccionEncontrada) {
          inputCantidad.value = seccionEncontrada.cantidad_max || 30;
          inputWsp.value = seccionEncontrada.grupo_whatsapp || "";
          if (nombreSeccion) nombreSeccion.value = seccionEncontrada.seccion;
        } else {
          if (nombreSeccion) nombreSeccion.value = "";
          inputCantidad.value = "";
          inputWsp.value = "";
        }
      });
    }
  }, 50);

  return contenedor;
}

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
 * RENDERIZADO EN TARJETAS (Usando 100% tus clases CSS originales)
 */
function renderizarTabla(datos) {
  const contenedorGrid = document.getElementById("contenedorSeccionesCards");
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

  datos.forEach((item) => {
    const card = document.createElement("div");
    card.className = "card-oferta";

    const imagen =
      item.flyer ||
      item.flyer_url ||
      item.imagen ||
      "public/img/default-flyer.jpg";
    const nombreOferta =
      item.programa ||
      item.oferta ||
      item.nombre_oferta ||
      "Programa Académico";

    // 1. EVALUAR SI LA OFERTA TIENE SECCIONES ASIGNADAS
    const tieneListaSecciones =
      Array.isArray(item.secciones) && item.secciones.length > 0;
    const tieneSeccionSimple = Boolean(item.seccion || item.seccion_nombre);
    const totalSecciones =
      Number(item.total_secciones) ||
      (tieneListaSecciones
        ? item.secciones.length
        : tieneSeccionSimple
          ? 1
          : 0);
    const tieneSecciones = totalSecciones > 0;

    // 2. BOTÓN DE EDICIÓN: Solo si tiene secciones creadas
    const botonGestionarSecciones = tieneSecciones
      ? `<button type="button" class="btn-editar-card btn-editar btn-editar-seccion" title="Gestionar Secciones">
           ${svgEditar}
         </button>`
      : "";

    // 3. LISTADO DE SECCIONES ASOCIADAS
    let htmlListaSecciones = "";
    if (tieneListaSecciones) {
      htmlListaSecciones = '<ol class="lista-ofertas-tarjeta mb-2">';
      item.secciones.forEach((sec) => {
        const estadoBadge =
          sec.estado == 1
            ? '<span class="badge status-activo">Activa</span>'
            : '<span class="badge status-inactivo">Inactiva</span>';

        htmlListaSecciones += `
          <li class="small">
            <strong>${escaparHTML(sec.seccion || sec.nombre || "Sección")}</strong> 
            <span class="text-muted">(${escaparHTML(String(sec.cantidad_max || 0))} cupos)</span>
            ${estadoBadge}
          </li>`;
      });
      htmlListaSecciones += "</ol>";
    } else if (tieneSeccionSimple) {
      const estadoBadge =
        item.estado == 1
          ? '<span class="badge status-activo">Activa</span>'
          : '<span class="badge status-inactivo">Inactiva</span>';

      htmlListaSecciones = `
        <ol class="lista-ofertas-tarjeta mb-2">
          <li class="small">
            <strong>${escaparHTML(item.seccion)}</strong> 
            <span class="text-muted">(${escaparHTML(String(item.cantidad_max || 0))} cupos)</span>
            ${estadoBadge}
          </li>
        </ol>`;
    } else {
      htmlListaSecciones =
        '<p class="text-muted small mb-2">Sin secciones asignadas</p>';
    }

    // Cabecera y cuerpo usando tus clases CSS exactas
    card.innerHTML = `
      <div class="card-oferta-header">
        <input type="checkbox" class="form-check-input check-oferta" data-id="${item.id}">
        <div class="container-button-card-header">
          <!-- Botón 1: Agregar Nueva Sección (Usa tus iconos de SVG de la vista) -->
          <button type="button" class="btn-editar-card btn-guardar-card btn-agregar-seccion" title="Agregar Sección">
            <svg class="icono-outline"><use href="#icono-agregar"></use></svg>
          </button>
        
          ${botonGestionarSecciones}
        </div>
      </div>

      <div class="card-oferta-img">
        <img src="${escaparHTML(imagen)}" alt="Flyer ${escaparHTML(nombreOferta)}">
      </div>

      <div class="card-oferta-body">
        <h3 class="card-title">${escaparHTML(nombreOferta)}</h3>
        <p class="card-subtitle"><strong>Núcleo:</strong> ${escaparHTML(item.nucleo || "General")}</p>
        
        <div class="card-details">
          <p class="mb-1"><strong>Secciones asociadas (${totalSecciones}):</strong></p>
          ${htmlListaSecciones}
        </div>
      </div>
    `;

    // Eventos de los botones de la cabecera
    const btnAgregar = card.querySelector(".btn-agregar-seccion");
    if (btnAgregar) {
      btnAgregar.addEventListener("click", (e) => {
        e.stopPropagation();
        abrirModalAgregarParaOferta(item);
      });
    }

    const btnEditarSeccion = card.querySelector(".btn-editar-seccion");
    if (btnEditarSeccion) {
      btnEditarSeccion.addEventListener("click", (e) => {
        e.stopPropagation();
        abrirModalEditarSeccion(item);
      });
    }

    fragmento.appendChild(card);
  });

  contenedorGrid.appendChild(fragmento);
  vincularEventosFilas();
}

/**
 * Eventos e Inicialización
 */
document.addEventListener("DOMContentLoaded", () => {
  const idsControles = ["limit", "intput-buscar", "offset", "nucleo"];
  const url = "seccion/paginar";

  configurarTablaDinamica(
    idsControles,
    url,
    (respuesta) => {
      const lista = respuesta?.data || [];
      const totalRegistros =
        typeof respuesta?.total === "number" ? respuesta.total : lista.length;

      const inputOffset = document.getElementById("offset");
      const inputLimit = document.getElementById("limit");

      const offsetSQL = parseInt(inputOffset?.value) || 0;
      const limite = parseInt(inputLimit?.value) || 6;
      const paginaActual = Math.floor(offsetSQL / limite) + 1;

      renderizarTabla(lista);
      actualizarPaginacion(totalRegistros, paginaActual, limite);
    },
    { tiempoDebounce: 300 },
  );

  const btnEliminarMasivo = document.getElementById("btnEliminarMasivo");
  if (btnEliminarMasivo) {
    btnEliminarMasivo.addEventListener("click", eliminarSeccionesSeleccionadas);
  }
});

function abrirModalAgregarParaOferta(oferta) {
  const formNodo = crearFormularioAgregarSeccion(oferta);
  AlertApp.show("", formNodo, "", null, {
    claseExtra: "ventana-modal modal-large",
    btnTexto: "Cerrar",
    btnIcono: svgCerrar,
    btnClase: "btn-ghost-danger",
    ocultarHeader: true,
  });

  inicializarFormulario("formAgregarSeccion", "seccion/guardar", (res) => {
    AlertApp.show("Sección creada con éxito", "", "success");
    refrescarTabla(false);
  });
}

function abrirModalEditarSeccion(datos) {
  const formEditar = crearFormularioEditarSeccion(datos);
  AlertApp.show("", formEditar, "", null, {
    claseExtra: "ventana-modal modal-large",
    btnTexto: "Cerrar",
    btnIcono: svgCerrar,
    btnClase: "btn-ghost-danger",
    ocultarHeader: true,
  });

  inicializarFormulario("formEditarSeccion", "seccion/actualizar", (res) => {
    AlertApp.show("Sección actualizada con éxito", "", "success");
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
  limitePorPagina = 6,
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
  const limite = Number(limitePorPagina) || 6;
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
    "#contenedorSeccionesCards .card-oferta",
  );

  cards.forEach((card) => {
    const checkbox = card.querySelector(".check-oferta");
    if (!checkbox) return;

    card.onclick = (e) => {
      if (
        e.target.tagName === "INPUT" ||
        e.target.closest("button") ||
        e.target.closest("a")
      ) {
        return;
      }
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
        if (cardPadre) {
          cardPadre.classList.toggle("card-seleccionada", cb.checked);
        }
      });
    };
  }
}

function eliminarSeccionesSeleccionadas() {
  const checksMarcados = document.querySelectorAll(".check-oferta:checked");
  const idsParaEliminar = Array.from(checksMarcados).map((cb) =>
    cb.getAttribute("data-id"),
  );

  if (idsParaEliminar.length === 0) {
    AlertApp.show(
      "Atención",
      "Debe seleccionar al menos una oferta/sección para eliminar.",
      "warning",
    );
    return;
  }

  AlertApp.show(
    "Atención",
    "¿Seguro que deseas eliminar los elementos seleccionados?",
    "warning",
    () => {
      const payload = JSON.stringify({ ids: idsParaEliminar });
      const tokenCSRF =
        document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute("content") || "";

      fetch("seccion/eliminacionMultiple", {
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
              "Los elementos seleccionados han sido removidos.",
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
          console.error("Error al eliminar secciones:", err);
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
 * Control desplegable del Panel de Filtros y Herramientas (#panelHerramientas)
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
