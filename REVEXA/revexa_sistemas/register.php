<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';

session_start();

$redirect = $_GET['redirect'] ?? 'my-account.php';

if (isset($_SESSION['customer_id'])) {
    header("Location: $redirect");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $redirect = $_POST['redirect'] ?? 'my-account.php';

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Por favor, preencha todos os campos.';
    } elseif ($password !== $confirm_password) {
        $error = 'As senhas não coincidem.';
    } else {
        $db = StoreDatabase::getInstance();
        $conn = $db->getConnection();
        
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Este e-mail já está cadastrado.';
        } else {
            // Create account
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO customers (name, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$name, $email, $hash])) {
                $_SESSION['customer_id'] = $conn->lastInsertId();
                $_SESSION['customer_name'] = $name;
                $_SESSION['customer_email'] = $email;
                header("Location: $redirect");
                exit;
            } else {
                $error = 'Erro ao criar conta. Tente novamente.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro | RevexaSistemas</title>
    <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-container {
            max-width: 400px;
            margin: 50px auto;
            background: var(--dark-lighter);
            padding: 40px;
            border-radius: var(--radius);
            border: 1px solid rgba(255,255,255,0.05);
        }
        .auth-title {
            color: var(--white);
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            color: var(--gray-300);
            margin-bottom: 8px;
            font-size: 14px;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: var(--white);
            font-size: 16px;
        }
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
        }
        .btn-block {
            width: 100%;
            padding: 12px;
            font-size: 16px;
        }
        .auth-footer {
            text-align: center;
            margin-top: 20px;
            color: var(--gray-400);
            font-size: 14px;
        }
        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-content">
            <a href="index.php" class="logo">
                <i class="fas fa-cube"></i> RevexaSistemas
            </a>
            <nav class="nav-links">
                <a href="index.php">Voltar para Loja</a>
            </nav>
        </div>
    </header>

    <div class="auth-container">
        <h1 class="auth-title">Criar Conta</h1>
        
        <?php if ($error): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
            <div class="form-group">
                <label class="form-label">Nome Completo</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Senha</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Confirmar Senha</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Cadastrar</button>
        </form>
        
        <div class="auth-footer">
            Já tem uma conta? <a href="login.php?redirect=<?= urlencode($redirect) ?>">Fazer Login</a>
        </div>
    </div>
</body>
</html>