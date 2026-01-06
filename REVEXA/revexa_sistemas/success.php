<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/OrderProcessor.php';

// Process Payment Return
$payment_id = $_GET['payment_id'] ?? '';
$status = $_GET['status'] ?? '';
$preference_id = $_GET['preference_id'] ?? '';
$external_reference = $_GET['external_reference'] ?? ''; // Product ID

if ($status === 'approved') {
    $db = StoreDatabase::getInstance();
    
    // 1. Find the Order by Preference ID
    $order = $db->fetch("SELECT * FROM orders WHERE payment_id = ?", [$preference_id]);
    
    if ($order) {
        try {
            OrderProcessor::approveOrder($order['id']);
        } catch (Exception $e) {
            // Log error
            error_log("Error approving order: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Aprovado | RevexaSistemas</title>
    <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .status-container {
            max-width: 600px;
            margin: 100px auto;
            background: var(--dark-lighter);
            padding: 40px;
            border-radius: var(--radius);
            text-align: center;
            border: 1px solid rgba(20, 184, 166, 0.2);
        }
        .icon-success {
            font-size: 64px;
            color: var(--primary);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="status-container">
        <i class="fas fa-check-circle icon-success"></i>
        <h1 style="color: var(--white);">Pagamento Aprovado!</h1>
        <p style="color: var(--gray-300); margin: 20px 0;">Obrigado pela sua compra. Seu pedido foi processado com sucesso.</p>
        <p style="color: var(--gray-400); font-size: 14px;">Você receberá um e-mail com os detalhes do seu produto em breve.</p>
        
        <a href="index.php" class="btn btn-primary" style="margin-top: 30px; display: inline-block;">Voltar para a Loja</a>
    </div>
</body>
</html>