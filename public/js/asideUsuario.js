document.addEventListener("DOMContentLoaded", () => {
  const botonesGrupo = document.querySelectorAll(".grupo-titulo");
  const asideMenu = document.getElementById("aside-menu");

  /**
   * Realiza un scroll suave hacia el elemento objetivo dentro de <aside id="aside-menu">
   */
  const hacerScrollDirectoEnAside = (elementoObjetivo) => {
    if (!elementoObjetivo || !asideMenu) return;

    // Esperamos 250ms a que la animación CSS del desplegable despliegue los <li>
    setTimeout(() => {
      const rectAside = asideMenu.getBoundingClientRect();
      const rectElemento = elementoObjetivo.getBoundingClientRect();

      // Distancia entre el borde superior del aside y el elemento
      const desfaseTop = rectElemento.top - rectAside.top;

      // Calculamos la posición exacta de scroll sumando el scrollTop actual del aside
      const posicionObjetivo = asideMenu.scrollTop + desfaseTop - 15;

      asideMenu.scrollTo({
        top: Math.max(0, posicionObjetivo),
        behavior: "smooth",
      });
    }, 250);
  };

  // 1. AUTO-OPEN Y SCROLL AL CARGAR LA PÁGINA
  // Buscamos directamente la clase activa que usas en tu HTML: ".linkactivo"
  const opcionActiva = asideMenu?.querySelector(".linkactivo");
  if (opcionActiva) {
    const grupoPadre = opcionActiva.closest(".menu-grupo");
    if (grupoPadre) {
      grupoPadre.classList.add("abierto");
    }
    // Hacemos scroll directo al <li> o <a> que tiene la clase .linkactivo (ej. Logs)
    hacerScrollDirectoEnAside(opcionActiva);
  }

  // 2. GESTIÓN DE ACCORDEÓN Y SCROLL AL ABRIR UN GRUPO
  botonesGrupo.forEach((boton) => {
    boton.addEventListener("click", () => {
      const grupoActual = boton.closest(".menu-grupo");
      if (!grupoActual) return;

      const yaEstabaAbierto = grupoActual.classList.contains("abierto");

      // Cerramos los demás grupos desplegados
      document.querySelectorAll(".menu-grupo.abierto").forEach((grupo) => {
        if (grupo !== grupoActual) {
          grupo.classList.remove("abierto");
        }
      });

      // Alternamos el estado del grupo seleccionado
      if (yaEstabaAbierto) {
        grupoActual.classList.remove("abierto");
      } else {
        grupoActual.classList.add("abierto");
        // Scroll suave al grupo que se acaba de desplegar
        hacerScrollDirectoEnAside(grupoActual);
      }
    });
  });

  // 3. BOTÓN MENÚ RESPONSIVE
  const btnMenu = document.getElementById("btn-menu");
  if (btnMenu && asideMenu) {
    btnMenu.addEventListener("click", () => {
      asideMenu.classList.toggle("left");
      btnMenu.classList.toggle("activo");
    });
  }
});
