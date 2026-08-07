<?php
// Conexion.php

class Conexion {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        // ── MOTOR PREFERIDO ──
        $driver = 'pgsql'; 
        
        // --- OPCIONES Y PARÁMETROS ---
        
        // Configuración PostgreSQL (Servidor Local)
        $pgsql_dsn = "pgsql:host=localhost;port=5432;dbname=franguesa_games;sslmode=prefer;options='--client_encoding=UTF8'";
        $pgsql_user = 'postgres';
        $pgsql_pass = 'Gabu1206'; 
        $pgsql_opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 5,
            PDO::ATTR_PERSISTENT         => false
        ];

        // Configuración MySQL (Respaldo/Fallback)
        $mysql_dsn = "mysql:host=localhost;dbname=franguesa_games;charset=utf8mb4";
        $mysql_user = 'root';
        $mysql_pass = '';
        $mysql_opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 5,
            PDO::ATTR_PERSISTENT         => false
        ];

        // ── INTENTO DE CONEXIÓN ──
        try {
            if ($driver === 'pgsql') {
                $this->pdo = new PDO($pgsql_dsn, $pgsql_user, $pgsql_pass, $pgsql_opts);
                $this->pdo->exec("SET TIME ZONE 'America/Caracas'");
                $this->pdo->exec("SET search_path TO public");
            } else {
                $this->pdo = new PDO($mysql_dsn, $mysql_user, $mysql_pass, $mysql_opts);
            }
        } catch (PDOException $e) {
            if ($driver === 'pgsql') {
                try {
                    error_log("Aviso: Falló PostgreSQL (" . $e->getMessage() . "). Conectando a MySQL...");
                    $this->pdo = new PDO($mysql_dsn, $mysql_user, $mysql_pass, $mysql_opts);
                } catch (PDOException $e_mysql) {
                    error_log("Error crítico en ambos motores: " . $e_mysql->getMessage());
                    throw new Exception("Error de conexión a la base de datos de Franbuesa-Games: " . $e->getMessage());
                }
            } else {
                error_log("Error crítico de conexión MySQL: " . $e->getMessage());
                throw new Exception("Error de conexión a la base de datos de Franbuesa-Games: " . $e->getMessage());
            }
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

// Inicialización automática
$pdo = Conexion::getInstance()->getConnection();
?>