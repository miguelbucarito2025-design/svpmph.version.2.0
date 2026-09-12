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
 * Obtiene el contenido dinámico del <select> de tipos de programa del DOM
 */
function obtenerOpcionesSelect() {
  const selectDOM = document.getElementById("programa-id");
  return selectDOM ? selectDOM.innerHTML : "";
}

/**
 * Genera dinámicamente el nodo del formulario para Agregar Programa (con Vista Previa de Imágenes)
 */
function crearFormularioAgregar() {
  const contenedor = document.createElement("div");
  const opciones = obtenerOpcionesSelect();

  contenedor.innerHTML = `
    <h2 class="h2-agregar success">
      <svg class="icono-outline"><use href="#icono-agregar"></use></svg>
      Agregar Asignatura
    </h2>
      
    <form method="post" class="form-usuario" id="formAgregar">
      <div class="grid-form">
        
        <div class="campo-grupo">
          <label>Nombre *</label>
          <input type="text" id="nombre" name="asignatura" required>
        </div>
       
        <div class="campo-grupo">
          <label>Codigo *</label>
          <input type="text" name="codigo">
        </div>

      <div class="campo-grupo">
          <label>Horas Teoricas *</label>
          <input type="number" id="" name="teoricas" required>
        </div>
       
        <div class="campo-grupo">
          <label>Horas Practicas *</label>
          <input type="number" name="practicas" required>
        </div>

        <div class="campo-grupo">
          <label>Programa</label>
          <select name="programa_id" required>
            ${opciones}
          </select>
        </div>

       

      </div>
    </form>
    <hr />
    <div class="container-button-anuncion">
      <button type="submit" class="success button-anuncio" form="formAgregar">
        <svg class="icono-outline"><use href="#icono-agregar"></use></svg>
        Vamos
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
 * Genera el nodo del formulario para Editar Programa
 */
function crearFormularioEditar(datos) {
  const contenedor = document.createElement("div");
  const opciones = obtenerOpcionesSelect();

  contenedor.innerHTML = `
    <h2 class="info">
      ${svgEditar}
      Editar Programa
    </h2>

          
    <form method="post" class="form-usuario" id="formEditar">
      <input type="hidden" name="id" value="${datos.id}">

      <div class="grid-form">
        <div class="campo-grupo">
          <label>Nombre *</label>
          <input type="text" id="nombre" name="asignatura" value="${escaparHTML(datos.asignatura)}" required>
        </div>
        <div class="campo-grupo">
          <label>Codigo *</label>
          <input type="text" name="codigo" value="${escaparHTML(datos.codigo)}">
        </div>

      <div class="campo-grupo">
          <label>Horas Teoricas *</label>
          <input type="number" id="" name="teoricas" required value="${escaparHTML(datos.horas_teoricas)}">
        </div>
       
        <div class="campo-grupo">
          <label>Horas Practicas *</label>
          <input type="number" name="practicas" required value="${escaparHTML(datos.horas_practicas)}">
        </div>

        <div class="campo-grupo">
          <label>Programa</label>
          <select name="programa_id" required>
          <option value="${escaparHTML(datos.programa_id)}" >Mantener </option>
          ${opciones}
          </select>
        </div>

        

      </div>
    </form>
    <hr />
    <div class="container-button-anuncion">
      <button type="submit" class="success button-anuncio" form="formEditar">
        Actualizar
      </button>
    </div>
  `;

  vincularPrevisualizacionArchivos(contenedor);
  return contenedor;
}

/**
 * Escucha los cambios de los inputs de archivos dentro de los elementos creados en memoria
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

  const formulario = nodoPadre.querySelector("form");
  if (formulario) {
    formulario.addEventListener("reset", () => {
      inputsFile.forEach((input) => {
        const targetId = input.getAttribute("data-target");
        const imgTarget = nodoPadre.querySelector(`#${targetId}`);
        if (imgTarget) {
          imgTarget.src = "";
          imgTarget.style.display = "none";
        }
      });
    });
  }
}

// INICIALIZACIÓN DEL BOTÓN DE AGREGAR
let agregarPrograma = document.getElementById("agregar-programa");
if (agregarPrograma) {
  agregarPrograma.addEventListener("click", () => {
    const formAgregarNodo = crearFormularioAgregar();

    AlertApp.show("", formAgregarNodo, "", null, {
      claseExtra: "ventana-modal",
      btnTexto: "Cerrar",
      btnIcono: svgCerrar,
      btnClase: "btn-ghost-danger",
      ocultarHeader: true,
    });

    inicializarFormulario("formAgregar", "asignaturas/guardar", (res) => {
      AlertApp.show("Guardado con exito", "", "success");
      refrescarTabla(true);
    });
  });
}

function refrescarTabla(resetearPagina = true) {
  const inputOffset = document.getElementById("offset");

  if (resetearPagina && inputOffset) {
    inputOffset.value = 0;
  }

  const control = document.getElementById("programa-buscar") || inputOffset;
  if (control) {
    control.dispatchEvent(new Event("input"));
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const idsControles = ["limit", "intput-buscar", "offset", "programa-id"];
  const url = "asignaturas/paginar";

  configurarTablaDinamica(
    idsControles,
    url,
    (respuesta) => {
      const listaProgramas = respuesta?.data || [];
      const totalRegistros =
        typeof respuesta?.total === "number"
          ? respuesta.total
          : listaProgramas.length;

      const inputOffset = document.getElementById("offset");
      const inputLimit = document.getElementById("limit");

      const offsetSQL = parseInt(inputOffset?.value) || 0;
      const limite = parseInt(inputLimit?.value) || 10;
      const paginaActual = Math.floor(offsetSQL / limite) + 1;

      renderizarTabla(listaProgramas, paginaActual, limite);
      actualizarPaginacion(totalRegistros, paginaActual, limite);
    },
    { tiempoDebounce: 300 },
  );
});

function renderizarTabla(datos, paginaActual = 1, limitePorPagina = 10) {
  const tbody = document.getElementById("tablaCuerpo");
  if (!tbody) return;

  tbody.innerHTML = "";

  if (!Array.isArray(datos) || datos.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="text-center py-3 text-muted">
          No se encontraron registros de programas.
        </td>
      </tr>`;
    return;
  }

  let numeroRegistro = (paginaActual - 1) * limitePorPagina + 1;
  const fragmento = document.createDocumentFragment();

  datos.forEach((datos) => {
    const fila = document.createElement("tr");

    fila.innerHTML = `
      <td class="btn-fila-table">
        <input type="checkbox" class="form-check-input check-programa" data-id="${datos.id}">
        <button type="button" class="btn-editar">
          ${svgEditar}
        </button>
      </td>
      <td>${numeroRegistro}</td>
      <td>${escaparHTML(datos.asignatura)}</td>
      <td>${escaparHTML(datos.codigo)}</td>
    `;

    const btnEditar = fila.querySelector(".btn-editar");
    btnEditar.addEventListener("click", () => {
      abrirModalEdicion(datos);
    });

    fragmento.appendChild(fila);
    numeroRegistro++;
  });

  tbody.appendChild(fragmento);
  vincularEventosFilas();
}

function abrirModalEdicion(programa) {
  const formEditar = crearFormularioEditar(programa);

  AlertApp.show("", formEditar, "", null, {
    claseExtra: "ventana-modal",
    btnTexto: "Cerrar",
    btnIcono: svgCerrar,
    btnClase: "btn-ghost-danger",
    ocultarHeader: true,
  });

  inicializarFormulario("formEditar", "asignaturas/actualizar", (res) => {
    AlertApp.show("Asignatura actualizada con éxito", "", "success");
    refrescarTabla(false);
  });
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
  const fin = Math.min(pagActual * limite, total);

  textoMetrica.textContent = `Total en DB:  ${total}`;

  const irAPagina = (destinoPagina) => {
    if (!inputOffset) return;

    const numPaginaTarget = Number(destinoPagina);
    const offsetSQL = (numPaginaTarget - 1) * limite;

    const pagActualTd = document.getElementById("pag-actual");
    if (pagActualTd) {
      pagActualTd.textContent = "Pagina " + numPaginaTarget;
    }

    inputOffset.value = offsetSQL;
    inputOffset.dispatchEvent(new Event("input"));
  };

  if (btnRetroceder) {
    if (pagActual <= 1) {
      btnRetroceder.style.display = "none";
      btnRetroceder.disabled = true;
      btnRetroceder.onclick = null;
    } else {
      btnRetroceder.style.display = "";
      btnRetroceder.disabled = false;
      btnRetroceder.removeAttribute("disabled");

      btnRetroceder.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        irAPagina(pagActual - 1);
      };
    }
  }

  if (btnAvanzar) {
    if (pagActual >= totalPaginas) {
      btnAvanzar.style.display = "none";
      btnAvanzar.disabled = true;
      btnAvanzar.onclick = null;
    } else {
      btnAvanzar.style.display = "";
      btnAvanzar.disabled = false;
      btnAvanzar.removeAttribute("disabled");

      btnAvanzar.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        irAPagina(pagActual + 1);
      };
    }
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
  const filas = document.querySelectorAll("#tablaCuerpo tr");

  const actualizarEstadoFila = (fila, checkbox) => {
    if (checkbox.checked) {
      fila.classList.add("fila-seleccionada");
    } else {
      fila.classList.remove("fila-seleccionada");
    }
  };

  filas.forEach((fila) => {
    const checkbox = fila.querySelector(".check-programa");
    if (!checkbox) return;

    fila.onclick = (e) => {
      if (
        e.target.tagName === "INPUT" ||
        e.target.closest("button") ||
        e.target.closest("a")
      ) {
        return;
      }

      checkbox.checked = !checkbox.checked;
      actualizarEstadoFila(fila, checkbox);
      checkbox.dispatchEvent(new Event("change"));
    };

    checkbox.onchange = () => {
      actualizarEstadoFila(fila, checkbox);
    };
  });

  if (checkTodos) {
    checkTodos.checked = false;
    checkTodos.onclick = () => {
      const checksFila = document.querySelectorAll(".check-programa");
      checksFila.forEach((cb) => {
        cb.checked = checkTodos.checked;
        const filaPadre = cb.closest("tr");
        if (filaPadre) {
          actualizarEstadoFila(filaPadre, cb);
        }
      });
    };
  }
}

function eliminarProgramasSeleccionados() {
  const checksMarcados = document.querySelectorAll(".check-programa:checked");

  const idsParaEliminar = Array.from(checksMarcados).map((cb) =>
    cb.getAttribute("data-id"),
  );

  if (idsParaEliminar.length === 0) {
    AlertApp.show(
      "Atención",
      "Debe seleccionar al menos un registro para eliminar.",
      "warning",
    );
    return;
  }

  AlertApp.show(
    "Atención",
    "Seguro que deceas Eliminar?. Esta acción no la puedrás deshacer . Confirme la Acceción por favor",
    "warning",
    () => {
      const payload = JSON.stringify({ ids: idsParaEliminar });
      const tokenCSRF = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

      fetch("asignatura/eliminacionMultiple", {
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
              "Los registros seleccionados han sido removidos.",
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
          console.error("Error al despachar eliminación masiva:", err);
          AlertApp.show("Error", "Ocurrió una falla en el servidor.", "error");
        });
    },
    {
      mostrarCancelar: true,
      btnTexto: "Si Seguro",
      btnClase: "btn-danger-red", // Botón rojo
      btnCancelarTexto: "No Losiento",
      btnCancelarClase: "btn-cancel",
    },
  );
}

document.addEventListener("DOMContentLoaded", () => {
  const btnEliminarMasivo = document.getElementById("btnEliminarMasivo");
  if (btnEliminarMasivo) {
    btnEliminarMasivo.addEventListener("click", eliminarProgramasSeleccionados);
  }
});
