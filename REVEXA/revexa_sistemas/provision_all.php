<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/StoreProvisioner.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$db = StoreDatabase::getInstance();
$conn = $db->getConnection();

echo "<h1>Provisionamento em Massa</h1>";

// Buscar todas as licenças hosted sem instância
$stmt = $conn->query("
    SELECT l.*, p.name as product_name 
    FROM licenses l 
    JOIN products p ON l.product_id = p.id 
    WHERE l.delivery_method = 'hosted' 
    AND l.status = 'active'
");

$licenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<p>Total de licenças SaaS: " . count($licenses) . "</p>";

foreach ($licenses as $license) {
    $license_key = $license['license_key'];
    $slug = 'store-' . substr($license_key, 0, 8);
    $targetPath = __DIR__ . '/lojas/' . $slug;
    
    echo "<hr><h3>Licença: {$license['id']} - {$license['product_name']}</h3>";
    echo "<p>Slug: $slug</p>";
    
    // Check if instance already exists
    if (is_dir($targetPath)) {
        echo "<p>✅ Instância já existe.</p>";
        
        // Just update URL if needed
        $expectedUrl = SITE_URL . '/lojas/' . $slug;
        if ($license['domain'] != $expectedUrl) {
            $conn->prepare("UPDATE licenses SET domain = ? WHERE id = ?")->execute([$expectedUrl, $license['id']]);
            echo "<p>🔄 URL atualizada: $expectedUrl</p>";
        }
        continue;
    }
    
    echo "<p>⚠️ Instância não existe. Criando...</p>";
    
    // Detect system type
    $sourceName = 'NeoDelivery'; // Default
    if (stripos($license['product_name'], 'Dental') !== false || stripos($license['product_name'], 'Dentista') !== false) {
        $sourceName = 'RevexaDental';
    } elseif (stripos($license['product_name'], 'Delivery') !== false) {
        $sourceName = 'NeoDelivery';
    }
    
    $sourcePath = __DIR__ . '/Sistemas/' . $sourceName;
    $destRoot = __DIR__ . '/lojas';
    
    echo "<p>Sistema Base: $sourceName</p>";
    
    if (!is_dir($sourcePath)) {
        echo "<p style='color: red;'>❌ Pasta fonte não encontrada: $sourcePath</p>";
        continue;
    }
    
    try {
        // Ensure lojas directory exists
        if (!is_dir($destRoot)) {
            mkdir($destRoot, 0755, true);
        }
        
        // Provision
        StoreProvisioner::createInstance($slug, $sourcePath, $destRoot);
        
        // Update license
        $storeUrl = SITE_URL . '/lojas/' . $slug;
        $conn->prepare("UPDATE licenses SET domain = ? WHERE id = ?")->execute([$storeUrl, $license['id']]);
        
        echo "<p style='color: green;'>✅ Instância criada com sucesso!</p>";
        echo "<p><strong>URL:</strong> <a href='$storeUrl/dashboard.php' target='_blank'>$storeUrl</a></p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

echo "<hr><h2>✅ Processo Concluído!</h2>";
echo "<p><a href='my-account.php'>Voltar para Minha Conta</a></p>";
?>
<style>
    body { font-family: sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
    h1 { color: #333; }
    hr { margin: 30px 0; border: none; border-top: 2px solid #ddd; }
</style>
