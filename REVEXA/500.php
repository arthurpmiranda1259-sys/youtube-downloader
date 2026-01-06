<?php
/**
 * REVEXA Sistemas - 500 Error Page
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro no servidor - REVEXA Sistemas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            color: #fff;
            text-align: center;
            padding: 20px;
        }
        .error-container {
            max-width: 500px;
        }
        .error-code {
            font-size: 120px;
            font-weight: 800;
            background: linear-gradient(135deg, #6366f1 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 20px;
        }
        .error-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 16px;
        }
        .error-message {
            font-size: 16px;
            color: rgba(255,255,255,0.7);
            margin-bottom: 32px;
            line-height: 1.7;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 28px;
            background: linear-gradient(135deg, #6366f1 0%, #ec4899 100%);
            color: #fff;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(99, 102, 241, 0.4);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">500</div>
        <h1 class="error-title">Erro interno do servidor</h1>
        <p class="error-message">
            Ops! Algo deu errado em nosso servidor.
            Nossa equipe já foi notificada e está trabalhando para resolver o problema.
        </p>
        <a href="/" class="btn">
            <i class="fas fa-home"></i> Voltar ao Início
        </a>
    </div>
</body>
</html>
