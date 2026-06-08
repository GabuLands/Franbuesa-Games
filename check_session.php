<?php
// check_session.php
require_once 'Conexion.php';
require_once 'verificar_sesion.php';

header('Content-Type: application/json');

// Si ni siquiera hay sesión en el navegador, no es válida
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['ID_Sesion'])) {
    echo json_encode(['valid' => false]);
    exit;
}

// Ejecutamos nuestra función del escudo
$bloqueado = verificarSesionUnica();

// Si verificarSesionUnica() devuelve un array con 'bloqueado' => true, significa que ya no es válida
if ($bloqueado !== false && isset($bloqueado['bloqueado']) && $bloqueado['bloqueado']) {
    // Aprovechamos de destruir la sesión del navegador inmediatamente
    $_SESSION = array();
    session_destroy();
    
    echo json_encode(['valid' => false, 'motivo' => $bloqueado['motivo']]);
    exit;
}

// Si todo está en orden
echo json_encode(['valid' => true]);
exit;