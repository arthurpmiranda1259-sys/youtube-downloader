<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../includes/Database.php';
$db = StoreDatabase::getInstance();

// Get Stats
$totalProducts = $db->fetch("SELECT COUNT(*) as count FROM products")['count'];
$totalOrders = $db->fetch("SELECT COUNT(*) as count FROM orders")['count'];
$totalRevenue = $db->fetch("SELECT SUM(amount) as total FROM orders WHERE status = 'approved'")['total'] ?? 0;
$recentOrders = $db->fetchAll("SELECT o.*, p.name as product_name FROM orders o LEFT JOIN products p ON o.product_id = p.id ORDER BY o.created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | RevexaSistemas</title>
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
                <h1 style="color: var(--white);">Visão Geral</h1>
                <div style="color: var(--gray-400);">Bem-vindo, <?= htmlspecialchars($_SESSION['admin_name']) ?></div>
            </div>
            
            <!-- Stats Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px;">
                <div style="background: var(--dark-lighter); padding: 25px; border-radius: var(--radius); border: 1px solid rgba(255,255,255,0.05);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3 style="color: var(--gray-400); font-size: 14px; text-transform: uppercase;">Vendas Totais</h3>
                        <i class="fas fa-dollar-sign" style="color: var(--success); font-size: 20px;"></i>
                    </div>
                    <div style="font-size: 28px; font-weight: 700; color: var(--white);">R$ <?= number_format($totalRevenue, 2, ',', '.') ?></div>
                </div>
                
                <div style="background: var(--dark-lighter); padding: 25px; border-radius: var(--radius); border: 1px solid rgba(255,255,255,0.05);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3 style="color: var(--gray-400); font-size: 14px; text-transform: uppercase;">Pedidos</h3>
                        <i class="fas fa-shopping-bag" style="color: var(--primary); font-size: 20px;"></i>
                    </div>
                    <div style="font-size: 28px; font-weight: 700; color: var(--white);"><?= $totalOrders ?></div>
                </div>
                
                <div style="background: var(--dark-lighter); padding: 25px; border-radius: var(--radius); border: 1px solid rgba(255,255,255,0.05);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3 style="color: var(--gray-400); font-size: 14px; text-transform: uppercase;">Produtos Ativos</h3>
                        <i class="fas fa-box" style="color: var(--secondary); font-size: 20px;"></i>
                    </div>
                    <div style="font-size: 28px; font-weight: 700; color: var(--white);"><?= $totalProducts ?></div>
                </div>
            </div>
            
            <h2 style="color: var(--white); margin-bottom: 20px;">Pedidos Recentes</h2>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Produto</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentOrders)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--gray-400); padding: 20px;">Nenhum pedido recente.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td style="color: var(--white);"><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td style="color: var(--gray-300);"><?= htmlspecialchars($order['product_name']) ?></td>
                                <td style="color: var(--white);">R$ <?= number_format($order['amount'], 2, ',', '.') ?></td>
                                <td>
                                    <?php
                                    $statusColor = 'var(--gray-400)';
                                    $statusBg = 'rgba(255,255,255,0.1)';
                                    if ($order['status'] == 'approved') { $statusColor = 'var(--success)'; $statusBg = 'rgba(34, 197, 94, 0.1)'; }
                                    if ($order['status'] == 'pending') { $statusColor = 'var(--secondary)'; $statusBg = 'rgba(245, 158, 11, 0.1)'; }
                                    if ($order['status'] == 'rejected') { $statusColor = 'var(--danger)'; $statusBg = 'rgba(239, 68, 68, 0.1)'; }
                                    ?>
                                    <span style="background: <?= $statusBg ?>; color: <?= $statusColor ?>; padding: 4px 8px; border-radius: 4px; font-size: 12px; text-transform: uppercase; font-weight: 600;">
                                        <?= $order['status'] ?>
                                    </span>
                                </td>
                                <td style="color: var(--gray-400);"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
