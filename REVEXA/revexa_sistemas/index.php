<?php
require_once __DIR__ . '/includes/Database.php';
$db = StoreDatabase::getInstance();
$products = $db->fetchAll("SELECT * FROM products WHERE active = 1 ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RevexaSistemas | Loja de Soluções Digitais</title>
    <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <a href="index.php" class="logo">
                <i class="fas fa-cube"></i> RevexaSistemas
            </a>
            <nav class="nav-links">
                <a href="../index.php">Voltar ao Site Principal</a>
                <a href="login.php" class="btn btn-outline">Área do Cliente</a>
            </nav>
        </div>
    </header>

    <section class="store-hero">
        <div class="container">
            <h1>Soluções Prontas para seu Negócio</h1>
            <p>Adquira sistemas, sites e aplicativos de alta performance com código fonte incluso e suporte especializado.</p>
        </div>
    </section>

    <section class="container">
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                </div>
                <div class="product-info">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span class="product-type"><?= htmlspecialchars($product['type']) ?></span>
                        <?php if(isset($product['delivery_method']) && $product['delivery_method'] == 'hosted'): ?>
                            <span style="font-size: 11px; background: rgba(245, 158, 11, 0.1); color: var(--secondary); padding: 2px 6px; border-radius: 4px; border: 1px solid rgba(245, 158, 11, 0.2);">
                                <i class="fas fa-cloud"></i> Hospedado
                            </span>
                        <?php else: ?>
                            <span style="font-size: 11px; background: rgba(255, 255, 255, 0.1); color: var(--gray-400); padding: 2px 6px; border-radius: 4px;">
                                <i class="fas fa-download"></i> Download
                            </span>
                        <?php endif; ?>
                    </div>
                    <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="product-desc"><?= htmlspecialchars($product['description']) ?></p>
                    <div class="product-footer">
                        <span class="product-price">R$ <?= number_format($product['price'], 2, ',', '.') ?></span>
                        <button class="btn btn-primary" onclick="window.location.href='checkout.php?id=<?= $product['id'] ?>'">
                            <i class="fas fa-shopping-cart"></i> Comprar
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <script>
        // Removed placeholder function
    </script>
</body>
</html>
