        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <h2><?php echo htmlspecialchars($businessName); ?></h2>
                <p style="font-size: 14px; opacity: 0.8;">Painel Admin</p>
                <div style="font-size: 12px; margin-top: 5px; color: rgba(255,255,255,0.7);">
                    Olá, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>
                </div>
            </div>
            <ul class="admin-menu">
                <li class="admin-menu-item">
                    <a href="<?php echo BASE_URL; ?>admin/" class="admin-menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                        <span class="admin-menu-icon">📊</span>
                        Dashboard
                    </a>
                </li>
                <li class="admin-menu-item">
                    <a href="<?php echo BASE_URL; ?>admin/orders.php" class="admin-menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
                        <span class="admin-menu-icon">📦</span>
                        Pedidos
                    </a>
                </li>
                <li class="admin-menu-item">
                    <a href="<?php echo BASE_URL; ?>admin/products.php" class="admin-menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
                        <span class="admin-menu-icon">🍔</span>
                        Produtos
                    </a>
                </li>
                <li class="admin-menu-item">
                    <a href="<?php echo BASE_URL; ?>admin/categories.php" class="admin-menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
                        <span class="admin-menu-icon">📁</span>
                        Categorias
                    </a>
                </li>
                <li class="admin-menu-item">
                    <a href="<?php echo BASE_URL; ?>admin/banners.php" class="admin-menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'banners.php' ? 'active' : ''; ?>">
                        <span class="admin-menu-icon">🖼️</span>
                        Banners
                    </a>
                </li>
                <li class="admin-menu-item">
                    <a href="<?php echo BASE_URL; ?>admin/delivery_areas.php" class="admin-menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'delivery_areas.php' ? 'active' : ''; ?>">
                        <span class="admin-menu-icon">🗺️</span>
                        Áreas de Entrega
                    </a>
                </li>
                
                <li class="admin-menu-item">
                    <a href="<?php echo BASE_URL; ?>admin/kitchen.php" class="admin-menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'kitchen.php' ? 'active' : ''; ?>">
                        <span class="admin-menu-icon">🍳</span>
                        Modo Cozinha
                    </a>
                </li>
                
                <?php if(hasRole('admin')): ?>
                <li class="admin-menu-item">
                    <a href="<?php echo BASE_URL; ?>admin/users.php" class="admin-menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                        <span class="admin-menu-icon">👥</span>
                        Usuários
                    </a>
                </li>
                <li class="admin-menu-item">
                    <a href="<?php echo BASE_URL; ?>admin/settings.php" class="admin-menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                        <span class="admin-menu-icon">⚙️</span>
                        Configurações
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="admin-menu-item">
                    <a href="<?php echo BASE_URL; ?>" class="admin-menu-link" target="_blank">
                        <span class="admin-menu-icon">🌐</span>
                        Ver Site
                    </a>
                </li>
                
                <li class="admin-menu-item" style="margin-top: auto;">
                    <a href="<?php echo BASE_URL; ?>admin/logout.php" class="admin-menu-link">
                        <span class="admin-menu-icon">🚪</span>
                        Sair
                    </a>
                </li>
            </ul>
        </aside>