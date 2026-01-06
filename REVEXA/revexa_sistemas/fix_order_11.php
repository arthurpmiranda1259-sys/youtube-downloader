<?php
/**
 * Script de Correção para o Pedido #11 e Licença #3
 * 
 * Problemas identificados:
 * 1. Licença #3 criada sem 'delivery_method' (causando botão de Download)
 * 2. Licença #3 sem 'order_id' vinculado
 * 3. Pedido #11 pendente
 * 4. Instância do RevexaDental não criada
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/StoreProvisioner.php';

$db = StoreDatabase::getInstance();
$conn = $db->getConnection();

echo "<h1>Correção de Pedido e Licença</h1>";

$orderId = 11;
$licenseId = 3;
$customerEmail = 'arthurmiranda1259@gmail.com';

// 1. Corrigir Licença
echo "<h2>1. Corrigindo Licença #$licenseId</h2>";
$stmt = $conn->prepare("UPDATE licenses SET delivery_method = 'hosted', order_id = ? WHERE id = ?");
$stmt->execute([$orderId, $licenseId]);
echo "<p>✅ Licença atualizada para 'hosted' e vinculada ao pedido #$orderId.</p>";

// 2. Aprovar Pedido
echo "<h2>2. Aprovando Pedido #$orderId</h2>";
$stmt = $conn->prepare("UPDATE orders SET status = 'approved' WHERE id = ?");
$stmt->execute([$orderId]);
echo "<p>✅ Pedido marcado como Aprovado.</p>";

// 3. Provisionar Instância
echo "<h2>3. Provisionando Sistema RevexaDental</h2>";

// Buscar dados atualizados da licença
$license = $conn->query("SELECT * FROM licenses WHERE id = $licenseId")->fetch(PDO::FETCH_ASSOC);
$product = $conn->query("SELECT * FROM products WHERE id = {$license['product_id']}")->fetch(PDO::FETCH_ASSOC);

if ($license && $product) {
    $license_key = $license['license_key'];
    $slug = 'store-' . substr($license_key, 0, 8);
    
    echo "<p>Chave: $license_key</p>";
    echo "<p>Slug: $slug</p>";
    
    // Detectar origem
    $sourceName = 'RevexaDental'; // Forçando RevexaDental pois sabemos que é o produto 7
    $sourcePath = __DIR__ . '/Sistemas/' . $sourceName;
    $destRoot = __DIR__ . '/lojas';
    
    echo "<p>Origem: $sourcePath</p>";
    
    if (is_dir($sourcePath)) {
        try {
            // Provisionar
            StoreProvisioner::createInstance($slug, $sourcePath, $destRoot);
            
            // Atualizar URL na licença
            $storeUrl = SITE_URL . '/lojas/' . $slug;
            $conn->prepare("UPDATE licenses SET domain = ? WHERE id = ?")->execute([$storeUrl, $licenseId]);
            
            echo "<p class='success'>✅ Instância criada com sucesso!</p>";
            echo "<p>URL: <a href='$storeUrl' target='_blank'>$storeUrl</a></p>";
            
        } catch (Exception $e) {
            echo "<p class='error'>❌ Erro ao provisionar: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='error'>❌ Pasta fonte não encontrada: $sourcePath</p>";
    }
} else {
    echo "<p class='error'>❌ Licença ou Produto não encontrados.</p>";
}

echo "<h2>Concluído!</h2>";
echo "<p><a href='my-account.php'>Voltar para Minha Conta</a></p>";
?>
<style>
    body { font-family: sans-serif; background: #1a1a1a; color: #fff; padding: 20px; }
    .success { color: #4ade80; font-weight: bold; }
    .error { color: #f87171; font-weight: bold; }
    a { color: #60a5fa; }
</style>
