<?php
session_start();
require_once __DIR__ . '/utils.php';

// Credenciais de login (HARDCODED para simplicidade, mas deve ser alterado em produção)
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', '123456'); // Mudar para uma senha forte!

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = sanitize_input($_POST['password'] ?? '');

    if ($username === ADMIN_USER && $password === ADMIN_PASS) {
        $_SESSION['logged_in'] = true;
        redirect('index.php');
    } else {
        $error = 'Usuário ou senha inválidos.';
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #333; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.5); width: 300px; }
        h1 { text-align: center; color: #333; margin-bottom: 20px; }
        .error { background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #f5c6cb; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; box-sizing: border-box; border-radius: 4px; }
        button { width: 100%; background-color: #5cb85c; color: white; padding: 10px; border: none; cursor: pointer; border-radius: 4px; font-size: 16px; }
        button:hover { background-color: #4cae4c; }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Acesso Administrativo</h1>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <label for="username">Usuário:</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Senha:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>
