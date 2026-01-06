<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../includes/Database.php';
$db = StoreDatabase::getInstance();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$customer = $db->fetch("SELECT * FROM customers WHERE id = ?", [$id]);

if (!$customer) {
    header('Location: customers.php');
    exit;
}

$message = '';
$error = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        try {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $db->query("UPDATE customers SET name = ?, email = ?, password = ? WHERE id = ?", [$name, $email, $hashed_password, $id]);
            } else {
                $db->query("UPDATE customers SET name = ?, email = ? WHERE id = ?", [$name, $email, $id]);
            }
            
            // Update email in orders and licenses if changed
            if ($email !== $customer['email']) {
                $db->query("UPDATE orders SET customer_email = ? WHERE customer_email = ?", [$email, $customer['email']]);
                $db->query("UPDATE licenses SET customer_email = ? WHERE customer_email = ?", [$email, $customer['email']]);
            }
            
            $message = "Cliente atualizado com sucesso!";
            $customer = $db->fetch("SELECT * FROM customers WHERE id = ?", [$id]); // Refresh data
        } catch (Exception $e) {
            $error = "Erro ao atualizar: " . $e->getMessage();
        }
    }
}

// Fetch Related Data
$orders = $db->fetchAll("
    SELECT o.*, p.name as product_name 
    FROM orders o 
    LEFT JOIN products p ON o.product_id = p.id 
    WHERE o.customer_email = ? 
    ORDER BY o.created_at DESC
", [$customer['email']]);

$licenses = $db->fetchAll("
    SELECT l.*, p.name as product_name 
    FROM licenses l 
    LEFT JOIN products p ON l.product_id = p.id 
    WHERE l.customer_email = ? 
    ORDER BY l.created_at DESC
", [$customer['email']]);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente | RevexaSistemas</title>
    <link rel="icon" type="image/svg+xml" href="../assets/images/favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .section-title {
            color: var(--white);
            font-size: 1.2rem;
            margin: 30px 0 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--gray-700);
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .card {
            background: var(--gray-800);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--gray-700);
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
                    <h1 style="color: var(--white); margin: 0;">Editar Cliente: <?= htmlspecialchars($customer['name']) ?></h1>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert success"><?= $message ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert error"><?= $error ?></div>
            <?php endif; ?>

            <div class="card">
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nome Completo</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($customer['name']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($customer['email']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Nova Senha (deixe em branco para manter)</label>
                            <input type="password" name="password" class="form-control" placeholder="********">
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px; text-align: right;">
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>

            <h2 class="section-title"><i class="fas fa-shopping-cart"></i> Histórico de Pedidos</h2>
            <?php if (empty($orders)): ?>
                <p style="color: var(--gray-400);">Nenhum pedido encontrado.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Produto</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td style="color: var(--white);"><?= htmlspecialchars($order['product_name']) ?></td>
                            <td style="color: var(--primary);">R$ <?= number_format($order['amount'], 2, ',', '.') ?></td>
                            <td>
                                <span class="status-badge status-<?= $order['status'] ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td style="color: var(--gray-400);"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h2 class="section-title"><i class="fas fa-key"></i> Licenças Ativas</h2>
            <?php if (empty($licenses)): ?>
                <p style="color: var(--gray-400);">Nenhuma licença encontrada.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Produto</th>
                            <th>Chave</th>
                            <th>Status</th>
                            <th>Vencimento</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($licenses as $license): ?>
                        <tr>
                            <td>#<?= $license['id'] ?></td>
                            <td style="color: var(--white);"><?= htmlspecialchars($license['product_name']) ?></td>
                            <td><code style="background: rgba(255,255,255,0.1); padding: 2px 5px; border-radius: 4px;"><?= htmlspecialchars(substr($license['license_key'], 0, 10)) ?>...</code></td>
                            <td>
                                <?php if($license['status'] == 'active'): ?>
                                    <span style="color: #10b981;">Ativo</span>
                                <?php elseif($license['status'] == 'blocked'): ?>
                                    <span style="color: #ef4444;">Bloqueado</span>
                                <?php else: ?>
                                    <span style="color: #f59e0b;">Expirado</span>
                                <?php endif; ?>
                            </td>
                            <td style="color: var(--white);">
                                <?php if($license['expires_at']): ?>
                                    <?= date('d/m/Y', strtotime($license['expires_at'])) ?>
                                <?php else: ?>
                                    Vitalício
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="licenses.php?email=<?= urlencode($customer['email']) ?>" class="btn btn-outline" style="padding: 4px 8px; font-size: 12px;">Gerenciar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        </main>
    </div>
</body>
</html>
