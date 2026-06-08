<?php
/**
 * Clase completa para backup y restore de PostgreSQL
 */
class PostgreSQLBackup {
    private $host;
    private $port;
    private $database;
    private $username;
    private $password;
    private $backupDir;
    
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
    
    public function backupDatabase($customName = null) {
        $filename = $customName ?: $this->database . '_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $this->backupDir . '/' . $filename;
        
        $command = sprintf(
            'PGPASSWORD=%s pg_dump -h %s -p %s -U %s -F p -f %s %s 2>&1',
            escapeshellarg($this->password),
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
                'message' => 'Backup completado exitosamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error: ' . implode("\n", $output)
            ];
        }
    }
    
    public function backupSpecificTables($tables, $customName = null) {
        if (empty($tables)) {
            return ['success' => false, 'message' => 'Seleccione al menos una tabla'];
        }
        
        $filename = $customName ?: $this->database . '_' . date('Y-m-d_H-i-s') . '_tables.sql';
        $filepath = $this->backupDir . '/' . $filename;
        
        $tablesList = implode(' -t ', array_map('escapeshellarg', $tables));
        $command = sprintf(
            'PGPASSWORD=%s pg_dump -h %s -p %s -U %s -F p -t %s -f %s %s 2>&1',
            escapeshellarg($this->password),
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
            return [
                'success' => true,
                'filename' => $filename . '.gz',
                'message' => 'Backup de tablas completado'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error: ' . implode("\n", $output)
            ];
        }
    }
    
    public function backupStructureOnly($customName = null) {
        $filename = $customName ?: $this->database . '_' . date('Y-m-d_H-i-s') . '_structure.sql';
        $filepath = $this->backupDir . '/' . $filename;
        
        $command = sprintf(
            'PGPASSWORD=%s pg_dump -h %s -p %s -U %s -F p -s -f %s %s 2>&1',
            escapeshellarg($this->password),
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
                'message' => 'Backup de estructura completado'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error: ' . implode("\n", $output)
            ];
        }
    }
    
    public function restoreBackup($filename, $options = []) {
        $filepath = $this->backupDir . '/' . $filename;
        
        if (!file_exists($filepath)) {
            return ['success' => false, 'message' => 'El archivo no existe'];
        }
        
        // Descomprimir si es necesario
        $sqlFile = $filepath;
        $isCompressed = false;
        
        if (pathinfo($filepath, PATHINFO_EXTENSION) === 'gz') {
            $isCompressed = true;
            $sqlFile = str_replace('.gz', '', $filepath);
            $this->decompressBackup($filepath, $sqlFile);
        }
        
        // Opciones de restauración
        $dropDatabase = isset($options['drop_database']) ? $options['drop_database'] : false;
        $cleanBeforeRestore = isset($options['clean_before_restore']) ? $options['clean_before_restore'] : false;
        
        if ($dropDatabase) {
            $dropResult = $this->dropAndRecreateDatabase();
            if (!$dropResult['success']) {
                return $dropResult;
            }
        }
        
        if ($cleanBeforeRestore) {
            $cleanResult = $this->cleanDatabase();
            if (!$cleanResult['success']) {
                return $cleanResult;
            }
        }
        
        // Restaurar
        $command = sprintf(
            'PGPASSWORD=%s psql -h %s -p %s -U %s -d %s -f %s 2>&1',
            escapeshellarg($this->password),
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
        } else {
            return ['success' => false, 'message' => 'Error: ' . implode("\n", $output)];
        }
    }
    
    public function restoreFromUploadedFile($tmpFile, $originalName) {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, ['sql', 'gz'])) {
            return ['success' => false, 'message' => 'Solo archivos .sql o .gz'];
        }
        
        $filename = 'uploaded_' . date('Y-m-d_H-i-s') . '.' . $extension;
        $filepath = $this->backupDir . '/' . $filename;
        
        if (!move_uploaded_file($tmpFile, $filepath)) {
            return ['success' => false, 'message' => 'Error al subir el archivo'];
        }
        
        return $this->restoreBackup($filename, ['clean_before_restore' => true]);
    }
    
    public function restoreSpecificTables($filename, $tables) {
        if (empty($tables)) {
            return ['success' => false, 'message' => 'Seleccione al menos una tabla'];
        }
        
        $filepath = $this->backupDir . '/' . $filename;
        
        if (!file_exists($filepath)) {
            return ['success' => false, 'message' => 'El archivo no existe'];
        }
        
        // Descomprimir
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
            $pattern = '/CREATE TABLE ' . $table . '.*?;/is';
            preg_match($pattern, $content, $createMatches);
            
            $insertPattern = '/INSERT INTO ' . $table . ' .*?;/is';
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
        
        if (empty($errors)) {
            return ['success' => true, 'message' => 'Tablas restauradas: ' . implode(', ', $restored)];
        } else {
            return ['success' => false, 'message' => 'Restauración parcial con errores'];
        }
    }
    
    private function dropAndRecreateDatabase() {
        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname=postgres";
            $pdo = new PDO($dsn, $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $pdo->exec("SELECT pg_terminate_backend(pg_stat_activity.pid) FROM pg_stat_activity WHERE pg_stat_activity.datname = '{$this->database}' AND pid <> pg_backend_pid()");
            $pdo->exec("DROP DATABASE IF EXISTS {$this->database}");
            $pdo->exec("CREATE DATABASE {$this->database}");
            
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
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
                $pdo->exec("TRUNCATE TABLE {$table} CASCADE");
            }
            $pdo->exec("SET session_replication_role = DEFAULT");
            
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    private function executeSQL($sql) {
        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->database}";
            $pdo = new PDO($dsn, $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec($sql);
            return true;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    public function verifyBackup($filename) {
        $filepath = $this->backupDir . '/' . $filename;
        
        if (!file_exists($filepath)) {
            return ['success' => false, 'message' => 'El archivo no existe'];
        }
        
        $result = [
            'filename' => $filename,
            'size' => $this->formatBytes(filesize($filepath)),
            'modified' => date('Y-m-d H:i:s', filemtime($filepath))
        ];
        
        if (pathinfo($filepath, PATHINFO_EXTENSION) === 'gz') {
            $gz = @gzopen($filepath, 'rb');
            if ($gz === false) {
                return ['success' => false, 'message' => 'Archivo corrupto'];
            }
            $content = gzread($gz, 100);
            gzclose($gz);
            
            if (strpos($content, 'CREATE TABLE') !== false || strpos($content, 'INSERT') !== false) {
                $result['valid'] = true;
                return ['success' => true, 'message' => 'Backup válido', 'info' => $result];
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
        $filepath = $this->backupDir . '/' . $filename;
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
        $filepath = $this->backupDir . '/' . $filename;
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

// Procesar acciones
session_start();
$message = '';
$messageType = '';

$defaultConfig = [
    'host' => 'localhost',
    'port' => '5432',
    'database' => 'mi_base_datos',
    'username' => 'mi_usuario',
    'password' => 'mi_contraseña'
];

if (!isset($_SESSION['db_config'])) {
    $_SESSION['db_config'] = $defaultConfig;
}

if (isset($_POST['action']) && $_POST['action'] === 'test_connection') {
    $_SESSION['db_config'] = [
        'host' => $_POST['host'],
        'port' => $_POST['port'],
        'database' => $_POST['database'],
        'username' => $_POST['username'],
        'password' => $_POST['password']
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
                $message = 'Seleccione backup y tablas';
                $messageType = 'error';
            }
            break;
        case 'verify_backup':
            if (!empty($_POST['backup_file'])) {
                $result = $backup->verifyBackup($_POST['backup_file']);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
            }
            break;
        case 'clean_backups':
            $days = (int)($_POST['days_to_keep'] ?? 30);
            $deleted = $backup->cleanOldBackups($days);
            $message = count($deleted) > 0 ? "Eliminados " . count($deleted) . " backups" : "No hay backups antiguos";
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
    }
}

$backups = $backup->listBackups();
$tables = $backup->getTables();
$isConnected = $backup->testConnection()['success'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PostgreSQL Backup & Restore Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 1400px; margin: 0 auto; }
        .header { text-align: center; color: white; margin-bottom: 30px; }
        .header h1 { font-size: 2.5em; margin-bottom: 10px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s;
        }
        .card:hover { transform: translateY(-3px); }
        .card h2 {
            color: #667eea;
            margin-bottom: 20px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .card h3 { color: #4a5568; margin: 15px 0 10px; font-size: 1.1em; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #333; font-weight: 500; }
        input[type="text"], input[type="password"], input[type="number"], select, input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        input:focus, select:focus { outline: none; border-color: #667eea; }
        button {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
            margin-top: 10px;
        }
        button:hover { background: #5a67d8; }
        button.danger { background: #e53e3e; }
        button.danger:hover { background: #c53030; }
        button.success { background: #48bb78; }
        button.success:hover { background: #38a169; }
        button.info { background: #4299e1; }
        button.info:hover { background: #3182ce; }
        button.warning { background: #ed8936; }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            animation: slideDown 0.5s ease;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .message.success { background: #c6f6d5; color: #22543d; border-left: 4px solid #48bb78; }
        .message.error { background: #fed7d7; color: #742a2a; border-left: 4px solid #e53e3e; }
        .message.info { background: #bee3f8; color: #2c5282; border-left: 4px solid #4299e1; }
        .backup-list { max-height: 500px; overflow-y: auto; }
        .backup-item {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .backup-info { flex: 1; }
        .backup-name { font-weight: 600; color: #2d3748; margin-bottom: 5px; }
        .backup-meta { font-size: 12px; color: #718096; }
        .backup-actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .backup-actions button { margin: 0; padding: 5px 10px; font-size: 12px; }
        .checkbox-group {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
        }
        .checkbox-item { margin-bottom: 8px; }
        .checkbox-item label { display: inline; margin-left: 8px; }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .stat-number { font-size: 2em; font-weight: bold; color: #667eea; }
        .stat-label { color: #718096; margin-top: 5px; }
        .connection-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }
        .status-connected { background: #48bb78; color: white; }
        .status-disconnected { background: #e53e3e; color: white; }
        .restore-options {
            background: #fef5e7;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        hr { margin: 15px 0; border: none; border-top: 1px solid #e2e8f0; }
        @media (max-width: 768px) {
            .grid { grid-template-columns: 1fr; }
            .backup-item { flex-direction: column; align-items: flex-start; }
            .backup-actions { margin-top: 10px; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>PostgreSQL Backup & Restore Manager</h1>
        <p>Herramienta completa para respaldar y restaurar bases de datos</p>
    </div>
    
    <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <div class="stats">
        <div class="stat-card"><div class="stat-number"><?php echo count($backups); ?></div><div class="stat-label">Backups</div></div>
        <div class="stat-card"><div class="stat-number"><?php echo count($tables); ?></div><div class="stat-label">Tablas</div></div>
        <div class="stat-card"><div class="stat-number"><?php echo $isConnected ? '✅' : '❌'; ?></div><div class="stat-label">Conexión</div></div>
    </div>
    
    <div class="grid">
        <div class="card">
            <h2>🔌 Conexión</h2>
            <form method="POST">
                <input type="hidden" name="action" value="test_connection">
                <div class="form-group"><label>Host:</label><input type="text" name="host" value="<?php echo htmlspecialchars($config['host']); ?>" required></div>
                <div class="form-group"><label>Puerto:</label><input type="text" name="port" value="<?php echo htmlspecialchars($config['port']); ?>" required></div>
                <div class="form-group"><label>Base Datos:</label><input type="text" name="database" value="<?php echo htmlspecialchars($config['database']); ?>" required></div>
                <div class="form-group"><label>Usuario:</label><input type="text" name="username" value="<?php echo htmlspecialchars($config['username']); ?>" required></div>
                <div class="form-group"><label>Contraseña:</label><input type="password" name="password" value="<?php echo htmlspecialchars($config['password']); ?>"></div>
                <button type="submit" class="info">🔌 Probar Conexión</button>
                <span class="connection-status <?php echo $isConnected ? 'status-connected' : 'status-disconnected'; ?>">
                    <?php echo $isConnected ? '✓ Conectado' : '✗ Desconectado'; ?>
                </span>
            </form>
        </div>
        
        <div class="card">
            <h2>💾 Backup Completo</h2>
            <form method="POST">
                <input type="hidden" name="action" value="full_backup">
                <div class="form-group"><label>Nombre (opcional):</label><input type="text" name="custom_name" placeholder="backup.sql"></div>
                <button type="submit" class="success">✅ Crear Backup Completo</button>
            </form>
            
            <hr>
            
            <h2>🏗️ Solo Estructura</h2>
            <form method="POST">
                <input type="hidden" name="action" value="structure_backup">
                <div class="form-group"><label>Nombre (opcional):</label><input type="text" name="custom_name" placeholder="estructura.sql"></div>
                <button type="submit" class="info">📐 Backup Estructura</button>
            </form>
        </div>
        
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
                            <p style="color:#999;">No hay tablas o no hay conexión</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-group"><label>Nombre (opcional):</label><input type="text" name="custom_name" placeholder="tablas.sql"></div>
                <button type="submit" class="info">📝 Backup Seleccionadas</button>
            </form>
        </div>
        
        <div class="card">
            <h2>🔄 Restaurar Backup</h2>
            <form method="POST">
                <input type="hidden" name="action" value="restore_backup">
                <div class="form-group">
                    <label>Seleccionar backup:</label>
                    <select name="backup_file" required>
                        <option value="">-- Seleccionar --</option>
                        <?php foreach ($backups as $bk): ?>
                            <option value="<?php echo htmlspecialchars($bk['name']); ?>"><?php echo htmlspecialchars($bk['name']); ?> (<?php echo $bk['size']; ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="restore-options">
                    <div class="checkbox-item">
                        <input type="checkbox" name="drop_database" id="drop_db">
                        <label for="drop_db">⚠️ Eliminar y recrear BD antes de restaurar</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="clean_before_restore" id="clean_db">
                        <label for="clean_db">🧹 Limpiar tablas existentes (Cascada)</label>
                    </div>
                </div>
                <button type="submit" class="danger">⚡ Restaurar Base de Datos</button>
            </form>
        </div>

        <div class="card" style="grid-column: 1 / -1;">
            <h2>📂 Historial de Backups Realizados</h2>
            <div class="backup-list">
                <?php if (!empty($backups)): ?>
                    <?php foreach ($backups as $bk): ?>
                        <div class="backup-item">
                            <div class="backup-info">
                                <div class="backup-name"><?php echo htmlspecialchars($bk['name']); ?></div>
                                <div class="backup-meta">Tamaño: <?php echo $bk['size']; ?> | Fecha: <?php echo $bk['date']; ?></div>
                            </div>
                            <div class="backup-actions">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="download_backup">
                                    <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($bk['name']); ?>">
                                    <button type="submit" class="success">Descargar</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="verify_backup">
                                    <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($bk['name']); ?>">
                                    <button type="submit" class="info">Verificar</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_backup">
                                    <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($bk['name']); ?>">
                                    <button type="submit" class="danger" onclick="return confirm('¿Seguro que deseas eliminar este respaldo?');">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #999; padding: 20px;">No se han encontrado archivos de respaldo guardados en el servidor.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>