<?php
require_once __DIR__ . '/config/config.php';

$db = Database::getInstance()->getConnection();

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <title>Cadastro Rápido de Banner</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f6fa; }
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin: 20px 0; }
        h1 { color: #6c5ce7; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        button { background: #6c5ce7; color: white; border: none; padding: 12px 30px; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #5548c8; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #6c5ce7; color: white; }
        img { max-width: 200px; height: auto; }
    </style>
</head>
<body>
    <h1>🚀 Cadastro Rápido de Banner</h1>";

// Se vier do POST, cadastrar banner
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['image_path'])) {
    $imagePath = sanitizeInput($_POST['image_path']);
    $title = sanitizeInput($_POST['title'] ?? 'Banner');
    $description = sanitizeInput($_POST['description'] ?? '');
    $active = isset($_POST['active']) ? 1 : 0;
    
    $stmt = $db->prepare("INSERT INTO banners (title, description, image, link, active, display_order, created_at) 
                          VALUES (:title, :description, :image, '', :active, 0, datetime('now'))");
    $stmt->bindValue(':title', $title, SQLITE3_TEXT);
    $stmt->bindValue(':description', $description, SQLITE3_TEXT);
    $stmt->bindValue(':image', $imagePath, SQLITE3_TEXT);
    $stmt->bindValue(':active', $active, SQLITE3_INTEGER);
    $stmt->execute();
    
    echo "<div class='success'>✓ Banner cadastrado com sucesso!</div>";
    echo "<div><a href='" . BASE_URL . "' target='_blank'>Ver site agora</a></div>";
}

// Formulário de cadastro rápido
echo "<div class='card'>
    <h2>Cadastrar Banner que você acabou de enviar:</h2>
    <form method='POST'>
        <p><strong>Caminho da imagem que você enviou:</strong><br>
        <input type='text' name='image_path' value='uploads/banners/691ce2801205e_1763500672.png' style='width: 100%; padding: 8px;' required></p>
        
        <p><strong>Título (opcional):</strong><br>
        <input type='text' name='title' placeholder='Título do banner' style='width: 100%; padding: 8px;'></p>
        
        <p><strong>Descrição (opcional):</strong><br>
        <input type='text' name='description' placeholder='Descrição do banner' style='width: 100%; padding: 8px;'></p>
        
        <p><label><input type='checkbox' name='active' checked> <strong>Banner Ativo (marcar para aparecer no site)</strong></label></p>
        
        <button type='submit'>Cadastrar Banner</button>
    </form>
</div>";

// Listar banners existentes
echo "<div class='card'>
    <h2>Banners Já Cadastrados:</h2>";

$bannersQuery = $db->query("SELECT * FROM banners ORDER BY display_order, id DESC");
$banners = [];
while ($row = $bannersQuery->fetchArray(SQLITE3_ASSOC)) {
    $banners[] = $row;
}

if (empty($banners)) {
    echo "<p>Nenhum banner cadastrado ainda.</p>";
} else {
    echo "<table>
        <tr>
            <th>ID</th>
            <th>Imagem</th>
            <th>Título</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>";
    
    foreach ($banners as $banner) {
        $imageUrl = BASE_URL . $banner['image'];
        $active = $banner['active'] ? '✓ Ativo' : '✗ Inativo';
        echo "<tr>
            <td>{$banner['id']}</td>
            <td><img src='{$imageUrl}' alt='Banner' onerror='this.src=\"data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27200%27 height=%2780%27%3E%3Crect fill=%27%23ddd%27 width=%27200%27 height=%2780%27/%3E%3Ctext x=%2750%25%27 y=%2750%25%27 dominant-baseline=%27middle%27 text-anchor=%27middle%27 fill=%27%23999%27%3EImagem não encontrada%3C/text%3E%3C/svg%3E\"'></td>
            <td>" . htmlspecialchars($banner['title']) . "</td>
            <td>{$active}</td>
            <td><a href='" . BASE_URL . "admin/banners.php?edit={$banner['id']}'>Editar</a> | 
                <a href='" . BASE_URL . "admin/banners.php?delete={$banner['id']}' onclick='return confirm(\"Excluir?\")'>Excluir</a></td>
        </tr>";
    }
    
    echo "</table>";
}

echo "</div>";

echo "<div class='card'>
    <h2>Links Úteis:</h2>
    <ul>
        <li><a href='" . BASE_URL . "' target='_blank'>Ver Site (Página Inicial)</a></li>
        <li><a href='" . BASE_URL . "admin/banners.php'>Gerenciar Banners (Admin)</a></li>
        <li><a href='" . BASE_URL . "debug-banners.php'>Ver Debug Completo</a></li>
    </ul>
</div>";

echo "</body></html>";
?>
