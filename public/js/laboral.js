configurarBusquedaTiempoReal(
  "institucion_id",
  "buscar/cargos",
  function (datosAgremiado) {
    const contenedor = document.getElementById("cargo_id");

    if (!datosAgremiado.data || datosAgremiado.data.length === 0) {
      contenedor.innerHTML = `<option value="">No se encontro nada relacionado</option>`;
      return;
    }

    // Recorremos todos los elementos del arreglo y generamos un option por cada uno
    const opcionesHTML = datosAgremiado.data
      .map(
        (m) => `
      <option value="${m.id}">
        ${m.cargo}
      </option>
    `,
      )
      .join("");

    // Inyectamos todas las opciones en el select
    contenedor.innerHTML = opcionesHTML;
  },
  {
    nombreParametro: "institucion_id",
    tiempoDebounce: 300, // Espera 300ms tras escribir
  },
);

const ruta = document.getElementById("perfilLaboral").action;

inicializarFormulario("perfilLaboral", ruta, () => {
  AlertApp.show("Guardado con Exito", "", "success", "laboral");
});
