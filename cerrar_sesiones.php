<?php
// 1. Cargamos la conexión y el verificador (siguiendo el orden correcto)
require_once 'Conexion.php';
require_once 'verificar_sesion.php';

// 2. Activamos el escudo. El usuario debe estar logueado para usar este archivo
requerirSesionUnica(); 

// 3. Lo ideal para procesar acciones de bases de datos es usar el método POST 
// para evitar que alguien ejecute la acción simplemente escribiendo la URL.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $usuario_id = $_SESSION['usuario_id'];
    $id_sesion_actual = $_SESSION['ID_Sesion']; // El ID que salvamos en login.php

    try {
        $sql = "UPDATE sesiones 
                SET Estado_Sesion = 'Inactiva' 
                WHERE ID_Usuario = ? AND ID_Sesion != ? AND Estado_Sesion = 'Activa'";
                
        // Usamos marcadores (?) puros para que el prepare sea 100% seguro contra Inyección SQL
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id, $id_sesion_actual]);

        // Guardamos el mensaje de éxito en la sesión
        $_SESSION['mensaje'] = "Se han cerrado todas las sesiones en otras computadoras.";
        
        // Redirigimos a tu panel real (gestion_consultas.php)
        header("Location: gestion_consultas.php?status=otras_cerradas");
        exit();

    } catch (PDOException $e) {
        error_log("Error al cerrar otras sesiones: " . $e->getMessage());
        header("Location: gestion_consultas.php?status=error");
        exit();
    }
} else {
    
    header("Location: gestion_consultas.php");
    exit();
}
?>