<?php
include 'conexion.php';

$mensaje = "";

// 2. Verificamos si el usuario presionó el botón de registrarse
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre   = $_POST['nombre'];
    $correo   = $_POST['correo'];
    $password = $_POST['clave']; // En un proyecto real usaríamos password_hash
    $telefono = $_POST['telefono']; // Campo nuevo para cumplir con SQL

    try {
        // 3. Preparamos la consulta SQL según tablas
        $sql = "INSERT INTO USUARIO (Nombre_Completo, Correo_Electronico, Contraseña, Telefono, Estado_Usuario) 
                VALUES (?, ?, ?, ?, 'Activo')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $correo, $password, $telefono]);
        
        $mensaje = "<p style='color: #2ecc71; background: rgba(0,0,0,0.5); padding: 10px;'>¡Registro exitoso! Ya puedes iniciar sesión.</p>";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Error de correo duplicado
            $mensaje = "<p style='color: #e74c3c; background: rgba(0,0,0,0.5); padding: 10px;'>Error: El correo ya está registrado.</p>";
        } else {
            $mensaje = "<p style='color: #e74c3c; background: rgba(0,0,0,0.5); padding: 10px;'>Error: " . $e->getMessage() . "</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Registro | Franbuesa-Games</title>
  <link rel="stylesheet" href="css/estilos.css" />
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

  <header class="barra-superior">
    <button id="btn-menu" class="btn-hamburguesa" aria-label="Abrir menú">☰</button>

    <div class="logo-container">
      <a href="index.html">
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
      <a href="registro.php" class="btn-morado">Registrarse</a>
      <a href="login.php" class="btn-morado">Iniciar sesión</a>
    </div>
  </header>

 <nav class="menu-vertical">
  <ul>
    <li><a href="index.php"><i data-lucide="home"></i> Inicio</a></li>
    <li><a href="juegos.php"><i data-lucide="gamepad-2"></i> Juegos</a></li>
    <li><a href="recarga.php"><i data-lucide="dollar-sign"></i> Recargas</a></li>
    <li><a href="registro.php"><i data-lucide="user-plus"></i> Registrarse</a></li>
    <li><a href="perfil.php"><i data-lucide="user"></i> Perfil</a></li>
  </ul>
</nav>

  <main class="contenido-principal">
    <h1>Registro de usuario</h1>
    
    <!-- Mostramos mensajes de éxito o error aquí -->
    <?php echo $mensaje; ?>

    <!-- Agregamos method="POST" y action="" -->
    <form id="form-registro" class="formulario-usuario" method="POST" action="registro.php">
      <label for="nombre">Nombre completo:</label>
      <input type="text" id="nombre" name="nombre" required />

      <label for="correo">Correo electrónico:</label>
      <input type="email" id="correo" name="correo" required />

      <label for="telefono">Teléfono:</label>
      <input type="text" id="telefono" name="telefono" placeholder="Ej: 04121234567" required />

      <label for="clave">Contraseña:</label>
      <input type="password" id="clave" name="clave" required />

      <button type="submit" class="btn-morado">Registrarse</button>

      <p class="mensaje-secundario">
        ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
      </p>
    </form>
  </main>

  <script src="js/menu-toggle.js"></script>
  <script> lucide.createIcons();</script>
</body>
</html>
