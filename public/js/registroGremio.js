/**
 * Gestiona el llenado dinámico de Menciones, Promociones, vistas previas
 * y la lista de Requisitos según el objeto seleccionado.
 *
 * @param {HTMLElement|string} contenedorOrSelector - Contenedor principal o selector CSS.
 * @param {Object} [valoresIniciales={}] - IDs preseleccionados para edición.
 */
function cargarMencionesYPromociones(
  contenedorOrSelector,
  valoresIniciales = {},
) {
  const contenedor =
    typeof contenedorOrSelector === "string"
      ? document.querySelector(contenedorOrSelector)
      : contenedorOrSelector;

  if (!contenedor) return;

  busquedaRapida(null, "carreras/select", function (respuesta) {
    const selectMencion = contenedor.querySelector("#mencion-id");
    const selectPromocion = contenedor.querySelector("#promocion-id");

    const imgMencion = contenedor.querySelector("#mencion-img");
    const txtNombreMencion = contenedor.querySelector("#mencion-nombre");

    const imgPromocion = contenedor.querySelector("#promocion-img");
    const txtNombrePromocion = contenedor.querySelector("#promocion-nombre");

    // Referencia al elemento <ul>
    const ulRequisitos = contenedor.querySelector("#lista-requisitos");

    if (!selectMencion || !selectPromocion) return;

    const datos = respuesta?.data || {};
    const listaMenciones = Array.isArray(datos.mencion) ? datos.mencion : [];
    const listaPromociones = Array.isArray(datos.promocion)
      ? datos.promocion
      : [];

    /**
     * Llena un elemento <select> con datos.
     */
    const poblarSelect = (selectEl, items, propiedadTexto, idSeleccionado) => {
      let opcionesHTML = ``;
      items.forEach((item) => {
        const isSelected = item.id == idSeleccionado ? "selected" : "";
        opcionesHTML += `<option value="${item.id}" ${isSelected}>${item[propiedadTexto] || "Seleccione"}</option>`;
      });
      selectEl.innerHTML = opcionesHTML;
    };

    /**
     * Actualiza la imagen y el texto de vista previa.
     */
    const actualizarVistaPrevia = (item, imgEl, txtEl, textoDefecto = "") => {
      if (!imgEl || !txtEl) return;

      if (item && item.id) {
        imgEl.src = item.img || "";
        imgEl.alt = item.mencion || item.promocion || "Imagen";
        txtEl.textContent = item.mencion || item.promocion || textoDefecto;
        imgEl.style.display = "block";
      } else {
        imgEl.src = "";
        txtEl.textContent = textoDefecto;
      }
    };

    /**
     * Renderiza la lista <ul> de requisitos según la mención seleccionada.
     *
     * @param {Array<Object>} requisitos - Arreglo de requisitos devuelto por la API.
     */
    const renderizarRequisitos = (requisitos = []) => {
      if (!ulRequisitos) return;

      if (!Array.isArray(requisitos) || requisitos.length === 0) {
        ulRequisitos.innerHTML = `
          <li class="item-requisito-vacio">
            No hay requisitos consignados para esta mención.
          </li>`;
        return;
      }

      let itemsHTML = "";
      requisitos.forEach((req, index) => {
        const nombreDoc = req.nombre || req.documento || req.descripcion || req;
        itemsHTML += `
          <li class="item-requisito">
            <span class="num-requisito">${index + 1}.</span>
            <span class="nombre-requisito">${nombreDoc}</span>
          </li>`;
      });

      ulRequisitos.innerHTML = itemsHTML;
    };

    // 1. Poblamos los selectores
    poblarSelect(
      selectMencion,
      listaMenciones,
      "mencion",
      valoresIniciales.mencion_id,
    );
    poblarSelect(
      selectPromocion,
      listaPromociones,
      "promocion",
      valoresIniciales.promocion_id,
    );

    // 2. Manejador de cambio para Mención
    const alCambiarMencion = () => {
      const id = selectMencion.value;
      const mencionEncontrada = listaMenciones.find((m) => m.id == id);

      actualizarVistaPrevia(mencionEncontrada, imgMencion, txtNombreMencion);
      renderizarRequisitos(mencionEncontrada?.requisitos || []);
    };

    // 3. Manejador de cambio para Promoción
    const alCambiarPromocion = () => {
      const id = selectPromocion.value;
      const promocionEncontrada = listaPromociones.find((p) => p.id == id);

      actualizarVistaPrevia(
        promocionEncontrada,
        imgPromocion,
        txtNombrePromocion,
      );
    };

    // 4. Escuchadores de eventos
    selectMencion.addEventListener("change", alCambiarMencion);
    selectPromocion.addEventListener("change", alCambiarPromocion);

    // 5. Carga inicial
    alCambiarMencion();
    alCambiarPromocion();
  });
}

document.addEventListener("DOMContentLoaded", function () {
  cargarMencionesYPromociones("#gremio");
});

inicializarFormulario("gremio", "registro/guardar-Gremio", (res) => {
  AlertApp.show(
    "Promoción actualizada con éxito",
    "",
    "success",
    cargarMencionesYPromociones("#gremio"),
  );
});
