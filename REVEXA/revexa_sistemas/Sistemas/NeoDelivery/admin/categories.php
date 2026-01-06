<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$db = Database::getInstance()->getConnection();

// Excluir categoria
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->exec("DELETE FROM categories WHERE id = {$id}");
    header('Location: ' . BASE_URL . 'admin/categories.php');
    exit;
}

// Salvar/Atualizar categoria
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = sanitizeInput($_POST['name']);
    $slug = sanitizeInput($_POST['slug']);
    $image = sanitizeInput($_POST['image'] ?? '');
    $active = isset($_POST['active']) ? 1 : 0;
    $displayOrder = (int)$_POST['display_order'];
    
    if ($id > 0) {
        // Atualizar
        $stmt = $db->prepare("UPDATE categories SET name = :name, slug = :slug, image = :image, active = :active, display_order = :display_order WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    } else {
        // Inserir
        $stmt = $db->prepare("INSERT INTO categories (name, slug, image, active, display_order) VALUES (:name, :slug, :image, :active, :display_order)");
    }
    
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':slug', $slug, SQLITE3_TEXT);
    $stmt->bindValue(':image', $image, SQLITE3_TEXT);
    $stmt->bindValue(':active', $active, SQLITE3_INTEGER);
    $stmt->bindValue(':display_order', $displayOrder, SQLITE3_INTEGER);
    $stmt->execute();
    
    header('Location: ' . BASE_URL . 'admin/categories.php');
    exit;
}

// Buscar categorias
$categoriesQuery = $db->query("SELECT * FROM categories ORDER BY display_order, name");
$categories = [];
while ($row = $categoriesQuery->fetchArray(SQLITE3_ASSOC)) {
    $categories[] = $row;
}

$businessName = getSetting('business_name', 'X Delivery');

// Categoria sendo editada
$editCategory = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $editCategory = $result->fetchArray(SQLITE3_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorias - <?php echo htmlspecialchars($businessName); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
    <style>
        .upload-area {
            border: 2px dashed var(--border-color);
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: var(--light-color);
        }
        .upload-area:hover {
            border-color: var(--primary-color);
            background: white;
        }
        .upload-area.dragover {
            border-color: var(--success-color);
            background: #e8f5e9;
        }
        .preview-image {
            max-width: 100%;
            max-height: 150px;
            margin-top: 15px;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="admin-content">
            <header class="admin-header">
                <h1>Gerenciar Categorias</h1>
            </header>
            
            <main class="admin-main">
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
                    <!-- Form -->
                    <div class="admin-card">
                        <h2 class="admin-card-title" style="margin-bottom: 20px;">
                            <?php echo $editCategory ? 'Editar' : 'Nova'; ?> Categoria
                        </h2>
                        
                        <form method="POST" id="categoryForm">
                            <?php if ($editCategory): ?>
                                <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label class="form-label">Nome *</label>
                                <input type="text" name="name" class="form-control" required
                                       value="<?php echo $editCategory ? htmlspecialchars($editCategory['name']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Slug (URL) *</label>
                                <input type="text" name="slug" class="form-control" required
                                       value="<?php echo $editCategory ? htmlspecialchars($editCategory['slug']) : ''; ?>">
                                <small style="color: var(--text-muted);">Ex: hamburgueres, pizzas, bebidas</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Imagem da Categoria</label>
                                <div class="upload-area" id="uploadArea">
                                    <p>Clique ou arraste uma imagem aqui</p>
                                    <p style="font-size: 14px; color: var(--text-muted);">Recomendado: 600x400px | Máx: 5MB</p>
                                    <input type="file" id="fileInput" accept="image/*" style="display: none;">
                                    <input type="hidden" name="image" id="imageInput" 
                                           value="<?php echo $editCategory ? htmlspecialchars($editCategory['image']) : ''; ?>">
                                </div>
                                <div id="previewContainer"></div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Ordem de Exibição</label>
                                <input type="number" name="display_order" class="form-control"
                                       value="<?php echo $editCategory ? $editCategory['display_order'] : '0'; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="checkbox" name="active" value="1" class="form-checkbox"
                                           <?php echo (!$editCategory || $editCategory['active']) ? 'checked' : ''; ?>>
                                    <strong style="margin-left: 10px;">Categoria Ativa</strong>
                                </label>
                            </div>
                            
                            <div style="background: #fff3cd; border: 2px solid #ffc107; padding: 15px; border-radius: 8px; margin: 20px 0;">
                                <strong style="color: #856404;">⚠️ LEMBRE-SE:</strong>
                                <p style="color: #856404; margin: 10px 0 0 0;">Após fazer upload da imagem, clique no botão abaixo para salvar a categoria!</p>
                            </div>
                            
                            <button type="submit" class="btn btn-success btn-block" style="font-size: 18px; padding: 15px;">
                                <?php echo $editCategory ? '💾 Atualizar' : '💾 SALVAR'; ?> Categoria
                            </button>
                            
                            <?php if ($editCategory): ?>
                                <a href="<?php echo BASE_URL; ?>admin/categories.php" class="btn btn-outline btn-block" style="margin-top: 10px;">
                                    Cancelar Edição
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                    
                    <!-- List -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h2 class="admin-card-title">Categorias Cadastradas (<?php echo count($categories); ?>)</h2>
                        </div>
                        
                        <?php if (empty($categories)): ?>
                            <p style="text-align: center; color: var(--text-muted); padding: 40px 0;">
                                Nenhuma categoria cadastrada ainda
                            </p>
                        <?php else: ?>
                            <div style="overflow-x: auto;">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Ordem</th>
                                            <th>Nome</th>
                                            <th>Slug</th>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $category): ?>
                                            <tr>
                                                <td><?php echo $category['display_order']; ?></td>
                                                <td><strong><?php echo htmlspecialchars($category['name']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($category['slug']); ?></td>
                                                <td>
                                                    <?php if ($category['active']): ?>
                                                        <span class="badge badge-ready">Ativa</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-cancelled">Inativa</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="?edit=<?php echo $category['id']; ?>" class="btn btn-primary btn-sm">Editar</a>
                                                        <a href="?delete=<?php echo $category['id']; ?>" 
                                                           class="btn btn-danger btn-sm"
                                                           onclick="return confirm('Tem certeza que deseja excluir esta categoria?')">
                                                            Excluir
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const imageInput = document.getElementById('imageInput');
        const previewContainer = document.getElementById('previewContainer');
        
        if (uploadArea && fileInput) {
            uploadArea.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', handleFileSelect);
            
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });
            
            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('dragover');
            });
            
            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    handleFileSelect();
                }
            });
        }
        
        function handleFileSelect() {
            const file = fileInput.files[0];
            if (!file) return;
            
            if (!file.type.match('image.*')) {
                alert('Por favor, selecione uma imagem válida.');
                return;
            }
            
            if (file.size > 5 * 1024 * 1024) {
                alert('Arquivo muito grande. Máximo 5MB.');
                return;
            }
            
            const formData = new FormData();
            formData.append('file', file);
            formData.append('type', 'categories');
            // Se estiver editando uma categoria, não criar nova
            const editMode = document.querySelector('input[name="id"]')?.value > 0;
            formData.append('edit_mode', editMode ? 'true' : 'false');
            
            uploadArea.innerHTML = '<p>Fazendo upload...</p>';
            
            fetch('<?php echo BASE_URL; ?>admin/upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Response text:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('Parsed data:', data);
                    
                    if (data.success) {
                        imageInput.value = data.path;
                        previewContainer.innerHTML = `<img src="${data.url}" class="preview-image">`;
                        
                        // Se foi cadastrado automaticamente (nova categoria), redirecionar para edição
                        if (data.auto_registered) {
                            alert('✓ ' + data.message);
                            window.location.href = '<?php echo BASE_URL; ?>admin/categories.php?edit=' + data.item_id;
                        } else {
                            // Se está editando, apenas mostrar sucesso (usuário clica em Salvar)
                            uploadArea.innerHTML = '<p style="color: green;">✓ Imagem carregada! Clique em SALVAR para atualizar.</p>';
                        }
                    } else {
                        console.error('Upload falhou:', data);
                        alert('Erro: ' + data.message + (data.debug ? '\n\nDebug: ' + JSON.stringify(data.debug, null, 2) : ''));
                        uploadArea.innerHTML = '<p>Clique ou arraste uma imagem aqui</p>';
                    }
                } catch (e) {
                    console.error('Erro ao fazer parse do JSON:', e);
                    console.error('Resposta recebida:', text);
                    alert('Erro na resposta do servidor. Abra o console (F12) para mais detalhes.');
                    uploadArea.innerHTML = '<p style="color: red;">Erro no servidor</p>';
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                alert('Erro ao fazer upload: ' + error.message + '\n\nAbra o console (F12) para mais detalhes.');
                uploadArea.innerHTML = '<p>Clique ou arraste uma imagem aqui</p>';
            });
        }
        
        // Mostrar preview se estiver editando
        <?php if ($editCategory && $editCategory['image']): ?>
            previewContainer.innerHTML = '<img src="<?php echo BASE_URL . $editCategory['image']; ?>" class="preview-image">';
            uploadArea.innerHTML = '<p style="color: green;">✓ Imagem atual</p><p style="font-size: 14px;">Clique para alterar</p>';
        <?php endif; ?>
    </script>
</body>
</html>
