<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Franbuesa-Games - Inicio</title>

  <link rel="stylesheet" href="css/estilos.css" />

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- jQuery UI (para accordion) -->
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

  <!-- jqPlot -->
  <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/jqPlot/1.0.9/jquery.jqplot.min.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jqPlot/1.0.9/jquery.jqplot.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jqPlot/1.0.9/plugins/jqplot.pieRenderer.min.js"></script>

  <script src="https://unpkg.com/lucide@latest"></script>

  <style>
    #grafica-juegos {
      height: 300px;
      width: 100%;
      max-width: 500px;
      margin: 30px auto;
    }

    .ui-accordion-header {
      background: #6a0dad !important;
      color: white !important;
      border: 1px solid #ff4ff5 !important;
      margin-top: 5px !important;
    }

    .ui-accordion-content {
      background: rgba(43, 0, 64, 0.8) !important;
      color: white !important;
      border: 1px solid #ff4ff5 !important;
    }

    .seccion-extra {
      margin-top: 40px;
      padding-top: 20px;
      border-top: 1px solid #ff4ff5;
    }
  </style>
</head>
<body>

<header class="barra-superior">
  <button id="btn-menu" class="btn-hamburguesa">&#9776;</button>

  <div class="logo-container">
    <a href="index.php">
      <img src="img/logo.png" alt="Franbuesa-Games Logo" class="logo-brillante-redondo" />
    </a>
    <span class="titulo-sitio">Franbuesa-Games</span>
  </div>

  <div class="busqueda-container">
    <input type="text" placeholder="Buscar juegos..." class="input-busqueda" />
    <select class="selector-idioma">
      <option value="es"> Español</option>
      <option value="en"> English</option>
      <option value="pt"> Português</option>
    </select>
  </div>

  <div class="botones-sesion">
    <a href="registro.php" class="btn-morado">Registrarse</a>
    <a href="login.php" class="btn-morado">Iniciar sesión</a>
  </div>
</header>

<nav class="menu-vertical">
  <ul>
    <li><a href="index.html"><i data-lucide="home"></i> Inicio</a></li>
    <li><a href="juegos.html"><i data-lucide="gamepad-2"></i> Juegos</a></li>
    <li><a href="recarga.php"><i data-lucide="dollar-sign"></i> Recargas</a></li>
    <li><a href="registro.php"><i data-lucide="user-plus"></i> Registrarse</a></li>
    <li><a href="gestion_consultas.php"><i data-lucide="user"></i> Perfil</a></li>
  </ul>
</nav>

<main>
  <div class="bloque-transparente">
    <h1>Bienvenido a Franbuesa-Games</h1>

    <p>
      Tu plataforma favorita para recargas de juegos móviles como Call of Duty Mobile,
      Mobile Legends y Free Fire.
    </p>

    <h2>Misión</h2>
    <p>
      Facilitar el acceso a recargas y contenido premium para gamers de toda Latinoamérica.
    </p>

    <h2>Visión</h2>
    <p>
      Convertirnos en la plataforma líder en servicios digitales para gamers móviles.
    </p>

    <h2>¿Qué ofrecemos?</h2>
    <ul>
      <li>💎 Recargas rápidas y seguras</li>
      <li>🎁 Promociones semanales</li>
      <li>🤝 Soporte personalizado</li>
      <li>🛒 Métodos de pago adaptados</li>
    </ul>

    <!-- GRÁFICA -->
    <div class="seccion-extra">
      <h2 style="text-align:center;">Juegos más recargados del mes</h2>
      <div id="grafica-juegos"></div>
    </div>

    <!-- FAQ ACCORDION -->
    <div class="seccion-extra">
      <h2>Preguntas Frecuentes</h2>

      <div id="accordion">
        <h3>¿Cuánto tarda la recarga?</h3>
        <div>
          <p>Entre 5 y 15 minutos después de verificar el pago.</p>
        </div>

        <h3>¿Qué métodos de pago aceptan?</h3>
        <div>
          <p>Pago móvil, transferencia y Binance Pay.</p>
        </div>

        <h3>¿Es seguro mi ID?</h3>
        <div>
          <p>Sí, solo se usa para enviar la recarga.</p>
        </div>
      </div>
    </div>

  </div>
</main>

<script>
$(document).ready(function(){

  // ACTIVAR ACCORDION
  $("#accordion").accordion({
    heightStyle: "content",
    collapsible: true,
    active: false
  });

  // CREAR GRÁFICA CON JQPLOT
  var datos = [
    ['Free Fire', 45],
    ['Call of Duty Mobile', 30],
    ['Mobile Legends', 25]
  ];

  $.jqplot('grafica-juegos', [datos], {
    seriesDefaults: {
      renderer: $.jqplot.PieRenderer,
      rendererOptions: {
        showDataLabels: true
      }
    },
    legend: {
      show: true,
      location: 'e'
    },
    grid: {
      background: 'transparent',
      borderWidth: 0,
      shadow: false
    }
  });

  lucide.createIcons();
});
</script>

<script src="js/menu-toggle.js"></script>
<script src="js/login.js"></script>
<script src="js/activar-links.js"></script>

</body>
</html>