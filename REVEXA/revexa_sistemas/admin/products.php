<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../includes/Database.php';
$db = StoreDatabase::getInstance();

// Handle Product Deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM products WHERE id = ?", [$id]);
    header('Location: products.php');
    exit;
}

// Handle Product Addition/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $type = $_POST['type'];
    $delivery_method = $_POST['delivery_method'];
    $delivery_options = $_POST['delivery_options'];
    $billing_cycle = $_POST['billing_cycle'];
    $image_url = $_POST['image_url'];
    $monthly_price = isset($_POST['monthly_price']) ? $_POST['monthly_price'] : 0;
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $db->query("UPDATE products SET name=?, description=?, price=?, monthly_price=?, type=?, delivery_method=?, delivery_options=?, billing_cycle=?, image_url=? WHERE id=?", 
            [$name, $description, $price, $monthly_price, $type, $delivery_method, $delivery_options, $billing_cycle, $image_url, $_POST['id']]);
    } else {
        $db->query("INSERT INTO products (name, description, price, monthly_price, type, delivery_method, delivery_options, billing_cycle, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
            [$name, $description, $price, $monthly_price, $type, $delivery_method, $delivery_options, $billing_cycle, $image_url]);
    }
    header('Location: products.php');
    exit;
}

$products = $db->fetchAll("SELECT * FROM products ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos | RevexaSistemas</title>
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
                <h1 style="color: var(--white);">Gerenciar Produtos</h1>
                <button onclick="openModal()" class="btn btn-primary"><i class="fas fa-plus"></i> Novo Produto</button>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imagem</th>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Entrega</th>
                        <th>Preço</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>#<?= $product['id'] ?></td>
                        <td><img src="<?= htmlspecialchars($product['image_url']) ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;"></td>
                        <td style="color: var(--white); font-weight: 500;"><?= htmlspecialchars($product['name']) ?></td>
                        <td><span style="background: rgba(20, 184, 166, 0.1); color: var(--primary); padding: 4px 8px; border-radius: 4px; font-size: 12px;"><?= htmlspecialchars($product['type']) ?></span></td>
                        <td>
                            <?php if($product['delivery_method'] == 'hosted'): ?>
                                <span style="background: rgba(245, 158, 11, 0.1); color: var(--secondary); padding: 4px 8px; border-radius: 4px; font-size: 12px;">Hospedado</span>
                            <?php else: ?>
                                <span style="background: rgba(255, 255, 255, 0.1); color: var(--gray-300); padding: 4px 8px; border-radius: 4px; font-size: 12px;">Arquivo</span>
                            <?php endif; ?>
                        </td>
                        <td style="color: var(--white);">R$ <?= number_format($product['price'], 2, ',', '.') ?></td>
                        <td>
                            <button onclick='editProduct(<?= json_encode($product) ?>)' class="btn btn-outline" style="padding: 6px 12px;"><i class="fas fa-edit"></i></button>
                            <a href="?delete=<?= $product['id'] ?>" onclick="return confirm('Tem certeza?')" class="btn btn-outline" style="padding: 6px 12px; border-color: var(--danger); color: var(--danger);"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>

    <!-- Modal -->
    <div id="productModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: var(--dark-lighter); padding: 30px; border-radius: var(--radius); width: 500px; max-width: 90%;">
            <h2 style="color: var(--white); margin-bottom: 20px;" id="modalTitle">Novo Produto</h2>
            <form method="POST">
                <input type="hidden" name="id" id="productId">
                <div class="form-group">
                    <label>Nome do Produto</label>
                    <input type="text" name="name" id="productName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Tipo</label>
                    <select name="type" id="productType" class="form-control">
                        <option value="system">Sistema</option>
                        <option value="site">Site</option>
                        <option value="app">Aplicativo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Método de Entrega (Padrão)</label>
                    <select name="delivery_method" id="productDelivery" class="form-control">
                        <option value="file">Arquivo (Download)</option>
                        <option value="hosted">Hospedado (SaaS/Interno)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Opções Disponíveis</label>
                    <select name="delivery_options" id="productDeliveryOptions" class="form-control">
                        <option value="file">Apenas Download</option>
                        <option value="hosted">Apenas Hospedado</option>
                        <option value="both">Ambos (Cliente Escolhe)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ciclo de Cobrança</label>
                    <select name="billing_cycle" id="productBilling" class="form-control">
                        <option value="one_time">Pagamento Único</option>
                        <option value="monthly">Mensal (Assinatura)</option>
                        <option value="yearly">Anual (Assinatura)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Preço Base / Download (R$)</label>
                    <input type="number" step="0.01" name="price" id="productPrice" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Preço Mensal / SaaS (R$)</label>
                    <input type="number" step="0.01" name="monthly_price" id="productMonthlyPrice" class="form-control" placeholder="0.00">
                    <small style="color: var(--gray-400);">Opcional. Usado se o cliente escolher "Hospedado".</small>
                </div>
                <div class="form-group">
                    <label>URL da Imagem</label>
                    <input type="url" name="image_url" id="productImage" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Descrição</label>
                    <textarea name="description" id="productDesc" class="form-control" rows="3"></textarea>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="closeModal()" class="btn btn-outline">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('productModal').style.display = 'flex';
            document.getElementById('modalTitle').innerText = 'Novo Produto';
            document.getElementById('productId').value = '';
            document.getElementById('productName').value = '';
            document.getElementById('productPrice').value = '';
            document.getElementById('productMonthlyPrice').value = '';
            document.getElementById('productImage').value = '';
            document.getElementById('productDesc').value = '';
            document.getElementById('productDelivery').value = 'file';
            document.getElementById('productDeliveryOptions').value = 'file';
            document.getElementById('productBilling').value = 'one_time';
        }

        function closeModal() {
            document.getElementById('productModal').style.display = 'none';
        }

        function editProduct(product) {
            openModal();
            document.getElementById('modalTitle').innerText = 'Editar Produto';
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productType').value = product.type;
            document.getElementById('productDelivery').value = product.delivery_method || 'file';
            document.getElementById('productDeliveryOptions').value = product.delivery_options || 'file';
            document.getElementById('productBilling').value = product.billing_cycle || 'one_time';
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productMonthlyPrice').value = product.monthly_price || '';
            document.getElementById('productImage').value = product.image_url;
            document.getElementById('productDesc').value = product.description;
        }
        
        document.getElementById('productModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>
