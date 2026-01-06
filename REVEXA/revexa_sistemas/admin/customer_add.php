<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../includes/Database.php';
$db = StoreDatabase::getInstance();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = "Todos os campos são obrigatórios.";
    } else {
        try {
            // Check if email exists
            $exists = $db->fetch("SELECT id FROM customers WHERE email = ?", [$email]);
            if ($exists) {
                $error = "Este email já está cadastrado.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $db->query("INSERT INTO customers (name, email, password) VALUES (?, ?, ?)", [$name, $email, $hashed_password]);
                header('Location: customers.php');
                exit;
            }
        } catch (Exception $e) {
            $error = "Erro ao criar cliente: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Cliente | RevexaSistemas</title>
    <link rel="icon" type="image/svg+xml" href="../assets/images/favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .card {
            background: var(--gray-800);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--gray-700);
            max-width: 600px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <a href="customers.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Voltar</a>
                    <h1 style="color: var(--white); margin: 0;">Novo Cliente</h1>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert error" style="max-width: 600px; margin: 0 auto 20px;"><?= $error ?></div>
            <?php endif; ?>

            <div class="card">
                <form method="POST">
                    <div class="form-group">
                        <label>Nome Completo</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Senha</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <div style="margin-top: 20px; text-align: right;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Criar Cliente
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
