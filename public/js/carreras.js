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

const svgTerminos = `
<svg class="icono-outline"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
  <polyline points="14 2 14 8 20 8"/>
  <line x1="8" y1="13" x2="16" y2="13"/>
  <line x1="8" y1="17" x2="12" y2="17"/>
  <path d="M15 16l1.5 1.5 3.5-3.5"/>
</svg>
`;

function formularioTerminos(datos) {
  const contenedor = document.createElement("div");
  contenedor.className = "modal-ofertas-container";

  contenedor.innerHTML = `
    <h2 class="h2-agregar  grey">
     ${svgTerminos}
      Vinculación de Terminos y Condiciones
    </h2>

    <form method="post" class="form-usuario" id="formTerminos" enctype="multipart/form-data">
    <input type="hidden" name="termino_id" value="${datos.id_terminos}">
    <input type="hidden" name="rol_id" value="${datos.rol_id}">

      <div class="grid-form">
          <div class="campo-grupo">
            <label>Titulo *</label>
            <input type="text" name="titulo" value="${datos.titulo || "No definido"}"  required>
          </div>
          <div class="campo-grupo">
            <label>Versión *</label>
            <input type="text"  name="version" value="${datos.version || "No definido"}"  required>
          </div>
          <div class="campo-grupo container-textarea">
            <label>Contenido *</label>
            <textarea name="contenido" required>${datos.contenido || "No definido"}</textarea>
          </div>
        
      </div>
   
    </form>



    <hr />

    <div class="container-button-anuncion">
      <button type="submit" class="success button-anuncio" form="formTerminos">
        <svg class="icono-outline"><use href="#icono-agregar"></use></svg>
        Guardar 
      </button>
      <button type="reset" class="limpiar button-anuncio" form="formTerminos">
        <svg class="icono-outline"><use href="#icon-limpiar" /></svg>
        Limpiar
      </button>
    </div>
  `;

  return contenedor;
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
      Crear
    </h2>

    <form method="post" class="form-usuario" id="formAgregar" enctype="multipart/form-data">
      <div class="grid-form-2col">
          <div class="campo-grupo">
            <label>Nombre de la Carrera *</label>
            <input type="text" name="mencion"  required>
          </div>
          <div class="campo-grupo">
            <label>Requisitos (separados por ",") *</label>
            <input type="text"  name="requisitos"  required>
          </div>
      </div>
      <div class="campo-grupo input-file-lone ">
            <label>Imagen *</label>
            <input type="file" required name="img" class="input-preview" data-target="previewFlyerAdd" accept="image/*">
       </div>

    </form>

    <div class="contenedor-preview-unit">
      <img id="previewFlyerAdd" src="" alt="Vista previa del flyer" style="display:none; object-fit:cover; border-radius:6px; margin: 0 auto;">
    </div>

    <hr />

    <div class="container-button-anuncion">
      <button type="submit" class="success button-anuncio" form="formAgregar">
        <svg class="icono-outline"><use href="#icono-agregar"></use></svg>
        Guardar 
      </button>
      <button type="reset" class="limpiar button-anuncio" form="formAgregar">
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
function crearFormularioEditar(datos) {
  const contenedor = document.createElement("div");
  contenedor.className = "modal-ofertas-container";

  const img = datos.img || "";

  contenedor.innerHTML = `
    <h2 class="info">
      ${svgEditar}
      Edición
    </h2>

    <form method="post" class="form-usuario" id="formEditar" enctype="multipart/form-data">
    <input type="hidden" name="id" value="${datos.id}">
      <div class="grid-form-2col">
          <div class="campo-grupo">
             <label>Nombre de la Carrera *</label>
             <input type="text" name="mencion" value="${datos.mencion}"  required>
          </div>
          <div class="campo-grupo">
             <label>Requisitos *</label>
             <input type="text"  name="requisitos" value="${datos.requisitos}"  required>
          </div>
      </div>
      <div class="campo-grupo input-file-lone ">
          <label>Estado *</label>
<select name="estado">
<option value="${datos.estado}">${datos.estado ? "Disponible" : "No Disponible"}</option>
<option value="${datos.estado == 1 ? 0 : 1}">${datos.estado == 1 ? "No Disponible" : "Disponible"}</option>
</select>
      </div>
      <div class="campo-grupo input-file-lone ">
          <label>Imagen *</label>

          <input type="file"  name="img" class="input-preview" data-target="previewFlyerEdit" accept="image/*">
      </div>

    </form>

    <div class="contenedor-preview-unit">

      <img id="previewFlyerEdit" src="${img}" alt="Flyer Oferta" style="display:block; object-fit:cover; border-radius:6px; margin: 0 auto;">
    </div>

    <hr />

    <div class="container-button-anuncion">
      <button type="submit" class="success button-anuncio" form="formEditar">
        Actualizar 
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
function renderizarTabla(datos, paginaActual = 1, limitePorPagina = 3) {
  const contenedorGrid = document.getElementById("contenedorOfertasCards");
  if (!contenedorGrid) return;

  contenedorGrid.innerHTML = "";

  if (!Array.isArray(datos) || datos.length === 0) {
    contenedorGrid.innerHTML = `
      <div class="empty-state text-center py-4 text-muted">
        No se encontraron Menciones registradas.
      </div>`;
    return;
  }

  const fragmento = document.createDocumentFragment();

  datos.forEach((rol) => {
    const card = document.createElement("div");
    card.className = "card-oferta card-rol ";

    const imagen = rol.img || "public/img/default-flyer.jpg";

    card.innerHTML = `
      <div class="card-oferta-header">
      <input type="checkbox" class="form-check-input check-oferta" data-id="${rol.id}">
     <div class="container-button-card-header">
   
     <button type="button" class="btn-editar-card btn-editar" title="Editar">
     ${svgEditar}
     </button>
     </div>
      
      </div>

      <div class="card-img">
        <img src="${escaparHTML(imagen)}" alt="Flyer ${escaparHTML(rol.mencion || "Rol")}">
      </div>

      <div class="card-oferta-body">
        <h3 class="card-title">${escaparHTML(rol.mencion || "Rol N/A")}</h3>
        
        <div class="card-details-rol">
                 <p class="card-subtitle"><strong>Requisitos:</strong> ${escaparHTML(rol.requisitos || "Descripción N/A")}</p>
                 <p class="card-subtitle" style="${rol.estado == 0 ? "color: red ;" : ""}"><strong>Estado:</strong> ${escaparHTML(rol.estado == 1 ? "Disponible" : " No Disponible")}</p>
        </div>
      </div>
    `;

    const btnEditar = card.querySelector(".btn-editar");
    btnEditar.addEventListener("click", () => {
      abrirModalEdicion(rol);
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
  const idsControles = ["limit", "offset"];
  const url = "carreras/paginar";

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
      const limite = parseInt(inputLimit?.value) || 3;
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

      inicializarFormulario("formAgregar", "carreras/guardar", (res) => {
        AlertApp.show("Carrera guardada con éxito", "", "success");
        refrescarTabla(false);
      });
    });
  }

  const btnEliminarMasivo = document.getElementById("btnEliminarMasivo");
  if (btnEliminarMasivo) {
    btnEliminarMasivo.addEventListener("click", eliminarOfertasSeleccionadas);
  }
});

function abrirModalEdicion(rol) {
  const formEditar = crearFormularioEditar(rol);

  AlertApp.show("", formEditar, "", null, {
    claseExtra: "ventana-modal modal-large",
    btnTexto: "Cerrar",
    btnIcono: svgCerrar,
    btnClase: "btn-ghost-danger",
    ocultarHeader: true,
  });

  inicializarFormulario("formEditar", "carreras/actualizar", (res) => {
    AlertApp.show("Carrera actualizada con éxito", "", "success");
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
      "Debe seleccionar al menos una Carrera para eliminar.",
      "warning",
    );
    return;
  }

  AlertApp.show(
    "Atención",
    "¿Seguro que deseas eliminar las Carreras seleccionadas? Esta acción no se puede deshacer.",
    "warning",
    () => {
      const payload = JSON.stringify({ ids: idsParaEliminar });
      const tokenCSRF =
        document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute("content") || "";

      fetch("carreras/eliminacionMultiple", {
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
              "Las Carreras seleccionadas han sido removidas.",
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
          console.error("Error al eliminar Carreras:", err);
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
