<?php
require_once __DIR__ . '/config/config.php';

$orderNumber = $_GET['order'] ?? '';

if (!$orderNumber) {
    header('Location: ' . BASE_URL);
    exit;
}

$db = Database::getInstance()->getConnection();

// Buscar pedido
$stmt = $db->prepare("SELECT * FROM orders WHERE order_number = :order_number");
$stmt->bindValue(':order_number', $orderNumber, SQLITE3_TEXT);
$result = $stmt->execute();
$order = $result->fetchArray(SQLITE3_ASSOC);

if (!$order) {
    header('Location: ' . BASE_URL);
    exit;
}

// Buscar itens do pedido
$stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
$stmt->bindValue(':order_id', $order['id'], SQLITE3_INTEGER);
$result = $stmt->execute();
$items = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $items[] = $row;
}

$businessName = getSetting('business_name', 'X Delivery');
$businessAddress = getSetting('business_address', '');
$businessWhatsapp = getSetting('business_whatsapp', '');
$businessLogo = getSetting('business_logo', '');
$pixKey = getSetting('pix_key', '');
$pixKeyType = getSetting('pix_key_type', 'CPF');
$pixHolderName = getSetting('pix_holder_name', '');

// Montar mensagem do WhatsApp
$deliveryTypeText = $order['delivery_type'] === 'delivery' ? 'Entrega' : 'Retirada Balcão';
$paymentText = '';

switch ($order['payment_method']) {
    case 'pix':
        $paymentText = 'PIX';
        break;
    case 'dinheiro':
        $paymentText = 'Dinheiro na entrega';
        break;
    case 'cartao':
        $paymentText = 'Cartão na entrega';
        break;
}

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
    <title>Pedido Confirmado - <?php echo htmlspecialchars($businessName); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        .confirmation-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .confirmation-header {
            text-align: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, var(--success-color), #00d2b8);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .confirmation-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .order-password {
            font-size: 48px;
            font-weight: 700;
            margin: 20px 0;
        }
        
        .order-info {
            background: white;
            padding: 30px;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--text-muted);
        }
        
        .info-value {
            text-align: right;
            font-weight: 600;
        }
        
        .order-items {
            margin: 20px 0;
            padding: 20px;
            background-color: var(--light-color);
            border-radius: 10px;
        }
        
        .order-item {
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .pix-info {
            background-color: #e8f5e9;
            border: 2px solid var(--success-color);
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .pix-info h3 {
            color: var(--success-color);
            margin-bottom: 15px;
        }
        
        .pix-key {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 16px;
            word-break: break-all;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="confirmation-header">
            <h1>Pedido Confirmado!</h1>
            <p>Seu pedido foi recebido com sucesso</p>
            <div class="order-password">
                Senha: <?php echo $order['password']; ?>
            </div>
            <p style="font-size: 14px; opacity: 0.9;">Use esta senha para acompanhar seu pedido</p>
        </div>
        
        <div class="order-info">
            <div class="info-row">
                <span class="info-label">Número do Pedido:</span>
                <span class="info-value"><?php echo $order['order_number']; ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Data e Hora:</span>
                <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
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
                    <br><?php echo htmlspecialchars($order['customer_neighborhood']); ?>
                </span>
            </div>
            <?php else: ?>
            <div class="info-row">
                <span class="info-label">Retirar em:</span>
                <span class="info-value"><?php echo htmlspecialchars($businessAddress); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="info-row">
                <span class="info-label">Estimativa:</span>
                <span class="info-value"><?php echo htmlspecialchars($order['estimated_time']); ?></span>
            </div>
            
            <div class="order-items">
                <h3 style="margin-bottom: 15px;">Itens do Pedido</h3>
                <?php foreach ($items as $item): ?>
                    <div class="order-item">
                        <div style="display: flex; justify-content: space-between;">
                            <span><?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['product_name']); ?></span>
                            <span style="font-weight: 600;"><?php echo formatMoney($item['subtotal']); ?></span>
                        </div>
                        <?php if ($item['options']): ?>
                            <div style="font-size: 14px; color: var(--text-muted); margin-top: 5px;">
                                <?php echo htmlspecialchars($item['options']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="info-row">
                <span class="info-label">Subtotal:</span>
                <span class="info-value"><?php echo formatMoney($order['subtotal']); ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Taxa de Entrega:</span>
                <span class="info-value"><?php echo formatMoney($order['delivery_fee']); ?></span>
            </div>
            
            <div class="info-row" style="font-size: 20px; padding-top: 20px; margin-top: 10px; border-top: 2px solid var(--border-color);">
                <span class="info-label">TOTAL:</span>
                <span class="info-value" style="color: var(--primary-color);"><?php echo formatMoney($order['total']); ?></span>
            </div>
            
            <?php if ($order['payment_method'] === 'pix' && $pixKey): ?>
            <div class="pix-info">
                <h3>Informações para Pagamento PIX</h3>
                <p style="margin-bottom: 10px;">Realize o pagamento usando a chave PIX abaixo:</p>
                <div class="pix-key">
                    <?php echo htmlspecialchars($pixKey); ?>
                </div>
                <div style="font-size: 14px; color: var(--text-muted);">
                    Tipo: <?php echo htmlspecialchars($pixKeyType); ?>
                    <?php if ($pixHolderName): ?>
                        <br>Favorecido: <?php echo htmlspecialchars($pixHolderName); ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($businessWhatsapp): ?>
            <a href="<?php echo $whatsappUrl; ?>" target="_blank" class="btn btn-success btn-block" style="margin-top: 20px; font-size: 18px;">
                Enviar Pedido para WhatsApp
            </a>
            <?php endif; ?>
            
            <a href="<?php echo BASE_URL; ?>" class="btn btn-outline btn-block" style="margin-top: 10px;">
                Fazer Novo Pedido
            </a>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($businessName); ?>. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script>
        // Limpar carrinho após confirmação
        localStorage.removeItem('cart');
    </script>
</body>
</html>
