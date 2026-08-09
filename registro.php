<?php
date_default_timezone_set('America/Caracas');
include 'conexion.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitización básica y asignación segura con operador ?? ''
    $nombre   = trim($_POST['nombre'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');
    $password = $_POST['clave'] ?? ''; 
    $telefono = trim($_POST['telefono'] ?? ''); 
    
    // Captura segura de preguntas y respuestas
    $pregunta_seguridad_1  = $_POST['pregunta_seguridad_1'] ?? '';
    $respuesta_seguridad_1 = trim($_POST['respuesta_seguridad_1'] ?? '');
    $pregunta_seguridad_2  = $_POST['pregunta_seguridad_2'] ?? '';
    $respuesta_seguridad_2 = trim($_POST['respuesta_seguridad_2'] ?? '');

    try {
        $sql = "INSERT INTO USUARIO (\"Nombre_Completo\", \"Correo_Electronico\", \"Contraseña\", \"Telefono\", \"Pregunta_Seguridad_1\", \"Respuesta_Seguridad_1\", \"Pregunta_Seguridad_2\", \"Respuesta_Seguridad_2\", \"Estado_Usuario\") 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Activo')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nombre, 
            $correo, 
            $password, 
            $telefono, 
            $pregunta_seguridad_1, 
            $respuesta_seguridad_1, 
            $pregunta_seguridad_2, 
            $respuesta_seguridad_2
        ]);
        
        $nuevo_id_usuario = $pdo->lastInsertId();

        // REGISTRO EN AUDITORÍA
        $detalle_traza = "REGISTRO EXITOSO. Se creó el nuevo usuario: " . $nombre . " [Correo: " . $correo . "]";
        $stmt_audit = $pdo->prepare("INSERT INTO auditoria (\"ID_Usuario\", \"Accion\", \"Tabla_Afectada\", \"Detalle\", \"IP_Direccion\") VALUES (?, 'REGISTRO_USUARIO', 'USUARIO', ?, ?)");
        $stmt_audit->execute([$nuevo_id_usuario, $detalle_traza, $_SERVER['REMOTE_ADDR']]);

        $mensaje = "<p style='color: #2ecc71; background: rgba(0,0,0,0.5); padding: 10px; border-radius: 5px;'>¡Registro exitoso! Ya puedes iniciar sesión.</p>";
    } catch (PDOException $e) {
        if ($e->getCode() == 23505 || $e->getCode() == 23000) { // Error de duplicidad (Correo / Clave única)
            $mensaje = "<p style='color: #e74c3c; background: rgba(0,0,0,0.5); padding: 10px; border-radius: 5px;'>Error: El correo electrónico ya se encuentra registrado.</p>";
        } else {
            $mensaje = "<p style='color: #e74c3c; background: rgba(0,0,0,0.5); padding: 10px; border-radius: 5px;'>Error de BD: " . $e->getMessage() . "</p>";
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

      <!-- Pregunta y Respuesta 1 -->
      <label for="pregunta_seguridad_1">Primera pregunta de seguridad:</label>
      <select id="pregunta_seguridad_1" name="pregunta_seguridad_1" required style="width: 104%; padding: 10px; border-radius: 8px; background-color: #fff; font-size: 14px; padding: 0.5rem; margin-top: 0.3rem; border: none;">
        <option value="">Selecciona la primera pregunta</option>
        <option value="¿Cuál es el nombre de tu primera mascota?">¿Cuál es el nombre de tu primera mascota?</option>
        <option value="¿En qué ciudad nació tu madre?">¿En qué ciudad nació tu madre?</option>
        <option value="¿Cuál era tu videojuego favorito de la infancia?">¿Cuál era tu videojuego favorito de la infancia?</option>
        <option value="¿Cuál es el nombre de tu escuela primaria?">¿Cuál es el nombre de tu escuela primaria?</option>
      </select>

      <label for="respuesta_seguridad_1">Primera respuesta de seguridad:</label>
      <input type="text" id="respuesta_seguridad_1" name="respuesta_seguridad_1" placeholder="Tu primera respuesta secreta" required />

      <!-- Pregunta y Respuesta 2 -->
      <label for="pregunta_seguridad_2">Segunda pregunta de seguridad:</label>
      <select id="pregunta_seguridad_2" name="pregunta_seguridad_2" required style="width: 104%; padding: 10px; border-radius: 8px; background-color: #fff; font-size: 14px; padding: 0.5rem; margin-top: 0.3rem; border: none;">
        <option value="">Selecciona la segunda pregunta</option>
        <option value="¿Cuál es tu película favorita?">¿Cuál es tu película favorita?</option>
        <option value="¿Nombre de tu mejor amigo de la infancia?">¿Nombre de tu mejor amigo de la infancia?</option>
        <option value="¿Marca de tu primer coche/vehículo?">¿Marca de tu primer coche/vehículo?</option>
        <option value="¿En qué calle vivías cuando eras niño?">¿En qué calle vivías cuando eras niño?</option>
      </select>

      <label for="respuesta_seguridad_2">Segunda respuesta de seguridad:</label>
      <input type="text" id="respuesta_seguridad_2" name="respuesta_seguridad_2" placeholder="Tu segunda respuesta secreta" required />

      <button type="submit" class="btn-morado" style="margin-top: 20px;">Registrarse</button>

      <p class="mensaje-secundario">
        ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
      </p>
    </form>
  </main>

  <script src="js/menu-toggle.js"></script>
  <script> lucide.createIcons();</script>
</body>
</html>