<?php
date_default_timezone_set('America/Caracas');
include 'Conexion.php'; 
session_start(); 

$error = "";

if (!isset($_SESSION['intentos_fallidos'])) {
    $_SESSION['intentos_fallidos'] = 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $clave  = $_POST['clave'];

    try {
        $fecha_ahora = date('Y-m-d H:i:s');
        
        // Limpieza de sesiones expiradas
        $stmt_clean = $pdo->prepare("UPDATE public.sesiones SET \"Estado_Sesion\" = 'Inactiva' WHERE \"Estado_Sesion\" = 'Activa' AND \"Expired_At\" < ?");
        $stmt_clean->execute([$fecha_ahora]);
        
        // Búsqueda del usuario
        $stmt = $pdo->prepare("SELECT * FROM public.usuario WHERE \"Correo_Electronico\" = ?");
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            
            if ($usuario['Estado_Usuario'] === 'Bloqueado') {
                $error = "🚫 Tu cuenta ha sido bloqueada por seguridad tras 5 intentos fallidos. Contacta al administrador.";
            } 
            else if ($usuario['Contraseña'] == $clave) {
                
                // Buscar y cerrar sesión previa activa
                $stmt_check = $pdo->prepare("SELECT * FROM public.sesiones WHERE \"ID_Usuario\" = ? AND \"Estado_Sesion\" = 'Activa' ORDER BY \"ID_Sesion\" DESC LIMIT 1");
                $stmt_check->execute([$usuario['ID_Usuario']]);
                $sesion_activa = $stmt_check->fetch();

                if ($sesion_activa) {
                    $stmt_close = $pdo->prepare("UPDATE public.sesiones SET \"Estado_Sesion\" = 'Inactiva' WHERE \"ID_Sesion\" = ?");
                    $stmt_close->execute([$sesion_activa['ID_Sesion']]);
                    
                    $detalle_tumba = "Sesión previa [ID: " . $sesion_activa['ID_Sesion'] . "] cerrada automáticamente por nuevo inicio de sesión.";
                    $stmt_audit_tumba = $pdo->prepare("INSERT INTO public.auditoria (\"ID_Usuario\", \"Accion\", \"Tabla_Afectada\", \"Detalle\", \"IP_Direccion\") VALUES (?, 'SESION_SOLAPADA_CIERRE', 'usuario', ?, ?)");
                    $stmt_audit_tumba->execute([$usuario['ID_Usuario'], $detalle_tumba, $_SERVER['REMOTE_ADDR']]);
                }

                $_SESSION['intentos_fallidos'] = 0;
                
                $_SESSION['usuario_id']     = $usuario['ID_Usuario'];
                $_SESSION['usuario_nombre'] = $usuario['Nombre_Completo'];
                $_SESSION['usuario_rol']    = $usuario['Rol'];
                
                $ip_direccion = $_SERVER['REMOTE_ADDR'];
                $user_agent   = $_SERVER['HTTP_USER_AGENT'];
                $estado       = 'Activa';
                
                $created_at       = date('Y-m-d H:i:s');
                $last_activity_at = date('Y-m-d H:i:s');
                $expired_at       = date('Y-m-d H:i:s', strtotime('+2 hours'));

                // Registrar la nueva sesión activa retornando el ID generado
                $sql_sesion = "INSERT INTO public.sesiones (\"ID_Usuario\", \"IP_Direccion\", \"User_Agent\", \"Estado_Sesion\", \"Created_At\", \"Last_Activity_At\", \"Expired_At\") 
                               VALUES (?, ?, ?, ?, ?, ?, ?) 
                               RETURNING \"ID_Sesion\"";

                $stmt_sesion = $pdo->prepare($sql_sesion);
                $stmt_sesion->execute([
                    $usuario['ID_Usuario'], 
                    $ip_direccion, 
                    $user_agent, 
                    $estado, 
                    $created_at, 
                    $last_activity_at, 
                    $expired_at
                ]);

                // Capturamos el ID de la sesión recién creada
                $_SESSION['ID_Sesion'] = $stmt_sesion->fetchColumn();
                
                // Auditoría de inicio exitoso
                $detalle_login = "INICIO DE SESIÓN EXITOSO. El usuario " . $usuario['Nombre_Completo'] . " [ID: " . $usuario['ID_Usuario'] . "] ingresó al sistema.";
                $stmt_audit_ok = $pdo->prepare("INSERT INTO public.auditoria (\"ID_Usuario\", \"Accion\", \"Tabla_Afectada\", \"Detalle\", \"IP_Direccion\") VALUES (?, 'LOGIN_EXITOSO', 'usuario', ?, ?)");
                $stmt_audit_ok->execute([$usuario['ID_Usuario'], $detalle_login, $ip_direccion]);
                
                header("Location: bienvenida.php");
                exit();
            } else {
                $_SESSION['intentos_fallidos']++;
                
                if ($_SESSION['intentos_fallidos'] >= 5) {
                    $stmt_bloqueo = $pdo->prepare("UPDATE public.usuario SET \"Estado_Usuario\" = 'Bloqueado' WHERE \"ID_Usuario\" = ?");
                    $stmt_bloqueo->execute([$usuario['ID_Usuario']]);
                    
                    $detalles_auditoria = "CUENTA BLOQUEADA AUTOMÁTICAMENTE. El usuario con correo [ " . $correo . " ] superó el límite de 5 intentos fallidos.";
                    $stmt_audit = $pdo->prepare("INSERT INTO public.auditoria (\"ID_Usuario\", \"Accion\", \"Tabla_Afectada\", \"Detalle\", \"IP_Direccion\") VALUES (?, 'BLOQUEO_FUERZA_BRUTA', 'usuario', ?, ?)");
                    $stmt_audit->execute([$usuario['ID_Usuario'], $detalles_auditoria, $_SERVER['REMOTE_ADDR']]);
                    
                    $error = "🚫 Has alcanzado el límite de intentos. Tu cuenta ha sido bloqueada por seguridad.";
                    $_SESSION['intentos_fallidos'] = 0; 
                } else {
                    $intentos_restantes = 5 - $_SESSION['intentos_fallidos'];
                    $error = "Contraseña incorrecta. Te quedan $intentos_restantes intentos antes de bloquear la cuenta.";
                    
                    $detalle_fallo = "Intento fallido de inicio de sesión para el correo: " . $correo;
                    $stmt_audit_fail = $pdo->prepare("INSERT INTO public.auditoria (\"ID_Usuario\", \"Accion\", \"Tabla_Afectada\", \"Detalle\", \"IP_Direccion\") VALUES (?, 'INTENTO_FALLIDO', 'usuario', ?, ?)");
                    $stmt_audit_fail->execute([$usuario['ID_Usuario'], $detalle_fallo, $_SERVER['REMOTE_ADDR']]);
                }
            }
        } else {
            $error = "El correo electrónico no se encuentra registrado.";
            
            $detalle_desconocido = "Intento de acceso con correo no registrado: " . $correo;
            $stmt_audit_unknown = $pdo->prepare("INSERT INTO public.auditoria (\"ID_Usuario\", \"Accion\", \"Tabla_Afectada\", \"Detalle\", \"IP_Direccion\") VALUES (NULL, 'LOG_CORREO_INEXISTENTE', 'usuario', ?, ?)");
            $stmt_audit_unknown->execute([$detalle_desconocido, $_SERVER['REMOTE_ADDR']]);
        }
    } catch (PDOException $e) {
        $error = "Error en el sistema de autenticación: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Iniciar sesión | Franbuesa-Games</title>
  <link rel="stylesheet" href="css/estilos.css" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

  <nav class="menu-vertical" id="menuVertical">
    <ul>
      <li><a href="index.php"><i data-lucide="home"></i> Inicio</a></li>
      <li><a href="juegos.php"><i data-lucide="gamepad-2"></i> Juegos</a></li>
      <li><a href="recargas.php"><i data-lucide="dollar-sign"></i> Recargas</a></li>
    </ul>
  </nav>

  <main class="contenido-principal">
    <div class="formulario-usuario">
      <h1>Iniciar sesión</h1>

      <form id="form-login" method="POST" action="login.php">
        <label for="correo">Correo electrónico:</label>
        <input type="email" id="correo" name="correo" required />

        <label for="clave">Contraseña:</label>
        <input type="password" id="clave" name="clave" required />

        <button type="submit" class="btn-morado" style="width:100%; margin-top:20px;">Entrar</button>

        <p class="mensaje-secundario">
          ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
        </p>

        <?php if($error): ?>
          <p style="color: #ff4d4d; text-align: center; margin-top: 10px; font-weight: bold;">
            <?php echo $error; ?>
          </p>
        <?php endif; ?>

        <?php if(isset($_GET['error_sesion'])): ?>
          <p style="color: #ffcc00; text-align: center; margin-top: 10px; font-weight: bold;">
            <?php 
              if($_GET['error_sesion'] == 'otra_computadora') {
                echo "Tu sesión se cerró porque se inició sesión en otro dispositivo.";
              } else {
                echo "Tu sesión ha expirado por inactividad. Por favor, vuelve a ingresar.";
              }
            ?>
          </p>
        <?php endif; ?>
      </form>
    </div>
  </main>

  <script>
    $(document).ready(function() {
      $("#btn-menu").click(function() {
        $("#menuVertical").toggleClass("activo");
      });
      lucide.createIcons();
    });
  </script>
</body>
</html>