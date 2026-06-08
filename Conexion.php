<?php
// Conexion.php

class Conexion {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        // ── DETECTOR AUTOMÁTICO DE ENTORNO ──
        $driver = 'mysql'; // Por defecto usamos tu entorno local
        
        // 1. Detectar por cómo se ejecuta (Si es por consola/PowerShell para los scripts del profesor)
        if (php_sapi_name() === 'cli') {
            $driver = 'pgsql';
        }
        // 2. Detectar por la URL del navegador
        else if (isset($_SERVER['HTTP_HOST'])) {
            $host_actual = $_SERVER['HTTP_HOST'];
            
            // Si la URL NO contiene 'localhost' o '127.0.0.1' (entorno del profesor/servidor externo)
            if (strpos($host_actual, 'localhost') === false && strpos($host_actual, '127.0.0.1') === false) {
                $driver = 'pgsql';
            }
        }
        
        try {
            if ($driver === 'mysql') {
                // --- ENTORNO LOCAL: MYSQL (phpMyAdmin / XAMPP) ---
                $host = '127.0.0.1';
                $dbname = 'franbuesa-Games';
                $user = 'root'; 
                $password = ''; 
                
                $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
                
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false, // Blindaje contra SQL Injection
                    PDO::ATTR_TIMEOUT            => 5,
                    PDO::ATTR_PERSISTENT         => false
                ];
                
                $this->pdo = new PDO($dsn, $user, $password, $options);
                
            } else if ($driver === 'pgsql') {
                // --- ENTORNO DE EVALUACIÓN: POSTGRESQL (Profesor Jesus Reina) ---
                $host = 'localhost';
                $port = '5432';
                $dbname = 'franbuesa-Games'; // Ajustar si el profesor usa un nombre específico como 'pnfi'
                $user = 'postgres';
                $password = 'tu_contraseña'; // Clave de Postgres de la evaluación
                
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false, 
                    PDO::ATTR_TIMEOUT            => 5,
                    PDO::ATTR_PERSISTENT         => false,
                    PDO::PGSQL_ATTR_SSL_MODE     => 'require',
                    PDO::PGSQL_ATTR_SSL_VERIFY_CERT => false
                ];
                
                $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;options='--client_encoding=UTF8'";
                
                $this->pdo = new PDO($dsn, $user, $password, $options);
                $this->pdo->exec("SET TIME ZONE 'UTC'");
                $this->pdo->exec("SET search_path TO public, seguridad, auditoria");
            }
            
        } catch (PDOException $e) {
            error_log("Error crítico de conexión: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos de Franbuesa-Games.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}

// Inicialización automática para compatibilidad total con tu código viejo
$pdo = Conexion::getInstance()->getConnection();
?>