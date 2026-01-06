<?php
/**
 * REVEXA Sistemas - Admin Panel
 * Simple CRUD interface for managing content
 */

session_start();

// Simple authentication (change credentials in production)
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'revexa2024'); // Change this!

require_once __DIR__ . '/../includes/Database.php';

$db = Database::getInstance();

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Handle login
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if ($username === ADMIN_USER && $password === ADMIN_PASS) {
        $_SESSION['admin_logged'] = true;
        header('Location: index.php');
        exit;
    } else {
        $loginError = 'Usuário ou senha inválidos';
    }
}

// Check if logged in
$isLoggedIn = isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;

// Get current page
$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle form submissions
$message = '';
$messageType = '';

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_service'])) {
        $data = [
            'title' => trim($_POST['title']),
            'description' => trim($_POST['description']),
            'icon' => trim($_POST['icon']),
            'features' => trim($_POST['features'])
        ];
        
        if (!empty($_POST['id'])) {
            $db->update('services', $data, 'id = ?', [(int)$_POST['id']]);
            $message = 'Serviço atualizado com sucesso!';
        } else {
            $db->insert('services', $data);
            $message = 'Serviço criado com sucesso!';
        }
        $messageType = 'success';
    }
    
    if (isset($_POST['save_portfolio'])) {
        $data = [
            'title' => trim($_POST['title']),
            'description' => trim($_POST['description']),
            'category' => trim($_POST['category']),
            'image_url' => trim($_POST['image_url']),
            'project_url' => trim($_POST['project_url']),
            'client_name' => trim($_POST['client_name'])
        ];
        
        if (!empty($_POST['id'])) {
            $db->update('portfolio', $data, 'id = ?', [(int)$_POST['id']]);
            $message = 'Projeto atualizado com sucesso!';
        } else {
            $db->insert('portfolio', $data);
            $message = 'Projeto criado com sucesso!';
        }
        $messageType = 'success';
    }
    
    if (isset($_POST['save_differential'])) {
        $data = [
            'title' => trim($_POST['title']),
            'description' => trim($_POST['description']),
            'icon' => trim($_POST['icon'])
        ];
        
        if (!empty($_POST['id'])) {
            $db->update('differentials', $data, 'id = ?', [(int)$_POST['id']]);
            $message = 'Diferencial atualizado com sucesso!';
        } else {
            $db->insert('differentials', $data);
            $message = 'Diferencial criado com sucesso!';
        }
        $messageType = 'success';
    }
    
    if (isset($_POST['update_contact_status'])) {
        $db->update('contacts', ['status' => $_POST['status']], 'id = ?', [(int)$_POST['id']]);
        $message = 'Status atualizado com sucesso!';
        $messageType = 'success';
    }
    
    if (isset($_POST['delete_item'])) {
        $table = $_POST['table'];
        $allowedTables = ['services', 'portfolio', 'differentials', 'contacts'];
        if (in_array($table, $allowedTables)) {
            $db->delete($table, 'id = ?', [(int)$_POST['id']]);
            $message = 'Item excluído com sucesso!';
            $messageType = 'success';
        }
    }
}

// Get data for current page
$services = $db->fetchAll("SELECT * FROM services ORDER BY id");
$portfolio = $db->fetchAll("SELECT * FROM portfolio ORDER BY id DESC");
$differentials = $db->fetchAll("SELECT * FROM differentials ORDER BY id");
$contacts = $db->fetchAll("SELECT * FROM contacts ORDER BY created_at DESC");

// Count stats
$stats = [
    'services' => count($services),
    'portfolio' => count($portfolio),
    'contacts' => count($contacts),
    'pending' => count(array_filter($contacts, fn($c) => $c['status'] === 'pending'))
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - REVEXA Sistemas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #ec4899;
            --dark: #1f2937;
            --light: #f9fafb;
            --white: #ffffff;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --sidebar-width: 260px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--gray-100);
            color: var(--dark);
        }
        
        /* Login Page */
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--dark) 0%, #374151 100%);
        }
        
        .login-box {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .login-logo i {
            font-size: 48px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .login-logo h1 {
            margin-top: 16px;
            font-size: 24px;
            color: var(--dark);
        }
        
        .login-logo p {
            color: var(--gray-500);
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 8px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: var(--white);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
        }
        
        .btn-secondary {
            background: var(--gray-200);
            color: var(--dark);
        }
        
        .btn-danger {
            background: var(--danger);
            color: var(--white);
        }
        
        .btn-success {
            background: var(--success);
            color: var(--white);
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 13px;
        }
        
        .btn-block {
            width: 100%;
        }
        
        .error-message {
            padding: 12px 16px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--danger);
            border-radius: 8px;
            color: var(--danger);
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        /* Admin Layout */
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            background: var(--dark);
            color: var(--white);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-logo {
            padding: 24px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .sidebar-logo i {
            font-size: 28px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .sidebar-logo span {
            font-size: 20px;
            font-weight: 700;
        }
        
        .sidebar-nav {
            padding: 20px 0;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 24px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .nav-item:hover,
        .nav-item.active {
            background: rgba(255,255,255,0.1);
            color: var(--white);
        }
        
        .nav-item.active {
            border-left: 3px solid var(--primary);
        }
        
        .nav-item i {
            width: 20px;
            text-align: center;
        }
        
        .nav-badge {
            margin-left: auto;
            padding: 2px 8px;
            background: var(--danger);
            border-radius: 10px;
            font-size: 12px;
        }
        
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 30px;
        }
        
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--white);
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .stat-card-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 22px;
        }
        
        .stat-card-icon.primary {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }
        
        .stat-card-icon.success {
            background: rgba(34, 197, 94, 0.1);
            color: var(--success);
        }
        
        .stat-card-icon.warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }
        
        .stat-card-icon.danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }
        
        .stat-card-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--dark);
        }
        
        .stat-card-label {
            font-size: 14px;
            color: var(--gray-500);
            margin-top: 4px;
        }
        
        /* Data Table */
        .data-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .data-card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .data-card-title {
            font-size: 18px;
            font-weight: 600;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th,
        .data-table td {
            padding: 16px 24px;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .data-table th {
            background: var(--gray-100);
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--gray-500);
        }
        
        .data-table tr:hover {
            background: var(--gray-100);
        }
        
        .data-table .actions {
            display: flex;
            gap: 8px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-pending {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }
        
        .badge-answered {
            background: rgba(34, 197, 94, 0.1);
            color: var(--success);
        }
        
        .badge-archived {
            background: rgba(107, 114, 128, 0.1);
            color: var(--gray-500);
        }
        
        /* Form Card */
        .form-card {
            background: var(--white);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            max-width: 700px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--gray-200);
        }
        
        /* Alert Messages */
        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: var(--success);
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }
        
        /* Contact Detail */
        .contact-detail {
            background: var(--white);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .contact-detail-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .contact-detail h3 {
            font-size: 20px;
            color: var(--dark);
            margin-bottom: 8px;
        }
        
        .contact-detail p {
            color: var(--gray-500);
            line-height: 1.7;
        }
        
        .contact-message {
            background: var(--gray-100);
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .contact-message-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .data-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <?php if (!$isLoggedIn): ?>
    <!-- Login Page -->
    <div class="login-page">
        <div class="login-box">
            <div class="login-logo">
                <i class="fas fa-cube"></i>
                <h1>REVEXA Admin</h1>
                <p>Painel de Controle</p>
            </div>
            
            <?php if ($loginError): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($loginError) ?>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Usuário</label>
                    <input type="text" id="username" name="username" required placeholder="Digite seu usuário">
                </div>
                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" required placeholder="Digite sua senha">
                </div>
                <button type="submit" name="login" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
            </form>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Admin Layout -->
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fas fa-cube"></i>
                <span>REVEXA</span>
            </div>
            <nav class="sidebar-nav">
                <a href="?page=dashboard" class="nav-item <?= $page === 'dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="?page=services" class="nav-item <?= $page === 'services' ? 'active' : '' ?>">
                    <i class="fas fa-cogs"></i> Serviços
                </a>
                <a href="?page=portfolio" class="nav-item <?= $page === 'portfolio' ? 'active' : '' ?>">
                    <i class="fas fa-briefcase"></i> Portfólio
                </a>
                <a href="?page=differentials" class="nav-item <?= $page === 'differentials' ? 'active' : '' ?>">
                    <i class="fas fa-star"></i> Diferenciais
                </a>
                <a href="?page=contacts" class="nav-item <?= $page === 'contacts' ? 'active' : '' ?>">
                    <i class="fas fa-envelope"></i> Contatos
                    <?php if ($stats['pending'] > 0): ?>
                    <span class="nav-badge"><?= $stats['pending'] ?></span>
                    <?php endif; ?>
                </a>
                <a href="../" class="nav-item" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Ver Site
                </a>
                <a href="?logout=1" class="nav-item">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>">
                <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>
            
            <?php if ($page === 'dashboard'): ?>
            <!-- Dashboard -->
            <div class="page-header">
                <h1 class="page-title">Dashboard</h1>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-icon primary">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <div class="stat-card-value"><?= $stats['services'] ?></div>
                    <div class="stat-card-label">Serviços</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon success">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="stat-card-value"><?= $stats['portfolio'] ?></div>
                    <div class="stat-card-label">Projetos no Portfólio</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon warning">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-card-value"><?= $stats['contacts'] ?></div>
                    <div class="stat-card-label">Total de Contatos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon danger">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-card-value"><?= $stats['pending'] ?></div>
                    <div class="stat-card-label">Contatos Pendentes</div>
                </div>
            </div>
            
            <div class="data-card">
                <div class="data-card-header">
                    <h3 class="data-card-title">Últimos Contatos</h3>
                    <a href="?page=contacts" class="btn btn-sm btn-secondary">Ver Todos</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Assunto</th>
                            <th>Status</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($contacts, 0, 5) as $contact): ?>
                        <tr>
                            <td><?= htmlspecialchars($contact['name']) ?></td>
                            <td><?= htmlspecialchars($contact['email']) ?></td>
                            <td><?= htmlspecialchars($contact['subject'] ?: '-') ?></td>
                            <td><span class="badge badge-<?= $contact['status'] ?>"><?= ucfirst($contact['status']) ?></span></td>
                            <td><?= date('d/m/Y H:i', strtotime($contact['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php elseif ($page === 'services'): ?>
            <!-- Services -->
            <?php if ($action === 'list'): ?>
            <div class="page-header">
                <h1 class="page-title">Serviços</h1>
                <a href="?page=services&action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Serviço
                </a>
            </div>
            
            <div class="data-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Ícone</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                        <tr>
                            <td><?= $service['id'] ?></td>
                            <td><?= htmlspecialchars($service['title']) ?></td>
                            <td><i class="<?= htmlspecialchars($service['icon']) ?>"></i> <?= htmlspecialchars($service['icon']) ?></td>
                            <td class="actions">
                                <a href="?page=services&action=edit&id=<?= $service['id'] ?>" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Excluir este serviço?')">
                                    <input type="hidden" name="table" value="services">
                                    <input type="hidden" name="id" value="<?= $service['id'] ?>">
                                    <button type="submit" name="delete_item" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php else: ?>
            <?php 
                $editService = null;
                if ($action === 'edit' && $id) {
                    $editService = $db->fetch("SELECT * FROM services WHERE id = ?", [$id]);
                }
            ?>
            <div class="page-header">
                <h1 class="page-title"><?= $action === 'edit' ? 'Editar' : 'Novo' ?> Serviço</h1>
            </div>
            
            <div class="form-card">
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $editService['id'] ?? '' ?>">
                    
                    <div class="form-group">
                        <label for="title">Título</label>
                        <input type="text" id="title" name="title" required value="<?= htmlspecialchars($editService['title'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Descrição</label>
                        <textarea id="description" name="description" required><?= htmlspecialchars($editService['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="icon">Ícone (Font Awesome)</label>
                        <input type="text" id="icon" name="icon" placeholder="fas fa-cogs" value="<?= htmlspecialchars($editService['icon'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="features">Features (separadas por vírgula)</label>
                        <input type="text" id="features" name="features" placeholder="Feature 1, Feature 2, Feature 3" value="<?= htmlspecialchars($editService['features'] ?? '') ?>">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="save_service" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar
                        </button>
                        <a href="?page=services" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            
            <?php elseif ($page === 'portfolio'): ?>
            <!-- Portfolio -->
            <?php if ($action === 'list'): ?>
            <div class="page-header">
                <h1 class="page-title">Portfólio</h1>
                <a href="?page=portfolio&action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Projeto
                </a>
            </div>
            
            <div class="data-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Categoria</th>
                            <th>Cliente</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($portfolio as $project): ?>
                        <tr>
                            <td><?= $project['id'] ?></td>
                            <td><?= htmlspecialchars($project['title']) ?></td>
                            <td><?= htmlspecialchars($project['category']) ?></td>
                            <td><?= htmlspecialchars($project['client_name']) ?></td>
                            <td class="actions">
                                <a href="?page=portfolio&action=edit&id=<?= $project['id'] ?>" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Excluir este projeto?')">
                                    <input type="hidden" name="table" value="portfolio">
                                    <input type="hidden" name="id" value="<?= $project['id'] ?>">
                                    <button type="submit" name="delete_item" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php else: ?>
            <?php 
                $editProject = null;
                if ($action === 'edit' && $id) {
                    $editProject = $db->fetch("SELECT * FROM portfolio WHERE id = ?", [$id]);
                }
            ?>
            <div class="page-header">
                <h1 class="page-title"><?= $action === 'edit' ? 'Editar' : 'Novo' ?> Projeto</h1>
            </div>
            
            <div class="form-card">
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $editProject['id'] ?? '' ?>">
                    
                    <div class="form-group">
                        <label for="title">Título</label>
                        <input type="text" id="title" name="title" required value="<?= htmlspecialchars($editProject['title'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Descrição</label>
                        <textarea id="description" name="description"><?= htmlspecialchars($editProject['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Categoria</label>
                            <select id="category" name="category">
                                <option value="Sistemas" <?= ($editProject['category'] ?? '') === 'Sistemas' ? 'selected' : '' ?>>Sistemas</option>
                                <option value="Sites" <?= ($editProject['category'] ?? '') === 'Sites' ? 'selected' : '' ?>>Sites</option>
                                <option value="Apps" <?= ($editProject['category'] ?? '') === 'Apps' ? 'selected' : '' ?>>Apps</option>
                                <option value="Design" <?= ($editProject['category'] ?? '') === 'Design' ? 'selected' : '' ?>>Design</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="client_name">Nome do Cliente</label>
                            <input type="text" id="client_name" name="client_name" value="<?= htmlspecialchars($editProject['client_name'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="image_url">URL da Imagem</label>
                            <input type="text" id="image_url" name="image_url" value="<?= htmlspecialchars($editProject['image_url'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="project_url">URL do Projeto</label>
                            <input type="text" id="project_url" name="project_url" value="<?= htmlspecialchars($editProject['project_url'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="save_portfolio" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar
                        </button>
                        <a href="?page=portfolio" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            
            <?php elseif ($page === 'differentials'): ?>
            <!-- Differentials -->
            <?php if ($action === 'list'): ?>
            <div class="page-header">
                <h1 class="page-title">Diferenciais</h1>
                <a href="?page=differentials&action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Diferencial
                </a>
            </div>
            
            <div class="data-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Ícone</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($differentials as $diff): ?>
                        <tr>
                            <td><?= $diff['id'] ?></td>
                            <td><?= htmlspecialchars($diff['title']) ?></td>
                            <td><i class="<?= htmlspecialchars($diff['icon']) ?>"></i> <?= htmlspecialchars($diff['icon']) ?></td>
                            <td class="actions">
                                <a href="?page=differentials&action=edit&id=<?= $diff['id'] ?>" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Excluir este diferencial?')">
                                    <input type="hidden" name="table" value="differentials">
                                    <input type="hidden" name="id" value="<?= $diff['id'] ?>">
                                    <button type="submit" name="delete_item" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php else: ?>
            <?php 
                $editDiff = null;
                if ($action === 'edit' && $id) {
                    $editDiff = $db->fetch("SELECT * FROM differentials WHERE id = ?", [$id]);
                }
            ?>
            <div class="page-header">
                <h1 class="page-title"><?= $action === 'edit' ? 'Editar' : 'Novo' ?> Diferencial</h1>
            </div>
            
            <div class="form-card">
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $editDiff['id'] ?? '' ?>">
                    
                    <div class="form-group">
                        <label for="title">Título</label>
                        <input type="text" id="title" name="title" required value="<?= htmlspecialchars($editDiff['title'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Descrição</label>
                        <textarea id="description" name="description"><?= htmlspecialchars($editDiff['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="icon">Ícone (Font Awesome)</label>
                        <input type="text" id="icon" name="icon" placeholder="fas fa-star" value="<?= htmlspecialchars($editDiff['icon'] ?? '') ?>">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="save_differential" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar
                        </button>
                        <a href="?page=differentials" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            
            <?php elseif ($page === 'contacts'): ?>
            <!-- Contacts -->
            <?php if ($action === 'view' && $id): ?>
            <?php $contact = $db->fetch("SELECT * FROM contacts WHERE id = ?", [$id]); ?>
            <div class="page-header">
                <h1 class="page-title">Detalhes do Contato</h1>
                <a href="?page=contacts" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
            
            <?php if ($contact): ?>
            <div class="contact-detail">
                <div class="contact-detail-header">
                    <div>
                        <h3><?= htmlspecialchars($contact['name']) ?></h3>
                        <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($contact['email']) ?></p>
                        <?php if ($contact['phone']): ?>
                        <p><i class="fas fa-phone"></i> <?= htmlspecialchars($contact['phone']) ?></p>
                        <?php endif; ?>
                        <p><i class="fas fa-calendar"></i> <?= date('d/m/Y H:i', strtotime($contact['created_at'])) ?></p>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $contact['id'] ?>">
                        <select name="status" onchange="this.form.submit()">
                            <option value="pending" <?= $contact['status'] === 'pending' ? 'selected' : '' ?>>Pendente</option>
                            <option value="answered" <?= $contact['status'] === 'answered' ? 'selected' : '' ?>>Respondido</option>
                            <option value="archived" <?= $contact['status'] === 'archived' ? 'selected' : '' ?>>Arquivado</option>
                        </select>
                        <input type="hidden" name="update_contact_status" value="1">
                    </form>
                </div>
                
                <?php if ($contact['subject']): ?>
                <p><strong>Assunto:</strong> <?= htmlspecialchars($contact['subject']) ?></p>
                <?php endif; ?>
                
                <div class="contact-message">
                    <div class="contact-message-label">Mensagem</div>
                    <p><?= nl2br(htmlspecialchars($contact['message'])) ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="page-header">
                <h1 class="page-title">Contatos</h1>
            </div>
            
            <div class="data-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Assunto</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contacts as $contact): ?>
                        <tr>
                            <td><?= htmlspecialchars($contact['name']) ?></td>
                            <td><?= htmlspecialchars($contact['email']) ?></td>
                            <td><?= htmlspecialchars($contact['subject'] ?: '-') ?></td>
                            <td><span class="badge badge-<?= $contact['status'] ?>"><?= ucfirst($contact['status']) ?></span></td>
                            <td><?= date('d/m/Y H:i', strtotime($contact['created_at'])) ?></td>
                            <td class="actions">
                                <a href="?page=contacts&action=view&id=<?= $contact['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Excluir este contato?')">
                                    <input type="hidden" name="table" value="contacts">
                                    <input type="hidden" name="id" value="<?= $contact['id'] ?>">
                                    <button type="submit" name="delete_item" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
            <?php endif; ?>
        </main>
    </div>
    <?php endif; ?>
</body>
</html>
