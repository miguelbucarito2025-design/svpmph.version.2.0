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
  const selectDOM = document.getElementById("tipo-programa");
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
      Agregar Programa
    </h2>
            

    <form method="post" class="form-usuario" id="formProgramas" enctype="multipart/form-data">
      <div class="grid-form">
        <div class="campo-grupo">
          <label>Nombre *</label>
          <input type="text" id="nombre" name="programa" required>
        </div>
       
        <div class="campo-grupo">
          <label>Duración</label>
          <input type="text" name="duracion">
        </div>
        <div class="campo-grupo container-textarea">
          <label>Descripción</label>
          <textarea name="descripcion"></textarea>
        </div>
         <div class="campo-grupo container-textarea">
          <label>Requisitos</label>
          <textarea name="requisitos"></textarea>
        </div>
        <div class="campo-grupo">
          <label>Tipo</label>
          <select name="tipo_programa">
            ${opciones}
          </select>
        </div>

        <!-- LOGO DEL PROGRAMA -->
        <div class="campo-grupo">
          <label>Logo del Programa</label>
          <input type="file" name="logo" class="input-preview" data-target="previewLogoAdd" accept="image/*">
          
        </div>

        <!-- FONDO CERTIFICADO DOMPDF -->
        <div class="campo-grupo">
          <label>Fondo Certificado (DomPDF)</label>
          <input type="file" name="certificado" class="input-preview" data-target="previewFondoAdd" accept="image/*">
          
        </div>

      </div>
    </form>

    <div class="contaniner-img-muestras">

            <div class="contenedor-preview ">
                    <img id="previewLogoAdd" src="" alt="Previa Logo" >
            </div>

            <div class="contenedor-preview mt-2">
                      <img id="previewFondoAdd" src="" alt="Previa Fondo">
            </div>

           </div>
    <hr />
    <div class="container-button-anuncion">
      <button type="submit" class="success button-anuncio" form="formProgramas">
        <svg class="icono-outline"><use href="#icono-agregar"></use></svg>
        Vamos
      </button>
      <button type="reset" class="limpiar button-anuncio" form="formProgramas">
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
function crearFormularioEditar(programa) {
  const contenedor = document.createElement("div");
  const opciones = obtenerOpcionesSelect();

  const urlLogoActual = programa.logo;
  const urlFondoActual = programa.certificado;

  contenedor.innerHTML = `
    <h2 class="info">
      ${svgEditar}
      Editar Programa
    </h2>

           


    <form method="post" class="form-usuario" id="formEditarPrograma" enctype="multipart/form-data">
      <input type="hidden" name="id" value="${programa.id}">

      <div class="grid-form">
        <div class="campo-grupo">
          <label>Nombre *</label>
          <input type="text" id="nombre" name="programa" value="${escaparHTML(programa.programa)}" required>
        </div>
        <div class="campo-grupo">
          <label>Estado</label>
<select name="estado" >
<option value="${escaparHTML(programa.estado)}"> Mantener ${escaparHTML(programa.estado) == 1 ? "Activo " : "Desactivado"}</option>
<option value=" ${escaparHTML(programa.estado) != 1 ? 1 : 0}">  ${escaparHTML(programa.estado) != 1 ? "Activar " : "Desactivar"}</option>

</select>
        </div>
        <div class="campo-grupo">
          <label>Duración</label>
          <input type="text" name="duracion" value="${escaparHTML(programa.duracion || "")}">
        </div>
        <div class="campo-grupo container-textarea">
          <label>Descripción</label>
          <textarea name="descripcion">${escaparHTML(programa.descripcion || "")}</textarea>
        </div>
        <div class="campo-grupo container-textarea">
          <label>Requisitos</label>
          <textarea name="requisitos"> ${programa.requisitos}</textarea>
        </div>
        <div class="campo-grupo">
          <label>Tipo</label>
          <select name="tipo_programa">
            <option value="${programa.tipo_programa}">Sin cambios</option>
            ${opciones}
          </select>
        </div>

        <!-- LOGO DEL PROGRAMA (EDITAR) -->
        <div class="campo-grupo">
          <label>Actualizar Logo</label>
          <input type="file" name="logo" class="input-preview" data-target="previewLogoEdit" accept="image/*">
          
        </div>

        <!-- FONDO CERTIFICADO (EDITAR) -->
        <div class="campo-grupo">
          <label>Actualizar Fondo (DomPDF)</label>
          <input type="file" name="certificado" class="input-preview" data-target="previewFondoEdit" accept="image/*">
          
        </div>

      </div>
    </form>
    
          <div class="contaniner-img-muestras">
             <div class="contenedor-preview mt-2">
                   <img id="previewLogoEdit" src="${urlLogoActual}" alt="Previa Logo" >
             </div>
             <div class="contenedor-preview mt-2">
                   <img id="previewFondoEdit" src="${urlFondoActual}" alt="Previa Fondo" ne"  >
             </div>
          </div>

    <hr />
    <div class="container-button-anuncion">
      <button type="submit" class="success button-anuncio" form="formEditarPrograma">
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

    inicializarFormulario("formProgramas", "programas/guardar", (res) => {
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
  const idsControles = [
    "limit",
    "programa-buscar",
    "offset",
    "tipo-programa",
    "estado-id",
  ];
  const url = "programas/paginar";

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

      renderizarTablaProgramas(listaProgramas, paginaActual, limite);
      actualizarPaginacion(totalRegistros, paginaActual, limite);
    },
    { tiempoDebounce: 300 },
  );
});

function renderizarTablaProgramas(
  datos,
  paginaActual = 1,
  limitePorPagina = 10,
) {
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

  datos.forEach((programa) => {
    const fila = document.createElement("tr");
    const estadoBadge =
      programa.estado == 1
        ? '<span class="badge bg-success">Activo</span>'
        : '<span class="badge bg-secondary">Inactivo</span>';

    fila.innerHTML = `
      <td class="btn-fila-table">
        <input type="checkbox" class="form-check-input check-programa" data-id="${programa.id}">
        <button type="button" class="btn-editar">
          ${svgEditar}
        </button>
      </td>
      <td>${numeroRegistro}</td>
      <td>${escaparHTML(programa.programa)}</td>
      <td>${estadoBadge}</td>
    `;

    const btnEditar = fila.querySelector(".btn-editar");
    btnEditar.addEventListener("click", () => {
      abrirModalEdicion(programa);
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

  inicializarFormulario("formEditarPrograma", "programas/actualizar", (res) => {
    AlertApp.show("Programa actualizado con éxito", "", "success");
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

      fetch("programas/eliminacionMultiple", {
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
      btnClase: "btn-danger-red",
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
