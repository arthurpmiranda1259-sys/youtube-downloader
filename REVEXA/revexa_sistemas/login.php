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
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $redirect = $_POST['redirect'] ?? 'my-account.php';

    if (empty($email) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        $db = StoreDatabase::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        $customer = $stmt->fetch();

        if ($customer && password_verify($password, $customer['password'])) {
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_name'] = $customer['name'];
            $_SESSION['customer_email'] = $customer['email'];
            header("Location: $redirect");
            exit;
        } else {
            $error = 'E-mail ou senha incorretos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Cliente | RevexaSistemas</title>
    <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-container {
            max-width: 400px;
            margin: 100px auto;
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
        <h1 class="auth-title">Login do Cliente</h1>
        
        <?php if ($error): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
            <div class="form-group">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Senha</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
        </form>
        
        <div class="auth-footer">
            Não tem uma conta? <a href="register.php?redirect=<?= urlencode($redirect) ?>">Cadastre-se</a>
        </div>
    </div>
</body>
</html>