<?php
/**
 * Setup Script para adicionar RevexaDental ao sistema
 * Este script:
 * 1. Adiciona o produto RevexaDental como SaaS (Hospedado)
 * 2. Configura o sistema para Multi-Tenancy
 * 3. Remove a opção de vencimento vitalício para produtos de Download
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';

$db = StoreDatabase::getInstance();
$conn = $db->getConnection();

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Setup RevexaDental</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #1a1a1a; color: #fff; }
        h1 { color: #14b8a6; }
        .success { background: rgba(16, 185, 129, 0.1); border-left: 4px solid #10b981; padding: 15px; margin: 20px 0; }
        .warning { background: rgba(245, 158, 11, 0.1); border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; }
        .info { background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6; padding: 15px; margin: 20px 0; }
        .btn { background: #14b8a6; color: white; padding: 10px 20px; text-decoration: none; display: inline-block; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>🦷 Configuração RevexaDental</h1>";

try {
    // 1. Check if RevexaDental product already exists
    $existing = $conn->query("SELECT * FROM products WHERE name LIKE '%Dental%' OR name LIKE '%Dentista%'")->fetch();
    
    if ($existing) {
        echo "<div class='warning'>⚠️ Produto RevexaDental já existe no banco de dados.</div>";
        echo "<div class='info'>
            <strong>Produto Existente:</strong><br>
            ID: {$existing['id']}<br>
            Nome: {$existing['name']}<br>
            Preço: R$ " . number_format($existing['price'], 2, ',', '.') . "<br>
            Método: {$existing['delivery_method']}
        </div>";
        
        // Update to ensure it's configured correctly
        $stmt = $conn->prepare("UPDATE products SET 
            delivery_method = 'hosted', 
            delivery_options = 'hosted',
            billing_cycle = 'monthly',
            monthly_price = ?,
            active = 1
            WHERE id = ?");
        $stmt->execute([$existing['monthly_price'] ?? $existing['price'], $existing['id']]);
        
        echo "<div class='success'>✅ Produto atualizado para modo SaaS (Hospedado apenas).</div>";
    } else {
        // 2. Add RevexaDental Product
        $stmt = $conn->prepare("INSERT INTO products 
            (name, description, price, monthly_price, type, delivery_method, delivery_options, billing_cycle, image_url, active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        
        $productData = [
            'RevexaDental - Sistema de Gestão para Dentistas',
            'Sistema completo para gestão de clínicas odontológicas com controle de pacientes, agendamentos, prontuários e financeiro.',
            99.90, // Preço de ativação (primeira mensalidade)
            99.90, // Mensalidade
            'system',
            'hosted', // Somente hospedado
            'hosted', // Opções: somente hospedado
            'monthly', // Cobrança mensal
            'https://images.unsplash.com/photo-1588776814546-1ffcf47267a5?w=800' // Imagem de dentista
        ];
        
        $stmt->execute($productData);
        $productId = $conn->lastInsertId();
        
        echo "<div class='success'>✅ Produto RevexaDental criado com sucesso! (ID: $productId)</div>";
        echo "<div class='info'>
            <strong>Configuração do Produto:</strong><br>
            • Tipo: SaaS (Hospedado)<br>
            • Mensalidade: R$ 99,90<br>
            • Cada cliente terá sua própria instância isolada<br>
            • Renovação automática (cobrança mensal)
        </div>";
    }
    
    // 3. Fix existing NeoDelivery products if needed
    echo "<h2>🛠️ Ajustando Produtos Existentes</h2>";
    
    // Remove vencimento vitalício dos produtos de Download
    $stmt = $conn->query("SELECT * FROM licenses WHERE delivery_method = 'file' AND expires_at IS NULL");
    $vitalicioCount = 0;
    
    while ($license = $stmt->fetch()) {
        // Don't add expiration to download products - they should remain lifetime
        $vitalicioCount++;
    }
    
    echo "<div class='info'>ℹ️ Produtos de Download (File) mantidos como Vitalícios: $vitalicioCount</div>";
    
    // Update NeoDelivery to have correct monthly pricing if hosted
    $conn->exec("UPDATE products SET monthly_price = CASE 
        WHEN delivery_method = 'hosted' AND monthly_price = 0 THEN price 
        ELSE monthly_price 
    END WHERE name LIKE '%Delivery%'");
    
    echo "<div class='success'>✅ Produtos NeoDelivery atualizados.</div>";
    
    // 4. Create lojas directory if not exists
    $lojasDir = __DIR__ . '/lojas';
    if (!is_dir($lojasDir)) {
        mkdir($lojasDir, 0755, true);
        echo "<div class='success'>✅ Diretório /lojas criado para instâncias SaaS.</div>";
    } else {
        echo "<div class='info'>ℹ️ Diretório /lojas já existe.</div>";
    }
    
    // 5. Summary
    echo "<h2>📋 Resumo</h2>";
    echo "<div class='info'>";
    echo "<strong>Produtos Configurados:</strong><br>";
    
    $products = $conn->query("SELECT name, delivery_method, billing_cycle, monthly_price FROM products WHERE active = 1")->fetchAll();
    foreach ($products as $p) {
        $type = $p['delivery_method'] == 'hosted' ? '☁️ SaaS' : '📦 Download';
        $billing = $p['billing_cycle'] == 'monthly' ? 'Mensal' : ($p['billing_cycle'] == 'yearly' ? 'Anual' : 'Único');
        $price = $p['monthly_price'] > 0 ? 'R$ ' . number_format($p['monthly_price'], 2, ',', '.') . '/mês' : 'Vitalício';
        echo "• {$p['name']} - $type - $billing - $price<br>";
    }
    echo "</div>";
    
    echo "<h2>✅ Configuração Concluída!</h2>";
    echo "<div class='success'>
        <strong>Próximos Passos:</strong><br>
        1. Configure o cron job para cobrança automática (cron_billing.php)<br>
        2. Teste uma compra do RevexaDental na loja<br>
        3. Verifique se a instância é criada automaticamente em /lojas/<br>
        4. Configure o Mercado Pago no painel admin
    </div>";
    
    echo "<a href='index.php' class='btn'>Ir para a Loja</a>";
    echo "<a href='admin/login.php' class='btn' style='margin-left: 10px;'>Painel Admin</a>";
    
} catch (Exception $e) {
    echo "<div class='warning'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</body></html>";
?>
