document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("form-login");

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const correoIngresado = document.getElementById("correo").value;
    const claveIngresada = document.getElementById("clave").value;

    const datos = JSON.parse(localStorage.getItem("usuario"));

    if (!datos) {
      alert("No hay usuarios registrados. Por favor regístrate primero.");
      return;
    }

    if (correoIngresado == datos.correo && claveIngresada == datos.clave) {
      alert("Inicio de sesión exitoso ✅");
      localStorage.setItem("usuarioActivo", "si");
      window.location.href = "perfil.html";
    } else {
      alert("Correo o clave incorrectos. Intenta nuevamente.");
    }
  });
});