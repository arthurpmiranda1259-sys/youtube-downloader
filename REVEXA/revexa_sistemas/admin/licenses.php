<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../includes/Database.php';
$db = StoreDatabase::getInstance();

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'block') {
        $db->query("UPDATE licenses SET status = 'blocked' WHERE id = ?", [$id]);
    } elseif ($action === 'unblock') {
        $db->query("UPDATE licenses SET status = 'active' WHERE id = ?", [$id]);
    } elseif ($action === 'extend') {
        // Extend by 30 days
        $db->query("UPDATE licenses SET expires_at = datetime(expires_at, '+30 days') WHERE id = ?", [$id]);
    }
    
    header('Location: licenses.php');
    exit;
}

$whereClause = "";
$params = [];

if (isset($_GET['email'])) {
    $whereClause = "WHERE l.customer_email = ?";
    $params[] = $_GET['email'];
}

$licenses = $db->fetchAll("
    SELECT l.*, p.name as product_name, p.billing_cycle 
    FROM licenses l 
    JOIN products p ON l.product_id = p.id 
    $whereClause
    ORDER BY l.created_at DESC
", $params);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Licenças e Assinaturas | RevexaSistemas</title>
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
                <h1 style="color: var(--white);">Gerenciar Licenças</h1>
                <a href="license_add.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nova Licença</a>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Produto</th>
                        <th>Chave</th>
                        <th>Status</th>
                        <th>Vencimento</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($licenses as $license): ?>
                    <tr>
                        <td>#<?= $license['id'] ?></td>
                        <td style="color: var(--white);"><?= htmlspecialchars($license['customer_email']) ?></td>
                        <td>
                            <?= htmlspecialchars($license['product_name']) ?>
                            <br>
                            <span style="font-size: 11px; color: var(--gray-400);"><?= $license['billing_cycle'] == 'monthly' ? 'Mensal' : 'Único' ?></span>
                        </td>
                        <td><code style="background: rgba(255,255,255,0.1); padding: 2px 5px; border-radius: 4px;"><?= htmlspecialchars(substr($license['license_key'], 0, 10)) ?>...</code></td>
                        <td>
                            <?php if($license['status'] == 'active'): ?>
                                <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Ativo</span>
                            <?php elseif($license['status'] == 'blocked'): ?>
                                <span style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Bloqueado</span>
                            <?php else: ?>
                                <span style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Expirado</span>
                            <?php endif; ?>
                        </td>
                        <td style="color: var(--white);">
                            <?php if($license['expires_at']): ?>
                                <?= date('d/m/Y', strtotime($license['expires_at'])) ?>
                                <?php if(strtotime($license['expires_at']) < time()): ?>
                                    <span style="color: var(--danger); font-size: 11px;">(Vencido)</span>
                                <?php endif; ?>
                            <?php else: ?>
                                Vitalício
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($license['status'] == 'active'): ?>
                                <a href="?action=block&id=<?= $license['id'] ?>" class="btn btn-outline" style="padding: 6px 12px; border-color: var(--danger); color: var(--danger);" title="Bloquear"><i class="fas fa-ban"></i></a>
                            <?php else: ?>
                                <a href="?action=unblock&id=<?= $license['id'] ?>" class="btn btn-outline" style="padding: 6px 12px; border-color: var(--success); color: var(--success);" title="Desbloquear"><i class="fas fa-check"></i></a>
                            <?php endif; ?>
                            
                            <?php if($license['billing_cycle'] != 'one_time'): ?>
                                <a href="?action=extend&id=<?= $license['id'] ?>" class="btn btn-outline" style="padding: 6px 12px;" title="Estender 30 dias"><i class="fas fa-calendar-plus"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>