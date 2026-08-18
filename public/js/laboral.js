configurarBusquedaTiempoReal(
  "institucion_id",
  "buscar/cargos",
  function (datosAgremiado) {
    const contenedor = document.getElementById("cargo_id");

    if (!datosAgremiado || datosAgremiado.length === 0) {
      contenedor.innerHTML = ` <option value="">no se encontro nada relacionado</option>`;

      return;
    }

    const m = datosAgremiado[0] || datosAgremiado;
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

inicializarFormulario("perfilLaboral", ruta, (res) => {
  setTimeout(() => {
    window.location.href = "laboral";
  }, 1500);
});
