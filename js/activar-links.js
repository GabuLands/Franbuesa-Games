document.addEventListener("DOMContentLoaded", () => {
  // Activa todos los enlaces de registro.html
  const registroLinks = document.querySelectorAll('a[href="registro.html"]');
  registroLinks.forEach(link => link.classList.add("active"));

  // Activa todos los enlaces de login.html
  const loginLinks = document.querySelectorAll('a[href="login.html"]');
  loginLinks.forEach(link => link.classList.add("active"));
});