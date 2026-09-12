configurarBusquedaTiempoReal(
  "institucion_id",
  "buscar/cargos",
  function (datosAgremiado) {
    const contenedor = document.getElementById("cargo_id");

    if (!datosAgremiado.data || datosAgremiado.data.length === 0) {
      contenedor.innerHTML = ` <option value="">No se encontro nada relacionado</option>`;

      return;
    }

    const m = datosAgremiado.data[0] || datosAgremiado.data;
    contenedor.innerHTML = `
    <option value="${m.id}">
    ${m.cargo}
   </option>
    `;
  },
  {
    nombreParametro: "institucion_id",
    tiempoDebounce: 300, // Espera 600ms tras escribir
  },
);

const ruta = document.getElementById("perfilLaboral").action;

inicializarFormulario("perfilLaboral", ruta, () => {
  AlertApp.show("Guardado con Exito", "", "success", "laboral");
});
