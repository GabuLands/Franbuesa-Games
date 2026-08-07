<?php
require_once 'verificar_sesion.php';
require_once 'Conexion.php';

// Actualizar la última actividad en la base de datos (PostgreSQL requiere "Columna")
if (isset($_SESSION['ID_Sesion'])) {
    $fecha_ahora = date('Y-m-d H:i:s');
    $stmt_update_actividad = $pdo->prepare("UPDATE public.sesiones SET \"Last_Activity_At\" = ? WHERE \"ID_Sesion\" = ?");
    $stmt_update_actividad->execute([$fecha_ahora, $_SESSION['ID_Sesion']]);
}

// Consultar los datos del usuario actual desde la BD
$stmt_usr = $pdo->prepare("SELECT * FROM public.usuario WHERE \"ID_Usuario\" = ?");
$stmt_usr->execute([$_SESSION['usuario_id']]);
$datos_usuario = $stmt_usr->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bienvenido | Franbuesa-Games</title>
  <link rel="stylesheet" href="css/estilos.css" />
  <style>
    .tarjeta-bienvenida {
      background: rgba(0, 0, 0, 0.7);
      border: 1px solid #7d2ae8;
      border-radius: 12px;
      padding: 30px;
      max-width: 600px;
      margin: 40px auto;
      text-align: center;
      box-shadow: 0 8px 32px rgba(125, 42, 232, 0.3);
      color: white;
    }
    .tarjeta-bienvenida h1 {
      color: #00ffb3;
      margin-bottom: 10px;
      font-size: 2.2rem;
      text-transform: uppercase;
    }
    .tarjeta-bienvenida p {
      color: #ccc;
      font-size: 1.1rem;
      margin-bottom: 25px;
    }
    .info-usuario {
      background: rgba(255, 255, 255, 0.05);
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 25px;
      text-align: left;
    }
    .info-item {
      margin: 8px 0;
      font-size: 1rem;
    }
    .info-item strong {
      color: #7d2ae8;
    }
    .badge-admin {
      background: #e74c3c;
      color: white;
      padding: 3px 10px;
      border-radius: 12px;
      font-size: 0.9em;
      font-weight: bold;
    }
    .badge-cliente {
      background: #3498db;
      color: white;
      padding: 3px 10px;
      border-radius: 12px;
      font-size: 0.9em;
      font-weight: bold;
    }
    .acciones-usuario {
      display: flex;
      gap: 15px;
      justify-content: center;
      flex-wrap: wrap;
    }
    .btn-accion {
      background: #7d2ae8;
      color: white;
      padding: 12px 20px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      transition: background 0.3s;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .btn-accion:hover {
      background: #9b51e0;
    }
  </style>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="js/temporizador.js"></script>
  <script src="js/confirmar_salida.js"></script>
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
      <a href="logout.php" class="btn-morado">Cerrar sesión</a>
    </div>
  </header>

  <nav class="menu-vertical" id="menuVertical">
    <ul>
      <li><a href="index.php"><i data-lucide="home"></i> Inicio</a></li>
      <li><a href="juegos.php"><i data-lucide="gamepad-2"></i> Juegos</a></li>
      <li><a href="recargas.php"><i data-lucide="dollar-sign"></i> Recargas</a></li>
      
      <!-- Si es Admin, mostramos la opción del panel en el menú lateral -->
      <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'Admin'): ?>
        <li><a href="gestion_consultas.php"><i data-lucide="shield-alert"></i> Panel Gestión</a></li>
      <?php endif; ?>

      <li><a href="bienvenida.php" class="active"><i data-lucide="user"></i> Mi Perfil</a></li>
      <li><a href="logout.php"><i data-lucide="log-out"></i> Salir</a></li>
    </ul>
  </nav>

  <main class="contenido-principal" style="padding: 20px;">
    <div class="tarjeta-bienvenida">
      <h1>¡HOLA, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>! 👋</h1>
      <p>Bienvenido de nuevo a Franbuesa-Games. Tu sesión está activa de forma segura.</p>

      <!-- Tarjeta con la información del usuario en sesión -->
      <div class="info-usuario">
        <div class="info-item">
          <strong>Nombre:</strong> <?php echo htmlspecialchars($datos_usuario['Nombre_Completo'] ?? $_SESSION['usuario_nombre']); ?>
        </div>
        <div class="info-item">
          <strong>Correo:</strong> <?php echo htmlspecialchars($datos_usuario['Correo_Electronico'] ?? ''); ?>
        </div>
        <div class="info-item">
          <strong>Teléfono:</strong> <?php echo htmlspecialchars($datos_usuario['Telefono'] ?? 'No registrado'); ?>
        </div>
        <div class="info-item">
          <strong>Tipo de Cuenta:</strong> 
          <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'Admin'): ?>
            <span class="badge-admin">Admin</span>
          <?php else: ?>
            <span class="badge-cliente">Cliente</span>
          <?php endif; ?>
        </div>
      </div>

      <!-- BOTONES DINÁMICOS SEGÚN EL ROL -->
      <div class="acciones-usuario">
        <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'Admin'): ?>
          
          <!-- Botones Exclusivos del Administrador -->
          <a href="gestion_consultas.php" class="btn-accion">
            <i data-lucide="users"></i> Panel de Gestión
          </a>
          <a href="implementar_seg_sql.php" class="btn-accion" style="background: #27ae60;">
            <i data-lucide="shield-check"></i> Seguridad SQL
          </a>

        <?php else: ?>
          
          <!-- Botones del Cliente Normal -->
          <a href="juegos.php" class="btn-accion">
            <i data-lucide="gamepad-2"></i> Ver Catálogo de Juegos
          </a>
          <a href="recargas.php" class="btn-accion" style="background: #27ae60;">
            <i data-lucide="dollar-sign"></i> Realizar Recarga
          </a>

        <?php endif; ?>
      </div>

    </div>
  </main>

  <script>
    $(document).ready(function() {
      $("#btn-menu").click(function() {
        $("#menuVertical").toggleClass("activo");
      });
      lucide.createIcons();
    });
  </script>
</body>
</html>