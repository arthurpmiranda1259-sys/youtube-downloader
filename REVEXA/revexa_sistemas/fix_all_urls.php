<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';

$db = StoreDatabase::getInstance();
$conn = $db->getConnection();

echo "<h1>Atualizando URLs do Banco de Dados</h1>";

// Update all licenses with old domain to new domain
$stmt = $conn->prepare("SELECT id, domain FROM licenses WHERE domain LIKE '%oticaemfoco.com.br%'");
$stmt->execute();
$licenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Licenças encontradas com URL antiga: " . count($licenses) . "</h3>";

foreach ($licenses as $license) {
    $oldDomain = $license['domain'];
    $newDomain = str_replace('oticaemfoco.com.br/sistema/REVEXA/revexa_sistemas', 'revexa.com.br/revexa_sistemas', $oldDomain);
    
    echo "<p>ID {$license['id']}: <br>";
    echo "Antiga: $oldDomain<br>";
    echo "Nova: $newDomain</p>";
    
    $update = $conn->prepare("UPDATE licenses SET domain = ? WHERE id = ?");
    $update->execute([$newDomain, $license['id']]);
}

echo "<h2>✅ Todas as URLs foram atualizadas!</h2>";
echo "<p><a href='my-account.php'>Voltar para Minha Conta</a></p>";
?>
