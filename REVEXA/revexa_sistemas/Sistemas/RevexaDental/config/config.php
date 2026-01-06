<?php
// ============================================================
// REVEXA DENTAL - Configurações do Sistema
// ============================================================

// Configurações de Sessão (DEVE vir ANTES de qualquer output)
ini_set('session.gc_maxlifetime', 7200); // 2 horas
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações de Ambiente (Dinâmicas para Multi-Tenancy)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '')));
$scriptPath = rtrim($scriptPath, '/');
define('BASE_URL', $protocol . "://" . $host . $scriptPath . '/');
define('BASE_PATH', __DIR__ . '/../');

// Configurações do Banco de Dados
define('DB_PATH', BASE_PATH . 'config/dentista.db');

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de Upload
define('UPLOAD_PATH', BASE_PATH . 'uploads/');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Configurações do Sistema
define('SYSTEM_NAME', 'REVEXA Dental');
define('SYSTEM_VERSION', '1.0.0');
define('ITEMS_PER_PAGE', 20);

// Funções Auxiliares
function get_db() {
    static $db = null;
    
    if ($db === null) {
        try {
            $db = new PDO('sqlite:' . DB_PATH);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Habilitar foreign keys
            $db->exec('PRAGMA foreign_keys = ON');
            
            // Criar tabelas se não existirem
            if (!file_exists(DB_PATH)) {
                $sql = file_get_contents(BASE_PATH . 'config/database.sql');
                $db->exec($sql);
            }
        } catch (PDOException $e) {
            die('Erro ao conectar no banco de dados: ' . $e->getMessage());
        }
    }
    
    return $db;
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function get_current_dental_user() {
    if (!is_logged_in()) {
        return null;
    }
    
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ? AND ativo = 1");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function has_permission($required_profile) {
    $user = get_current_dental_user();
    if (!$user) return false;
    
    $hierarchy = ['recepcionista' => 1, 'dentista' => 2, 'admin' => 3];
    
    return $hierarchy[$user['perfil']] >= $hierarchy[$required_profile];
}

function redirect($path) {
    header('Location: ' . BASE_URL . $path);
    exit;
}

function format_date($date, $format = 'd/m/Y') {
    if (!$date) return '';
    $dt = new DateTime($date);
    return $dt->format($format);
}

function format_datetime($datetime, $format = 'd/m/Y H:i') {
    if (!$datetime) return '';
    $dt = new DateTime($datetime);
    return $dt->format($format);
}

function format_currency($value) {
    return 'R$ ' . number_format($value, 2, ',', '.');
}

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function log_audit($acao, $tabela = null, $registro_id = null, $dados_anteriores = null, $dados_novos = null) {
    if (!is_logged_in()) return;
    
    $db = get_db();
    $stmt = $db->prepare("
        INSERT INTO log_auditoria 
        (usuario_id, acao, tabela, registro_id, dados_anteriores, dados_novos, ip_address)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $acao,
        $tabela,
        $registro_id,
        $dados_anteriores ? json_encode($dados_anteriores) : null,
        $dados_novos ? json_encode($dados_novos) : null,
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);
}

function show_alert($message, $type = 'success') {
    $_SESSION['alert'] = ['message' => $message, 'type' => $type];
}

function get_alert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}

// Banco de dados será criado sob demanda quando necessário
// Não executar get_db() aqui pois pode causar erro 500
