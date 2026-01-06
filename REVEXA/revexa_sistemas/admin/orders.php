<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/OrderProcessor.php';
$db = StoreDatabase::getInstance();

// Handle Status Update
if (isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $status = $_POST['status'];
    
    if ($status === 'approved') {
        try {
            OrderProcessor::approveOrder($orderId);
            // Success message could be set here
        } catch (Exception $e) {
            // Log error and maybe set a session flash message
            error_log("Admin Approval Error: " . $e->getMessage());
            // For now, we just redirect, but the status won't change if it failed.
        }
    } else {
        $db->query("UPDATE orders SET status = ? WHERE id = ?", [$status, $orderId]);
    }
    
    header('Location: orders.php');
    exit;
}

$orders = $db->fetchAll("
    SELECT o.*, p.name as product_name, p.type as product_type 
    FROM orders o 
    LEFT JOIN products p ON o.product_id = p.id 
    ORDER BY o.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos | RevexaSistemas</title>
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
                <h1 style="color: var(--white);">Gerenciar Pedidos</h1>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Produto</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--gray-400); padding: 40px;">Nenhum pedido encontrado.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td>
                                <div style="color: var(--white); font-weight: 500;"><?= htmlspecialchars($order['customer_name']) ?></div>
                                <div style="color: var(--gray-400); font-size: 12px;"><?= htmlspecialchars($order['customer_email']) ?></div>
                            </td>
                            <td>
                                <div style="color: var(--white);"><?= htmlspecialchars($order['product_name']) ?></div>
                                <span style="font-size: 11px; background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px; color: var(--gray-300);"><?= htmlspecialchars($order['product_type']) ?></span>
                            </td>
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
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <?php if($order['status'] != 'approved'): ?>
                                        <button type="submit" name="update_status" value="approved" class="btn btn-outline" style="padding: 6px 12px; color: var(--success); border-color: var(--success);" title="Aprovar e Liberar">
                                            <i class="fas fa-check"></i> Aprovar
                                        </button>
                                    <?php endif; ?>
                                    <?php if($order['status'] != 'rejected'): ?>
                                        <button type="submit" name="update_status" value="rejected" class="btn btn-outline" style="padding: 6px 12px; color: var(--danger); border-color: var(--danger);" title="Cancelar Pedido" onclick="return confirm('Tem certeza que deseja cancelar este pedido?');">
                                            <i class="fas fa-times"></i> Cancelar
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
