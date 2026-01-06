<aside class="sidebar">
    <a href="dashboard.php" class="sidebar-brand">
        <i class="fas fa-cube"></i> Admin Panel
    </a>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><i class="fas fa-chart-line"></i> Dashboard</a></li>
        <li><a href="products.php" class="<?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>"><i class="fas fa-box"></i> Produtos</a></li>
        <li><a href="orders.php" class="<?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
        <li><a href="customers.php" class="<?= basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : '' ?>"><i class="fas fa-users"></i> Clientes</a></li>
        <li><a href="licenses.php" class="<?= basename($_SERVER['PHP_SELF']) == 'licenses.php' ? 'active' : '' ?>"><i class="fas fa-key"></i> Licenças</a></li>
        <li><a href="create_store.php" class="<?= basename($_SERVER['PHP_SELF']) == 'create_store.php' ? 'active' : '' ?>"><i class="fas fa-server"></i> Criar Loja (SaaS)</a></li>
        <li><a href="settings.php" class="<?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>"><i class="fas fa-cog"></i> Configurações</a></li>
        <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> Ver Loja</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
    </ul>
</aside>