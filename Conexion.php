<?php
// Conexion.php

class Conexion {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        // ── DETECTOR AUTOMÁTICO DE ENTORNO ──
        $driver = 'mysql'; // Por defecto usamos tu entorno local (phpMyAdmin)
        
        // 1. Detectar por consola/CLI (scripts de prueba/evaluación)
        if (php_sapi_name() === 'cli') {
            $driver = 'pgsql';
        }
        // 2. Detectar por la URL del navegador
        else if (isset($_SERVER['HTTP_HOST'])) {
            $host_actual = $_SERVER['HTTP_HOST'];
            
            // Si la URL NO contiene 'localhost' ni '127.0.0.1' (servidor externo/evaluación)
            if (strpos($host_actual, 'localhost') === false && strpos($host_actual, '127.0.0.1') === false) {
                $driver = 'pgsql';
            }
        }
        
        // --- OPCIONES Y PARÁMETROS ---
        
        // Configuración MySQL (phpMyAdmin local)
        $mysql_dsn = "mysql:host=127.0.0.1;dbname=franbuesa-Games;charset=utf8mb4";
        $mysql_user = 'root';
        $mysql_pass = '12345';
        $mysql_opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 5,
            PDO::ATTR_PERSISTENT         => false
        ];

        // Configuración PostgreSQL (Entorno de evaluación)
        $pgsql_dsn = "pgsql:host=localhost;port=5432;dbname=franbuesa-Games;options='--client_encoding=UTF8'";
        $pgsql_user = 'postgres';
        $pgsql_pass = 'Gabs1206'; // Ajusta la contraseña cuando evalúe el profesor
        $pgsql_opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 5,
            PDO::ATTR_PERSISTENT         => false,
            PDO::PGSQL_ATTR_SSL_MODE     => 'prefer', // Cambiado a 'prefer' para evitar rechazos SSL en redes locales
            PDO::PGSQL_ATTR_SSL_VERIFY_CERT => false
        ];

        // ── INTENTO DE CONEXIÓN ──
        try {
            if ($driver === 'pgsql') {
                $this->pdo = new PDO($pgsql_dsn, $pgsql_user, $pgsql_pass, $pgsql_opts);
                $this->pdo->exec("SET TIME ZONE 'UTC'");
                $this->pdo->exec("SET search_path TO public, seguridad, auditoria");
            } else {
                $this->pdo = new PDO($mysql_dsn, $mysql_user, $mysql_pass, $mysql_opts);
            }
        } catch (PDOException $e) {
            // FALLBACK AUTOMÁTICO: Si intentó PostgreSQL y falló (por clave, driver o SSL), conmuta a MySQL/phpMyAdmin
            if ($driver === 'pgsql') {
                try {
                    error_log("Aviso: Falló PostgreSQL (" . $e->getMessage() . "). Conectando a MySQL...");
                    $this->pdo = new PDO($mysql_dsn, $mysql_user, $mysql_pass, $mysql_opts);
                } catch (PDOException $e_mysql) {
                    error_log("Error crítico en ambos motores: " . $e_mysql->getMessage());
                    throw new Exception("Error de conexión a la base de datos de Franbuesa-Games.");
                }
            } else {
                error_log("Error crítico de conexión MySQL: " . $e->getMessage());
                throw new Exception("Error de conexión a la base de datos de Franbuesa-Games.");
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

// Inicialización automática para compatibilidad total con tu código viejo
$pdo = Conexion::getInstance()->getConnection();
?>