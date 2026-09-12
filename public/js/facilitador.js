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

const svgOferta = `
  <svg class="icono-outline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
  </svg>
`;

const svgEliminar = `
  <svg class="icono-outline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <polyline points="3 6 5 6 21 6"></polyline>
    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
  </svg>
`;

/**
 * FORMULARIO 1: AGREGAR NUEVA OFERTA AL FACILITADOR
 */
function formularioEdicionCard(datos) {
  const contenedor = document.createElement("div");
  contenedor.className = "modal-ofertas-container";

  if (!datos) {
    contenedor.innerHTML =
      '<p class="sin-registros">No se recibieron datos del registro.</p>';
    return contenedor;
  }

  contenedor.innerHTML = `
    <h2 class="h2-agregar success">
      <svg class="icono-outline"><use href="#icono-agregar"></use></svg>
      Agregar Oferta al Facilitador
    </h2>

    <form method="post" id="formEditarFacilitador" action="facilitador/guardar">
      <input type="hidden" id="asignacion_id" name="cuenta_id" value="${datos.id || datos.cuenta_id}">

      <div class="grid-form">
        <div class="campo-grupo">
          <label>Usuario / Docente</label>
          <input type="text" value="${escaparHTML((datos.nombre || "") + " " + (datos.apellido || ""))}" readonly disabled style="background: #e9ecef;">
        </div>

        <div class="campo-grupo">
          <label>Núcleo *</label>
          <select name="nucleo_id" id="nucleo_id_agregar" required>
            <option value="">-- Seleccione Núcleo --</option>
          </select>
        </div> 

        <div class="campo-grupo">
          <label>Oferta Académica *</label>
          <select name="oferta_id" id="oferta_id_agregar" required>
            <option value="">-- Seleccione primero un núcleo --</option>
          </select>
          
          <div id="loader" class="cargando-oculto">
            <svg class="icono-girando" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
            </svg>
            <span>Cargando ofertas...</span>
          </div>
        </div>
      </div>
    </form>

    <div class="container-button-anuncion mt-3">
      <button type="submit" class="success button-anuncio" form="formEditarFacilitador">
        <svg class="icono-outline"><use href="#icono-agregar"></use></svg>
        Asignar Oferta
      </button>
    </div>
  `;

  // Cargar lista de Núcleos inicial
  busquedaRapida(null, "nucleo/buscar", function (respuesta) {
    const selectNucleo = contenedor.querySelector("#nucleo_id_agregar");
    if (!selectNucleo || !respuesta.data) return;

    respuesta.data.forEach((item) => {
      selectNucleo.innerHTML += `<option value="${item.id}">${item.nucleo || item.nombre}</option>`;
    });
  });

  return contenedor;
}

/**
 * FORMULARIO 2: EDICIÓN / DESVINCULACIÓN DE OFERTAS ASIGNADAS (Corregido)
 */
function formularioGestionOfertasCard(datos) {
  const contenedor = document.createElement("div");
  contenedor.className = "modal-ofertas-container";

  const ofertas =
    Array.isArray(datos.lista_ofertas) && datos.lista_ofertas.length > 0
      ? datos.lista_ofertas
      : [datos];

  const ofertaInicial = ofertas[0] || datos;

  contenedor.innerHTML = `
    <h2 class="h2-agregar info">
      ${svgEditar}
      Gestionar Ofertas Asignadas
    </h2>

    <form method="post" id="formGestionOferta" action="facilitador/actualizar">
      <!-- ID del Facilitador/Cuenta -->
      <input type="hidden" id="cuenta_id_oferta" name="cuenta_id" value="${datos.cuenta_id}">

      <div class="grid-form">
        <div class="campo-grupo mb-3">
          <label for="select_oferta_a_editar" style="font-weight: 600; color: #333;">Seleccione la Oferta a Modificar o Eliminar *</label>
          <select id="select_oferta_a_editar" name="oferta_actual" class="form-select mt-1" required>
            <option value="">-- Cargando ofertas asignadas... --</option>
          </select>
        </div>

        <div class="campo-grupo">
          <label>Cambiar a Núcleo *</label>
          <!-- CORREGIDO: Se añadió el atributo name="nucleo_id" -->
          <select name="nucleo_id" id="nucleo_id_edit" required>
            <option value="">-- Seleccione Núcleo --</option>
          </select>
        </div> 

        <div class="campo-grupo">
          <label>Nueva Oferta Académica *</label>
          <select name="oferta_id_nueva" id="oferta_id_edit" required>
            <option value="${ofertaInicial.oferta_id ?? datos.oferta_id ?? ""}">
              ${ofertaInicial.programa || ofertaInicial.programa_nombre || datos.programa || "-- Seleccione primero un núcleo --"}
            </option>
          </select>
          <div id="loader" class="cargando-oculto">
            <svg class="icono-girando" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
            </svg>
            <span>Cargando datos...</span>
          </div>
        </div>
      </div>
    </form>

    <div class="container-button-anuncion d-flex justify-content-between align-items-center mt-3">
      <button type="button" class="danger button-anuncio" id="btnEliminarOfertaEspecifica" style="background-color: #dc3545; color: #fff;">
        ${svgEliminar}
        Eliminar esta Oferta
      </button>

      <button type="submit" class="success button-anuncio" form="formGestionOferta">
        <svg class="icono-outline"><use href="#icono-agregar"></use></svg>
        Guardar Cambios de Oferta
      </button>
    </div>
  `;

  // Búsqueda AJAX para poblar el select de ofertas que dicta este facilitador
  busquedaRapida(
    datos.cuenta_id || datos.id,
    "facilitador/opciones",
    function (respuesta) {
      const selectOfertasAsignadas = contenedor.querySelector(
        "#select_oferta_a_editar",
      );
      if (!selectOfertasAsignadas) return;

      selectOfertasAsignadas.innerHTML = `<option value="">-- Seleccione --</option>`;
      const listaOpciones = respuesta?.data || respuesta || [];

      if (Array.isArray(listaOpciones) && listaOpciones.length > 0) {
        listaOpciones.forEach((item) => {
          const selected =
            item.id == (ofertaInicial.oferta_id || datos.oferta_id)
              ? "selected"
              : "";
          selectOfertasAsignadas.innerHTML += `<option value="${item.id}" ${selected}>${item.programa || item.nombre}</option>`;
        });
      }
    },
    { nombreParametro: "cuenta_id" },
  );

  // Cargar lista de Núcleos
  busquedaRapida(null, "nucleo/buscar", function (respuesta) {
    const selectNucleo = contenedor.querySelector("#nucleo_id_edit");
    if (!selectNucleo || !respuesta.data) return;

    respuesta.data.forEach((item) => {
      const selected =
        item.id == (ofertaInicial.nucleo_id || datos.nucleo_id)
          ? "selected"
          : "";
      selectNucleo.innerHTML += `<option value="${item.id}" ${selected}>${item.nucleo || item.nombre}</option>`;
    });

    const nucleoActual = ofertaInicial.nucleo_id || datos.nucleo_id;
    if (nucleoActual) {
      cargarOfertasPorNucleo(
        "oferta_id_edit",
        nucleoActual,
        ofertaInicial.oferta_id || datos.oferta_id,
      );
    }
  });

  // Listener para eliminar la oferta seleccionada
  const btnEliminarOferta = contenedor.querySelector(
    "#btnEliminarOfertaEspecifica",
  );
  if (btnEliminarOferta) {
    btnEliminarOferta.addEventListener("click", () => {
      const cuentaId = contenedor.querySelector("#cuenta_id_oferta").value;
      const selectOferta = contenedor.querySelector("#select_oferta_a_editar");
      const ofertaId = selectOferta ? selectOferta.value : "";

      if (!ofertaId) {
        AlertApp.show(
          "Atención",
          "Debe seleccionar una oferta válida para eliminar.",
          "warning",
        );
        return;
      }

      AlertApp.show(
        "¿Eliminar esta asignación?",
        "Se desvinculará únicamente la oferta seleccionada de este facilitador.",
        "warning",
        () => {
          const tokenCSRF =
            document
              .querySelector('meta[name="csrf-token"]')
              ?.getAttribute("content") || "";

          fetch("facilitador/eliminar", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-Requested-With": "XMLHttpRequest",
              "X-CSRF-TOKEN": tokenCSRF,
            },
            body: JSON.stringify({ cuenta_id: cuentaId, id: ofertaId }),
          })
            .then((res) => res.json())
            .then((res) => {
              if (res.status === "success" || res.exito) {
                AlertApp.show(
                  "Éxito",
                  "La oferta fue eliminada correctamente.",
                  "success",
                );
                refrescarTabla(false);
              } else {
                AlertApp.show(
                  "Error",
                  res.mensaje || "No se pudo eliminar la oferta.",
                  "error",
                );
              }
            })
            .catch((err) => {
              console.error("Error al eliminar oferta:", err);
              AlertApp.show(
                "Error",
                "Error de servidor al procesar la solicitud.",
                "error",
              );
            });
        },
        {
          mostrarCancelar: true,
          btnTexto: "Sí, desvincular",
          btnClase: "btn-danger-red",
          btnCancelarTexto: "Cancelar",
          btnCancelarClase: "btn-cancel",
        },
      );
    });
  }

  return contenedor;
}

/**
 * HELPER: Poblar select de Ofertas
 */
function poblarSelectOfertas(targetSelectId, respuesta) {
  const contenedor = document.getElementById(targetSelectId);
  if (!contenedor) return;

  if (!respuesta.data || respuesta.data.length === 0) {
    contenedor.innerHTML = `<option value="">No se encontraron ofertas en este núcleo</option>`;
    return;
  }

  contenedor.innerHTML = "";
  respuesta.data.forEach((o) => {
    contenedor.innerHTML += `<option value="${o.id}">${o.programa_nombre || o.programa || o.nombre}</option>`;
  });
}

/**
 * HELPER: Cargar Ofertas por Núcleo
 */
function cargarOfertasPorNucleo(
  targetSelectId,
  nucleoId,
  ofertaIdSeleccionada = null,
) {
  busquedaRapida(
    nucleoId,
    "oferta/buscar",
    function (res) {
      const select = document.getElementById(targetSelectId);
      if (!select || !res.data) return;

      select.innerHTML = `<option value="">-- Seleccione Oferta --</option>`;
      res.data.forEach((o) => {
        const selected = o.id == ofertaIdSeleccionada ? "selected" : "";
        select.innerHTML += `<option value="${o.id}" ${selected}>${o.programa_nombre || o.programa} - ${o.cohorte || "Oferta"}</option>`;
      });
    },
    { nombreParametro: "nucleo_id" },
  );
}

/**
 * APERTURA DE MODALES
 */
function abrirModalEditarFacilitador(datos) {
  const formHtml = formularioEdicionCard(datos);

  AlertApp.show("", formHtml, "", null, {
    claseExtra: "ventana-modal ancho-auto",
    btnTexto: "Cerrar",
    btnIcono: svgCerrar,
    btnClase: "btn-ghost-danger",
    ocultarHeader: true,
  });

  const ruta = document.getElementById("formEditarFacilitador").action;
  inicializarFormulario("formEditarFacilitador", ruta, () => {
    AlertApp.show("Oferta Asignada con Éxito", "", "success");
    refrescarTabla(false);
  });

  // Búsqueda reactiva al elegir Núcleo en el modal AGREGAR
  configurarBusquedaTiempoReal(
    "nucleo_id_agregar",
    "oferta/buscar",
    function (respuesta) {
      poblarSelectOfertas("oferta_id_agregar", respuesta);
    },
    {
      nombreParametro: "nucleo_id",
      tiempoDebounce: 300,
    },
  );
}

function abrirModalEditarOferta(datos) {
  const formHtml = formularioGestionOfertasCard(datos);

  AlertApp.show("", formHtml, "", null, {
    claseExtra: "ventana-modal ancho-auto",
    btnTexto: "Cerrar",
    btnIcono: svgCerrar,
    btnClase: "btn-ghost-danger",
    ocultarHeader: true,
  });

  const ruta = document.getElementById("formGestionOferta").action;
  inicializarFormulario("formGestionOferta", ruta, () => {
    AlertApp.show("Oferta Actualizada con Éxito", "", "success");
    refrescarTabla(false);
  });

  // Búsqueda reactiva al elegir Núcleo en el modal GESTIONAR
  configurarBusquedaTiempoReal(
    "nucleo_id_edit",
    "oferta/buscar",
    function (respuesta) {
      poblarSelectOfertas("oferta_id_edit", respuesta);
    },
    {
      nombreParametro: "nucleo_id",
      tiempoDebounce: 300,
    },
  );
}

function abrirModalCrear() {
  const formHtml = formularioCrearCard();

  AlertApp.show("", formHtml, "", null, {
    claseExtra: "ventana-modal ancho-auto",
    btnTexto: "Cerrar",
    btnIcono: svgCerrar,
    btnClase: "btn-ghost-danger",
    ocultarHeader: true,
  });

  const ruta = document.getElementById("formCrearFacilitador").action;
  inicializarFormulario("formCrearFacilitador", ruta, () => {
    AlertApp.show("Facilitador Asignado con Éxito", "", "success");
    refrescarTabla(true);
  });
}

/**
 * RENDERIZADO EN TARJETAS
 */
function renderizarTabla(datos) {
  const contenedorGrid = document.getElementById("contenedorOfertasCards");
  if (!contenedorGrid) return;

  contenedorGrid.innerHTML = "";

  if (!Array.isArray(datos) || datos.length === 0) {
    contenedorGrid.innerHTML = `
      <div class="empty-state text-center py-4 text-muted">
        No se encontraron Facilitadores registrados.
      </div>`;
    return;
  }

  const fragmento = document.createDocumentFragment();

  datos.forEach((item) => {
    const card = document.createElement("div");
    card.className = "card-oferta card-rol";

    const imagen = item.foto || item.img || "public/img/default-avatar.png";
    const nombreCompleto =
      `${item.nombre || ""} ${item.s_nombre || ""} ${item.apellido || ""} ${item.s_apellido || ""}`.trim();
    const estadoBadge =
      item.estado === 1
        ? '<span class="badge status-activo">Activo</span>'
        : '<span class="badge status-inactivo">Inactivo</span>';

    // 1. EVALUAR SI EL USUARIO TIENE OFERTAS ASIGNADAS
    const tieneListaOfertas =
      Array.isArray(item.lista_ofertas) && item.lista_ofertas.length > 0;
    const tieneOfertaSimple = Boolean(
      item.programa_nombre || item.programa || item.oferta_id,
    );
    const totalOfertas =
      Number(item.total_ofertas) ||
      (tieneListaOfertas
        ? item.lista_ofertas.length
        : tieneOfertaSimple
          ? 1
          : 0);
    const tieneOfertas = totalOfertas > 0;

    // 2. CONSTRUIR EL BOTÓN DE EDICIÓN / GESTIÓN SOLO SI TIENE OFERTAS
    const botonGestionarOfertas = tieneOfertas
      ? `<!-- Botón 2: Gestionar / Editar Ofertas -->
         <button type="button" class="btn-editar-card btn-editar btn-oferta btn-editar-oferta" title="Gestionar Ofertas">
           ${svgEditar}
         </button>`
      : "";

    // 3. RENDERIZADO DE LA LISTA DE PROGRAMAS
    let htmlListaOfertas = "";
    if (tieneListaOfertas) {
      htmlListaOfertas =
        '<ol class="lista-ofertas-tarjeta mb-2" style="padding-left: 18px; margin-top: 4px;">';
      item.lista_ofertas.forEach((of) => {
        htmlListaOfertas += `
          <li class="small">
            <strong>${escaparHTML(of.programa || of.programa_nombre || "Programa")}</strong>
            <span class="text-muted">(${escaparHTML(of.nucleo || of.nucleo_nombre || "Núcleo")})</span>
          </li>`;
      });
      htmlListaOfertas += "</ol>";
    } else if (tieneOfertaSimple) {
      htmlListaOfertas = `
        <ol class="lista-ofertas-tarjeta mb-2" style="padding-left: 18px; margin-top: 4px;">
          <li class="small">
            <strong>${escaparHTML(item.programa_nombre || item.programa)}</strong>
            <span class="text-muted">(${escaparHTML(item.nucleo_nombre || item.nucleo || "Núcleo")})</span>
          </li>
        </ol>`;
    } else {
      htmlListaOfertas =
        '<p class="text-muted small mb-2">Sin ofertas asignadas actualmente</p>';
    }

    card.innerHTML = `
      <div class="card-oferta-header">
        <input type="checkbox" class="form-check-input check-oferta" data-id="${item.id}">
        <div class="container-button-card-header">
          <!-- Botón 1: Agregar Nueva Oferta (Siempre visible) -->
          <button type="button" class="btn-editar-card btn-guardar-card btn-datos" title="Agregar Oferta">
            <svg class="icono-outline"><use href="#icono-agregar"></use></svg>
          </button>
        
          ${botonGestionarOfertas}
        </div>
      </div>

      <div class="card-img">
        <img src="${escaparHTML(imagen)}" alt="Avatar">
      </div>

      <div class="card-oferta-body">
        <div class="container-target-usuario">
          <div class="card-usuario-header">
            <div class="card-usuario-main">
              <h4 class="card-usuario-nombre">${escaparHTML(nombreCompleto)}</h4>
              <span class="card-usuario-user">@${escaparHTML(item.usuario || "")} • ${escaparHTML(item.rol_asignado || item.rol || "Facilitador")}</span>
            </div>
            ${estadoBadge}
          </div>
          
          <div class="card-usuario-body">
            <div class="seccion-ofertas-asignadas">
              <p class="mb-0"><strong>Programas Asignados (${totalOfertas}):</strong></p>
              ${htmlListaOfertas}
            </div>
            <p class="mb-1"><strong>Cédula:</strong> V-${escaparHTML(String(item.id_cedula || ""))}</p>
            <p class="mb-1"><strong>Teléfono:</strong> ${escaparHTML(item.tlf || "N/A")}</p>
            <p class="mb-1"><strong>Correo:</strong> ${escaparHTML(item.correo || "")}</p>
          </div>
          
          <div class="card-usuario-footer mt-2">
            <small>Ingreso: ${escaparHTML(item.creado_en || item.ingreso || "")}</small>
          </div>
        </div>
      </div>
    `;

    // Evento Botón 1: Agregar Oferta
    const btnDatos = card.querySelector(".btn-datos");
    if (btnDatos) {
      btnDatos.addEventListener("click", (e) => {
        e.stopPropagation();
        abrirModalEditarFacilitador(item);
      });
    }

    // Evento Botón 2: Gestionar / Eliminar Ofertas (Solo si el botón existe)
    const btnEditarOferta = card.querySelector(".btn-editar-oferta");
    if (btnEditarOferta) {
      btnEditarOferta.addEventListener("click", (e) => {
        e.stopPropagation();
        abrirModalEditarOferta(item);
      });
    }

    fragmento.appendChild(card);
  });

  contenedorGrid.appendChild(fragmento);
  vincularEventosFilas();
}
/**
 * INICIALIZACIÓN Y PAGINACIÓN
 */
document.addEventListener("DOMContentLoaded", () => {
  const idsControles = ["limit", "offset", "intput-buscar"];
  const url = "facilitador/paginar";

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
      const limite = parseInt(inputLimit?.value) || 3;
      const paginaActual = Math.floor(offsetSQL / limite) + 1;

      renderizarTabla(lista);
      actualizarPaginacion(totalRegistros, paginaActual, limite);
    },
    { tiempoDebounce: 300 },
  );

  const btnNuevo = document.getElementById("btnNuevoFacilitador");
  if (btnNuevo) btnNuevo.addEventListener("click", abrirModalCrear);

  const btnEliminarMasivo = document.getElementById("btnEliminarMasivo");
  if (btnEliminarMasivo)
    btnEliminarMasivo.addEventListener("click", eliminarOfertasSeleccionadas);
});

function refrescarTabla(resetearPagina = true) {
  const inputOffset = document.getElementById("offset");
  if (resetearPagina && inputOffset) inputOffset.value = 0;
  const control = document.getElementById("intput-buscar") || inputOffset;
  if (control) control.dispatchEvent(new Event("input"));
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
    inputOffset.value = (Number(destinoPagina) - 1) * limite;
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
      "Debe seleccionar al menos un registro para eliminar.",
      "warning",
    );
    return;
  }

  AlertApp.show(
    "Atención",
    "¿Seguro que deseas desvincular a los facilitadores seleccionados?",
    "warning",
    () => {
      const payload = JSON.stringify({ ids: idsParaEliminar });
      const tokenCSRF =
        document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute("content") || "";

      fetch("facilitador/eliminar", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": tokenCSRF,
        },
        body: payload,
      })
        .then((res) => res.json())
        .then((res) => {
          if (res.status === "success" || res.exito) {
            AlertApp.show(
              "Eliminado",
              "Los facilitadores seleccionados han sido desvinculados.",
              "success",
            );
            refrescarTabla(false);
          } else {
            AlertApp.show(
              "Error",
              res.mensaje || "No se pudo eliminar.",
              "error",
            );
          }
        })
        .catch((err) => {
          console.error("Error al eliminar:", err);
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

  panelTools.addEventListener("click", (e) => e.stopPropagation());
  document.addEventListener("click", () => {
    if (!panelTools.classList.contains("is-hidden")) cerrarPanel();
  });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && !panelTools.classList.contains("is-hidden"))
      cerrarPanel();
  });
});
