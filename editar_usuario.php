<?php
include 'conexion.php';
$id = $_GET['id']; // Recibimos el ID del usuario a editar

// 1. Se buscan los datos actuales del usuario
$stmt = $pdo->prepare("SELECT * FROM USUARIO WHERE ID_Usuario = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch();

$mensaje = "";

// 2. Lógica para GUARDAR los cambios
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $tlf = $_POST['telefono'];

    $sql = "UPDATE USUARIO SET Nombre_Completo = ?, Correo_Electronico = ?, Telefono = ? WHERE ID_Usuario = ?";
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
  <main class="contenido-principal">
    <div class="formulario-usuario">
      <h1>Modificar Cliente</h1>
      <form method="POST">
        <label>Nombre Completo:</label>
        <input type="text" name="nombre" value="<?php echo $usuario['Nombre_Completo']; ?>" required>

        <label>Correo Electrónico:</label>
        <input type="email" name="correo" value="<?php echo $usuario['Correo_Electronico']; ?>" required>

        <label>Teléfono:</label>
        <input type="text" name="telefono" value="<?php echo $usuario['Telefono']; ?>" required>

        <button type="submit" class="btn-morado" style="width:100%; margin-top:20px; padding:10px;">Guardar Cambios</button>
        <br><br>
        <a href="gestion_consultas.php" style="display:block; text-align:center;">Volver atrás</a>
      </form>
    </div>
  </main>
</body>
</html>
