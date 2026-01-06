<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../includes/Database.php';
$db = StoreDatabase::getInstance();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mp_access_token = $_POST['mp_access_token'] ?? '';
    $mp_public_key = $_POST['mp_public_key'] ?? '';
    $site_name = $_POST['site_name'] ?? '';
    
    $db->saveSetting('mp_access_token', $mp_access_token);
    $db->saveSetting('mp_public_key', $mp_public_key);
    $db->saveSetting('site_name', $site_name);
    
    $message = 'Configurações salvas com sucesso!';
}

$mp_access_token = $db->getSetting('mp_access_token');
$mp_public_key = $db->getSetting('mp_public_key');
$site_name = $db->getSetting('site_name');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações | RevexaSistemas</title>
    <link rel="icon" type="image/svg+xml" href="../assets/images/favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1 style="color: var(--white);">Configurações do Sistema</h1>
            </div>
            
            <?php if ($message): ?>
                <div style="background: rgba(34, 197, 94, 0.1); color: var(--success); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?= $message ?>
                </div>
            <?php endif; ?>
            
            <div style="max-width: 800px;">
                <form method="POST">
                    <div style="background: var(--dark-lighter); padding: 30px; border-radius: var(--radius); border: 1px solid rgba(255,255,255,0.05); margin-bottom: 30px;">
                        <h3 style="color: var(--white); margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
                            <i class="fas fa-globe"></i> Geral
                        </h3>
                        <div class="form-group">
                            <label>Nome da Loja</label>
                            <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars($site_name) ?>">
                        </div>
                    </div>

                    <div style="background: var(--dark-lighter); padding: 30px; border-radius: var(--radius); border: 1px solid rgba(255,255,255,0.05); margin-bottom: 30px;">
                        <h3 style="color: var(--white); margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
                            <i class="fas fa-credit-card"></i> Mercado Pago
                        </h3>
                        <div class="form-group">
                            <label>Public Key</label>
                            <input type="text" name="mp_public_key" class="form-control" value="<?= htmlspecialchars($mp_public_key) ?>" placeholder="TEST-...">
                        </div>
                        <div class="form-group">
                            <label>Access Token</label>
                            <input type="password" name="mp_access_token" class="form-control" value="<?= htmlspecialchars($mp_access_token) ?>" placeholder="TEST-...">
                            <small style="color: var(--gray-400); margin-top: 5px; display: block;">Suas credenciais de produção ou teste do Mercado Pago.</small>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">
                        <i class="fas fa-save"></i> Salvar Configurações
                    </button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
