<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$orderId = (int)($_GET['id'] ?? 0);
$db = Database::getInstance()->getConnection();

// Buscar pedido
$stmt = $db->prepare("SELECT * FROM orders WHERE id = :id");
$stmt->bindValue(':id', $orderId, SQLITE3_INTEGER);
$result = $stmt->execute();
$order = $result->fetchArray(SQLITE3_ASSOC);

if (!$order) {
    header('Location: ' . BASE_URL . 'admin/orders.php');
    exit;
}

// Buscar itens
$stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
$stmt->bindValue(':order_id', $orderId, SQLITE3_INTEGER);
$result = $stmt->execute();
$items = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $items[] = $row;
}

// Atualizar status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $status = sanitizeInput($_POST['status']);
    $stmt = $db->prepare("UPDATE orders SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
    $stmt->bindValue(':status', $status, SQLITE3_TEXT);
    $stmt->bindValue(':id', $orderId, SQLITE3_INTEGER);
    $stmt->execute();
    
    header('Location: ' . BASE_URL . 'admin/order_detail.php?id=' . $orderId);
    exit;
}

$businessName = getSetting('business_name', 'X Delivery');
$businessAddress = getSetting('business_address', '');
$businessWhatsapp = getSetting('business_whatsapp', '');
$pixKey = getSetting('pix_key', '');
$pixKeyType = getSetting('pix_key_type', 'CPF');
$pixHolderName = getSetting('pix_holder_name', '');

// Montar mensagem WhatsApp
$deliveryTypeText = $order['delivery_type'] === 'delivery' ? 'Entrega' : 'Retirada Balcão';
$paymentText = strtoupper($order['payment_method']);

$whatsappMessage = "Pedido {$businessName} aceito e irá começar o preparo!\n\n";
$whatsappMessage .= "Senha: {$order['password']}\n";
$whatsappMessage .= "Pedido: {$order['order_number']} (" . date('d/m/Y H:i', strtotime($order['created_at'])) . ")\n";
$whatsappMessage .= "Tipo: {$deliveryTypeText}\n";

if ($order['delivery_type'] === 'delivery') {
    $whatsappMessage .= "Endereço: {$order['customer_address']}";
    if ($order['customer_complement']) {
        $whatsappMessage .= ", {$order['customer_complement']}";
    }
    $whatsappMessage .= " - {$order['customer_neighborhood']}\n";
} else {
    $whatsappMessage .= "Endereço: {$businessAddress}\n";
}

$whatsappMessage .= "Estimativa: {$order['estimated_time']}\n";
$whatsappMessage .= "------------------------------\n";
$whatsappMessage .= "NOME: {$order['customer_name']}\n";
$whatsappMessage .= "Fone: {$order['customer_phone']}\n";
$whatsappMessage .= "------------------------------\n";

foreach ($items as $item) {
    $whatsappMessage .= "{$item['quantity']}x {$item['product_name']} " . formatMoney($item['subtotal']) . "\n";
    if ($item['options']) {
        $whatsappMessage .= "   {$item['options']}\n";
    }
}

$whatsappMessage .= "------------------------------\n";
$whatsappMessage .= "Itens: " . formatMoney($order['subtotal']) . "\n";
$whatsappMessage .= "Desconto: R$ 0,00\n";
$whatsappMessage .= "Entrega: " . formatMoney($order['delivery_fee']) . "\n\n";
$whatsappMessage .= "TOTAL: " . formatMoney($order['total']) . "\n";
$whatsappMessage .= "------------------------------\n";
$whatsappMessage .= "Pagamento: {$paymentText}\n";

if ($order['payment_method'] === 'pix' && $pixKey) {
    $whatsappMessage .= "Chave PIX: {$pixKey} ({$pixKeyType})";
    if ($pixHolderName) {
        $whatsappMessage .= " - {$pixHolderName}";
    }
    $whatsappMessage .= "\n\n";
}

$whatsappMessage .= "Para repetir este pedido:\n";
$whatsappMessage .= BASE_URL;

$whatsappUrl = "https://wa.me/" . preg_replace('/\D/', '', $businessWhatsapp) . "?text=" . urlencode($whatsappMessage);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido #<?php echo $order['order_number']; ?> - <?php echo htmlspecialchars($businessName); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
    <style>
        .order-detail-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 968px) {
            .order-detail-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .print-button {
            display: none;
        }
        
        @media print {
            .admin-sidebar,
            .admin-header,
            .no-print {
                display: none !important;
            }
            
            .admin-content {
                margin-left: 0;
            }
            
            .print-button {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="admin-content">
            <header class="admin-header no-print">
                <div>
                    <a href="<?php echo BASE_URL; ?>admin/orders.php" class="btn btn-outline" style="margin-right: 10px;">← Voltar</a>
                    <h1 style="display: inline;">Pedido #<?php echo $order['order_number']; ?></h1>
                </div>
                <div style="display: flex; gap: 10px;">
                    <?php if ($businessWhatsapp): ?>
                        <a href="<?php echo $whatsappUrl; ?>" target="_blank" class="btn btn-success">WhatsApp</a>
                    <?php endif; ?>
                    <button onclick="window.print()" class="btn btn-primary print-button">Imprimir</button>
                </div>
            </header>
            
            <main class="admin-main">
                <div class="order-detail-grid">
                    <!-- Order Info -->
                    <div>
                        <div class="admin-card">
                            <div style="text-align: center; padding: 20px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border-radius: 10px; margin-bottom: 20px;">
                                <div style="font-size: 64px; font-weight: 700; margin: 20px 0;">
                                    <?php echo $order['password']; ?>
                                </div>
                                <div style="font-size: 18px;">Senha do Pedido</div>
                            </div>
                            
                            <h3 style="margin-bottom: 15px;">Informações do Cliente</h3>
                            <div class="info-row">
                                <span class="info-label">Nome:</span>
                                <span class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Telefone:</span>
                                <span class="info-value"><?php echo htmlspecialchars($order['customer_phone']); ?></span>
                            </div>
                            
                            <h3 style="margin: 20px 0 15px;">Detalhes do Pedido</h3>
                            <div class="info-row">
                                <span class="info-label">Data/Hora:</span>
                                <span class="info-value"><?php echo date('d/m/Y H:i:s', strtotime($order['created_at'])); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Tipo:</span>
                                <span class="info-value"><?php echo $deliveryTypeText; ?></span>
                            </div>
                            
                            <?php if ($order['delivery_type'] === 'delivery'): ?>
                                <div class="info-row">
                                    <span class="info-label">Endereço:</span>
                                    <span class="info-value">
                                        <?php echo htmlspecialchars($order['customer_address']); ?>
                                        <?php if ($order['customer_complement']): ?>
                                            , <?php echo htmlspecialchars($order['customer_complement']); ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Bairro:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($order['customer_neighborhood']); ?></span>
                                </div>
                                <?php if ($order['customer_reference']): ?>
                                    <div class="info-row">
                                        <span class="info-label">Referência:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($order['customer_reference']); ?></span>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <div class="info-row">
                                <span class="info-label">Pagamento:</span>
                                <span class="info-value"><?php echo strtoupper($order['payment_method']); ?></span>
                            </div>
                            
                            <div class="info-row">
                                <span class="info-label">Estimativa:</span>
                                <span class="info-value"><?php echo htmlspecialchars($order['estimated_time']); ?></span>
                            </div>
                            
                            <?php if ($order['notes']): ?>
                                <h3 style="margin: 20px 0 15px;">Observações</h3>
                                <div style="padding: 15px; background-color: var(--light-color); border-radius: 8px;">
                                    <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="admin-card">
                            <h3 style="margin-bottom: 15px;">Itens do Pedido</h3>
                            <?php foreach ($items as $item): ?>
                                <div style="padding: 15px; border-bottom: 1px solid var(--border-color);">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                        <strong><?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['product_name']); ?></strong>
                                        <strong><?php echo formatMoney($item['subtotal']); ?></strong>
                                    </div>
                                    <?php if ($item['options']): ?>
                                        <div style="font-size: 14px; color: var(--text-muted);">
                                            <?php echo htmlspecialchars($item['options']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            
                            <div style="margin-top: 20px;">
                                <div class="info-row">
                                    <span>Subtotal:</span>
                                    <span><?php echo formatMoney($order['subtotal']); ?></span>
                                </div>
                                <div class="info-row">
                                    <span>Taxa de Entrega:</span>
                                    <span><?php echo formatMoney($order['delivery_fee']); ?></span>
                                </div>
                                <div class="info-row" style="font-size: 24px; font-weight: 700; padding-top: 15px; margin-top: 15px; border-top: 2px solid var(--border-color);">
                                    <span>TOTAL:</span>
                                    <span style="color: var(--primary-color);"><?php echo formatMoney($order['total']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Update -->
                    <div>
                        <div class="admin-card no-print">
                            <h3 style="margin-bottom: 15px;">Atualizar Status</h3>
                            <form method="POST">
                                <div class="form-group">
                                    <label class="form-label">Status Atual</label>
                                    <select name="status" class="form-control" required>
                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pendente</option>
                                        <option value="preparing" <?php echo $order['status'] === 'preparing' ? 'selected' : ''; ?>>Preparando</option>
                                        <option value="ready" <?php echo $order['status'] === 'ready' ? 'selected' : ''; ?>>Pronto</option>
                                        <option value="delivering" <?php echo $order['status'] === 'delivering' ? 'selected' : ''; ?>>Saiu p/ Entrega</option>
                                        <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Concluído</option>
                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Atualizar Status</button>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
