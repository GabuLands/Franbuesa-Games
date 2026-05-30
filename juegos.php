<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Juegos | Franbuesa-Games</title>
  <link rel="stylesheet" href="css/estilos.css" />
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

  <header class="barra-superior">
    <button id="btn-menu" class="btn-hamburguesa" aria-label="Abrir menú">&#9776;</button>

    <div class="logo-container">
      <a href="index.php">
        <img src="img/logo.png" alt="Franbuesa-Games Logo" class="logo-brillante-redondo" />
      </a>
      <span class="titulo-sitio">Franbuesa-Games</span>
    </div>

    <div class="busqueda-container">
      <input type="text" placeholder="Buscar juegos..." class="input-busqueda" />
      <select class="selector-idioma">
        <option value="es">🌎 Español</option>
        <option value="en">🇺🇸 English</option>
        <option value="pt">🇧🇷 Português</option>
      </select>
    </div>

    <div class="botones-sesion">
      <button class="btn-morado">Registrarse</button>
      <button class="btn-morado">Iniciar sesión</button>
    </div>
  </header>

<nav class="menu-vertical">
  <ul>
    <li><a href="index.php"><i data-lucide="home"></i> Inicio</a></li>
    <li><a href="juegos.php"><i data-lucide="gamepad-2"></i> Juegos</a></li>
    <li><a href="recargas.php"><i data-lucide="dollar-sign"></i> Recargas</a></li>
    <li><a href="registro.php"><i data-lucide="user-plus"></i> Registrarse</a></li>
    <li><a href="gestion_consultas.php"><i data-lucide="user"></i> Perfil</a></li>
  </ul>
</nav>

  <main class="contenido-principal">
    <h1>Juegos disponibles</h1>
    <div class="galeria-juegos">
      <div class="juego">
        <a href="recargas.php"><img src="img/cod.jpg" alt="Call of Duty Mobile" /></a>
        <p class="nombre-juego">Call of Duty Mobile</p>
        <p class="descripcion-juego">Disfruta de combates intensos en este shooter de guerra con gráficos de consola y jugabilidad rápida.</p>
      </div>

      <div class="juego">
        <a href="recargas.php"><img src="img/mobilelegends.jpg" alt="Mobile Legends" /></a>
        <p class="nombre-juego">Mobile Legends</p>
        <p class="descripcion-juego">Únete al campo de batalla 5v5 con héroes épicos y estrategia en tiempo real en este popular MOBA.</p>
      </div>

      <div class="juego">
        <a href="recargas.php"><img src="img/freefire.jpg" alt="Free Fire" /></a>
        <p class="nombre-juego">Free Fire</p>
        <p class="descripcion-juego">Sobrevive en esta batalla campal con partidas rápidas y una variedad de personajes y habilidades.</p>
      </div>
    </div>
  </main>

  <script src="js/menu-toggle.js"></script>
  <script src="js/login.js"></script>
  <script src="js/activar-links.js"></script>
  <script> lucide.createIcons();</script>
</body>
</html>