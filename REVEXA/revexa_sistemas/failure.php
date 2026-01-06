<?php
require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Falhou | RevexaSistemas</title>
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
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        .icon-failure {
            font-size: 64px;
            color: var(--danger);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="status-container">
        <i class="fas fa-times-circle icon-failure"></i>
        <h1 style="color: var(--white);">Pagamento Não Concluído</h1>
        <p style="color: var(--gray-300); margin: 20px 0;">Houve um problema ao processar seu pagamento. Por favor, tente novamente.</p>
        
        <a href="index.php" class="btn btn-primary" style="margin-top: 30px; display: inline-block; background: var(--gray-600);">Voltar para a Loja</a>
    </div>
</body>
</html>