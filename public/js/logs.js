let tiempoRestante = 10;
let intervaloSegundos = null;
let peticionEnCurso = false;

document.addEventListener("DOMContentLoaded", () => {
  const selectFrecuencia = document.getElementById("selectFrecuencia");
  const btnRefrescar = document.getElementById("btnRefrescarManual");

  // 1. Carga inicial
  cargarLogs();

  // 2. Iniciar el ciclo del temporizador
  reiniciarTemporizador();

  // 3. Listener al cambiar la frecuencia en el <select>
  if (selectFrecuencia) {
    selectFrecuencia.addEventListener("change", () => {
      reiniciarTemporizador();
    });
  }

  // 4. Botón de refresco inmediato
  if (btnRefrescar) {
    btnRefrescar.addEventListener("click", () => {
      cargarLogs();
      if (selectFrecuencia && parseInt(selectFrecuencia.value) > 0) {
        tiempoRestante = parseInt(selectFrecuencia.value);
      }
    });
  }

  // 5. Optimización: Pausar si el usuario cambia a otra pestaña del navegador
  document.addEventListener("visibilitychange", () => {
    if (document.hidden) {
      detenerTemporizador();
    } else {
      reiniciarTemporizador();
    }
  });

  // Eventos de limpieza
  document
    .getElementById("btnVaciarApp")
    ?.addEventListener("click", () => ejecutarLimpieza("app"));
  document
    .getElementById("btnVaciarDb")
    ?.addEventListener("click", () => ejecutarLimpieza("db"));
  document
    .getElementById("btnVaciarAmbos")
    ?.addEventListener("click", () => ejecutarLimpieza("ambos"));
});

function detenerTemporizador() {
  if (intervaloSegundos) {
    clearInterval(intervaloSegundos);
    intervaloSegundos = null;
  }
}

function reiniciarTemporizador() {
  detenerTemporizador();

  const selectFrecuencia = document.getElementById("selectFrecuencia");
  const segundosConfigurados = parseInt(selectFrecuencia?.value || "10");

  if (segundosConfigurados === 0) {
    actualizarBadgeMetrica("Pausado (Manual)");
    return;
  }

  tiempoRestante = segundosConfigurados;

  intervaloSegundos = setInterval(() => {
    tiempoRestante--;
    actualizarBadgeMetrica(`Refresco en: ${tiempoRestante}s`);

    if (tiempoRestante <= 0) {
      tiempoRestante = segundosConfigurados;
      cargarLogs();
    }
  }, 1000);
}

function actualizarBadgeMetrica(textoEstado) {
  const badgeMetrica = document.getElementById("metricaGeneralLogs");
  if (!badgeMetrica) return;
  const infoPeso = badgeMetrica.dataset.pesoInfo || "Cargando...";
  badgeMetrica.textContent = `${infoPeso} • ${textoEstado}`;
}

function cargarLogs() {
  if (peticionEnCurso) return; // Evita solicitudes encimadas si el servidor tarda
  peticionEnCurso = true;

  const terminalApp = document.getElementById("terminalApp");
  const terminalDb = document.getElementById("terminalDb");
  const pesoApp = document.getElementById("pesoAppLog");
  const pesoDb = document.getElementById("pesoDbLog");
  const badgeMetrica = document.getElementById("metricaGeneralLogs");

  fetch("errror/obtenerLogs", {
    method: "GET",
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
  })
    .then((res) => res.json())
    .then((res) => {
      const payload = res.data || res;

      if (res.status === "success" || payload.status === "success") {
        if (terminalApp)
          terminalApp.innerHTML = parsearTextoALogCards(
            payload.app?.log,
            "app",
          );
        if (pesoApp) pesoApp.textContent = payload.app?.peso || "0 Bytes";

        if (terminalDb)
          terminalDb.innerHTML = parsearTextoALogCards(payload.db?.log, "db");
        if (pesoDb) pesoDb.textContent = payload.db?.peso || "0 Bytes";

        const infoPeso = `APP: ${payload.app?.peso || "0B"} | DB: ${payload.db?.peso || "0B"}`;
        if (badgeMetrica) {
          badgeMetrica.dataset.pesoInfo = infoPeso;
          const selectFrecuencia = document.getElementById("selectFrecuencia");
          const esManual = parseInt(selectFrecuencia?.value || "10") === 0;
          actualizarBadgeMetrica(
            esManual ? "Pausado (Manual)" : `Refresco en: ${tiempoRestante}s`,
          );
        }
      }
    })
    .catch((err) => {
      console.error("Error al obtener logs:", err);
    })
    .finally(() => {
      peticionEnCurso = false;
    });
}

/**
 * Convierte el texto plano del archivo .log en Tarjetas HTML interactivas
 */
function parsearTextoALogCards(textoRaw, tipoLog) {
  if (!textoRaw || textoRaw.includes("---") || textoRaw.trim() === "") {
    return `<div class="text-muted p-3 text-center" style="background:#ffffff; border-radius:6px; border:1px dashed #cbd5e1; color:#64748b; font-size:13px;">No hay registros de error almacenados en este archivo.</div>`;
  }

  const lineas = textoRaw.trim().split("\n");
  let html =
    '<div class="d-flex flex-column gap-2" style="display:flex; flex-direction:column; gap:8px;">';

  lineas.forEach((linea) => {
    if (!linea.trim()) return;

    const matchFecha = linea.match(/^\[(.*?)\]/);
    const fecha = matchFecha ? matchFecha[1] : "Fecha N/A";

    let resto = linea.replace(/^\[.*?\]\s*/, "");
    let partesContexto = resto.split("| Context:");
    let mensajePrincipal = partesContexto[0] ? partesContexto[0].trim() : resto;
    let contextoRaw = partesContexto[1] ? partesContexto[1].trim() : "";

    let badgeColor = "#64748b"; // Gris
    if (
      mensajePrincipal.includes("[404]") ||
      mensajePrincipal.includes("WARNING")
    ) {
      badgeColor = "#d97706"; // Ámbar
    } else if (
      mensajePrincipal.includes("[500]") ||
      mensajePrincipal.includes("ERROR") ||
      tipoLog === "db"
    ) {
      badgeColor = "#dc2626"; // Rojo
    }

    let htmlContexto = "";
    let htmlUsuarioEquipo = "";

    if (contextoRaw) {
      try {
        const jsonObj = JSON.parse(contextoRaw);

        // 1. Extraer metadata de usuario y equipo
        const usuario = jsonObj.usuario
          ? `👤 <strong>${escaparHTML(jsonObj.usuario)}</strong>`
          : "";
        const rol = jsonObj.rol
          ? `• <span class="text-muted">${escaparHTML(jsonObj.rol)}</span>`
          : "";
        const equipo = jsonObj.equipo
          ? `💻 ${escaparHTML(jsonObj.equipo)}`
          : "";
        const ip = jsonObj.ip
          ? `<span class="text-muted">(${escaparHTML(jsonObj.ip)})</span>`
          : "";

        if (usuario || equipo) {
          htmlUsuarioEquipo = `
                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2" style="border-top: 1px dashed #e2e8f0; font-size: 11px; color: #475569;">
                            <div>${usuario} ${rol}</div>
                            <div>${equipo} ${ip}</div>
                        </div>
                    `;
        }

        // 2. Extraer archivo y línea si existen
        if (jsonObj.file) {
          htmlContexto = `<div class="mt-1" style="font-size: 11px; background: #f1f5f9; padding: 6px 10px; border-radius: 4px; font-family: monospace; color: #334155; border-left: 3px solid ${badgeColor}; margin-top:4px;">
                        <strong>Archivo:</strong> ${escaparHTML(jsonObj.file)} (Línea: ${jsonObj.line || "N/A"})
                    </div>`;
        }
      } catch (e) {
        htmlContexto = `<div class="mt-1" style="font-size: 11px; background: #f1f5f9; padding: 6px 10px; border-radius: 4px; font-family: monospace; color: #475569; margin-top:4px;">${escaparHTML(contextoRaw)}</div>`;
      }
    }

    html += `
            <div style="background: #ffffff; border-radius: 6px; padding: 10px 14px; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="background-color: ${badgeColor}; color: #fff; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 4px;">
                        ${escaparHTML(mensajePrincipal.split(":")[0] || "LOG")}
                    </span>
                    <small style="font-size: 12px; font-weight: 500; color:#64748b;">
                        🕒 ${escaparHTML(fecha)}
                    </small>
                </div>

                <div style="font-size: 13px; font-weight: 600; color: #1e293b; margin-top: 6px;">
                    ${escaparHTML(mensajePrincipal.substring(mensajePrincipal.indexOf(":") + 1) || mensajePrincipal)}
                </div>

                ${htmlContexto}
                ${htmlUsuarioEquipo}
            </div>
        `;
  });

  html += "</div>";
  return html;
}
function ejecutarLimpieza(tipo) {
  let titulo = "¿Vaciar registros?";
  if (tipo === "app") titulo = "¿Vaciar log de Aplicación?";
  if (tipo === "db") titulo = "¿Vaciar log de Base de Datos?";
  if (tipo === "ambos") titulo = "¿Vaciar TODOS los archivos de Logs?";

  AlertApp.show(
    titulo,
    "Los registros seleccionados se borrarán permanentemente.",
    "warning",
    () => {
      const tokenCSRF =
        document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute("content") || "";

      fetch("errror/limpiarLogs", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": tokenCSRF,
        },
        body: JSON.stringify({ tipo: tipo }),
      })
        .then((res) => res.json())
        .then((res) => {
          const payload = res.data || res;
          if (
            res.status === "success" ||
            payload.status === "success" ||
            res.exito
          ) {
            AlertApp.show(
              "Logs Limpios",
              "La limpieza se realizó con éxito.",
              "success",
            );
            cargarLogs();
          } else {
            AlertApp.show(
              "Error",
              res.message || payload.mensaje || "No se pudo vaciar.",
              "error",
            );
          }
        })
        .catch((err) => {
          console.error("Error al vaciar logs:", err);
          AlertApp.show(
            "Error",
            "Error de servidor al procesar la solicitud.",
            "error",
          );
        });
    },
    {
      mostrarCancelar: true,
      btnTexto: "Sí, vaciar",
      btnClase: "btn-success-alert",
      btnCancelarTexto: "Cancelar",
      btnCancelarClase: "btn-danger-red",
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

/**
 * Control del desplegable de herramientas con animaciones y autoplegado
 */
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

  panelTools.addEventListener("click", (e) => {
    e.stopPropagation();
  });

  document.addEventListener("click", () => {
    if (!panelTools.classList.contains("is-hidden")) {
      cerrarPanel();
    }
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && !panelTools.classList.contains("is-hidden")) {
      cerrarPanel();
    }
  });
});
