<?php
// Log de erro para debug
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/config.php';
requireAdmin();

/**
 * Redimensiona e otimiza imagem conforme o tipo
 */
function resizeImage($filePath, $type) {
    // Tamanhos recomendados por tipo
    $maxSizes = [
        'banners' => ['width' => 1920, 'height' => 600],
        'products' => ['width' => 800, 'height' => 800],
        'categories' => ['width' => 600, 'height' => 400],
        'logo' => ['width' => 400, 'height' => 400]
    ];
    
    $maxWidth = $maxSizes[$type]['width'] ?? 1200;
    $maxHeight = $maxSizes[$type]['height'] ?? 1200;
    
    // Detectar tipo de imagem
    $imageInfo = getimagesize($filePath);
    if (!$imageInfo) {
        return false;
    }
    
    $originalWidth = $imageInfo[0];
    $originalHeight = $imageInfo[1];
    $mimeType = $imageInfo['mime'];
    
    // Se já está no tamanho certo ou menor, só otimizar
    if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
        return optimizeImage($filePath, $mimeType);
    }
    
    // Criar imagem a partir do arquivo original
    switch ($mimeType) {
        case 'image/jpeg':
        case 'image/jpg':
            $source = imagecreatefromjpeg($filePath);
            break;
        case 'image/png':
            $source = imagecreatefrompng($filePath);
            break;
        case 'image/gif':
            $source = imagecreatefromgif($filePath);
            break;
        case 'image/webp':
            $source = imagecreatefromwebp($filePath);
            break;
        default:
            return false;
    }
    
    if (!$source) {
        return false;
    }
    
    // Calcular novas dimensões mantendo proporção
    $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
    $newWidth = round($originalWidth * $ratio);
    $newHeight = round($originalHeight * $ratio);
    
    // Criar nova imagem redimensionada
    $resized = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preservar transparência para PNG
    if ($mimeType === 'image/png') {
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
        imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    // Redimensionar
    imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
    
    // Salvar como JPG com qualidade 85% (ótimo balanço qualidade/tamanho)
    $saved = imagejpeg($resized, $filePath, 85);
    
    // Liberar memória
    imagedestroy($source);
    imagedestroy($resized);
    
    return $saved;
}

/**
 * Otimiza imagem sem redimensionar
 */
function optimizeImage($filePath, $mimeType) {
    switch ($mimeType) {
        case 'image/jpeg':
        case 'image/jpg':
            $image = imagecreatefromjpeg($filePath);
            if ($image) {
                imagejpeg($image, $filePath, 85);
                imagedestroy($image);
                return true;
            }
            break;
        case 'image/png':
            $image = imagecreatefrompng($filePath);
            if ($image) {
                imagejpeg($image, $filePath, 85);
                imagedestroy($image);
                return true;
            }
            break;
    }
    return false;
}

// Configurações de upload
$uploadDir = __DIR__ . '/../uploads/';
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

// Criar diretórios se não existirem
$directories = [
    'uploads/',
    'uploads/products/',
    'uploads/categories/',
    'uploads/banners/',
    'uploads/logo/'
];

foreach ($directories as $dir) {
    $fullPath = __DIR__ . '/../' . $dir;
    if (!file_exists($fullPath)) {
        mkdir($fullPath, 0755, true);
    }
}

$response = ['success' => false, 'message' => '', 'path' => '', 'debug' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $type = $_POST['type'] ?? 'products'; // products, categories, banners, logo
    $editMode = isset($_POST['edit_mode']) && $_POST['edit_mode'] === 'true'; // Se está editando algo existente
    
    // Debug info
    $response['debug'] = [
        'file_error' => $file['error'],
        'file_size' => $file['size'],
        'file_type' => $file['type'],
        'file_name' => $file['name'],
        'type' => $type,
        'edit_mode' => $editMode
    ];
    
    // Verificar erro de upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'Arquivo excede upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'Arquivo excede MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'Upload incompleto',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo enviado',
            UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária não encontrada',
            UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever no disco',
            UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão'
        ];
        $response['message'] = $errors[$file['error']] ?? 'Erro desconhecido no upload';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Validar tipo de arquivo
    if (!in_array($file['type'], $allowedTypes)) {
        $response['message'] = 'Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WEBP. Tipo recebido: ' . $file['type'];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Validar tamanho
    if ($file['size'] > $maxFileSize) {
        $response['message'] = 'Arquivo muito grande. Máximo 5MB.';
        echo json_encode($response);
        exit;
    }
    
    // Gerar nome único
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '_' . time() . '.jpg'; // Sempre salvar como JPG otimizado
    $relativePath = "uploads/{$type}/{$fileName}";
    $fullPath = __DIR__ . '/../' . $relativePath;
    
    // Fazer upload e redimensionar
    if (move_uploaded_file($file['tmp_name'], $fullPath)) {
        // Redimensionar imagem
        $resized = resizeImage($fullPath, $type);
        if (!$resized) {
            $response['debug']['resize_warning'] = 'Imagem não foi redimensionada, mas upload OK';
        }
        $response['success'] = true;
        $response['message'] = 'Upload realizado com sucesso!';
        $response['path'] = $relativePath;
        $response['url'] = BASE_URL . $relativePath;
        $response['debug']['full_path'] = $fullPath;
        $response['debug']['file_exists'] = file_exists($fullPath);
        
        // Cadastrar automaticamente no banco de dados conforme o tipo (SOMENTE se NÃO estiver editando)
        try {
            $db = Database::getInstance()->getConnection();
            $autoRegistered = false;
            
            if ($editMode) {
                // Se está editando, apenas retorna o caminho da imagem sem cadastrar
                $response['auto_registered'] = false;
                $response['message'] = 'Upload realizado! A imagem será atualizada ao salvar.';
            } elseif ($type === 'banners') {
                $stmt = $db->prepare("INSERT INTO banners (title, description, image, link, active, display_order, created_at) 
                                      VALUES (:title, :description, :image, '', 1, 0, datetime('now'))");
                $stmt->bindValue(':title', 'Banner ' . date('d/m/Y H:i'), SQLITE3_TEXT);
                $stmt->bindValue(':description', 'Adicionado automaticamente', SQLITE3_TEXT);
                $stmt->bindValue(':image', $relativePath, SQLITE3_TEXT);
                
                if ($stmt->execute()) {
                    $response['item_id'] = $db->lastInsertRowID();
                    $response['message'] = 'Banner cadastrado e ativo no site!';
                    $autoRegistered = true;
                }
            } elseif ($type === 'categories') {
                // Nome do arquivo sem extensão como nome da categoria
                $categoryName = ucfirst(str_replace(['-', '_'], ' ', pathinfo($file['name'], PATHINFO_FILENAME)));
                $slug = strtolower(str_replace(' ', '-', $categoryName));
                
                $stmt = $db->prepare("INSERT INTO categories (name, slug, image, active, display_order) 
                                      VALUES (:name, :slug, :image, 1, 0)");
                $stmt->bindValue(':name', $categoryName, SQLITE3_TEXT);
                $stmt->bindValue(':slug', $slug, SQLITE3_TEXT);
                $stmt->bindValue(':image', $relativePath, SQLITE3_TEXT);
                
                if ($stmt->execute()) {
                    $response['item_id'] = $db->lastInsertRowID();
                    $response['message'] = 'Categoria "' . $categoryName . '" cadastrada e ativa!';
                    $autoRegistered = true;
                }
            } elseif ($type === 'products') {
                // Para produtos, precisamos de uma categoria. Vamos pegar a primeira ou criar uma padrão
                $categoryQuery = $db->query("SELECT id FROM categories WHERE active = 1 LIMIT 1");
                $category = $categoryQuery->fetchArray(SQLITE3_ASSOC);
                
                if (!$category) {
                    // Criar categoria padrão
                    $db->exec("INSERT INTO categories (name, slug, image, active, display_order) VALUES ('Geral', 'geral', '', 1, 0)");
                    $categoryId = $db->lastInsertRowID();
                } else {
                    $categoryId = $category['id'];
                }
                
                $productName = ucfirst(str_replace(['-', '_'], ' ', pathinfo($file['name'], PATHINFO_FILENAME)));
                
                $stmt = $db->prepare("INSERT INTO products (category_id, name, description, price, image, active, featured, display_order) 
                                      VALUES (:category_id, :name, :description, 0, :image, 1, 0, 0)");
                $stmt->bindValue(':category_id', $categoryId, SQLITE3_INTEGER);
                $stmt->bindValue(':name', $productName, SQLITE3_TEXT);
                $stmt->bindValue(':description', 'Adicionado automaticamente', SQLITE3_TEXT);
                $stmt->bindValue(':image', $relativePath, SQLITE3_TEXT);
                
                if ($stmt->execute()) {
                    $response['item_id'] = $db->lastInsertRowID();
                    $response['message'] = 'Produto "' . $productName . '" cadastrado! Edite para adicionar preço.';
                    $autoRegistered = true;
                }
            }
            
            $response['auto_registered'] = $autoRegistered;
            
        } catch (Exception $e) {
            $response['message'] = 'Upload OK, erro no banco: ' . $e->getMessage();
            $response['auto_registered'] = false;
        }
    } else {
        $response['message'] = 'Erro ao mover arquivo. Verifique permissões do diretório.';
        $response['debug']['tmp_name'] = $file['tmp_name'];
        $response['debug']['full_path'] = $fullPath;
        $response['debug']['dir_exists'] = file_exists(dirname($fullPath));
        $response['debug']['dir_writable'] = is_writable(dirname($fullPath));
    }
} else {
    $response['message'] = 'Nenhum arquivo enviado.';
    $response['debug']['post'] = $_POST;
    $response['debug']['files'] = $_FILES;
}

header('Content-Type: application/json');
echo json_encode($response);
