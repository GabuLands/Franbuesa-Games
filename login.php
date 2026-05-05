<?php
include 'conexion.php';
session_start(); // se inicia sesión para que el navegador recuerde quién entró

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $clave  = $_POST['clave'];

    try {
        // Buscamos al usuario por su correo
        $stmt = $pdo->prepare("SELECT * FROM USUARIO WHERE Correo_Electronico = ?");
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch();

        // Verificamos si existe y si la clave coincide
        // Nota: En el registro no se utilizo password_hash para que sea más fácil la prueba.
        if ($usuario && $usuario['Contraseña'] == $clave) {
            $_SESSION['usuario_id'] = $usuario['ID_Usuario'];
            $_SESSION['usuario_nombre'] = $usuario['Nombre_Completo'];
            
            // Si es exitoso, se dirige al Panel de Gestión (gestion_consultas.php)
            header("Location: gestion_consultas.php");
            exit();
        } else {
            $error = "Correo o contraseña incorrectos.";
        }
    } catch (PDOException $e) {
        $error = "Error en la conexión: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Iniciar sesión | Franbuesa-Games</title>
  <link rel="stylesheet" href="css/estilos.css" />
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

  <header class="barra-superior">
    <button id="btn-menu" class="btn-hamburguesa">☰</button>
    <div class="logo-container">
      <a href="index.php"><img src="img/logo.png" alt="Logo" class="logo-brillante-redondo" /></a>
      <span class="titulo-sitio">Franbuesa-Games</span>
    </div>
  </header>

  <nav class="menu-vertical" id="menuVertical">
    <ul>
      <li><a href="index.php"><i data-lucide="home"></i> Inicio</a></li>
      <li><a href="juegos.php"><i data-lucide="gamepad-2"></i> Juegos</a></li>
      <li><a href="recargas.php"><i data-lucide="dollar-sign"></i> Recargas</a></li>
      <li><a href="registro.php"><i data-lucide="user-plus"></i> Registrarse</a></li>
      <li><a href="gestion_consultas.php"><i data-lucide="user"></i> Gestión</a></li>
    </ul>
  </nav>

  <main class="contenido-principal">
    <div class="formulario-usuario">
      <h1>Iniciar sesión</h1>

      <!-- Formulario ahora apunta a PHP -->
      <form id="form-login" method="POST" action="login.php">
        <label for="correo">Correo electrónico:</label>
        <input type="email" id="correo" name="correo" required />

        <label for="clave">Contraseña:</label>
        <input type="password" id="clave" name="clave" required />

        <button type="submit" class="btn-morado" style="width:100%; margin-top:20px;">Entrar</button>

        <p class="mensaje-secundario">
          ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
        </p>

        <?php if($error): ?>
          <p style="color: #ff4d4d; text-align: center; margin-top: 10px; font-weight: bold;">
            <?php echo $error; ?>
          </p>
        <?php endif; ?>
      </form>
    </div>
  </main>

  <script src="https://code.jquery.com"></script>
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
