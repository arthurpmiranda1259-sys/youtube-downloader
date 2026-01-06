<?php
require_once 'config/config.php';
require_once 'includes/Database.php';

$db = StoreDatabase::getInstance();
$conn = $db->getConnection();

echo "<h1>Corrigindo Assinatura e Produto</h1>";

// 1. Atualizar o produto para ser do tipo 'hosted' (SaaS)
$productName = "App de Delivery";
$stmt = $conn->prepare("UPDATE products SET delivery_method = 'hosted' WHERE name = ?");
$stmt->execute([$productName]);
echo "<p>Produto '$productName' atualizado para 'hosted'.</p>";

// 2. Atualizar a licença para ter validade de 1 mês e definir o caminho do sistema
// Vamos pegar a licença mais recente deste usuário
session_start();
if (isset($_SESSION['customer_email'])) {
    $email = $_SESSION['customer_email'];
    
    // Buscar a licença
    $stmt = $conn->prepare("SELECT id FROM licenses WHERE customer_email = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$email]);
    $license = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($license) {
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 month'));
        $domain = 'Sistemas/NeoDelivery'; // Caminho relativo para o sistema
        
        $update = $conn->prepare("UPDATE licenses SET expires_at = ?, domain = ? WHERE id = ?");
        $update->execute([$expiresAt, $domain, $license['id']]);
        
        echo "<p>Licença ID {$license['id']} atualizada:</p>";
        echo "<ul>";
        echo "<li>Vencimento: $expiresAt</li>";
        echo "<li>Caminho do Sistema: $domain</li>";
        echo "</ul>";
    } else {
        echo "<p>Nenhuma licença encontrada para $email.</p>";
    }
} else {
    echo "<p>Por favor, faça login para atualizar sua licença específica.</p>";
}

echo "<p><a href='my-account.php'>Voltar para Minha Conta</a></p>";
?>