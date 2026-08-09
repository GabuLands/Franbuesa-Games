<?php
/**
 * Módulo Integrado de Resguardo, Restauración y Utilidades de BD
 * Proyecto: Franbuesa-Games
 */

class PostgreSQLBackup {
    private $host;
    private $port;
    private $database;
    private $username;
    private $password;
    private $backupDir;
    // 💡 Ruta absoluta a los ejecutables de PostgreSQL 17 en Windows
    private $pgPath = 'C:\\Program Files\\PostgreSQL\\17\\bin\\';
    
    public function __construct($host, $port, $database, $username, $password, $backupDir = 'backups') {
        $this->host = $host;
        $this->port = $port;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
        $this->backupDir = $backupDir;
        
        if (!file_exists($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    public function testConnection() {
        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->database}";
            $pdo = new PDO($dsn, $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return ['success' => true, 'message' => 'Conexión exitosa'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    public function getTables() {
        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->database}";
            $pdo = new PDO($dsn, $this->username, $this->password);
            $stmt = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // 1. BACKUP COMPLETO
    public function backupDatabase($customName = null) {
        $cleanName = $customName ? preg_replace('/[^a-zA-Z0-9_\.-]/', '', $customName) : null;
        $filename = $cleanName ?: $this->database . '_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $this->backupDir . '/' . $filename;
        
        putenv("PGPASSWORD={$this->password}");
        $command = sprintf(
            '"%spg_dump" -h %s -p %s -U %s -F p -f %s %s 2>&1',
            $this->pgPath,
            escapeshellarg($this->host),
            escapeshellarg($this->port),
            escapeshellarg($this->username),
            escapeshellarg($filepath),
            escapeshellarg($this->database)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($filepath)) {
            $this->compressBackup($filepath);
            return [
                'success' => true,
                'filename' => $filename . '.gz',
                'size' => filesize($filepath . '.gz'),
                'message' => 'Backup completo creado exitosamente'
            ];
        }
        return ['success' => false, 'message' => 'Error: ' . implode("\n", $output)];
    }
    
    // 2. BACKUP DE TABLAS ESPECÍFICAS
    public function backupSpecificTables($tables, $customName = null) {
        if (empty($tables)) {
            return ['success' => false, 'message' => 'Seleccione al menos una tabla'];
        }
        
        $cleanName = $customName ? preg_replace('/[^a-zA-Z0-9_\.-]/', '', $customName) : null;
        $filename = $cleanName ?: $this->database . '_' . date('Y-m-d_H-i-s') . '_tables.sql';
        $filepath = $this->backupDir . '/' . $filename;
        
        $tablesList = implode(' -t ', array_map('escapeshellarg', $tables));
        
        putenv("PGPASSWORD={$this->password}");
        $command = sprintf(
            '"%spg_dump" -h %s -p %s -U %s -F p -t %s -f %s %s 2>&1',
            $this->pgPath,
            escapeshellarg($this->host),
            escapeshellarg($this->port),
            escapeshellarg($this->username),
            $tablesList,
            escapeshellarg($filepath),
            escapeshellarg($this->database)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($filepath)) {
            $this->compressBackup($filepath);
            return ['success' => true, 'filename' => $filename . '.gz', 'message' => 'Backup de tablas completado'];
        }
        return ['success' => false, 'message' => 'Error: ' . implode("\n", $output)];
    }
    
    // 3. BACKUP SOLO ESTRUCTURA
    public function backupStructureOnly($customName = null) {
        $cleanName = $customName ? preg_replace('/[^a-zA-Z0-9_\.-]/', '', $customName) : null;
        $filename = $cleanName ?: $this->database . '_' . date('Y-m-d_H-i-s') . '_structure.sql';
        $filepath = $this->backupDir . '/' . $filename;
        
        putenv("PGPASSWORD={$this->password}");
        $command = sprintf(
            '"%spg_dump" -h %s -p %s -U %s -F p -s -f %s %s 2>&1',
            $this->pgPath,
            escapeshellarg($this->host),
            escapeshellarg($this->port),
            escapeshellarg($this->username),
            escapeshellarg($filepath),
            escapeshellarg($this->database)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($filepath)) {
            $this->compressBackup($filepath);
            return ['success' => true, 'filename' => $filename . '.gz', 'message' => 'Backup de estructura completado'];
        }
        return ['success' => false, 'message' => 'Error: ' . implode("\n", $output)];
    }
    
    // 4. RESTAURAR BD (CON OPCIONES DE RECREAR O LIMPIAR)
    public function restoreBackup($filename, $options = []) {
        $filepath = $this->backupDir . '/' . basename($filename);
        
        if (!file_exists($filepath)) {
            return ['success' => false, 'message' => 'El archivo no existe'];
        }
        
        $sqlFile = $filepath;
        $isCompressed = false;
        
        if (pathinfo($filepath, PATHINFO_EXTENSION) === 'gz') {
            $isCompressed = true;
            $sqlFile = str_replace('.gz', '', $filepath);
            $this->decompressBackup($filepath, $sqlFile);
        }
        
        $dropDatabase = isset($options['drop_database']) ? $options['drop_database'] : false;
        $cleanBeforeRestore = isset($options['clean_before_restore']) ? $options['clean_before_restore'] : false;
        
        if ($dropDatabase) {
            $dropResult = $this->dropAndRecreateDatabase();
            if (!$dropResult['success']) return $dropResult;
        }
        
        if ($cleanBeforeRestore && !$dropDatabase) {
            $cleanResult = $this->cleanDatabase();
            if (!$cleanResult['success']) return $cleanResult;
        }
        
        putenv("PGPASSWORD={$this->password}");
        $command = sprintf(
            '"%spsql" -h %s -p %s -U %s -d %s -f %s 2>&1',
            $this->pgPath,
            escapeshellarg($this->host),
            escapeshellarg($this->port),
            escapeshellarg($this->username),
            escapeshellarg($this->database),
            escapeshellarg($sqlFile)
        );
        
        exec($command, $output, $returnCode);
        
        if ($isCompressed && file_exists($sqlFile)) {
            unlink($sqlFile);
        }
        
        if ($returnCode === 0) {
            return ['success' => true, 'message' => 'Restauración completada exitosamente'];
        }
        return ['success' => false, 'message' => 'Error: ' . implode("\n", $output)];
    }
    
    // 5. SUBIR ARCHIVO Y RESTAURAR
    public function restoreFromUploadedFile($tmpFile, $originalName) {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, ['sql', 'gz'])) {
            return ['success' => false, 'message' => 'Solo se permiten archivos .sql o .gz'];
        }
        
        $filename = 'uploaded_' . date('Y-m-d_H-i-s') . '.' . $extension;
        $filepath = $this->backupDir . '/' . $filename;
        
        if (!move_uploaded_file($tmpFile, $filepath)) {
            return ['success' => false, 'message' => 'Error al subir el archivo al servidor'];
        }
        
        return $this->restoreBackup($filename, ['clean_before_restore' => true]);
    }
    
    // 6. RESTAURAR TABLAS ESPECÍFICAS
    public function restoreSpecificTables($filename, $tables) {
        if (empty($tables)) {
            return ['success' => false, 'message' => 'Seleccione al menos una tabla'];
        }
        
        $filepath = $this->backupDir . '/' . basename($filename);
        if (!file_exists($filepath)) {
            return ['success' => false, 'message' => 'El archivo no existe'];
        }
        
        $sqlFile = $filepath;
        $isCompressed = false;
        
        if (pathinfo($filepath, PATHINFO_EXTENSION) === 'gz') {
            $isCompressed = true;
            $sqlFile = str_replace('.gz', '', $filepath);
            $this->decompressBackup($filepath, $sqlFile);
        }
        
        $content = file_get_contents($sqlFile);
        $restored = [];
        $errors = [];
        
        foreach ($tables as $table) {
            $pattern = '/CREATE TABLE ' . preg_quote($table, '/') . '.*?;/is';
            preg_match($pattern, $content, $createMatches);
            
            $insertPattern = '/INSERT INTO ' . preg_quote($table, '/') . ' .*?;/is';
            preg_match_all($insertPattern, $content, $insertMatches);
            
            if (!empty($createMatches)) {
                try {
                    $this->executeSQL($createMatches[0]);
                    $restored[] = $table;
                    
                    foreach ($insertMatches[0] as $insert) {
                        try {
                            $this->executeSQL($insert);
                        } catch (Exception $e) {
                            $errors[] = "Error en datos de {$table}";
                        }
                    }
                } catch (Exception $e) {
                    $errors[] = "Error en estructura de {$table}";
                }
            }
        }
        
        if ($isCompressed && file_exists($sqlFile)) {
            unlink($sqlFile);
        }
        
        return empty($errors) 
            ? ['success' => true, 'message' => 'Tablas restauradas: ' . implode(', ', $restored)]
            : ['success' => false, 'message' => 'Restauración parcial con errores: ' . implode(', ', $errors)];
    }
    
    private function dropAndRecreateDatabase() {
        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname=postgres";
            $pdo = new PDO($dsn, $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $pdo->exec("SELECT pg_terminate_backend(pg_stat_activity.pid) FROM pg_stat_activity WHERE pg_stat_activity.datname = '{$this->database}' AND pid <> pg_backend_pid()");
            $pdo->exec("DROP DATABASE IF EXISTS \"{$this->database}\"");
            $pdo->exec("CREATE DATABASE \"{$this->database}\"");
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al recrear la BD: ' . $e->getMessage()];
        }
    }
    
    private function cleanDatabase() {
        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->database}";
            $pdo = new PDO($dsn, $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $pdo->exec("SET session_replication_role = replica");
            $tables = $this->getTables();
            foreach ($tables as $table) {
                $pdo->exec("TRUNCATE TABLE \"{$table}\" CASCADE");
            }
            $pdo->exec("SET session_replication_role = DEFAULT");
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al limpiar BD: ' . $e->getMessage()];
        }
    }
    
    private function executeSQL($sql) {
        $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->database}";
        $pdo = new PDO($dsn, $this->username, $this->password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec($sql);
    }
    
    // UTILIDADES
    public function verifyBackup($filename) {
        $filepath = $this->backupDir . '/' . basename($filename);
        if (!file_exists($filepath)) {
            return ['success' => false, 'message' => 'El archivo no existe'];
        }
        
        $result = [
            'filename' => basename($filename),
            'size' => $this->formatBytes(filesize($filepath)),
            'modified' => date('Y-m-d H:i:s', filemtime($filepath))
        ];
        
        if (pathinfo($filepath, PATHINFO_EXTENSION) === 'gz') {
            $gz = @gzopen($filepath, 'rb');
            if ($gz === false) {
                return ['success' => false, 'message' => 'Archivo corrupto o imposible de abrir'];
            }
            $content = gzread($gz, 512);
            gzclose($gz);
            
            if (strpos($content, 'CREATE TABLE') !== false || strpos($content, 'INSERT') !== false || strpos($content, 'PostgreSQL database dump') !== false) {
                return ['success' => true, 'message' => 'Backup válido e íntegro', 'info' => $result];
            }
        }
        return ['success' => true, 'message' => 'Backup verificado', 'info' => $result];
    }
    
    private function compressBackup($filepath) {
        if (file_exists($filepath)) {
            $gzPath = $filepath . '.gz';
            $fp = gzopen($gzPath, 'w9');
            gzwrite($fp, file_get_contents($filepath));
            gzclose($fp);
            unlink($filepath);
        }
    }
    
    private function decompressBackup($gzPath, $outputPath) {
        $gz = gzopen($gzPath, 'rb');
        $fp = fopen($outputPath, 'wb');
        while (!gzeof($gz)) {
            fwrite($fp, gzread($gz, 4096));
        }
        fclose($fp);
        gzclose($gz);
    }
    
    public function cleanOldBackups($daysToKeep = 30) {
        $files = glob($this->backupDir . '/*.sql.gz');
        $deleted = [];
        $now = time();
        
        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file)) > ($daysToKeep * 24 * 60 * 60)) {
                unlink($file);
                $deleted[] = basename($file);
            }
        }
        return $deleted;
    }
    
    public function listBackups() {
        $backups = [];
        $files = glob($this->backupDir . '/*.sql.gz');
        
        foreach ($files as $file) {
            $backups[] = [
                'name' => basename($file),
                'size' => $this->formatBytes(filesize($file)),
                'size_bytes' => filesize($file),
                'date' => date('Y-m-d H:i:s', filemtime($file)),
                'timestamp' => filemtime($file)
            ];
        }
        
        usort($backups, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
        
        return $backups;
    }
    
    public function downloadBackup($filename) {
        $filepath = $this->backupDir . '/' . basename($filename);
        if (file_exists($filepath)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            exit;
        }
        return false;
    }
    
    public function deleteBackup($filename) {
        $filepath = $this->backupDir . '/' . basename($filename);
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }
    
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

// ── CONTROLADOR DE ACCIONES ──
session_start();
$message = '';
$messageType = '';

$defaultConfig = [
    'host' => '',
    'port' => '',
    'database' => '',
    'username' => '',
    'password' => ''
];

if (!isset($_SESSION['db_config'])) {
    $_SESSION['db_config'] = $defaultConfig;
}

if (isset($_POST['action']) && $_POST['action'] === 'test_connection') {
    $_SESSION['db_config'] = [
        'host' => $_POST['host'] ?? '127.0.0.1',
        'port' => $_POST['port'] ?? '5432',
        'database' => $_POST['database'] ?? '',
        'username' => $_POST['username'] ?? '',
        'password' => $_POST['password'] ?? ''
    ];
}

$config = $_SESSION['db_config'];
$backup = new PostgreSQLBackup(
    $config['host'], $config['port'], $config['database'],
    $config['username'], $config['password']
);

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'test_connection':
            $result = $backup->testConnection();
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            break;
        case 'full_backup':
            $result = $backup->backupDatabase($_POST['custom_name'] ?? null);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            break;
        case 'structure_backup':
            $result = $backup->backupStructureOnly($_POST['custom_name'] ?? null);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            break;
        case 'tables_backup':
            if (!empty($_POST['tables'])) {
                $result = $backup->backupSpecificTables($_POST['tables'], $_POST['custom_name'] ?? null);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
            } else {
                $message = 'Seleccione al menos una tabla';
                $messageType = 'error';
            }
            break;
        case 'restore_backup':
            if (!empty($_POST['backup_file'])) {
                $options = [
                    'drop_database' => isset($_POST['drop_database']),
                    'clean_before_restore' => isset($_POST['clean_before_restore'])
                ];
                $result = $backup->restoreBackup($_POST['backup_file'], $options);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
            } else {
                $message = 'Seleccione un archivo de backup';
                $messageType = 'error';
            }
            break;
        case 'upload_restore':
            if (isset($_FILES['restore_file']) && $_FILES['restore_file']['error'] === UPLOAD_ERR_OK) {
                $result = $backup->restoreFromUploadedFile($_FILES['restore_file']['tmp_name'], $_FILES['restore_file']['name']);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
            } else {
                $message = 'Error al subir el archivo';
                $messageType = 'error';
            }
            break;
        case 'restore_tables':
            if (!empty($_POST['backup_file']) && !empty($_POST['restore_tables_list'])) {
                $result = $backup->restoreSpecificTables($_POST['backup_file'], $_POST['restore_tables_list']);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
            } else {
                $message = 'Seleccione un backup y al menos una tabla a restaurar';
                $messageType = 'error';
            }
            break;
        case 'verify_backup':
            if (!empty($_POST['backup_file'])) {
                $result = $backup->verifyBackup($_POST['backup_file']);
                $message = $result['message'] . (isset($result['info']) ? " ({$result['info']['size']})" : "");
                $messageType = $result['success'] ? 'success' : 'error';
            }
            break;
        case 'clean_backups':
            $days = (int)($_POST['days_to_keep'] ?? 30);
            $deleted = $backup->cleanOldBackups($days);
            $message = count($deleted) > 0 ? "Eliminados " . count($deleted) . " backups" : "No hay backups antiguos para eliminar";
            $messageType = 'success';
            break;
        case 'delete_backup':
            if (!empty($_POST['backup_file'])) {
                $backup->deleteBackup($_POST['backup_file']);
                $message = 'Backup eliminado';
                $messageType = 'success';
            }
            break;
        case 'download_backup':
            if (!empty($_POST['backup_file'])) {
                $backup->downloadBackup($_POST['backup_file']);
            }
            break;
        case 'download_backup':
            if (!empty($_POST['backup_file'])) {
                $backup->downloadBackup($_POST['backup_file']);
            }
            break;
        // 🚪 AGREGAR DESDE AQUÍ:
        case 'logout':
            unset($_SESSION['db_config']);
            session_destroy();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
    }
}

$backups = $backup->listBackups();
$tables = $backup->getTables();
$isConnected = $backup->testConnection()['success'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Franbuesa-Games | Gestión de Resguardos y Restauración BD</title>
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
            <a href="logout.php" class="btn-morado">Cerrar sesión</a>
    </div>
</header>

<nav class="menu-vertical" id="menuVertical">
    <ul>
        <li><a href="index.php"><i data-lucide="home"></i> Inicio</a></li>
        <li><a href="juegos.php"><i data-lucide="gamepad-2"></i> Juegos</a></li>
        <li><a href="recargas.php"><i data-lucide="dollar-sign"></i> Recargas</a></li>
        <li><a href="gestion_consultas.php" class="active"><i data-lucide="user"></i> Panel Gestión</a></li>
        <li><a href="logout.php"><i data-lucide="log-out"></i> Salir</a></li>
    </ul>
</nav>
<div class="panel-container">

<div class="container">
    <div class="header">
        <h1>Módulo de Seguridad y Gestión de Respaldos PostgreSQL</h1>
    </div>
    
    <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <!-- 📊 ESTADÍSTICAS EN TIEMPO REAL -->
    <div class="stats">
        <div class="stat-card"><div class="stat-number"><?php echo count($backups); ?></div><div class="stat-label">Backups Guardados</div></div>
        <div class="stat-card"><div class="stat-number"><?php echo count($tables); ?></div><div class="stat-label">Tablas Detectadas</div></div>
        <div class="stat-card"><div class="stat-number"><?php echo $isConnected ? '✅' : '❌'; ?></div><div class="stat-label">Estado Conexión</div></div>
    </div>
    
    <div class="grid">
        <!-- 🔌 Configuración de Conexión -->
        <div class="card">
            <h2>🔌 Conexión BD</h2>
            <form method="POST">
                <input type="hidden" name="action" value="test_connection">
                <div class="form-group"><label>Host:</label><input type="text" name="host" value="<?php echo htmlspecialchars($config['host']); ?>" required></div>
                <div class="form-group"><label>Puerto:</label><input type="text" name="port" value="<?php echo htmlspecialchars($config['port']); ?>" required></div>
                <div class="form-group"><label>Base Datos:</label><input type="text" name="database" value="<?php echo htmlspecialchars($config['database']); ?>" required></div>
                <div class="form-group"><label>Usuario:</label><input type="text" name="username" value="<?php echo htmlspecialchars($config['username']); ?>" required></div>
                <div class="form-group"><label>Contraseña:</label><input type="password" name="password" value="<?php echo htmlspecialchars($config['password']); ?>"></div>
                <button type="submit" class="info">🔌 Probar / Guardar Conexión</button>
            </form>
            <!-- 🚪 BOTÓN DE DESCONECTAR (Agrega estas líneas): -->
            <form method="POST" style="margin-top: 10px;">
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="danger">🚪 Desconectar / Limpiar Sesión</button>
            </form>
        </div>
        
        <!-- 💾 SECCIÓN BACKUP -->
        <div class="card">
            <h2>💾 Backup Completo BD</h2>
            <form method="POST">
                <input type="hidden" name="action" value="full_backup">
                <div class="form-group"><label>Nombre personalizado (opcional):</label><input type="text" name="custom_name" placeholder="backup_completo.sql"></div>
                <button type="submit" class="success">✅ Crear Backup Completo</button>
            </form>
            
            <hr>
            
            <h2>🏗️ Backup Solo Estructura</h2>
            <form method="POST">
                <input type="hidden" name="action" value="structure_backup">
                <div class="form-group"><label>Nombre personalizado (opcional):</label><input type="text" name="custom_name" placeholder="estructura.sql"></div>
                <button type="submit" class="info">📐 Backup Solo Estructura</button>
            </form>
        </div>
        
        <!-- 📋 BACKUP DE TABLAS ESPECÍFICAS -->
        <div class="card">
            <h2>📋 Backup Tablas Específicas</h2>
            <form method="POST">
                <input type="hidden" name="action" value="tables_backup">
                <div class="form-group">
                    <label>Seleccionar tablas:</label>
                    <div class="checkbox-group">
                        <?php if (!empty($tables)): ?>
                            <?php foreach ($tables as $table): ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" name="tables[]" value="<?php echo htmlspecialchars($table); ?>" id="tb_<?php echo md5($table); ?>">
                                    <label for="tb_<?php echo md5($table); ?>"><?php echo htmlspecialchars($table); ?></label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color:#999;">Sin conexión o no se encontraron tablas</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-group"><label>Nombre opcional:</label><input type="text" name="custom_name" placeholder="mis_tablas.sql"></div>
                <button type="submit" class="info">📝 Backup Seleccionadas</button>
            </form>
        </div>
        
        <!-- 🔄 SECCIÓN RESTORE -->
        <div class="card">
            <h2>🔄 Restaurar desde Backup Existente</h2>
            <form method="POST">
                <input type="hidden" name="action" value="restore_backup">
                <div class="form-group">
                    <label>Seleccionar backup guardado:</label>
                    <select name="backup_file" required>
                        <option value="">-- Seleccionar --</option>
                        <?php foreach ($backups as $bk): ?>
                            <option value="<?php echo htmlspecialchars($bk['name']); ?>"><?php echo htmlspecialchars($bk['name']); ?> (<?php echo $bk['size']; ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="restore-options">
                    <div class="checkbox-item">
                        <input type="checkbox" name="clean_before_restore" id="clean_before">
                        <label for="clean_before"><b>Opción:</b> Limpiar BD (TRUNCATE) antes de restaurar</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="drop_database" id="drop_db">
                        <label for="drop_db" style="color: red;"><b>Opción:</b> Eliminar y Recrear BD completa antes de restaurar</label>
                    </div>
                </div>
                <button type="submit" class="warning" onclick="return confirm('¿Está seguro de restaurar este backup?');">⚠️ Restaurar BD</button>
            </form>

            <hr>

            <h2>📤 Subir Archivo y Restaurar</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_restore">
                <div class="form-group">
                    <label>Archivo SQL o SQL.GZ:</label>
                    <input type="file" name="restore_file" accept=".sql,.gz" required>
                </div>
                <button type="submit" class="danger" onclick="return confirm('¿Desea subir y restaurar el archivo?');">📤 Subir y Restaurar</button>
            </form>
        </div>

        <!-- 🧩 RESTAURAR TABLAS ESPECÍFICAS -->
        <div class="card">
            <h2>🧩 Restaurar Tablas Específicas</h2>
            <form method="POST">
                <input type="hidden" name="action" value="restore_tables">
                <div class="form-group">
                    <label>Seleccionar archivo de resguardo:</label>
                    <select name="backup_file" required>
                        <option value="">-- Seleccionar Backup --</option>
                        <?php foreach ($backups as $bk): ?>
                            <option value="<?php echo htmlspecialchars($bk['name']); ?>"><?php echo htmlspecialchars($bk['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Seleccionar tablas a restaurar:</label>
                    <div class="checkbox-group">
                        <?php if (!empty($tables)): ?>
                            <?php foreach ($tables as $table): ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" name="restore_tables_list[]" value="<?php echo htmlspecialchars($table); ?>" id="rst_tb_<?php echo md5($table); ?>">
                                    <label for="rst_tb_<?php echo md5($table); ?>"><?php echo htmlspecialchars($table); ?></label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color:#999;">Sin conexión o tablas disponibles</p>
                        <?php endif; ?>
                    </div>
                </div>
                <button type="submit" class="warning" onclick="return confirm('¿Restaurar únicamente las tablas seleccionadas?');">🧩 Restaurar Tablas Seleccionadas</button>
            </form>
        </div>

        <!-- 🧹 UTILIDADES: LIMPIEZA AUTOMÁTICA -->
        <div class="card">
            <h2>🧹 Limpieza Automática por Antigüedad</h2>
            <form method="POST">
                <input type="hidden" name="action" value="clean_backups">
                <div class="form-group">
                    <label>Eliminar resguardos con más de (días):</label>
                    <input type="number" name="days_to_keep" value="30" min="1" required>
                </div>
                <button type="submit" class="warning">🗑️ Ejecutar Limpieza Automática</button>
            </form>
        </div>
    </div>

    <!-- 📂 ARCHIVOS DE BACKUP Y UTILIDADES ADICIONALES -->
    <div class="card" style="margin-top: 20px;">
        <h2>📂 Archivos de Backup Almacenados (Verificación, Descarga, Eliminación)</h2>
        <div class="backup-list">
            <?php if (!empty($backups)): ?>
                <?php foreach ($backups as $bk): ?>
                    <div class="backup-item">
                        <div class="backup-info">
                            <div class="backup-name"><?php echo htmlspecialchars($bk['name']); ?></div>
                            <div class="backup-meta">Fecha: <?php echo $bk['date']; ?> | Tamaño: <?php echo $bk['size']; ?></div>
                        </div>
                        <div class="backup-actions">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="verify_backup">
                                <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($bk['name']); ?>">
                                <button type="submit" class="info">🔍 Verificar Integridad</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="download_backup">
                                <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($bk['name']); ?>">
                                <button type="submit" class="success">⬇️ Descargar</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete_backup">
                                <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($bk['name']); ?>">
                                <button type="submit" class="danger" onclick="return confirm('¿Eliminar este resguardo?');">🗑️ Eliminar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color:#718096; text-align:center; padding:10px;">No existen resguardos guardados en el servidor.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

</body>
<script>
    $(document).ready(function() {
      $("#btn-menu").click(function() {
        $("#menuVertical").toggleClass("activo");
      });
      lucide.createIcons();
    });
  </script>
</html>