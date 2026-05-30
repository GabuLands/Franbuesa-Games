class Usuario {
  #nombre;
  #correo;
  #clave;
  #edad;
  #genero;
  #juegos;
  #pais;

  constructor(nombre, correo, clave, edad, genero, juegos, pais) {
    this.#nombre = nombre.trim();
    this.#correo = correo.trim();
    this.#clave = clave;
    this.#edad = edad;
    this.#genero = genero;
    this.#juegos = juegos;
    this.#pais = pais;
  }

  get nombre() { return this.#nombre; }
  set nombre(valor) { this.#nombre = valor.trim(); }

  get correo() { return this.#correo; }
  set correo(valor) { this.#correo = valor.trim(); }

  get edad() { return this.#edad; }
  set edad(valor) { this.#edad = valor; }

  get genero() { return this.#genero; }
  set genero(valor) { this.#genero = valor; }

  get juegos() { return this.#juegos; }
  set juegos(lista) { this.#juegos = lista; }

  get pais() { return this.#pais; }
  set pais(valor) { this.#pais = valor; }

  get clave() { return this.#clave; }
  set clave(valor) { this.#clave = valor; }

  resumen() {
    return {
      nombre: this.#nombre,
      correo: this.#correo,
      edad: this.#edad,
      genero: this.#genero,
      juegos: this.#juegos,
      pais: this.#pais
    };
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const formulario = document.getElementById("form-registro");

  formulario.addEventListener("submit", function (e) {
    e.preventDefault();

    const nombre = document.getElementById("nombre").value;
    const correo = document.getElementById("correo").value;
    const clave = document.getElementById("clave").value;
    const edad = document.getElementById("edad").value;
    const pais = document.getElementById("pais").value;
    const generoSeleccionado = document.querySelector('input[name="genero"]:checked');
    const juegosSeleccionados = Array.from(document.querySelectorAll('input[name="juegos"]:checked')).map(c => c.value);

    let errores = [];

    if (nombre.trim() === "") errores.push("El nombre es obligatorio.");
    if (!correo.includes("@")) errores.push("El correo no es válido.");
    if (clave.length < 6) errores.push("La contraseña debe tener al menos 6 caracteres.");
    if (edad === "" || edad < 12) errores.push("La edad debe ser 12 o mayor.");
    if (!generoSeleccionado) errores.push("Debe seleccionar un género.");
    if (juegosSeleccionados.length === 0) errores.push("Debe elegir al menos un juego.");
    if (pais === "") errores.push("Debe seleccionar un país.");

    if (errores.length > 0) {
      alert("Corrige los siguientes errores:\n\n- " + errores.join("\n- "));
    } else {
      const usuario = new Usuario(
        nombre,
        correo,
        clave,
        edad,
        generoSeleccionado.value,
        juegosSeleccionados,
        pais
      );

      // Guardar en localStorage
      localStorage.setItem("usuarioFranbuesa", JSON.stringify(usuario.resumen()));

      alert("¡Registro exitoso! 🎮 Bienvenido a Franbuesa-Games 🍓");
      formulario.reset();
      window.location.href = "perfil.html";
    }
  });
});