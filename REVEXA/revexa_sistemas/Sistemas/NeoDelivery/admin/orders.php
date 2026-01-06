<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$db = Database::getInstance()->getConnection();

// Atualizar status se enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $orderId = (int)$_POST['order_id'];
    $status = sanitizeInput($_POST['status']);
    
    $stmt = $db->prepare("UPDATE orders SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
    $stmt->bindValue(':status', $status, SQLITE3_TEXT);
    $stmt->bindValue(':id', $orderId, SQLITE3_INTEGER);
    $stmt->execute();
    
    header('Location: ' . BASE_URL . 'admin/orders.php');
    exit;
}

// Filtros
$statusFilter = $_GET['status'] ?? '';
$dateFilter = $_GET['date'] ?? date('Y-m-d');

$query = "SELECT * FROM orders WHERE 1=1";
if ($statusFilter) {
    $query .= " AND status = '{$statusFilter}'";
}
if ($dateFilter) {
    $query .= " AND DATE(created_at) = '{$dateFilter}'";
}
$query .= " ORDER BY created_at DESC";

$ordersQuery = $db->query($query);
$orders = [];
while ($row = $ordersQuery->fetchArray(SQLITE3_ASSOC)) {
    $orders[] = $row;
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
    <title>Pedidos - <?php echo htmlspecialchars($businessName); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-content">

            <header class="admin-header">
                <h1>Gerenciar Pedidos</h1>
                <a href="<?php echo BASE_URL; ?>admin/kitchen.php" class="btn btn-primary" target="_blank">Modo Cozinha</a>
            </header>
            
            <main class="admin-main">
                <!-- Filters -->
                <div class="admin-card">
                    <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: end;">
                        <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="">Todos</option>
                                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pendente</option>
                                <option value="preparing" <?php echo $statusFilter === 'preparing' ? 'selected' : ''; ?>>Preparando</option>
                                <option value="ready" <?php echo $statusFilter === 'ready' ? 'selected' : ''; ?>>Pronto</option>
                                <option value="delivering" <?php echo $statusFilter === 'delivering' ? 'selected' : ''; ?>>Saiu p/ entrega</option>
                                <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Concluído</option>
                                <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                            </select>
                        </div>
                        
                        <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
                            <label class="form-label">Data</label>
                            <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($dateFilter); ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="<?php echo BASE_URL; ?>admin/orders.php" class="btn btn-outline">Limpar</a>
                    </form>
                </div>
                
                <!-- Orders Table -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Pedidos (<?php echo count($orders); ?>)</h2>
                    </div>
                    
                    <?php if (empty($orders)): ?>
                        <p style="text-align: center; color: var(--text-muted); padding: 40px 0;">Nenhum pedido encontrado</p>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Senha</th>
                                        <th>Cliente</th>
                                        <th>Telefone</th>
                                        <th>Total</th>
                                        <th>Tipo</th>
                                        <th>Pagamento</th>
                                        <th>Status</th>
                                        <th>Data/Hora</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><strong><?php echo $order['order_number']; ?></strong></td>
                                            <td><span style="font-size: 20px; font-weight: 700; color: var(--primary-color);"><?php echo $order['password']; ?></span></td>
                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                                            <td><strong><?php echo formatMoney($order['total']); ?></strong></td>
                                            <td><?php echo $order['delivery_type'] === 'delivery' ? 'Entrega' : 'Retirada'; ?></td>
                                            <td><?php echo strtoupper($order['payment_method']); ?></td>
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
