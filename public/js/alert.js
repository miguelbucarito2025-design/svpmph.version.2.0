const AlertApp = {
  dialog: document.getElementById("custom-alert"),
  icon: document.getElementById("alert-icon"),
  title: document.getElementById("alert-title"),
  body: document.getElementById("alert-body"),
  actions: document.getElementById("alert-actions"),
  btnClose: document.getElementById("alert-btn-close"),

  init() {
    if (this.btnClose) {
      this.btnClose.addEventListener("click", () => this.dialog.close());
    }
  },

  /**
   * Muestra la alerta ultra flexible.
   * @param {string} titulo - El título de la cabecera.
   * @param {string|HTMLElement} contenido - Texto plano, HTML en string o un Elemento del DOM real.
   * @param {string} tipo - 'success', 'error', 'info', 'warning', 'custom'.
   */
  show(titulo, contenido, tipo = "info") {
    if (!this.dialog) return;

    // 1. Configurar tipo y título
    this.dialog.className = "custom-alert " + tipo;
    this.title.textContent = titulo;

    // 2. Gestionar iconos predefinidos
    const iconos = {
      success: "✅",
      error: "❌",
      info: "ℹ️",
      warning: "⚠️",
      custom: "⚙️",
    };
    this.icon.textContent = iconos[tipo] || iconos.info;
    this.icon.style.display = tipo === "none" ? "none" : "block";

    // 3. LA MAGIA: Limpiar el cuerpo e inyectar el contenido dinámico
    this.body.innerHTML = "";

    if (contenido instanceof HTMLElement) {
      // 💡 Si es un elemento real del DOM (un form, una tabla, etc.), lo adoptamos manteniendo sus eventos
      this.body.appendChild(contenido);
    } else {
      // 💡 Si es texto ordinario o código HTML en string
      this.body.innerHTML = contenido;
    }

    // 4. Mostrar de forma nativa
    this.dialog.showModal();
  },
};

AlertApp.init();
