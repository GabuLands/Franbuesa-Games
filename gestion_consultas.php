<?php
//require_once 'verificar_sesion.php';
require_once 'Conexion.php';
//requerirSesionUnica();

// Al inicio de tus páginas protegidas, justo después de session_start():
if (isset($_SESSION['ID_Sesion'])) {
    $fecha_ahora = date('Y-m-d H:i:s');
    $stmt_update_actividad = $pdo->prepare("UPDATE sesiones SET Last_Activity_At = ? WHERE ID_Sesion = ?");
    $stmt_update_actividad->execute([$fecha_ahora, $_SESSION['ID_Sesion']]);
}
// --- LÓGICA PARA ELIMINAR (Punto 1.d del profesor) ---
if (isset($_GET['eliminar'])) {
    $id_a_borrar = $_GET['eliminar'];
    $sql_borrar = "DELETE FROM USUARIO WHERE ID_Usuario = ?";
    $stmt = $pdo->prepare($sql_borrar);
    $stmt->execute([$id_a_borrar]);
    // Recargamos la página con el nuevo nombre del archivo
    header("Location: gestion_consultas.php?msg=eliminado");
    exit();
}

// --- LÓGICA PARA CONSULTA GENERAL (Punto 1.c y 2 del profesor) ---
$sentencia = $pdo->query("SELECT * FROM USUARIO");
$usuarios = $sentencia->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gestión de Usuarios | Franbuesa-Games</title>
  <link rel="stylesheet" href="css/estilos.css" />
  <style>
    .tabla-usuarios { width: 100%; border-collapse: collapse; margin-top: 20px; color: white; background: rgba(0,0,0,0.6); }
    .tabla-usuarios th, .tabla-usuarios td { padding: 12px; border: 1px solid #7d2ae8; text-align: left; }
    .tabla-usuarios th { background-color: #7d2ae8; }
    .btn-borrar { color: #ff4d4d; text-decoration: none; font-weight: bold; }
    .btn-editar { color: #00ffb3; text-decoration: none; font-weight: bold; margin-right: 10px; }
    .btn-reporte { background: #27ae60; color: white; padding: 10px; text-decoration: none; border-radius: 5px; display: inline-block; margin-bottom: 10px; }
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
    <a href="registro.php" class="btn-morado">Registrarse</a>
    <a href="login.php" class="btn-morado">Iniciar sesión</a>
  </div>
</header>

  <nav class="menu-vertical" id="menuVertical">
    <ul>
      <li><a href="index.php"><i data-lucide="home"></i> Inicio</a></li>
      <li><a href="juegos.php"><i data-lucide="gamepad-2"></i> Juegos</a></li>
      <li><a href="recargas.php"><i data-lucide="dollar-sign"></i> Recargas</a></li>
      <li><a href="gestion_consultas.php" class="active"><i data-lucide="user"></i> Panel Gestión</a></li>
      <li><a href="logout.php"><i data-lucide="log-out"></i> Salir</a></li>
    </ul>
  </nav>

  <main class="contenido-principal" style="padding: 20px;">
    <div class="bloque-transparente">
      <h1>Panel de Gestión (Consulta General)</h1>
      
      <a href="reporte_pdf.php" class="btn-reporte" target="_blank">📄 Generar Reporte PDF</a>

      <?php 
        if(isset($_GET['msg']) && $_GET['msg'] == 'eliminado') echo "<p style='color:yellow'>Usuario eliminado correctamente.</p>"; 
        if(isset($_GET['msg']) && $_GET['msg'] == 'actualizado') echo "<p style='color:#00ffb3'>Usuario actualizado con éxito.</p>"; 
      ?>

      <table class="tabla-usuarios">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($usuarios as $u): ?>
          <tr>
            <td><?php echo $u['ID_Usuario']; ?></td>
            <td><?php echo $u['Nombre_Completo']; ?></td>
            <td><?php echo $u['Correo_Electronico']; ?></td>
            <td><?php echo $u['Telefono']; ?></td>
            <td>
              <!-- BOTÓN EDITAR (Punto 1.b) -->
              <a href="editar_usuario.php?id=<?php echo $u['ID_Usuario']; ?>" class="btn-editar">Editar</a>
              
              <!-- BOTÓN ELIMINAR (Punto 1.d) -->
              <a href="gestion_consultas.php?eliminar=<?php echo $u['ID_Usuario']; ?>" 
                 class="btn-borrar" 
                 onclick="return confirm('¿Seguro que deseas eliminar este usuario?')">Eliminar</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>

  <!-- Script para el menú -->
  <script src="https://code.jquery.com"></script>
  <script>
    $(document).ready(function() {
      // Control del menú hamburguesa
      $("#btn-menu").click(function() {
        $("#menuVertical").toggleClass("activo");
      });
      
      // Renderizar los iconos de Lucide
      lucide.createIcons();
    });
  </script>
</body>
</html>
