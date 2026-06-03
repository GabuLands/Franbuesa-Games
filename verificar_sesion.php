<?php
// Forzar la zona horaria de Venezuela para sincronizar con JavaScript
date_default_timezone_set('America/Caracas');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'Conexion.php'; // Tu archivo real de conexión

function verificarSesionUnica() {
    global $pdo; // Habilitamos la variable $pdo de Conexion.php
    
    // Si no ha iniciado sesión en el navegador, no hay nada que verificar
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['ID_Sesion'])) {
        return false;
    }
    
    $usuario_id = $_SESSION['usuario_id'];
    $id_sesion_actual = $_SESSION['ID_Sesion'];
    $fecha_actual = date('Y-m-d H:i:s');
    
    try {
        // 1. Verificar el estado de la sesión ACTUAL del usuario en la Base de Datos
        $stmt = $pdo->prepare("SELECT Estado_Sesion FROM sesiones WHERE ID_Sesion = ?");
        $stmt->execute([$id_sesion_actual]);
        $sesion_actual_db = $stmt->fetch();
        
        // Si la sesión fue marcada como 'Inactiva' o no existe
        if (!$sesion_actual_db || $sesion_actual_db['Estado_Sesion'] !== 'Activa') {
            return [
                'bloqueado' => true,
                'motivo' => 'expirada_o_remota'
            ];
        }

        // 2. Buscar si hay OTRA sesión más reciente que de verdad esté activa y vigente en el tiempo
        $stmt_otras = $pdo->prepare("SELECT IP_Direccion, User_Agent, Last_Activity_At 
                                     FROM sesiones 
                                     WHERE ID_Usuario = ? 
                                       AND ID_Sesion != ? 
                                       AND Estado_Sesion = 'Activa' 
                                       AND Expired_At > ?
                                     ORDER BY ID_Sesion DESC LIMIT 1");
        $stmt_otras->execute([$usuario_id, $id_sesion_actual, $fecha_actual]);
        $otra_sesion = $stmt_otras->fetch();
        
        if ($otra_sesion) {
            // Existe otra sesión activa concurrente y viva
            return [
                'bloqueado' => true,
                'motivo' => 'otra_computadora',
                'detalles' => $otra_sesion
            ];
        }
        
        // 3. Si todo está en orden, actualizamos el último movimiento y extendemos la expiración (1 minuto para pruebas)
        $nueva_expiracion = date('Y-m-d H:i:s', strtotime('+1 minutes'));
        $stmt_update = $pdo->prepare("UPDATE sesiones SET Last_Activity_At = ?, Expired_At = ? WHERE ID_Sesion = ?");
        $stmt_update->execute([$fecha_actual, $nueva_expiracion, $id_sesion_actual]);
        
    } catch (PDOException $e) {
        error_log("Error verificando sesión: " . $e->getMessage());
    }
    
    return false;
}

// Función obligatoria para colocar en la parte superior de tus páginas protegidas
function requerirSesionUnica() {
    $verificacion = verificarSesionUnica();
    
    if ($verificacion !== false && $verificacion['bloqueado']) {
        $motivo = $verificacion['motivo'];
        
        // Limpiamos y destruimos la sesión del navegador
        $_SESSION = array();
        session_destroy();
        
        // Redirigimos al login con el aviso correspondiente
        header("Location: login.php?error_sesion=" . $motivo);
        exit();
    }
    
    // Si ni siquiera existe la sesión básica, va al login
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit();
    }
}
?>