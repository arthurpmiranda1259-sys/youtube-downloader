<?php
// Minimal working config for RevexaDental instances
error_reporting(0); // Silence all warnings
ini_set('display_errors', '0');

// Session
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Dynamic BASE_URL
$base = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/'))), '/');
define('BASE_URL', ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $base . '/');
define('BASE_PATH', __DIR__ . '/../');
define('DB_PATH', BASE_PATH . 'config/dentista.db');

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// System
define('SYSTEM_NAME', 'REVEXA Dental');
define('UPLOAD_PATH', BASE_PATH . 'uploads/');
define('LICENSE_KEY', 'd3362fade295de66befaad45bb730db4');
define('CUSTOMER_EMAIL', 'arthurmiranda1259@gmail.com');

function get_db() {
    static $db = null;
    if ($db === null) {
        try {
            // Ensure directory exists
            $dbDir = dirname(DB_PATH);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
            
            $db = new PDO('sqlite:' . DB_PATH);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $db->exec('PRAGMA foreign_keys = ON');
            
            // Create tables if not exist
            $db->exec("CREATE TABLE IF NOT EXISTS usuarios (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                senha TEXT NOT NULL,
                perfil TEXT DEFAULT 'recepcionista',
                ativo INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Create default admin if no users
            $count = $db->query("SELECT COUNT(*) as c FROM usuarios")->fetch()['c'];
            if ($count == 0) {
                $stmt = $db->prepare("INSERT INTO usuarios (nome, email, senha, perfil) VALUES (?, ?, ?, ?)");
                $stmt->execute(['Admin', 'admin@admin.com', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);
            }
        } catch (PDOException $e) {
            die('DB Error: ' . $e->getMessage());
        }
    }
    return $db;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header('Location: ' . BASE_URL . $url);
    exit;
}

function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>
