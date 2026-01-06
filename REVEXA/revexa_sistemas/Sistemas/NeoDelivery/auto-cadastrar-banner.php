<?php
require_once __DIR__ . '/config/config.php';

$db = Database::getInstance()->getConnection();

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <title>Cadastrar Banner Automaticamente</title>
    <style>
        body { font-family: Arial; max-width: 900px; margin: 50px auto; padding: 20px; background: #f5f6fa; }
        .card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin: 20px 0; }
        h1 { color: #6c5ce7; }
        .success { background: #d4edda; color: #155724; padding: 20px; border-radius: 10px; margin: 20px 0; border: 2px solid #00b894; }
        .error { background: #f8d7da; color: #721c24; padding: 20px; border-radius: 10px; margin: 20px 0; }
        .button { display: inline-block; background: #6c5ce7; color: white; padding: 15px 30px; border-radius: 8px; text-decoration: none; margin: 10px 5px; font-size: 16px; }
        .button:hover { background: #5548c8; }
        .button-success { background: #00b894; }
        .button-success:hover { background: #00a383; }
        pre { background: #2d3436; color: #00b894; padding: 20px; border-radius: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🚀 Cadastro Automático do Banner</h1>";

// Verificar se já existe algum banner ativo
$existingQuery = $db->query("SELECT COUNT(*) as total FROM banners WHERE active = 1");
$existing = $existingQuery->fetchArray(SQLITE3_ASSOC);
$hasActiveBanners = $existing['total'] > 0;

if ($hasActiveBanners) {
    echo "<div class='card'>";
    echo "<h2 style='color: #00b894;'>✓ Você já tem {$existing['total']} banner(s) ativo(s)!</h2>";
    echo "<p>O site já deve estar mostrando banners. Se não está aparecendo:</p>";
    echo "<ol>
        <li>Limpe o cache do navegador (Ctrl + Shift + R)</li>
        <li>Verifique se está acessando a URL correta do site</li>
        <li>Abra o console do navegador (F12) para ver erros</li>
    </ol>";
    echo "<p><a href='" . BASE_URL . "' target='_blank' class='button button-success'>Ver Site Agora</a>";
    echo "<a href='" . BASE_URL . "debug-completo.php' class='button'>Ver Debug</a></p>";
    echo "</div>";
}

// Buscar o arquivo de banner mais recente
$uploadDir = __DIR__ . '/uploads/banners/';
$files = [];
if (is_dir($uploadDir)) {
    $allFiles = array_diff(scandir($uploadDir), ['.', '..']);
    foreach ($allFiles as $file) {
        $filePath = $uploadDir . $file;
        if (is_file($filePath) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
            $files[$file] = filemtime($filePath);
        }
    }
    arsort($files); // Ordenar por data, mais recente primeiro
}

if (!empty($files)) {
    $latestFile = array_key_first($files);
    $latestPath = 'uploads/banners/' . $latestFile;
    $latestUrl = BASE_URL . $latestPath;
    
    echo "<div class='card'>";
    echo "<h2>📸 Arquivo de Banner Mais Recente Encontrado:</h2>";
    echo "<p><strong>Arquivo:</strong> <code>{$latestFile}</code></p>";
    echo "<p><strong>Caminho:</strong> <code>{$latestPath}</code></p>";
    echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s', $files[$latestFile]) . "</p>";
    echo "<div style='text-align: center; margin: 20px 0;'>";
    echo "<img src='{$latestUrl}' style='max-width: 100%; max-height: 400px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.2);'>";
    echo "</div>";
    
    // Verificar se este arquivo já está cadastrado
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM banners WHERE image = :image");
    $stmt->bindValue(':image', $latestPath, SQLITE3_TEXT);
    $result = $stmt->execute();
    $check = $result->fetchArray(SQLITE3_ASSOC);
    
    if ($check['total'] > 0) {
        echo "<p style='color: #856404; background: #fff3cd; padding: 15px; border-radius: 8px;'>⚠️ Este arquivo já está cadastrado no banco de dados.</p>";
    } else {
        // Cadastrar automaticamente
        if (isset($_POST['auto_register'])) {
            $stmt = $db->prepare("INSERT INTO banners (title, description, image, link, active, display_order, created_at) 
                                  VALUES (:title, :description, :image, '', 1, 0, datetime('now'))");
            $stmt->bindValue(':title', 'Banner Principal', SQLITE3_TEXT);
            $stmt->bindValue(':description', 'Bem-vindo ao nosso delivery', SQLITE3_TEXT);
            $stmt->bindValue(':image', $latestPath, SQLITE3_TEXT);
            
            if ($stmt->execute()) {
                $bannerId = $db->lastInsertRowID();
                echo "<div class='success'>";
                echo "<h2 style='margin-top: 0;'>✓✓✓ BANNER CADASTRADO COM SUCESSO! ✓✓✓</h2>";
                echo "<p><strong>ID do Banner:</strong> {$bannerId}</p>";
                echo "<p><strong>Status:</strong> Ativo</p>";
                echo "<p><strong>Imagem:</strong> {$latestPath}</p>";
                echo "<p style='font-size: 18px; margin-top: 20px;'>🎉 <strong>O banner agora deve aparecer na página inicial do site!</strong></p>";
                echo "<p><a href='" . BASE_URL . "' target='_blank' class='button button-success' style='font-size: 20px;'>🌐 ABRIR SITE AGORA</a></p>";
                echo "</div>";
            } else {
                echo "<div class='error'>❌ Erro ao cadastrar banner no banco de dados.</div>";
            }
        } else {
            echo "<form method='POST'>";
            echo "<p style='text-align: center;'>";
            echo "<button type='submit' name='auto_register' class='button button-success' style='font-size: 20px; padding: 20px 40px;'>✨ CADASTRAR ESTE BANNER AUTOMATICAMENTE</button>";
            echo "</p>";
            echo "</form>";
            echo "<p style='text-align: center; color: #636e72;'>Isso vai cadastrar o banner como 'Ativo' e ele aparecerá no site imediatamente.</p>";
        }
    }
    echo "</div>";
} else {
    echo "<div class='card'>";
    echo "<p style='color: red;'>❌ Nenhum arquivo de banner encontrado em <code>uploads/banners/</code></p>";
    echo "<p>Você precisa primeiro fazer upload de uma imagem.</p>";
    echo "</div>";
}

// Listar todos os banners
echo "<div class='card'>";
echo "<h2>📋 Todos os Banners no Sistema:</h2>";
$allBannersQuery = $db->query("SELECT * FROM banners ORDER BY id DESC");
$allBanners = [];
while ($row = $allBannersQuery->fetchArray(SQLITE3_ASSOC)) {
    $allBanners[] = $row;
}

if (empty($allBanners)) {
    echo "<p>Nenhum banner cadastrado ainda.</p>";
} else {
    echo "<table style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #6c5ce7; color: white;'><th>ID</th><th>Título</th><th>Status</th><th>Imagem</th><th>Ações</th></tr>";
    foreach ($allBanners as $b) {
        $status = $b['active'] ? "<span style='color: #00b894;'>✓ Ativo</span>" : "<span style='color: #d63031;'>✗ Inativo</span>";
        echo "<tr style='border-bottom: 1px solid #ddd;'>";
        echo "<td style='padding: 10px;'>{$b['id']}</td>";
        echo "<td style='padding: 10px;'>" . htmlspecialchars($b['title']) . "</td>";
        echo "<td style='padding: 10px;'>{$status}</td>";
        echo "<td style='padding: 10px;'><code>" . htmlspecialchars($b['image']) . "</code></td>";
        echo "<td style='padding: 10px;'><a href='" . BASE_URL . "admin/banners.php?edit={$b['id']}'>Editar</a></td>";
        echo "</tr>";
    }
    echo "</table>";
}
echo "</div>";

echo "<div style='text-align: center; padding: 20px;'>";
echo "<a href='" . BASE_URL . "debug-completo.php' class='button'>🔍 Debug Completo</a>";
echo "<a href='" . BASE_URL . "admin/banners.php' class='button'>⚙️ Gerenciar Banners</a>";
echo "<a href='" . BASE_URL . "' target='_blank' class='button button-success'>🌐 Ver Site</a>";
echo "</div>";

echo "</body></html>";
?>
