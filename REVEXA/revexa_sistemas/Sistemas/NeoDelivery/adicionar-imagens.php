<?php
require_once __DIR__ . '/config/config.php';
requireAdmin();

$db = Database::getInstance()->getConnection();

// Processar upload e vincular
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_image') {
        $type = $_POST['type']; // 'product' ou 'category'
        $id = (int)$_POST['id'];
        $imagePath = $_POST['image_path'];
        
        if ($type === 'product') {
            $stmt = $db->prepare("UPDATE products SET image = :image WHERE id = :id");
        } else {
            $stmt = $db->prepare("UPDATE categories SET image = :image WHERE id = :id");
        }
        
        $stmt->bindValue(':image', $imagePath, SQLITE3_TEXT);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Imagem vinculada com sucesso!']);
        exit;
    }
}

// Buscar produtos sem imagem
$productsSemImagemQuery = $db->query("SELECT * FROM products WHERE image IS NULL OR image = '' ORDER BY id DESC");
$productsSemImagem = [];
while ($row = $productsSemImagemQuery->fetchArray(SQLITE3_ASSOC)) {
    $productsSemImagem[] = $row;
}

// Buscar categorias sem imagem
$categoriesSemImagemQuery = $db->query("SELECT * FROM categories WHERE image IS NULL OR image = '' ORDER BY id DESC");
$categoriesSemImagem = [];
while ($row = $categoriesSemImagemQuery->fetchArray(SQLITE3_ASSOC)) {
    $categoriesSemImagem[] = $row;
}

// Buscar imagens disponíveis no servidor
$imagensDisponiveis = [
    'products' => [],
    'categories' => []
];

foreach (['products', 'categories'] as $dir) {
    $fullDir = __DIR__ . '/uploads/' . $dir . '/';
    if (is_dir($fullDir)) {
        $files = array_diff(scandir($fullDir), ['.', '..']);
        foreach ($files as $file) {
            if (is_file($fullDir . $file) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
                $imagensDisponiveis[$dir][] = 'uploads/' . $dir . '/' . $file;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Imagens - NeoDelivery</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
    <style>
        body { background: #f5f6fa; }
        .container { max-width: 1400px; margin: 30px auto; padding: 20px; }
        .card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin: 20px 0; }
        h1 { color: #6c5ce7; }
        h2 { color: #2d3436; border-bottom: 3px solid #6c5ce7; padding-bottom: 10px; }
        .item-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
        .item-card { border: 2px solid #dfe6e9; padding: 20px; border-radius: 10px; background: white; }
        .item-card h3 { margin: 0 0 15px 0; color: #2d3436; }
        .image-selector { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px; }
        .image-option { cursor: pointer; border: 3px solid transparent; border-radius: 8px; transition: all 0.3s; }
        .image-option:hover { border-color: #6c5ce7; transform: scale(1.05); }
        .image-option.selected { border-color: #00b894; box-shadow: 0 0 10px rgba(0,184,148,0.5); }
        .image-option img { width: 100px; height: 100px; object-fit: cover; border-radius: 5px; display: block; }
        .btn-vincular { background: #00b894; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; margin-top: 10px; }
        .btn-vincular:hover { background: #00a383; }
        .btn-vincular:disabled { background: #b2bec3; cursor: not-allowed; }
        .success-msg { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 20px 0; border: 2px solid #00b894; }
        .alert-info { background: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; margin: 20px 0; border: 2px solid #ffc107; }
        .nav-buttons { text-align: center; margin: 20px 0; }
        .nav-buttons a { display: inline-block; background: #6c5ce7; color: white; padding: 12px 25px; border-radius: 8px; text-decoration: none; margin: 5px; }
        .nav-buttons a:hover { background: #5548c8; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🖼️ Adicionar Imagens aos Produtos e Categorias</h1>
        
        <?php if (empty($productsSemImagem) && empty($categoriesSemImagem)): ?>
            <div class="success-msg">
                <h2>✓ Tudo OK!</h2>
                <p>Todos os produtos e categorias já têm imagens cadastradas.</p>
            </div>
        <?php endif; ?>
        
        <!-- PRODUTOS SEM IMAGEM -->
        <?php if (!empty($productsSemImagem)): ?>
        <div class="card">
            <h2>📦 Produtos sem Imagem (<?php echo count($productsSemImagem); ?>)</h2>
            
            <?php if (empty($imagensDisponiveis['products'])): ?>
                <div class="alert-info">
                    <strong>⚠️ Atenção:</strong> Nenhuma imagem encontrada em <code>uploads/products/</code>
                    <p>Você precisa primeiro fazer upload de imagens na página de produtos.</p>
                </div>
            <?php else: ?>
                <div class="item-grid">
                    <?php foreach ($productsSemImagem as $produto): ?>
                    <div class="item-card" data-type="product" data-id="<?php echo $produto['id']; ?>">
                        <h3><?php echo htmlspecialchars($produto['name']); ?></h3>
                        <p><strong>ID:</strong> <?php echo $produto['id']; ?></p>
                        <p><strong>Preço:</strong> <?php echo formatMoney($produto['price']); ?></p>
                        
                        <p><strong>Escolha uma imagem:</strong></p>
                        <div class="image-selector">
                            <?php foreach ($imagensDisponiveis['products'] as $img): ?>
                            <div class="image-option" data-path="<?php echo $img; ?>">
                                <img src="<?php echo BASE_URL . $img; ?>" alt="Imagem">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button class="btn-vincular" disabled onclick="vincularImagem(this, 'product', <?php echo $produto['id']; ?>)">
                            Vincular Imagem
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- CATEGORIAS SEM IMAGEM -->
        <?php if (!empty($categoriesSemImagem)): ?>
        <div class="card">
            <h2>📁 Categorias sem Imagem (<?php echo count($categoriesSemImagem); ?>)</h2>
            
            <?php if (empty($imagensDisponiveis['categories'])): ?>
                <div class="alert-info">
                    <strong>⚠️ Atenção:</strong> Nenhuma imagem encontrada em <code>uploads/categories/</code>
                    <p>Você precisa primeiro fazer upload de imagens na página de categorias.</p>
                </div>
            <?php else: ?>
                <div class="item-grid">
                    <?php foreach ($categoriesSemImagem as $categoria): ?>
                    <div class="item-card" data-type="category" data-id="<?php echo $categoria['id']; ?>">
                        <h3><?php echo htmlspecialchars($categoria['name']); ?></h3>
                        <p><strong>ID:</strong> <?php echo $categoria['id']; ?></p>
                        <p><strong>Slug:</strong> <?php echo $categoria['slug']; ?></p>
                        
                        <p><strong>Escolha uma imagem:</strong></p>
                        <div class="image-selector">
                            <?php foreach ($imagensDisponiveis['categories'] as $img): ?>
                            <div class="image-option" data-path="<?php echo $img; ?>">
                                <img src="<?php echo BASE_URL . $img; ?>" alt="Imagem">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button class="btn-vincular" disabled onclick="vincularImagem(this, 'category', <?php echo $categoria['id']; ?>)">
                            Vincular Imagem
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="nav-buttons">
            <a href="<?php echo BASE_URL; ?>admin/products.php">⚙️ Gerenciar Produtos</a>
            <a href="<?php echo BASE_URL; ?>admin/categories.php">⚙️ Gerenciar Categorias</a>
            <a href="<?php echo BASE_URL; ?>debug-imagens.php">🔍 Debug de Imagens</a>
            <a href="<?php echo BASE_URL; ?>cardapio.php">🌐 Ver Cardápio</a>
        </div>
    </div>
    
    <script>
        // Selecionar imagem
        document.querySelectorAll('.image-option').forEach(option => {
            option.addEventListener('click', function() {
                const card = this.closest('.item-card');
                const selector = card.querySelector('.image-selector');
                
                // Remover seleção anterior
                selector.querySelectorAll('.image-option').forEach(opt => opt.classList.remove('selected'));
                
                // Adicionar seleção
                this.classList.add('selected');
                
                // Habilitar botão
                card.querySelector('.btn-vincular').disabled = false;
            });
        });
        
        // Vincular imagem
        function vincularImagem(btn, type, id) {
            const card = btn.closest('.item-card');
            const selected = card.querySelector('.image-option.selected');
            
            if (!selected) {
                alert('Selecione uma imagem primeiro!');
                return;
            }
            
            const imagePath = selected.dataset.path;
            
            btn.disabled = true;
            btn.textContent = 'Vinculando...';
            
            const formData = new FormData();
            formData.append('action', 'add_image');
            formData.append('type', type);
            formData.append('id', id);
            formData.append('image_path', imagePath);
            
            fetch('<?php echo BASE_URL; ?>adicionar-imagens.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    card.style.background = '#d4edda';
                    card.innerHTML = '<h3 style="color: #00b894;">✓ Imagem vinculada com sucesso!</h3><p>Atualizando página...</p>';
                    setTimeout(() => location.reload(), 1500);
                } else {
                    alert('Erro: ' + data.message);
                    btn.disabled = false;
                    btn.textContent = 'Vincular Imagem';
                }
            })
            .catch(error => {
                alert('Erro: ' + error.message);
                btn.disabled = false;
                btn.textContent = 'Vincular Imagem';
            });
        }
    </script>
</body>
</html>
