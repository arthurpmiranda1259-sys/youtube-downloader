<?php
require_once __DIR__ . '/../config/config.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$current_user = get_current_dental_user();
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? SYSTEM_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <script src="<?= BASE_URL ?>assets/js/main.js" defer></script>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>🦷 REVEXA</h2>
            <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
        </div>
        
        <nav class="sidebar-nav">
            <a href="<?= BASE_URL ?>dashboard.php" class="nav-item <?= $current_page == 'dashboard' ? 'active' : '' ?>">
                <span class="icon">📊</span>
                <span class="text">Dashboard</span>
            </a>
            
            <a href="<?= BASE_URL ?>modules/agenda.php" class="nav-item <?= $current_page == 'agenda' ? 'active' : '' ?>">
                <span class="icon">📅</span>
                <span class="text">Agenda</span>
            </a>
            
            <a href="<?= BASE_URL ?>modules/pacientes.php" class="nav-item <?= $current_page == 'pacientes' ? 'active' : '' ?>">
                <span class="icon">👥</span>
                <span class="text">Pacientes</span>
            </a>
            
            <?php if (has_permission('dentista')): ?>
            <a href="<?= BASE_URL ?>modules/procedimentos.php" class="nav-item <?= $current_page == 'procedimentos' ? 'active' : '' ?>">
                <span class="icon">🔧</span>
                <span class="text">Procedimentos</span>
            </a>
            <?php endif; ?>
            
            <a href="<?= BASE_URL ?>modules/financeiro.php" class="nav-item <?= $current_page == 'financeiro' ? 'active' : '' ?>">
                <span class="icon">💰</span>
                <span class="text">Financeiro</span>
            </a>
            
            <?php if (has_permission('admin')): ?>
            <a href="<?= BASE_URL ?>modules/usuarios.php" class="nav-item <?= $current_page == 'usuarios' ? 'active' : '' ?>">
                <span class="icon">👤</span>
                <span class="text">Usuários</span>
            </a>
            
            <a href="<?= BASE_URL ?>modules/permissoes.php" class="nav-item <?= $current_page == 'permissoes' ? 'active' : '' ?>">
                <span class="icon">🔐</span>
                <span class="text">Permissões</span>
            </a>
            
            <a href="<?= BASE_URL ?>modules/relatorios.php" class="nav-item <?= $current_page == 'relatorios' ? 'active' : '' ?>">
                <span class="icon">📈</span>
                <span class="text">Relatórios</span>
            </a>
            <?php endif; ?>
        </nav>
        
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar"><?= substr($current_user['nome'], 0, 1) ?></div>
                <div class="user-details">
                    <div class="user-name"><?= $current_user['nome'] ?></div>
                    <div class="user-role"><?= ucfirst($current_user['perfil']) ?></div>
                </div>
            </div>
            <a href="<?= BASE_URL ?>logout.php" class="btn-logout" title="Sair">
                🚪
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="topbar">
            <button class="mobile-menu-toggle" onclick="toggleSidebar()">☰</button>
            <h1 class="page-title"><?= $page_title ?? 'Dashboard' ?></h1>
        </div>
        
        <?php
        $alert = get_alert();
        if ($alert):
        ?>
            <div class="alert alert-<?= $alert['type'] ?>">
                <?= $alert['message'] ?>
            </div>
        <?php endif; ?>
        
        <div class="content-wrapper">
