<?php
require_once 'config/config.php';

// Se já está logado, redireciona
if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        $db = get_db();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ? AND ativo = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($senha, $user['senha'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nome'];
            $_SESSION['user_profile'] = $user['perfil'];
            
            // Atualizar último acesso
            $stmt = $db->prepare("UPDATE usuarios SET ultimo_acesso = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            log_audit('Login realizado');
            redirect('dashboard.php');
        } else {
            $error = 'Email ou senha inválidos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= SYSTEM_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>🦷 <?= SYSTEM_NAME ?></h1>
                <p>Sistema de Prontuário Odontológico</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        autofocus
                        value="<?= htmlspecialchars($email ?? '') ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input 
                        type="password" 
                        id="senha" 
                        name="senha" 
                        required
                    >
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    Entrar
                </button>
            </form>
            
            <div class="login-footer">
                <p class="text-muted">
                    <small>Usuário padrão: admin@revexa.com.br / Senha: admin123</small>
                </p>
                <p class="text-muted">
                    <small><?= SYSTEM_NAME ?> v<?= SYSTEM_VERSION ?></small>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
