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

function datosCard(datos) {
  const contenedor = document.createElement("div");
  contenedor.className = "modal-ofertas-container";

  // Verificación simple por si llega vacío
  if (!datos) {
    contenedor.innerHTML =
      '<p class="sin-registros">No se recibieron datos del usuario.</p>';
    return contenedor;
  }

  contenedor.innerHTML = `
    <h2 class="h2-agregar info">
      ${svgEditar}
      Editar Usuario
    </h2>

  
    <form method="post"  id="cuentaForm">
      
      <input type="hidden" id="cuenta_id" name="id" value="${datos.cuenta_id}">

      <div class="grid-form ">
          <div class="campo-grupo">
            <label>Rol *</label>
            <select name="rol_id" id="rol-cambiar">
            <option value="${datos.rol_id}">-- Seleccione --</option>
            </select>
          </div>
          <div class="campo-grupo">
            <label>Estado *</label>
             <select name="estado">
          <option value="${datos.estado}">-- Seleccione --</option>
          <option value="${datos.estado == 1 ? 0 : 1}">${datos.estado == 1 ? "desactivar" : "Activar"}</option>
            </select>
          </div>
      </div>
      </form>
        <div class="container-button-anuncion">
      <button type="submit" class="success button-anuncio" form="cuentaForm">
        <svg class="icono-outline"><use href="#icono-agregar"></use></svg>
        Guardar Cambios
      </button>
 
    </div>
  `;

  busquedaRapida(
    datos.rol_id,
    "rol/cambiar",
    function (datos) {
      const contenedor = document.getElementById("rol-cambiar");

      if (!datos.data || datos.data.length === 0) {
        contenedor.innerHTML = ` <option value="">No se encontro nada relacionado</option>`;

        return;
      }

      datos.data.forEach((datos) => {
        const m = datos;
        contenedor.innerHTML += `
    <option value="${m.id}">
    ${m.rol}
   </option>
    `;
      });
    },
    {
      nombreParametro: "rol_id",
    },
  );

  return contenedor;
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
        No se encontraron Usuarios registrados.
      </div>`;
    return;
  }

  const fragmento = document.createDocumentFragment();

  datos.forEach((datos) => {
    const card = document.createElement("div");
    card.className = "card-oferta card-rol";

    const imagen = datos.foto || datos.img;
    const nombreCompleto =
      `${datos.nombre || ""} ${datos.s_nombre || ""} ${datos.apellido || ""} ${datos.s_apellido || ""}`.trim();
    const estadoBadge =
      datos.estado === 1
        ? '<span class="badge status-activo">Activo</span>'
        : '<span class="badge status-inactivo">Inactivo</span>';

    card.innerHTML = `
      <div class="card-oferta-header">
      <input type="checkbox" class="form-check-input check-oferta" data-id="${datos.cuenta_id}">
     <div class="container-button-card-header">
   
     <button type="button" class="btn-editar-card btn-editar btn-datos" title="Editar">
     ${svgEditar}
     </button>
     </div>
      
      </div>

      <div class="card-img">
        <img src="${escaparHTML(imagen)}" alt="Flyer ${escaparHTML(datos.rol || "Rol")}">
      </div>

      <div class="card-oferta-body">
        
         <div class="container-target-usuario">
          <div class="card-usuario-header">
            <div class="card-usuario-main">
              <h4 class="card-usuario-nombre">${nombreCompleto}</h4>
              <span class="card-usuario-user">@${datos.usuario} • ${datos.rol}</span>
            </div>
            ${estadoBadge}
          </div>
          
          <div class="card-usuario-body">
            <p><strong>Cédula:</strong> V-${datos.id_cedula}</p>
            <p><strong>Teléfono:</strong> ${datos.tlf}</p>
            <p><strong>Correo:</strong> ${datos.correo}</p>
            <p><strong>Dirección:</strong> ${datos.direccion ? datos.direccion.replace(/\r\n|\r|\n/g, "<br>") : "No especificada"}</p>
          </div>
          
          <div class="card-usuario-footer">
            <small>Ingreso: ${datos.ingreso}</small>
          </div>
      </div>
      
    </div>
      </div>
    `;

    const btnDatos = card.querySelector(".btn-datos");
    btnDatos.addEventListener("click", () => {
      abrirModalDatos(datos);
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
  const idsControles = ["limit", "offset", "rol_id", "intput-buscar"];
  const url = "usuarios/paginar";

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

  const btnEliminarMasivo = document.getElementById("btnEliminarMasivo");
  if (btnEliminarMasivo) {
    btnEliminarMasivo.addEventListener("click", eliminarOfertasSeleccionadas);
  }
});

function abrirModalDatos(datos) {
  const form = datosCard(datos);

  AlertApp.show("", form, "", null, {
    claseExtra: "ventana-modal ancho-auto",
    btnTexto: "Cerrar",
    btnIcono: svgCerrar,
    btnClase: "btn-ghost-danger",
    ocultarHeader: true,
  });

  inicializarFormulario("cuentaForm", "cuenta/cambiar", (res) => {
    AlertApp.show("Cuenta actualizada con éxito", "", "success");
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

      fetch("usuario/eliminar", {
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
              "Los Usuario(s) seleccionados han sido removidos.",
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
