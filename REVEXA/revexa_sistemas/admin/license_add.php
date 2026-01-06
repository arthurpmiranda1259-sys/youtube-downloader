<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../includes/Database.php';
$db = StoreDatabase::getInstance();

$products = $db->fetchAll("SELECT * FROM products WHERE active = 1");
$prefill_email = isset($_GET['email']) ? $_GET['email'] : '';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)$_POST['product_id'];
    $email = $_POST['email'];
    $expires_in_days = (int)$_POST['expires_in_days']; // 0 for lifetime
    
    if (empty($email) || empty($product_id)) {
        $error = "Produto e Email são obrigatórios.";
    } else {
        try {
            $license_key = md5(uniqid($email . $product_id, true));
            
            $expires_at = null;
            if ($expires_in_days > 0) {
                $expires_at = date('Y-m-d H:i:s', strtotime("+$expires_in_days days"));
            }
            
            // Fetch product to get delivery method and name
            $product = $db->fetch("SELECT * FROM products WHERE id = ?", [$product_id]);
            $delivery_method = $product['delivery_method'] ?? 'file';
            $created_at = date('Y-m-d H:i:s');
            
            $db->query("INSERT INTO licenses (product_id, customer_email, license_key, status, expires_at, delivery_method, created_at) VALUES (?, ?, ?, 'active', ?, ?, ?)", 
                [$product_id, $email, $license_key, $expires_at, $delivery_method, $created_at]);
                
            // If hosted, trigger provisioning immediately
            if ($delivery_method == 'hosted') {
                require_once __DIR__ . '/../includes/StoreProvisioner.php';
                require_once __DIR__ . '/../config/config.php';
                
                try {
                    $slug = 'store-' . substr($license_key, 0, 8);
                    
                    // Detect system type
                    $sourceName = 'NeoDelivery'; // Default
                    if (stripos($product['name'], 'Dental') !== false || stripos($product['name'], 'Dentista') !== false) {
                        $sourceName = 'RevexaDental';
                    } elseif (stripos($product['name'], 'Delivery') !== false) {
                        $sourceName = 'NeoDelivery';
                    }
                    
                    $sourcePath = __DIR__ . '/../Sistemas/' . $sourceName;
                    $destRoot = __DIR__ . '/../lojas';
                    
                    if (is_dir($sourcePath)) {
                        StoreProvisioner::createInstance($slug, $sourcePath, $destRoot);
                        $storeUrl = SITE_URL . '/lojas/' . $slug;
                        
                        // Update license with domain
                        $db->query("UPDATE licenses SET domain = ? WHERE license_key = ?", [$storeUrl, $license_key]);
                    }
                } catch (Exception $e) {
                    // Log error but don't stop redirect, user will see "Aguardando criação"
                    error_log("Manual Provisioning Error: " . $e->getMessage());
                }
            }
                
            header('Location: licenses.php?email=' . urlencode($email));
            exit;
        } catch (Exception $e) {
            $error = "Erro ao criar licença: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Licença | RevexaSistemas</title>
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
                    <a href="licenses.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Voltar</a>
                    <h1 style="color: var(--white); margin: 0;">Nova Licença Manual</h1>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert error" style="max-width: 600px; margin: 0 auto 20px;"><?= $error ?></div>
            <?php endif; ?>

            <div class="card">
                <form method="POST">
                    <div class="form-group">
                        <label>Cliente (Email)</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($prefill_email) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Produto</label>
                        <select name="product_id" class="form-control" required>
                            <option value="">Selecione um produto...</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?> (<?= $product['type'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Validade (Dias)</label>
                        <input type="number" name="expires_in_days" class="form-control" value="30" min="0">
                        <small style="color: var(--gray-400);">Digite 0 para licença vitalícia.</small>
                    </div>
                    
                    <div style="margin-top: 20px; text-align: right;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key"></i> Gerar Licença
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
