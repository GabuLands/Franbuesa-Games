<?php
require_once 'verificar_sesion.php';
require_once 'Conexion.php';
requerirSesionUnica();

// Actualizar la última actividad en la base de datos
if (isset($_SESSION['ID_Sesion'])) {
    $fecha_ahora = date('Y-m-d H:i:s');
    $stmt_update_actividad = $pdo->prepare("UPDATE public.sesiones SET \"Last_Activity_At\" = ? WHERE \"ID_Sesion\" = ?");
    $stmt_update_actividad->execute([$fecha_ahora, $_SESSION['ID_Sesion']]);
}

// Validar que se reciba un ID válido por GET
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: gestion_consultas.php");
    exit();
}

// 1. Buscar los datos actuales del usuario en PostgreSQL
$stmt = $pdo->prepare("SELECT * FROM public.usuario WHERE \"ID_Usuario\" = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    header("Location: gestion_consultas.php?msg=no_encontrado");
    exit();
}

$mensaje = "";

// 2. Lógica para GUARDAR los cambios
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $tlf    = $_POST['telefono'];

    $sql = "UPDATE public.usuario 
            SET \"Nombre_Completo\" = ?, \"Correo_Electronico\" = ?, \"Telefono\" = ? 
            WHERE \"ID_Usuario\" = ?";
            
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$nombre, $correo, $tlf, $id])) {
        header("Location: gestion_consultas.php?msg=actualizado");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Usuario | Franbuesa-Games</title>
  <link rel="stylesheet" href="css/estilos.css">
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
      <li><a href="gestion_consultas.php"><i data-lucide="user"></i> Panel Gestión</a></li>
      <li><a href="implementar_seg_sql.php"><i data-lucide="shield"></i> Seguridad BD</a></li>
      <li><a href="logout.php"><i data-lucide="log-out"></i> Salir</a></li>
    </ul>
  </nav>

  <main class="contenido-principal">
    <div class="formulario-usuario">
      <h1>Modificar Cliente</h1>
      <form method="POST">
        <label>Nombre Completo:</label>
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['Nombre_Completo'] ?? $usuario['nombre_completo'] ?? ''); ?>" required>

        <label>Correo Electrónico:</label>
        <input type="email" name="correo" value="<?php echo htmlspecialchars($usuario['Correo_Electronico'] ?? $usuario['correo_electronico'] ?? ''); ?>" required>

        <label>Teléfono:</label>
        <input type="text" name="telefono" value="<?php echo htmlspecialchars($usuario['Telefono'] ?? $usuario['telefono'] ?? ''); ?>" required>

        <button type="submit" class="btn-morado" style="width:100%; margin-top:20px; padding:10px;">Guardar Cambios</button>
        <br><br>
        <a href="gestion_consultas.php" style="display:block; text-align:center;">Volver atrás</a>
      </form>
    </div>
  </main>
</body>
</html>