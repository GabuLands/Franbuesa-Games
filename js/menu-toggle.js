document.addEventListener('DOMContentLoaded', function() {
  const btnMenu = document.getElementById('btn-menu');
  const menuVertical = document.querySelector('.menu-vertical');
  const contenidoPrincipal = document.querySelector('.contenido-principal');

  btnMenu.addEventListener('click', function() {
    menuVertical.classList.toggle('activo');
    contenidoPrincipal.classList.toggle('menu-visible');
  });
});