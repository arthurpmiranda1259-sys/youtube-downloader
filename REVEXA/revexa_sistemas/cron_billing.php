<?php
/**
 * Cron Job para Cobrança Automática (Billing)
 * Este script deve ser executado diariamente via cron job
 * 
 * Funcionalidades:
 * 1. Verifica licenças que vencem em 7 dias e envia notificação
 * 2. Verifica licenças que vencem hoje e cria pedido de renovação
 * 3. Bloqueia licenças vencidas há mais de 5 dias
 * 
 * Para configurar no cron:
 * 0 3 * * * /usr/bin/php /path/to/cron_billing.php
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';

$db = StoreDatabase::getInstance();
$conn = $db->getConnection();

$today = date('Y-m-d');
$sevenDaysFromNow = date('Y-m-d', strtotime('+7 days'));
$fiveDaysAgo = date('Y-m-d', strtotime('-5 days'));

echo "=== Cron Billing - " . date('Y-m-d H:i:s') . " ===\n";

// 1. Notificar licenças que vencem em 7 dias
echo "\n[1] Verificando licenças próximas ao vencimento...\n";
$stmt = $conn->prepare("
    SELECT l.*, p.name as product_name, p.monthly_price 
    FROM licenses l 
    JOIN products p ON l.product_id = p.id 
    WHERE l.status = 'active' 
    AND DATE(l.expires_at) = ? 
    AND l.delivery_method = 'hosted'
");
$stmt->execute([$sevenDaysFromNow]);

while ($license = $stmt->fetch()) {
    echo "  → Notificação: {$license['customer_email']} - {$license['product_name']} vence em 7 dias\n";
    
    // TODO: Implementar envio de e-mail de aviso
    // sendEmail($license['customer_email'], "Sua assinatura vence em 7 dias", ...);
}

// 2. Criar pedidos de renovação automática para licenças que vencem hoje
echo "\n[2] Criando pedidos de renovação para licenças que vencem hoje...\n";
$stmt = $conn->prepare("
    SELECT l.*, p.name as product_name, p.monthly_price, p.id as product_id
    FROM licenses l 
    JOIN products p ON l.product_id = p.id 
    WHERE l.status = 'active' 
    AND DATE(l.expires_at) = ? 
    AND l.delivery_method = 'hosted'
    AND p.billing_cycle = 'monthly'
");
$stmt->execute([$today]);

while ($license = $stmt->fetch()) {
    // Check if renewal order already exists
    $existing = $conn->prepare("SELECT id FROM orders WHERE customer_email = ? AND product_id = ? AND status = 'pending' AND created_at > ?");
    $existing->execute([$license['customer_email'], $license['product_id'], date('Y-m-d', strtotime('-1 day'))]);
    
    if (!$existing->fetch()) {
        // Create renewal order
        $insertOrder = $conn->prepare("
            INSERT INTO orders (product_id, customer_name, customer_email, amount, status, delivery_method, external_reference, created_at) 
            VALUES (?, ?, ?, ?, 'pending', 'hosted', ?, CURRENT_TIMESTAMP)
        ");
        
        $customerName = "Cliente"; // You might want to fetch this from customers table
        $externalRef = "AUTO-RENEWAL-R" . $license['id'];
        
        $insertOrder->execute([
            $license['product_id'],
            $customerName,
            $license['customer_email'],
            $license['monthly_price'],
            $externalRef
        ]);
        
        $orderId = $conn->lastInsertId();
        echo "  → Pedido de renovação criado: Order #$orderId para {$license['customer_email']}\n";
        
        // TODO: Enviar link de pagamento via e-mail
        // $paymentLink = createMercadoPagoPayment($orderId, ...);
        // sendEmail($license['customer_email'], "Renovação de Assinatura", $paymentLink);
    } else {
        echo "  → Pedido pendente já existe para {$license['customer_email']}\n";
    }
}

// 3. Bloquear licenças vencidas há mais de 5 dias
echo "\n[3] Bloqueando licenças vencidas há mais de 5 dias...\n";
$stmt = $conn->prepare("
    UPDATE licenses 
    SET status = 'blocked' 
    WHERE status = 'active' 
    AND DATE(expires_at) <= ? 
    AND delivery_method = 'hosted'
");
$stmt->execute([$fiveDaysAgo]);
$blocked = $stmt->rowCount();
echo "  → $blocked licenças bloqueadas\n";

// 4. Estatísticas
echo "\n[4] Estatísticas Gerais:\n";

// Licenças ativas
$active = $conn->query("SELECT COUNT(*) as count FROM licenses WHERE status = 'active'")->fetch();
echo "  → Licenças Ativas: {$active['count']}\n";

// Licenças bloqueadas
$blockedCount = $conn->query("SELECT COUNT(*) as count FROM licenses WHERE status = 'blocked'")->fetch();
echo "  → Licenças Bloqueadas: {$blockedCount['count']}\n";

// Pedidos pendentes
$pending = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch();
echo "  → Pedidos Pendentes: {$pending['count']}\n";

// Receita mensal estimada
$revenue = $conn->query("
    SELECT SUM(p.monthly_price) as total 
    FROM licenses l 
    JOIN products p ON l.product_id = p.id 
    WHERE l.status = 'active' 
    AND l.delivery_method = 'hosted'
")->fetch();
echo "  → Receita Mensal Estimada: R$ " . number_format($revenue['total'] ?? 0, 2, ',', '.') . "\n";

echo "\n=== Cron Billing Finalizado ===\n";
?>
