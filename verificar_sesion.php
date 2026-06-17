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
        
        // LÓGICA REPARADA: Si la sesión en la BD ya no está 'Activa', significa 100% 
        // que el nuevo login fulminante la dio de baja desde otro dispositivo.
        if (!$sesion_actual_db || $sesion_actual_db['Estado_Sesion'] !== 'Activa') {
            return [
                'bloqueado' => true,
                'motivo' => 'otra_computadora' // Cambiado a 'otra_computadora' para mostrar el cartel correcto
            ];
        }

        // 2. Si todo está en orden, actualizamos el último movimiento y extendemos la expiración (2 horas normales)
        $nueva_expiracion = date('Y-m-d H:i:s', strtotime('+2 hours'));
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
        
        // Limpiamos las variables de sesión locales de PHP de forma segura
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        
        // Redirigimos al login con el aviso exacto de concurrencia
        header("Location: login.php?error_sesion=" . $motivo);
        exit();
    }
    
    // Si ni siquiera existe la sesión básica en el navegador, va al login sin mensajes
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit();
    }
}
?>