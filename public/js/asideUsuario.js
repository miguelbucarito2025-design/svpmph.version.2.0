document.addEventListener("DOMContentLoaded", () => {
  // Seleccionamos todos los botones/títulos de los grupos
  const botonesGrupo = document.querySelectorAll(".grupo-titulo");

  botonesGrupo.forEach((boton) => {
    boton.addEventListener("click", () => {
      // Obtenemos el contenedor <section class="menu-grupo"> actual
      const grupoActual = boton.closest(".menu-grupo");

      // Verificamos si ya está abierto
      const yaEstabaAbierto = grupoActual.classList.contains("abierto");

      // 1. PRIMER PASO: Cerramos TODOS los demás grupos que estén abiertos
      document.querySelectorAll(".menu-grupo.abierto").forEach((grupo) => {
        if (grupo !== grupoActual) {
          grupo.classList.remove("abierto");
        }
      });

      // 2. SEGUNDO PASO: Si no estaba abierto, lo abrimos; si ya lo estaba, se cierra
      if (yaEstabaAbierto) {
        grupoActual.classList.remove("abierto");
      } else {
        grupoActual.classList.add("abierto");
      }
    });
  });
});
