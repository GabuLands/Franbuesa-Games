<?php
date_default_timezone_set('America/Caracas');
include 'conexion.php'; // Asegúrate de que coincida con las mayúsculas/minúsculas de tu archivo (ej: Conexion.php si es necesario)

$mensaje = "";

// 2. Verificamos si el usuario presionó el botón de registrarse
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre   = $_POST['nombre'];
    $correo   = $_POST['correo'];
    $password = $_POST['clave']; 
    $telefono = $_POST['telefono']; 
    
    // Capturamos los campos nuevos de seguridad
    $pregunta_seguridad = $_POST['pregunta_seguridad'];
    $respuesta_seguridad = $_POST['respuesta_seguridad'];

    try {
        // 3. Preparamos la consulta SQL incluyendo los campos de preguntas de seguridad
        $sql = "INSERT INTO USUARIO (Nombre_Completo, Correo_Electronico, Contraseña, Telefono, Pregunta_Seguridad, Respuesta_Seguridad, Estado_Usuario) 
                VALUES (?, ?, ?, ?, ?, ?, 'Activo')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $correo, $password, $telefono, $pregunta_seguridad, $respuesta_seguridad]);
        
        // OBTENER ID GENERADO: Lo necesitamos para la traza de auditoría
        $nuevo_id_usuario = $pdo->lastInsertId();

        // =========================================================================
        // REGISTRAR ACTIVIDAD (TRAZAS): Auditoría de registro de nuevo usuario
        // =========================================================================
        $detalle_traza = "REGISTRO EXITOSO. Se creó el nuevo usuario: " . $nombre . " [Correo: " . $correo . "]";
        $stmt_audit = $pdo->prepare("INSERT INTO auditoria (ID_Usuario, Accion, Tabla_Afectada, Detalle, IP_Direccion) VALUES (?, 'REGISTRO_USUARIO', 'USUARIO', ?, ?)");
        $stmt_audit->execute([$nuevo_id_usuario, $detalle_traza, $_SERVER['REMOTE_ADDR']]);
        // =========================================================================

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
    <button id="btn-menu" class="btn-hamburguesa">☰</button>
    <div class="logo-container">
      <a href="index.php"><img src="img/logo.png" alt="Logo" class="logo-brillante-redondo" /></a>
      <span class="titulo-sitio">Franbuesa-Games</span>
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
    
    <?php echo $mensaje; ?>
    
    <form id="form-registro" class="formulario-usuario" method="POST" action="registro.php" style="margin: 40px auto;">
      <h1>Registro de usuario</h1>
      <label for="nombre">Nombre completo:</label>
      <input type="text" id="nombre" name="nombre" required />

      <label for="correo">Correo electrónico:</label>
      <input type="email" id="correo" name="correo" required />

      <label for="telefono">Teléfono:</label>
      <input type="text" id="telefono" name="telefono" placeholder="Ej: 04121234567" required />

      <label for="clave">Contraseña:</label>
      <input type="password" id="clave" name="clave" required />

      <label for="pregunta_seguridad">Pregunta de seguridad para recuperación:</label>
      <select class="pregunta_seguridad" name="pregunta_seguridad" required style="width: 104%; padding: 10px; border-radius: 8px; background-color: #fff; font-size: 14px; padding: 0.5rem; margin-top: 0.3rem; border: none;">
        <option value="">Selecciona una pregunta </option>
        <option value="¿Cuál es el nombre de tu primera mascota?">¿Cuál es el nombre de tu primera mascota?</option>
        <option value="¿En qué ciudad nació tu madre?">¿En qué ciudad nació tu madre?</option>
        <option value="¿Cuál era tu videojuego favorito de la infancia?">¿Cuál era tu videojuego favorito de la infancia?</option>
        <option value="¿Cuál es el nombre de tu escuela primaria?">¿Cuál es el nombre de tu escuela primaria?</option>
      </select>

      <label for="respuesta_seguridad">Respuesta de seguridad:</label>
      <input type="text" id="respuesta_seguridad" name="respuesta_seguridad" placeholder="Tu respuesta secreta" required />

      <label for="pregunta_seguridad">Pregunta de seguridad para recuperación:</label>
      <select class="pregunta_seguridad" name="pregunta_seguridad" required style="width: 104%; padding: 10px; border-radius: 8px; background-color: #fff; font-size: 14px; padding: 0.5rem; margin-top: 0.3rem; border: none;">
        <option value="">Selecciona una pregunta </option>
        <option value="¿Cuál es el nombre de tu primera mascota?">¿Cuál es el nombre de tu primera mascota?</option>
        <option value="¿En qué ciudad nació tu madre?">¿En qué ciudad nació tu madre?</option>
        <option value="¿Cuál era tu videojuego favorito de la infancia?">¿Cuál era tu videojuego favorito de la infancia?</option>
        <option value="¿Cuál es el nombre de tu escuela primaria?">¿Cuál es el nombre de tu escuela primaria?</option>
      </select>

      <label for="respuesta_seguridad">Respuesta de seguridad:</label>
      <input type="text" id="respuesta_seguridad" name="respuesta_seguridad" placeholder="Tu respuesta secreta" required />
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