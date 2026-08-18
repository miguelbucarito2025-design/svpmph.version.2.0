const ruta = document.getElementById("perfil").action;

inicializarFormulario("perfil", ruta, (res) => {
  setTimeout(() => {
    window.location.href = "perfil";
  }, 1500);
});

function validarEdadDirecta(input) {
  if (!input.value) return;

  const edadCalculada =
    new Date().getFullYear() - new Date(input.value).getFullYear();

  if (edadCalculada < 17 || edadCalculada > 80) {
    AlertApp.show(
      "Edad no permitida",
      "Usted no esta en el rango permitido de edad de 17 a 80 años",
      "error",
    );
    input.value = "";
    input.style.border = "2px solid #cf0707";
  } else {
    input.style.border = "2px solid #07cf11";
  }
}

const campoFecha = document.getElementById("edad");

if (campoFecha) {
  campoFecha.addEventListener("blur", () => validarEdadDirecta(campoFecha));
}
