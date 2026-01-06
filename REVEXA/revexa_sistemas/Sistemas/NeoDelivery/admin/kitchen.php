<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$db = Database::getInstance()->getConnection();

// Atualizar status do pedido
if (isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = sanitizeInput($_POST['status']);
    
    $stmt = $db->prepare("UPDATE orders SET status = :status WHERE id = :id");
    $stmt->bindValue(':status', $newStatus, SQLITE3_TEXT);
    $stmt->bindValue(':id', $orderId, SQLITE3_INTEGER);
    $stmt->execute();
    
    header('Location: ' . BASE_URL . 'admin/kitchen.php');
    exit;
}

// Buscar pedidos ativos (não cancelados e não entregues)
$ordersQuery = $db->query("SELECT * FROM orders 
                           WHERE status NOT IN ('delivered', 'cancelled') 
                           ORDER BY CASE 
                               WHEN status = 'pending' THEN 1
                               WHEN status = 'preparing' THEN 2
                               WHEN status = 'ready' THEN 3
                               WHEN status = 'out_for_delivery' THEN 4
                           END, created_at ASC");
$orders = [];
while ($row = $ordersQuery->fetchArray(SQLITE3_ASSOC)) {
    $orderId = $row['id'];
    
    // Buscar itens do pedido
    $itemsQuery = $db->prepare("SELECT oi.*, p.name as product_name 
                                 FROM order_items oi 
                                 LEFT JOIN products p ON oi.product_id = p.id 
                                 WHERE oi.order_id = :order_id");
    $itemsQuery->bindValue(':order_id', $orderId, SQLITE3_INTEGER);
    $itemsResult = $itemsQuery->execute();
    
    $items = [];
    while ($item = $itemsResult->fetchArray(SQLITE3_ASSOC)) {
        $items[] = $item;
    }
    
    $row['items'] = $items;
    $orders[] = $row;
}

$businessName = getSetting('business_name', 'X Delivery');

// Status colors
$statusColors = [
    'pending' => '#fdcb6e',
    'preparing' => '#0984e3',
    'ready' => '#00b894',
    'out_for_delivery' => '#6c5ce7'
];

$statusLabels = [
    'pending' => 'NOVO PEDIDO',
    'preparing' => 'PREPARANDO',
    'ready' => 'PRONTO',
    'out_for_delivery' => 'SAIU PARA ENTREGA'
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modo Cozinha - <?php echo htmlspecialchars($businessName); ?></title>
    <meta http-equiv="refresh" content="30">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #1e1e1e;
            color: white;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #6c5ce7, #a29bfe);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 20px;
            opacity: 0.9;
        }
        
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .order-card {
            background: #2d2d2d;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: 3px solid transparent;
            transition: all 0.3s ease;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.4);
        }
        
        .order-card.pending {
            border-color: #fdcb6e;
        }
        
        .order-card.preparing {
            border-color: #0984e3;
        }
        
        .order-card.ready {
            border-color: #00b894;
            animation: pulse 2s infinite;
        }
        
        .order-card.out_for_delivery {
            border-color: #6c5ce7;
        }
        
        @keyframes pulse {
            0%, 100% { box-shadow: 0 10px 30px rgba(0,184,148,0.3); }
            50% { box-shadow: 0 15px 40px rgba(0,184,148,0.6); }
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #3d3d3d;
        }
        
        .order-number {
            font-size: 36px;
            font-weight: bold;
        }
        
        .order-time {
            font-size: 18px;
            color: #aaa;
        }
        
        .order-status {
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .customer-info {
            background: #3d3d3d;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .customer-info p {
            margin: 8px 0;
            font-size: 18px;
        }
        
        .customer-name {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .order-items {
            background: #3d3d3d;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .order-item {
            padding: 12px 0;
            border-bottom: 1px solid #4d4d4d;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-quantity {
            background: #6c5ce7;
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }
        
        .item-name {
            flex: 1;
            margin-left: 15px;
            font-size: 20px;
            font-weight: 600;
        }
        
        .item-notes {
            font-size: 16px;
            color: #fdcb6e;
            margin-left: 50px;
            margin-top: 5px;
        }
        
        .order-notes {
            background: #4d4d4d;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .status-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
        .status-btn {
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }
        
        .status-btn:hover {
            transform: scale(1.05);
        }
        
        .btn-preparing {
            background: #0984e3;
            color: white;
        }
        
        .btn-ready {
            background: #00b894;
            color: white;
        }
        
        .btn-delivery {
            background: #6c5ce7;
            color: white;
        }
        
        .btn-delivered {
            background: #2d3436;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 100px 20px;
            color: #aaa;
        }
        
        .empty-state h2 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .order-total {
            background: #6c5ce7;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .delivery-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .badge-delivery {
            background: #d63031;
            color: white;
        }
        
        .badge-pickup {
            background: #00b894;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🍳 MODO COZINHA</h1>
        <p><?php echo htmlspecialchars($businessName); ?> | <?php echo date('d/m/Y H:i'); ?></p>
        <p style="font-size: 16px; margin-top: 10px; opacity: 0.8;">Atualização automática a cada 30 segundos</p>
    </div>
    
    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <h2>✨</h2>
            <h2>Nenhum pedido ativo no momento</h2>
            <p style="font-size: 24px; margin-top: 20px;">Aguardando novos pedidos...</p>
        </div>
    <?php else: ?>
        <div class="orders-grid">
            <?php foreach ($orders as $order): ?>
                <div class="order-card <?php echo $order['status']; ?>">
                    <div class="order-header">
                        <div>
                            <div class="order-number">#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></div>
                            <div class="order-time"><?php echo date('H:i', strtotime($order['created_at'])); ?></div>
                        </div>
                        <div style="text-align: right;">
                            <div class="order-time">Pedido há</div>
                            <div style="font-size: 24px; font-weight: bold; color: #fdcb6e;">
                                <?php
                                $diff = time() - strtotime($order['created_at']);
                                $minutes = floor($diff / 60);
                                echo $minutes . ' min';
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="order-status" style="background-color: <?php echo $statusColors[$order['status']]; ?>;">
                        <?php echo $statusLabels[$order['status']]; ?>
                    </div>
                    
                    <div class="customer-info">
                        <div class="customer-name"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                        <p>📞 <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                        <span class="delivery-badge <?php echo $order['delivery_type'] === 'delivery' ? 'badge-delivery' : 'badge-pickup'; ?>">
                            <?php echo $order['delivery_type'] === 'delivery' ? '🏍️ ENTREGA' : '🏪 RETIRADA'; ?>
                        </span>
                        <?php if ($order['delivery_type'] === 'delivery' && $order['customer_address']): ?>
                            <p style="margin-top: 10px;">
                                📍 <?php echo htmlspecialchars($order['customer_address']); ?><br>
                                <?php echo htmlspecialchars($order['customer_neighborhood']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="order-items">
                        <?php foreach ($order['items'] as $item): ?>
                            <div class="order-item">
                                <div class="item-quantity"><?php echo $item['quantity']; ?>x</div>
                                <div style="flex: 1;">
                                    <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                    <?php if ($item['options']): ?>
                                        <div class="item-notes"><?php echo htmlspecialchars($item['options']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($order['notes']): ?>
                        <div class="order-notes">
                            <strong>📝 Observações:</strong><br>
                            <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="order-total">
                        TOTAL: <?php echo formatMoney($order['total']); ?>
                    </div>
                    
                    <form method="POST" class="status-buttons">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        
                        <?php if ($order['status'] === 'pending'): ?>
                            <button type="submit" name="status" value="preparing" class="status-btn btn-preparing">
                                ▶️ INICIAR PREPARO
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] === 'preparing'): ?>
                            <button type="submit" name="status" value="ready" class="status-btn btn-ready">
                                ✅ MARCAR PRONTO
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] === 'ready' && $order['delivery_type'] === 'delivery'): ?>
                            <button type="button" onclick='notifyCustomer(<?php echo json_encode($order); ?>)' class="status-btn" style="background: #00b894;">
                                📱 AVISAR CLIENTE
                            </button>
                            <button type="submit" name="status" value="out_for_delivery" class="status-btn btn-delivery">
                                🏍️ SAIU P/ ENTREGA
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] === 'ready' && $order['delivery_type'] === 'pickup'): ?>
                            <button type="button" onclick='notifyCustomer(<?php echo json_encode($order); ?>)' class="status-btn" style="background: #00b894;">
                                📱 AVISAR CLIENTE
                            </button>
                            <button type="submit" name="status" value="delivered" class="status-btn btn-delivered">
                                ✅ RETIRADO
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] === 'out_for_delivery'): ?>
                            <button type="submit" name="status" value="delivered" class="status-btn btn-delivered">
                                ✅ ENTREGUE
                            </button>
                        <?php endif; ?>
                        
                        <input type="hidden" name="update_status" value="1">
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="<?php echo BASE_URL; ?>admin/" 
           style="display: inline-block; padding: 15px 40px; background: #2d3436; color: white; text-decoration: none; border-radius: 10px; font-size: 18px; font-weight: bold;">
            ← Voltar ao Admin
        </a>
    </div>
    
    <script>
        function notifyCustomer(order) {
            const businessName = <?php echo json_encode($businessName); ?>;
            
            // Remover caracteres não numéricos do telefone
            let phone = order.customer_phone.replace(/\D/g, '');
            
            // Adicionar código do país se não tiver
            if (!phone.startsWith('55')) {
                phone = '55' + phone;
            }
            
            // Montar lista de itens
            let itemsList = '';
            order.items.forEach((item, index) => {
                itemsList += `${index + 1}. ${item.quantity}x ${item.product_name}`;
                if (item.options) {
                    itemsList += `\n   _${item.options}_`;
                }
                itemsList += '\n';
            });
            
            // Tipo de entrega
            const tipoEntrega = order.delivery_type === 'delivery' ? 'ENTREGA' : 'RETIRADA';
            const emojiEntrega = order.delivery_type === 'delivery' ? '🏍' : '🏪';
            
            // Forma de pagamento
            const formasPagamento = {
                'money': 'Dinheiro',
                'credit_card': 'Cartão de Crédito',
                'debit_card': 'Cartão de Débito',
                'pix': 'PIX'
            };
            const pagamento = formasPagamento[order.payment_method] || order.payment_method;
            
            // Montar mensagem completa
            let message = `*${businessName}*\n`;
            message += `Ola *${order.customer_name}*!\n\n`;
            message += `Seu pedido *#${String(order.id).padStart(4, '0')}* esta *PRONTO*!\n\n`;
            message += `━━━━━━━━━━━━━━━━\n`;
            message += `*ITENS DO PEDIDO:*\n\n`;
            message += itemsList;
            message += `\n━━━━━━━━━━━━━━━━\n`;
            message += `*TOTAL:* R$ ${parseFloat(order.total).toFixed(2).replace('.', ',')}\n`;
            message += `*Pagamento:* ${pagamento}\n`;
            message += `*Tipo:* ${emojiEntrega} ${tipoEntrega}\n`;
            
            if (order.delivery_type === 'delivery' && order.customer_address) {
                message += `*Endereço:* ${order.customer_address}`;
                if (order.customer_neighborhood) {
                    message += `, ${order.customer_neighborhood}`;
                }
                message += '\n';
            }
            
            if (order.notes) {
                message += `\n*Observações:* ${order.notes}\n`;
            }
            
            message += `\n━━━━━━━━━━━━━━━━\n\n`;
            
            if (order.delivery_type === 'delivery') {
                message += `Nossa entrega esta saindo agora!\n`;
                message += `Em breve estara ai! 🏍\n\n`;
            } else {
                message += `Pode vir buscar quando quiser!\n`;
                message += `Te esperamos aqui! 😊\n\n`;
            }
            
            message += `Obrigado pela preferencia!`;
            
            // Codificar mensagem para URL
            const encodedMessage = encodeURIComponent(message);
            
            // Abrir WhatsApp
            const whatsappUrl = `https://wa.me/${phone}?text=${encodedMessage}`;
            window.open(whatsappUrl, '_blank');
            
            // Feedback visual
            alert('Abrindo WhatsApp para avisar o cliente!');
        }
    </script>
</body>
</html>
