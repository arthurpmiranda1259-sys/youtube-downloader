<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';

session_start();

if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit;
}

$db = StoreDatabase::getInstance();
$conn = $db->getConnection();

// Get Orders
$stmt = $conn->prepare("
    SELECT o.*, p.name as product_name, p.delivery_method 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    WHERE o.customer_email = ? 
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['customer_email']]);
$orders = $stmt->fetchAll();

// Get Licenses
$stmt = $conn->prepare("
    SELECT l.*, p.name as product_name, p.delivery_method as product_default_delivery, p.monthly_price 
    FROM licenses l 
    JOIN products p ON l.product_id = p.id 
    WHERE l.customer_email = ? 
    ORDER BY l.created_at DESC
");
$stmt->execute([$_SESSION['customer_email']]);
$licenses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta | RevexaSistemas</title>
    <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .account-header {
            background: var(--dark-lighter);
            padding: 40px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .account-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-welcome h1 {
            color: var(--white);
            font-size: 24px;
            margin-bottom: 5px;
        }
        .user-email {
            color: var(--gray-400);
        }
        .orders-section {
            padding: 40px 0;
        }
        .section-title {
            color: var(--white);
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .order-card {
            background: var(--dark-lighter);
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .order-info h3 {
            color: var(--white);
            font-size: 18px;
            margin-bottom: 5px;
        }
        .order-meta {
            color: var(--gray-400);
            font-size: 14px;
        }
        .order-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .status-approved { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .status-rejected { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        
        .btn-download {
            background: var(--primary);
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-download:hover {
            background: var(--primary-dark);
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
                <a href="index.php">Loja</a>
                <a href="logout.php" class="btn btn-outline">Sair</a>
            </nav>
        </div>
    </header>

    <div class="account-header">
        <div class="container account-info">
            <div class="user-welcome">
                <h1>Olá, <?= htmlspecialchars($_SESSION['customer_name']) ?></h1>
                <div class="user-email"><?= htmlspecialchars($_SESSION['customer_email']) ?></div>
            </div>
        </div>
    </div>

    <div class="container orders-section">
        <h2 class="section-title">Minhas Licenças e Assinaturas</h2>
        <?php if (empty($licenses)): ?>
            <p style="color: var(--gray-400);">Nenhuma licença ativa.</p>
        <?php else: ?>
            <?php foreach ($licenses as $license): ?>
                <div class="order-card">
                    <div class="order-info">
                        <h3><?= htmlspecialchars($license['product_name']) ?></h3>
                        <div class="order-meta">
                            Chave: <code style="background: rgba(255,255,255,0.1); padding: 2px 5px; border-radius: 4px; color: var(--secondary);"><?= htmlspecialchars($license['license_key']) ?></code>
                            <br>
                            Vencimento: <?= $license['expires_at'] ? date('d/m/Y', strtotime($license['expires_at'])) : 'Vitalício' ?>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <span class="order-status status-<?= $license['status'] ?>">
                            <?= $license['status'] == 'active' ? 'Ativo' : 'Bloqueado/Expirado' ?>
                        </span>
                        <?php if($license['status'] == 'active'): ?>
                            <div style="margin-top: 10px;">
                                <?php if ($license['delivery_method'] == 'hosted'): ?>
                                    <div style="background: rgba(20, 184, 166, 0.1); padding: 10px; border-radius: 4px; margin-bottom: 10px; text-align: left;">
                                        <div style="color: var(--secondary); font-size: 13px; font-weight: 600; margin-bottom: 5px;">
                                            <i class="fas fa-sync-alt"></i> Assinatura Ativa
                                        </div>
                                        <div style="color: var(--gray-300); font-size: 12px;">
                                            Próximo Pagamento: <strong style="color: var(--white);"><?= date('d/m/Y', strtotime($license['expires_at'])) ?></strong>
                                            <br>
                                            Valor: <strong style="color: var(--white);">R$ <?= number_format($license['monthly_price'], 2, ',', '.') ?></strong>
                                        </div>
                                        <a href="checkout.php?id=<?= $license['product_id'] ?>&renew=<?= $license['id'] ?>" class="btn btn-outline" style="width: 100%; margin-top: 8px; padding: 6px; font-size: 12px; text-align: center; border-color: var(--secondary); color: var(--secondary);">
                                            <i class="fas fa-credit-card"></i> Pagar Mensalidade
                                        </a>
                                    </div>

                                    <?php if (!empty($license['domain'])): ?>
                                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                            <?php
                                            // Detect system type from product name to show appropriate buttons
                                            $isDental = (stripos($license['product_name'], 'Dental') !== false || stripos($license['product_name'], 'Dentista') !== false);
                                            $isDelivery = stripos($license['product_name'], 'Delivery') !== false;
                                            
                                            if ($isDental) {
                                                // RevexaDental - Show Dashboard button
                                                echo '<a href="' . htmlspecialchars($license['domain']) . '/dashboard.php" target="_blank" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px; text-decoration: none; display: inline-block;">';
                                                echo '<i class="fas fa-tooth"></i> Acessar Sistema';
                                                echo '</a>';
                                            } elseif ($isDelivery) {
                                                // NeoDelivery - Show Store + Admin buttons
                                                echo '<a href="' . htmlspecialchars($license['domain']) . '" target="_blank" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px; text-decoration: none; display: inline-block;">';
                                                echo '<i class="fas fa-store"></i> Acessar Minha Loja';
                                                echo '</a>';
                                                echo '<a href="' . htmlspecialchars($license['domain']) . '/admin" target="_blank" class="btn btn-outline" style="padding: 8px 16px; font-size: 13px; text-decoration: none; display: inline-block; border-color: var(--white); color: var(--white);">';
                                                echo '<i class="fas fa-cog"></i> Acessar Admin';
                                                echo '</a>';
                                            } else {
                                                // Generic system
                                                echo '<a href="' . htmlspecialchars($license['domain']) . '" target="_blank" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px; text-decoration: none; display: inline-block;">';
                                                echo '<i class="fas fa-external-link-alt"></i> Acessar Sistema';
                                                echo '</a>';
                                            }
                                            ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: var(--gray-400); font-size: 13px;">
                                            <i class="fas fa-clock"></i> Aguardando criação do sistema...
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                        <a href="download.php?license_id=<?= $license['id'] ?>" class="btn btn-outline" style="padding: 8px 16px; font-size: 13px; text-decoration: none; display: inline-block; border-color: var(--primary); color: var(--primary);">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <h2 class="section-title" style="margin-top: 40px;">Meus Pedidos</h2>
        
        <?php if (empty($orders)): ?>
            <div style="text-align: center; padding: 40px; color: var(--gray-400);">
                <i class="fas fa-shopping-bag" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                <p>Você ainda não fez nenhum pedido.</p>
                <a href="index.php" class="btn btn-primary" style="margin-top: 20px; display: inline-block;">Ir para a Loja</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-info">
                        <h3><?= htmlspecialchars($order['product_name']) ?></h3>
                        <div class="order-meta">
                            Pedido #<?= $order['id'] ?> • <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="margin-bottom: 10px;">
                            <span class="order-status status-<?= $order['status'] ?>">
                                <?= $order['status'] == 'approved' ? 'Aprovado' : ($order['status'] == 'pending' ? 'Pendente' : 'Cancelado') ?>
                            </span>
                        </div>
                        <div style="font-weight: bold; color: var(--white); margin-bottom: 10px;">
                            R$ <?= number_format($order['amount'], 2, ',', '.') ?>
                        </div>
                        
                        <?php if ($order['status'] == 'approved'): ?>
                            <?php if ($order['delivery_method'] == 'file'): ?>
                                <a href="#" class="btn-download"><i class="fas fa-download"></i> Download</a>
                            <?php else: ?>
                                <span style="color: var(--primary); font-size: 14px;"><i class="fas fa-check"></i> Serviço Ativo</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>