<?php
require_once 'Conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si el usuario tiene una sesión registrada, la apagamos en la BD antes de borrarla
if (isset($_SESSION['ID_Sesion'])) {
    try {
        $id_sesion = $_SESSION['ID_Sesion'];
        
        $stmt = $pdo->prepare("UPDATE sesiones SET Estado_Sesion = 'Inactiva' WHERE ID_Sesion = ?");
        $stmt->execute([$id_sesion]);
        
    } catch (PDOException $e) {
        error_log("Error al desactivar sesión en logout: " . $e->getMessage());
    }
}

// Limpiar el array de sesión de PHP
$_SESSION = array();

// Destruir la sesión en el servidor
session_destroy();

// Redirigir al login limpiamente
header("Location: login.php");
exit();
?>