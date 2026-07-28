/**
 * Función global para realizar peticiones HTTP (AJAX) de forma flexible
 * @param {string} url - La ruta del archivo PHP (ej: 'controllers/usuario.php')
 * @param {string} metodo - 'GET', 'POST', 'PUT', 'DELETE'
 * @param {object|null} datos - El objeto con los datos que quieres enviar (para POST/PUT)
 * @returns {Promise<object>} - Devuelve la respuesta del servidor convertida en JSON
 */
async function realizarPeticion(url, metodo = "GET", datos = null) {
  const opciones = {
    method: metodo.toUpperCase(),
    headers: {
      "X-Requested-With": "XMLHttpRequest",
      // 💡 Quitamos el 'Content-Type: application/json' para que actúe como un formulario normal
    },
  };

  if ((opciones.method === "POST" || opciones.method === "PUT") && datos) {
    // Si ya es un FormData (por ejemplo, si envías un formulario HTML entero), lo dejamos igual
    if (datos instanceof FormData) {
      opciones.body = datos;
    } else {
      // 💡 Si es un objeto común de JS, lo convertimos automáticamente a formato de formulario ($_POST)
      const formularioVirtual = new FormData();
      for (const llave in datos) {
        formularioVirtual.append(llave, datos[llave]);
      }
      opciones.body = formularioVirtual;
    }
  }

  try {
    const respuesta = await fetch(url, opciones);

    // 💡 Si el estatus HTTP no es un éxito (200-299)
    if (!respuesta.ok) {
      try {
        const errorJson = await respuesta.json();

        // 💡 Creamos un objeto de error personalizado para JS
        const miError = new Error(errorJson.message || "Error en el proceso.");
        miError.status = respuesta.status; // Guardamos el 400, 403, 500, etc.

        throw miError; // Lo mandamos al formulario
      } catch (jsonError) {
        // Por si el servidor escupe un error fatal HTML (Sintaxis PHP rota)
        if (jsonError instanceof SyntaxError) {
          const errorCritico = new Error(
            `Error crítico en el backend (Código ${respuesta.status})`,
          );
          errorCritico.status = respuesta.status;
          throw errorCritico;
        }
        throw jsonError; // Propaga el error estructurado si ya se armó arriba
      }
    }

    // Si todo salió excelente (HTTP 200), parseamos y devolvemos la data limpia
    return await respuesta.json();
  } catch (error) {
    // Registramos en la consola de CodeLink para depuración interna
    console.error("Error en la petición AJAX:", error);
    // Re-lanzamos el error para que llegue vivo al catch de formEnv()
    throw error;
  }
}
