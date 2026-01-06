<?php
/**
 * Atualiza status do pedido e licenca no banco de dados
 */

$db_path = __DIR__ . '/database/store.db';

try {
    $db = new PDO('sqlite:' . $db_path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== ATUALIZANDO BANCO DE DADOS ===\n\n";
    
    // Atualiza pedido para aprovado
    $stmt = $db->prepare("UPDATE orders SET status = 'approved', updated_at = datetime('now') WHERE id = 11");
    $stmt->execute();
    echo "Pedido #11 marcado como APROVADO\n";
    
    // Atualiza licenca para ativa
    $stmt = $db->prepare("UPDATE licenses SET status = 'active', provisioned_at = datetime('now') WHERE id = 3");
    $stmt->execute();
    echo "Licenca #3 marcada como ATIVA\n";
    
    // Atualiza acesso da licenca com a URL correta
    $stmt = $db->prepare("UPDATE licenses SET access_url = 'https://revexa.com.br/revexa_sistemas/lojas/store-427cd6be/' WHERE id = 3");
    $stmt->execute();
    echo "URL de acesso atualizada\n";
    
    // Verifica resultado
    $stmt = $db->query("SELECT * FROM orders WHERE id = 11");
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\nPedido #11:\n";
    echo "  Status: " . $order['status'] . "\n";
    echo "  Total: R$ " . $order['total_amount'] . "\n";
    
    $stmt = $db->query("SELECT * FROM licenses WHERE id = 3");
    $license = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\nLicenca #3:\n";
    echo "  Status: " . $license['status'] . "\n";
    echo "  Chave: " . $license['license_key'] . "\n";
    echo "  URL: " . $license['access_url'] . "\n";
    
    echo "\n=== BANCO DE DADOS ATUALIZADO COM SUCESSO ===\n";
    
} catch (PDOException $e) {
    die("ERRO: " . $e->getMessage() . "\n");
}
