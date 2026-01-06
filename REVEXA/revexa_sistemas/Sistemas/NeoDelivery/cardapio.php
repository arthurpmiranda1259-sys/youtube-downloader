<?php
require_once __DIR__ . '/config/config.php';

$db = Database::getInstance()->getConnection();

// Buscar categoria selecionada
$categorySlug = isset($_GET['categoria']) ? $_GET['categoria'] : null;
$selectedCategory = null;

if ($categorySlug) {
    $stmt = $db->prepare("SELECT * FROM categories WHERE slug = :slug AND active = 1");
    $stmt->bindValue(':slug', $categorySlug, SQLITE3_TEXT);
    $result = $stmt->execute();
    $selectedCategory = $result->fetchArray(SQLITE3_ASSOC);
}

// Buscar todas as categorias
$categoriesQuery = $db->query("SELECT * FROM categories WHERE active = 1 ORDER BY display_order, name");
$categories = [];
while ($row = $categoriesQuery->fetchArray(SQLITE3_ASSOC)) {
    $categories[] = $row;
}

// Buscar produtos
if ($selectedCategory) {
    $stmt = $db->prepare("SELECT * FROM products WHERE category_id = :category_id AND active = 1 ORDER BY display_order, name");
    $stmt->bindValue(':category_id', $selectedCategory['id'], SQLITE3_INTEGER);
    $result = $stmt->execute();
} else {
    $result = $db->query("SELECT * FROM products WHERE active = 1 ORDER BY display_order, name");
}

$products = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $products[] = $row;
}

$businessName = getSetting('business_name', 'X Delivery');
$businessLogo = getSetting('business_logo', '');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cardápio - <?php echo htmlspecialchars($businessName); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <button class="menu-toggle">☰</button>
                <a href="<?php echo BASE_URL; ?>" class="logo">
                    <?php if ($businessLogo): ?>
                        <img src="<?php echo BASE_URL . $businessLogo; ?>" alt="<?php echo htmlspecialchars($businessName); ?>">
                    <?php else: ?>
                        <?php echo htmlspecialchars($businessName); ?>
                    <?php endif; ?>
                </a>
                <a href="#" class="cart-icon">
                    🛒
                    <span class="cart-badge" style="display: none;">0</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Mobile Menu -->
    <div class="mobile-menu">
        <h3 style="color: white; margin-bottom: 20px;">Menu</h3>
        <nav>
            <a href="<?php echo BASE_URL; ?>">Início</a>
            <a href="<?php echo BASE_URL; ?>cardapio.php">Cardápio</a>
            <a href="<?php echo BASE_URL; ?>admin/">Área Admin</a>
        </nav>
    </div>

    <!-- Category Filters -->
    <section class="categories-section" style="padding: 20px;">
        <div class="container">
            <div style="display: flex; gap: 10px; overflow-x: auto; padding: 10px 0;">
                <a href="<?php echo BASE_URL; ?>cardapio.php" 
                   class="btn <?php echo !$categorySlug ? 'btn-primary' : 'btn-outline'; ?>" 
                   style="white-space: nowrap;">
                    Todos
                </a>
                <?php foreach ($categories as $category): ?>
                    <a href="<?php echo BASE_URL; ?>cardapio.php?categoria=<?php echo $category['slug']; ?>" 
                       class="btn <?php echo ($categorySlug === $category['slug']) ? 'btn-primary' : 'btn-outline'; ?>" 
                       style="white-space: nowrap;">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Products -->
    <section class="products-section">
        <div class="container">
            <?php if ($selectedCategory): ?>
                <h2 class="section-title"><?php echo htmlspecialchars($selectedCategory['name']); ?></h2>
            <?php else: ?>
                <h2 class="section-title">TODOS OS PRODUTOS</h2>
            <?php endif; ?>
            
            <?php if (empty($products)): ?>
                <p style="text-align: center; color: #636e72; padding: 40px 0;">Nenhum produto encontrado nesta categoria.</p>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <img src="<?php echo $product['image'] ? BASE_URL . $product['image'] : BASE_URL . 'assets/images/placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="product-image">
                            <div class="product-info">
                                <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <?php if ($product['description']): ?>
                                    <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                                <?php endif; ?>
                                <div class="product-footer">
                                    <span class="product-price"><?php echo formatMoney($product['price']); ?></span>
                                    <button class="btn-add-cart" onclick='addToCart(<?php echo json_encode([
                                        "id" => $product["id"],
                                        "name" => $product["name"],
                                        "price" => $product["price"],
                                        "image" => $product["image"]
                                    ]); ?>)'>
                                        Comprar
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Cart Sidebar -->
    <div class="cart-sidebar">
        <div class="cart-header">
            <h2>CARRINHO</h2>
            <button class="cart-close" onclick="toggleCart()">×</button>
        </div>
        <div class="cart-items">
            <p style="text-align: center; color: #636e72; padding: 40px 0;">Seu carrinho está vazio</p>
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <span>Subtotal:</span>
                <span class="cart-total-value">R$ 0,00</span>
            </div>
            <a href="<?php echo BASE_URL; ?>checkout.php" class="btn btn-primary btn-block">FINALIZAR COMPRA</a>
            <button class="btn btn-outline btn-block" style="margin-top: 10px;" onclick="toggleCart(event)">Continuar Comprando</button>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($businessName); ?>. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
</body>
</html>
