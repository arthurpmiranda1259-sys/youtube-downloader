<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/StoreProvisioner.php';

$db = StoreDatabase::getInstance();

$message = '';
$error = '';

// Fetch Hosted Licenses that don't have a domain/path set yet (or we can overwrite)
$licenses = $db->fetchAll("
    SELECT l.*, p.name as product_name, c.name as customer_name 
    FROM licenses l 
    JOIN products p ON l.product_id = p.id 
    JOIN customers c ON l.customer_email = c.email
    WHERE p.delivery_method = 'hosted'
    ORDER BY l.created_at DESC
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $license_id = (int)$_POST['license_id'];
    $slug = trim($_POST['slug']);
    
    // Basic slug validation
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $slug));
    
    if (empty($slug)) {
        $error = "O endereço da loja (slug) é obrigatório e deve conter apenas letras, números e hífens.";
    } else {
        try {
            // 1. Get License Info
            $license = $db->fetch("SELECT * FROM licenses WHERE id = ?", [$license_id]);
            if (!$license) throw new Exception("Licença não encontrada.");
            
            // 2. Define Paths
            $sourcePath = __DIR__ . '/../Sistemas/NeoDelivery'; // Assuming NeoDelivery is the only hosted product for now
            $destRoot = __DIR__ . '/../lojas';
            
            // 3. Provision
            StoreProvisioner::createInstance($slug, $sourcePath, $destRoot);
            
            // 4. Update License with the new URL
            // We store the relative path or full URL in the 'domain' field or a new field. 
            // The 'domain' field in licenses table was originally for the customer's domain, but for SaaS we can use it for the store URL.
            
            // Construct the URL. Assuming the admin is at /admin/create_store.php, the stores are at ../lojas/slug
            // We need the absolute URL base.
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $path = dirname(dirname($_SERVER['SCRIPT_NAME'])); // Go up from /admin
            $storeUrl = $protocol . "://" . $host . $path . "/lojas/" . $slug;
            
            $db->query("UPDATE licenses SET domain = ? WHERE id = ?", [$storeUrl, $license_id]);
            
            $message = "Loja criada com sucesso! URL: " . $storeUrl;
            
            // Refresh list
            $licenses = $db->fetchAll("
                SELECT l.*, p.name as product_name, c.name as customer_name 
                FROM licenses l 
                JOIN products p ON l.product_id = p.id 
                JOIN customers c ON l.customer_email = c.email
                WHERE p.delivery_method = 'hosted'
                ORDER BY l.created_at DESC
            ");
            
        } catch (Exception $e) {
            $error = "Erro ao criar loja: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Loja (SaaS) | RevexaSistemas</title>
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
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1 style="color: var(--white);">Criar Loja (SaaS)</h1>
            </div>

            <?php if ($message): ?>
                <div class="alert success"><?= $message ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert error"><?= $error ?></div>
            <?php endif; ?>

            <div class="card">
                <h3 style="color: var(--white); margin-bottom: 15px;">Nova Instância</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Selecione a Licença/Cliente</label>
                        <select name="license_id" class="form-control" required>
                            <?php foreach ($licenses as $l): ?>
                                <option value="<?= $l['id'] ?>">
                                    #<?= $l['id'] ?> - <?= htmlspecialchars($l['customer_name']) ?> (<?= htmlspecialchars($l['product_name']) ?>)
                                    <?= $l['domain'] ? ' [Já possui loja]' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Endereço da Loja (Slug)</label>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="color: var(--gray-400);">.../lojas/</span>
                            <input type="text" name="slug" class="form-control" placeholder="ex: pizzaria-do-joao" required>
                        </div>
                        <small style="color: var(--gray-400);">Apenas letras minúsculas, números e hífens.</small>
                    </div>
                    
                    <div style="margin-top: 20px; text-align: right;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-server"></i> Criar Loja
                        </button>
                    </div>
                </form>
            </div>
            
            <h3 style="color: var(--white); margin-bottom: 15px;">Lojas Ativas</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>URL da Loja</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($licenses as $l): ?>
                        <?php if($l['domain']): ?>
                        <tr>
                            <td>#<?= $l['id'] ?></td>
                            <td style="color: var(--white);"><?= htmlspecialchars($l['customer_name']) ?></td>
                            <td>
                                <a href="<?= htmlspecialchars($l['domain']) ?>" target="_blank" style="color: var(--primary);">
                                    <?= htmlspecialchars($l['domain']) ?> <i class="fas fa-external-link-alt"></i>
                                </a>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $l['status'] ?>"><?= $l['status'] ?></span>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </main>
    </div>
</body>
</html>
