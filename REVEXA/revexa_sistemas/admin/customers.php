<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../includes/Database.php';
$db = StoreDatabase::getInstance();

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM customers WHERE id = ?", [$id]);
    header('Location: customers.php');
    exit;
}

$customers = $db->fetchAll("SELECT * FROM customers ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes | RevexaSistemas</title>
    <link rel="icon" type="image/svg+xml" href="../assets/images/favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1 style="color: var(--white);">Gerenciar Clientes</h1>
                <a href="customer_add.php" class="btn btn-primary"><i class="fas fa-plus"></i> Novo Cliente</a>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Data Cadastro</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td>#<?= $customer['id'] ?></td>
                        <td style="color: var(--white); font-weight: 500;"><?= htmlspecialchars($customer['name']) ?></td>
                        <td style="color: var(--gray-300);"><?= htmlspecialchars($customer['email']) ?></td>
                        <td style="color: var(--gray-400);"><?= date('d/m/Y H:i', strtotime($customer['created_at'])) ?></td>
                        <td>
                            <a href="customer_edit.php?id=<?= $customer['id'] ?>" class="btn btn-outline" style="padding: 6px 12px; margin-right: 5px;" title="Editar"><i class="fas fa-edit"></i></a>
                            <a href="?delete=<?= $customer['id'] ?>" onclick="return confirm('Tem certeza que deseja remover este cliente?')" class="btn btn-outline" style="padding: 6px 12px; border-color: var(--danger); color: var(--danger);"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>