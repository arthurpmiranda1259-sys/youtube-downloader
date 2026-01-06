<?php
session_start();

// Configurações gerais
// Detect Base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
// If we are in admin, we need to go up one level for the base URL
if (strpos($scriptDir, '/admin') !== false) {
    $scriptDir = dirname($scriptDir);
}
// Remove trailing slashes and backslashes
$scriptDir = rtrim(str_replace('\\', '/', $scriptDir), '/');
define('BASE_URL', $protocol . "://" . $host . $scriptDir . '/');

define('SITE_NAME', 'NeoDelivery');
define('LICENSE_SERVER_URL', 'https://revexa.com.br/revexa_sistemas/api/verify_license.php'); // URL da API de licenças

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Autoload da classe Database
require_once __DIR__ . '/database.php';

// Funções auxiliares
function getSetting($name, $default = '') {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT value FROM settings WHERE name = :name");
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    return $row ? $row['value'] : $default;
}

function updateSetting($name, $value) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("INSERT OR REPLACE INTO settings (name, value, updated_at) VALUES (:name, :value, CURRENT_TIMESTAMP)");
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':value', $value, SQLITE3_TEXT);
    return $stmt->execute();
}

function formatMoney($value) {
    return 'R$ ' . number_format($value, 2, ',', '.');
}

function generateOrderNumber() {
    return date('ymd') . rand(1000, 9999);
}

function generateOrderPassword() {
    return str_pad(rand(1, 999), 2, '0', STR_PAD_LEFT);
}

function isBusinessOpen() {
    $isOpen = getSetting('is_open', '1');
    if ($isOpen == '0') {
        return false;
    }
    
    $db = Database::getInstance()->getConnection();
    $dayOfWeek = date('w');
    $currentTime = date('H:i');
    
    $stmt = $db->prepare("SELECT * FROM business_hours WHERE day_of_week = :day AND is_open = 1");
    $stmt->bindValue(':day', $dayOfWeek, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $hours = $result->fetchArray(SQLITE3_ASSOC);
    
    if (!$hours) {
        return false;
    }
    
    return ($currentTime >= $hours['opening_time'] && $currentTime <= $hours['closing_time']);
}

function requireAdmin() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: ' . BASE_URL . 'admin/login.php');
        exit;
    }
    
    // Check if user is still active in DB (optional security measure)
    /*
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT active FROM users WHERE id = :id");
    $stmt->bindValue(':id', $_SESSION['user_id'], SQLITE3_INTEGER);
    $res = $stmt->execute();
    $row = $res->fetchArray(SQLITE3_ASSOC);
    if (!$row || $row['active'] != 1) {
        session_destroy();
        header('Location: ' . BASE_URL . 'admin/login.php');
        exit;
    }
    */
}

function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

function requireRole($role) {
    requireAdmin();
    if (!hasRole($role) && !hasRole('admin')) { // Admin always has access
        die('Acesso negado. Permissão insuficiente.');
    }
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// License Guard
require_once __DIR__ . '/license_guard.php';
