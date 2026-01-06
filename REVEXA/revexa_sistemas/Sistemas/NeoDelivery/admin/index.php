<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$db = Database::getInstance()->getConnection();

// Estatísticas
$todayStart = date('Y-m-d 00:00:00');
$todayEnd = date('Y-m-d 23:59:59');

$todayOrders = $db->querySingle("SELECT COUNT(*) FROM orders WHERE created_at BETWEEN '{$todayStart}' AND '{$todayEnd}'");
$todayRevenue = $db->querySingle("SELECT SUM(total) FROM orders WHERE created_at BETWEEN '{$todayStart}' AND '{$todayEnd}' AND status != 'cancelled'") ?: 0;
$pendingOrders = $db->querySingle("SELECT COUNT(*) FROM orders WHERE status IN ('pending', 'preparing')");
$totalProducts = $db->querySingle("SELECT COUNT(*) FROM products WHERE active = 1");

// Pedidos recentes
$ordersQuery = $db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");
$recentOrders = [];
while ($row = $ordersQuery->fetchArray(SQLITE3_ASSOC)) {
    $recentOrders[] = $row;
}

$businessName = getSetting('business_name', 'X Delivery');

function getStatusBadge($status) {
    $badges = [
        'pending' => 'badge-pending',
        'preparing' => 'badge-preparing',
        'ready' => 'badge-ready',
        'delivering' => 'badge-delivering',
        'completed' => 'badge-completed',
        'cancelled' => 'badge-cancelled'
    ];
    
    $labels = [
        'pending' => 'Pendente',
        'preparing' => 'Preparando',
        'ready' => 'Pronto',
        'delivering' => 'Saiu p/ entrega',
        'completed' => 'Concluído',
        'cancelled' => 'Cancelado'
    ];
    
    $class = $badges[$status] ?? 'badge-pending';
    $label = $labels[$status] ?? $status;
    
    return "<span class='badge {$class}'>{$label}</span>";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars($businessName); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-content">

            <header class="admin-header">
                <h1>Dashboard</h1>
                <span style="color: var(--text-muted);"><?php echo date('d/m/Y H:i'); ?></span>
            </header>
            
            <main class="admin-main">
                <!-- Stats -->
                <div class="stats-grid">
                    <div class="stat-card" style="border-top: 4px solid var(--primary-color);">
                        <div class="stat-icon">📦</div>
                        <div class="stat-value"><?php echo $todayOrders; ?></div>
                        <div class="stat-label">Pedidos Hoje</div>
                    </div>
                    
                    <div class="stat-card" style="border-top: 4px solid var(--success-color);">
                        <div class="stat-icon">💰</div>
                        <div class="stat-value"><?php echo formatMoney($todayRevenue); ?></div>
                        <div class="stat-label">Faturamento Hoje</div>
                    </div>
                    
                    <div class="stat-card" style="border-top: 4px solid var(--warning-color);">
                        <div class="stat-icon">⏳</div>
                        <div class="stat-value"><?php echo $pendingOrders; ?></div>
                        <div class="stat-label">Pedidos em Andamento</div>
                    </div>
                    
                    <div class="stat-card" style="border-top: 4px solid var(--info-color);">
                        <div class="stat-icon">🍔</div>
                        <div class="stat-value"><?php echo $totalProducts; ?></div>
                        <div class="stat-label">Produtos Ativos</div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Pedidos Recentes</h2>
                        <a href="<?php echo BASE_URL; ?>admin/orders.php" class="btn btn-primary">Ver Todos</a>
                    </div>
                    
                    <?php if (empty($recentOrders)): ?>
                        <p style="text-align: center; color: var(--text-muted); padding: 40px 0;">Nenhum pedido ainda</p>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Senha</th>
                                        <th>Cliente</th>
                                        <th>Total</th>
                                        <th>Tipo</th>
                                        <th>Status</th>
                                        <th>Data/Hora</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td><strong><?php echo $order['order_number']; ?></strong></td>
                                            <td><span style="font-size: 20px; font-weight: 700;"><?php echo $order['password']; ?></span></td>
                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td><strong><?php echo formatMoney($order['total']); ?></strong></td>
                                            <td><?php echo $order['delivery_type'] === 'delivery' ? 'Entrega' : 'Retirada'; ?></td>
                                            <td><?php echo getStatusBadge($order['status']); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="<?php echo BASE_URL; ?>admin/order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">Ver</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
