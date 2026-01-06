<?php
require_once __DIR__ . '/config/config.php';

$db = Database::getInstance()->getConnection();

// Buscar categorias
$categoriesQuery = $db->query("SELECT * FROM categories WHERE active = 1 ORDER BY display_order, name");
$categories = [];
while ($row = $categoriesQuery->fetchArray(SQLITE3_ASSOC)) {
    $categories[] = $row;
}

// Buscar produtos em destaque
$featuredQuery = $db->query("SELECT p.*, c.name as category_name FROM products p 
                              LEFT JOIN categories c ON p.category_id = c.id 
                              WHERE p.active = 1 AND p.featured = 1 
                              ORDER BY p.display_order 
                              LIMIT 6");
$featuredProducts = [];
while ($row = $featuredQuery->fetchArray(SQLITE3_ASSOC)) {
    $featuredProducts[] = $row;
}

// Buscar banners ativos para o carrossel
$bannersQuery = $db->query("SELECT * FROM banners WHERE active = 1 ORDER BY display_order, id");
$banners = [];
while ($row = $bannersQuery->fetchArray(SQLITE3_ASSOC)) {
    $banners[] = $row;
}

// DEBUG: Descomentar a linha abaixo para ver quantos banners foram encontrados
// echo "<!-- DEBUG: " . count($banners) . " banners ativos encontrados -->";

$businessName = getSetting('business_name', 'X Delivery');
$businessPhone = getSetting('business_phone', '');
$businessLogo = getSetting('business_logo', '');
$isOpen = isBusinessOpen();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($businessName); ?> - Delivery</title>
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
        </nav>
    </div>

    <!-- Hero Carousel -->
    <?php if (!empty($banners)): ?>
    <section class="hero-carousel">
        <div class="carousel-container">
            <?php foreach ($banners as $index => $banner): ?>
                <div class="carousel-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                    <?php if ($banner['link']): ?>
                        <a href="<?php echo htmlspecialchars($banner['link']); ?>">
                            <img src="<?php echo BASE_URL . htmlspecialchars($banner['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($banner['title']); ?>">
                        </a>
                    <?php else: ?>
                        <img src="<?php echo BASE_URL . htmlspecialchars($banner['image']); ?>" 
                             alt="<?php echo htmlspecialchars($banner['title']); ?>">
                    <?php endif; ?>
                    <?php if ($banner['title'] || $banner['description']): ?>
                        <div class="carousel-caption">
                            <?php if ($banner['title']): ?>
                                <h2><?php echo htmlspecialchars($banner['title']); ?></h2>
                            <?php endif; ?>
                            <?php if ($banner['description']): ?>
                                <p><?php echo htmlspecialchars($banner['description']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($banners) > 1): ?>
            <button class="carousel-prev" onclick="changeSlide(-1)">❮</button>
            <button class="carousel-next" onclick="changeSlide(1)">❯</button>
            
            <div class="carousel-dots">
                <?php foreach ($banners as $index => $banner): ?>
                    <span class="dot <?php echo $index === 0 ? 'active' : ''; ?>" onclick="currentSlide(<?php echo $index; ?>)"></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <?php else: ?>
    <!-- Hero Banner Fallback -->
    <section class="hero-banner">
        <div class="hero-content">
            <h1>DELIVERY<br>O MELHOR DA CIDADE</h1>
            <p>Peça agora mesmo!</p>
            <span class="cta-badge">
                <?php if ($isOpen): ?>
                    ABERTO AGORA
                <?php else: ?>
                    FECHADO NO MOMENTO
                <?php endif; ?>
            </span>
        </div>
    </section>
    <?php endif; ?>

    <!-- Categories -->
    <section class="categories-section">
        <div class="container">
            <h2 class="section-title">NOSSO CARDAPIO</h2>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <a href="<?php echo BASE_URL; ?>cardapio.php?categoria=<?php echo $category['slug']; ?>" class="category-card">
                        <?php if ($category['image']): ?>
                            <img src="<?php echo BASE_URL . $category['image']; ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                        <?php else: ?>
                            <img src="<?php echo BASE_URL; ?>assets/images/placeholder-category.jpg" alt="<?php echo htmlspecialchars($category['name']); ?>">
                        <?php endif; ?>
                        <div class="category-overlay">
                            <div class="category-name"><?php echo htmlspecialchars($category['name']); ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <?php if (!empty($featuredProducts)): ?>
    <section class="products-section">
        <div class="container">
            <h2 class="section-title">MAIS VENDIDOS</h2>
            <div class="products-grid">
                <?php foreach ($featuredProducts as $product): ?>
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
        </div>
    </section>
    <?php endif; ?>

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
            <a href="<?php echo BASE_URL; ?>cardapio.php" class="btn btn-outline btn-block" style="margin-top: 10px;">Continuar Comprando</a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($businessName); ?>. Todos os direitos reservados.</p>
            <?php if ($businessPhone): ?>
                <p>Telefone: <?php echo htmlspecialchars($businessPhone); ?></p>
            <?php endif; ?>
        </div>
    </footer>

    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
</body>
</html>
