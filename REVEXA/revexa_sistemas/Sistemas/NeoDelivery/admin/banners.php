<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$db = Database::getInstance()->getConnection();

// Excluir banner
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("SELECT image FROM banners WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $banner = $result->fetchArray(SQLITE3_ASSOC);
    
    if ($banner && file_exists(__DIR__ . '/../' . $banner['image'])) {
        unlink(__DIR__ . '/../' . $banner['image']);
    }
    
    $db->exec("DELETE FROM banners WHERE id = {$id}");
    header('Location: ' . BASE_URL . 'admin/banners.php');
    exit;
}

// Salvar/Atualizar banner
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $image = sanitizeInput($_POST['image']);
    $link = sanitizeInput($_POST['link'] ?? '');
    $active = isset($_POST['active']) ? 1 : 0;
    $displayOrder = (int)$_POST['display_order'];
    
    if ($id > 0) {
        // Atualizar
        $stmt = $db->prepare("UPDATE banners SET title = :title, description = :description, image = :image, link = :link, active = :active, display_order = :display_order WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    } else {
        // Inserir
        $stmt = $db->prepare("INSERT INTO banners (title, description, image, link, active, display_order) VALUES (:title, :description, :image, :link, :active, :display_order)");
    }
    
    $stmt->bindValue(':title', $title, SQLITE3_TEXT);
    $stmt->bindValue(':description', $description, SQLITE3_TEXT);
    $stmt->bindValue(':image', $image, SQLITE3_TEXT);
    $stmt->bindValue(':link', $link, SQLITE3_TEXT);
    $stmt->bindValue(':active', $active, SQLITE3_INTEGER);
    $stmt->bindValue(':display_order', $displayOrder, SQLITE3_INTEGER);
    $stmt->execute();
    
    header('Location: ' . BASE_URL . 'admin/banners.php');
    exit;
}

// Buscar banners
$bannersQuery = $db->query("SELECT * FROM banners ORDER BY display_order, id");
$banners = [];
while ($row = $bannersQuery->fetchArray(SQLITE3_ASSOC)) {
    $banners[] = $row;
}

$businessName = getSetting('business_name', 'X Delivery');

// Banner sendo editado
$editBanner = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM banners WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $editBanner = $result->fetchArray(SQLITE3_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banners - <?php echo htmlspecialchars($businessName); ?></title>
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
            max-height: 200px;
            margin-top: 15px;
            border-radius: 10px;
        }
        
        .banner-preview {
            width: 100%;
            max-height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="admin-content">
            <header class="admin-header">
                <h1>Gerenciar Banners do Carrossel</h1>
            </header>
            
            <main class="admin-main">
                <!-- Form -->
                <div class="admin-card">
                    <h2 class="admin-card-title" style="margin-bottom: 20px;">
                        <?php echo $editBanner ? 'Editar' : 'Novo'; ?> Banner
                    </h2>
                    
                    <form method="POST" id="bannerForm">
                        <?php if ($editBanner): ?>
                            <input type="hidden" name="id" value="<?php echo $editBanner['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label class="form-label">Imagem do Banner *</label>
                            <div class="upload-area" id="uploadArea">
                                <p>Clique ou arraste uma imagem aqui</p>
                                <p style="font-size: 14px; color: var(--text-muted);">Recomendado: 1920x600px | Máx: 5MB</p>
                                <input type="file" id="fileInput" accept="image/*" style="display: none;">
                                <input type="hidden" name="image" id="imageInput" 
                                       value="<?php echo $editBanner ? htmlspecialchars($editBanner['image']) : ''; ?>" required>
                            </div>
                            <div id="previewContainer"></div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Título</label>
                                <input type="text" name="title" class="form-control"
                                       value="<?php echo $editBanner ? htmlspecialchars($editBanner['title']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Link (opcional)</label>
                                <input type="text" name="link" class="form-control"
                                       value="<?php echo $editBanner ? htmlspecialchars($editBanner['link']) : ''; ?>"
                                       placeholder="<?php echo BASE_URL; ?>cardapio.php">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Descrição</label>
                            <textarea name="description" class="form-control" rows="2"><?php echo $editBanner ? htmlspecialchars($editBanner['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Ordem de Exibição</label>
                                <input type="number" name="display_order" class="form-control"
                                       value="<?php echo $editBanner ? $editBanner['display_order'] : '0'; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label style="display: flex; align-items: center; cursor: pointer; margin-top: 28px;">
                                    <input type="checkbox" name="active" value="1" class="form-checkbox"
                                           <?php echo (!$editBanner || $editBanner['active']) ? 'checked' : ''; ?>>
                                    <strong style="margin-left: 10px;">Banner Ativo</strong>
                                </label>
                            </div>
                        </div>
                        
                        <?php if (!$editBanner): ?>
                        <div style="background: #d4edda; border: 2px solid #00b894; padding: 15px; border-radius: 8px; margin: 20px 0;">
                            <strong style="color: #155724;">✨ NOVO: Upload Automático!</strong>
                            <p style="color: #155724; margin: 10px 0 0 0;">Agora quando você faz upload de um banner, ele é cadastrado automaticamente e já fica ATIVO no site!</p>
                        </div>
                        <?php endif; ?>
                        
                        <button type="submit" class="btn btn-success btn-block" style="font-size: 18px; padding: 15px;">
                            <?php echo $editBanner ? '💾 Atualizar' : '💾 Cadastrar Manualmente (Opcional)'; ?> 
                        </button>
                        
                        <?php if ($editBanner): ?>
                            <a href="<?php echo BASE_URL; ?>admin/banners.php" class="btn btn-outline btn-block" style="margin-top: 10px;">
                                Cancelar Edição
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- List -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Banners Cadastrados (<?php echo count($banners); ?>)</h2>
                    </div>
                    
                    <?php if (empty($banners)): ?>
                        <p style="text-align: center; color: var(--text-muted); padding: 40px 0;">
                            Nenhum banner cadastrado ainda
                        </p>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Ordem</th>
                                        <th>Imagem</th>
                                        <th>Título</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($banners as $banner): ?>
                                        <tr>
                                            <td><?php echo $banner['display_order']; ?></td>
                                            <td>
                                                <img src="<?php echo BASE_URL . $banner['image']; ?>" 
                                                     alt="Banner" class="banner-preview">
                                            </td>
                                            <td><strong><?php echo htmlspecialchars($banner['title']); ?></strong></td>
                                            <td>
                                                <?php if ($banner['active']): ?>
                                                    <span class="badge badge-ready">Ativo</span>
                                                <?php else: ?>
                                                    <span class="badge badge-cancelled">Inativo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="?edit=<?php echo $banner['id']; ?>" class="btn btn-primary btn-sm">Editar</a>
                                                    <a href="?delete=<?php echo $banner['id']; ?>" 
                                                       class="btn btn-danger btn-sm"
                                                       onclick="return confirm('Tem certeza que deseja excluir este banner?')">
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
            </main>
        </div>
    </div>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const imageInput = document.getElementById('imageInput');
        const previewContainer = document.getElementById('previewContainer');
        
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
            formData.append('type', 'banners');
            // Se estiver editando um banner, não criar novo
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
                        
                        // Se foi cadastrado automaticamente (novo banner), recarregar
                        if (data.auto_registered) {
                            alert('✓ Banner adicionado e já está ATIVO no site!');
                            window.location.href = '<?php echo BASE_URL; ?>admin/banners.php';
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
                    alert('Erro na resposta do servidor. Verifique o console do navegador (F12).');
                    uploadArea.innerHTML = '<p style="color: red;">Erro no servidor</p>';
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                alert('Erro ao fazer upload: ' + error.message + '\n\nVerifique o console do navegador (F12).');
                uploadArea.innerHTML = '<p>Clique ou arraste uma imagem aqui</p>';
            });
        }
        
        // Mostrar preview se estiver editando
        <?php if ($editBanner && $editBanner['image']): ?>
            previewContainer.innerHTML = '<img src="<?php echo BASE_URL . $editBanner['image']; ?>" class="preview-image">';
            uploadArea.innerHTML = '<p style="color: green;">✓ Imagem atual</p><p style="font-size: 14px;">Clique para alterar</p>';
        <?php endif; ?>
        
        // Validar formulário antes de enviar
        document.getElementById('bannerForm').addEventListener('submit', function(e) {
            const imageValue = imageInput.value.trim();
            if (!imageValue) {
                e.preventDefault();
                alert('Por favor, faça upload de uma imagem antes de salvar!');
                uploadArea.style.borderColor = 'red';
                setTimeout(() => {
                    uploadArea.style.borderColor = '';
                }, 2000);
                return false;
            }
        });
    </script>
</body>
</html>
