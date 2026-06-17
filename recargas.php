<?php
include 'conexion.php';
$mensaje = "";

if (isset($_SESSION['ID_Sesion'])) {
    $fecha_ahora = date('Y-m-d H:i:s');
    $stmt_update_actividad = $pdo->prepare("UPDATE sesiones SET Last_Activity_At = ? WHERE ID_Sesion = ?");
    $stmt_update_actividad->execute([$fecha_ahora, $_SESSION['ID_Sesion']]);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = 1; 
    $juego = $_POST['juego'];
    $usuario_ingame = $_POST['usuarioJuego'];
    $monto = $_POST['monto'];
    $referencia = $_POST['referencia'];
    $fecha = $_POST['fecha_pago']; 

    try {
        $sql = "INSERT INTO TRANSACCION 
                (ID_Usuario, ID_Paquete, ID_Cuenta, Metodo_Pago, Referencia_Bancaria, Monto_Pagado, Fecha_Hora, Estado) 
                VALUES (?, 1, 1, 'Transferencia', ?, ?, ?, 'Pendiente')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_usuario, $referencia, $monto, $fecha]);
        
        $mensaje = "<p style='color: #2ecc71; background: rgba(0,0,0,0.8); padding: 15px; border-radius: 5px;'>¡Recarga registrada con éxito! Referencia: $referencia</p>";
    } catch (PDOException $e) {
        $mensaje = "<p style='color: #e74c3c; background: rgba(0,0,0,0.8); padding: 15px; border-radius: 5px;'>Error al registrar: " . $e->getMessage() . "</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Recargas | Franbuesa-Games</title>

  <!--CSS -->
  <link rel="stylesheet" href="css/estilos.css" />

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- jQuery UI CSS -->
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

  <!-- jQuery UI JS -->
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

  <style>
    .ui-datepicker {
      z-index: 999999 !important; 
      background: #ffffff !important;
      border: 2px solid #ff4ff5 !important;
      box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.5) !important;
      color: #000 !important;
    }
    .ui-state-default { color: #333 !important; }
    #fecha_pago { cursor: pointer; background: white !important; color: black !important; }
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

  <nav class="menu-vertical" id="menuVertical">
    <ul>
      <li><a href="index.php"><i data-lucide="home"></i> Inicio</a></li>
      <li><a href="juegos.php"><i data-lucide="gamepad-2"></i> Juegos</a></li>
      <li><a href="gestion_consultas.php"><i data-lucide="user"></i> Gestión</a></li>
      <li><a href="logout.php"><i data-lucide="log-out"></i> Salir</a></li>
    </ul>
  </nav>

  <main class="contenido-principal">
    <section class="formulario-usuario" style="max-width: 500px; margin: 100px auto;">
      <h1>Recarga de Juego</h1>
      <div style="margin-bottom: 20px;"><?php echo $mensaje; ?></div>

      <form id="formRecarga" method="POST" action="recargas.php">
        
        <label for="juego">Selecciona el juego:</label>
        <select name="juego" id="juego" style="width:100%; padding:8px; border-radius:8px; margin-bottom: 10px;" required>
          <option value="cod">Call of Duty Mobile</option>
          <option value="freefire">Free Fire</option>
          <option value="ml">Mobile Legends</option>
        </select>

        <label for="usuarioJuego">ID o Nickname:</label>
        <input type="text" name="usuarioJuego" id="usuarioJuego" required />

        <label for="monto">Monto a pagar (Bs.):</label>
        <input type="number" name="monto" id="monto" required />

        <label for="referencia">Referencia Bancaria:</label>
        <input type="text" name="referencia" id="referencia" required />

        <label for="fecha_pago">Fecha del pago:</label>
        <input type="text" name="fecha_pago" id="fecha_pago" 
               placeholder="Toca para elegir fecha" readonly required />

        <button type="submit" class="btn-morado" style="width:100%; margin-top:20px;">
          Confirmar Recarga
        </button>
      </form>
    </section>
  </main>

  <script>
    $(document).ready(function() {
      
      $("#fecha_pago").datepicker({
        dateFormat: "yy-mm-dd",
        changeMonth: true,
        changeYear: true
      });

      $("#btn-menu").click(function() {
        $("#menuVertical").toggleClass("activo");
      });

    });
  </script>

</body>
</html>