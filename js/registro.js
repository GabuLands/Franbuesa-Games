document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("form-registro");

  form.addEventListener("submit", function (e) {
    // Obtenemos los valores de los campos
    const clave = document.getElementById("clave").value;
    const confirmarClave = document.getElementById("confirmar-clave").value;

    // Validación de contraseñas
    if (clave !== confirmarClave) {
      e.preventDefault(); // Detenemos el envío si fallan las claves
      alert("Las contraseñas no coinciden. Por favor, verifica.");
      return;
    }

    // Si las claves coinciden, NO usamos preventDefault.
    // El formulario seguirá su camino hacia el PHP en registro.php
    console.log("Validación exitosa, enviando datos al servidor...");
  });
});
