<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/utils.php';

$db = new Database();
$upload_dir_img = 'uploads/imagens';
$upload_dir_video = 'uploads/videos';

if (!is_logged_in()) {
    redirect('login.php');
}

// --- Lógica de Processamento ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $media_key = sanitize_input($_POST['media_key']);
    $upload_result = null;

    if ($media_key === 'hero_image' && isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
        $upload_result = handle_upload('media_file', $upload_dir_img, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    } elseif ($media_key === 'video_principal' && isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
        $upload_result = handle_upload('media_file', $upload_dir_video, ['mp4', 'webm', 'mov']);
    }

    if ($upload_result && $upload_result['success']) {
        $db->setMedia($media_key, $upload_result['path'], $upload_result['type']);
        redirect('midias.php?status=success_upload');
    } elseif ($upload_result) {
        $error = $upload_result['message'];
    }
}

// --- Interface de Usuário ---

$heroImage = $db->getImage('hero_image');
$videoPrincipal = $db->getVideo('video_principal');
$status = $_GET['status'] ?? null;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Mídias Principais</title>
    <style>
        body { font-family: 'IBM Plex Sans', Arial, sans-serif; background: linear-gradient(120deg, #181818 60%, #23272f 100%); color: #fff; margin: 0; padding: 0; }
        .container { max-width: 1000px; margin: 30px auto; background: #181818; padding: 32px 24px; border-radius: 18px; box-shadow: 0 4px 32px #0008; }
        h1 { color: #FF3B00; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; background: #23272f; color: #FF3B00; }
        .success { background: #1e2e1e; color: #5cb85c; border: 1px solid #5cb85c; }
        .error { background: #2e1e1e; color: #d9534f; border: 1px solid #d9534f; }
        .nav-menu { margin-bottom: 30px; }
        .nav-menu a { margin-right: 18px; text-decoration: none; color: #FF3B00; font-weight: bold; font-size: 1.1em; }
        .media-section { margin-top: 30px; padding: 24px; border-radius: 12px; background: #23272f; box-shadow: 0 2px 8px #0004; display: flex; gap: 40px; }
        .media-card { flex: 1; }
        .media-card h2 { margin-top: 0; color: #FF3B00; }
        .media-preview { margin-bottom: 15px; border: 1px solid #333; padding: 10px; text-align: center; background: #181818; border-radius: 8px; }
        .media-preview img, .media-preview video { max-width: 100%; height: auto; display: block; margin: 0 auto; border-radius: 8px; }
        form label { display: block; margin-top: 15px; font-weight: bold; color: #FF3B00; }
        form input[type="file"] { width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #333; background: #181818; color: #fff; }
        form button { background-color: #FF3B00; color: white; padding: 12px 32px; border: none; border-radius: 6px; cursor: pointer; margin-top: 20px; font-weight: bold; font-size: 1.1rem; box-shadow: 0 2px 8px #0004; transition: background 0.2s; }
        form button:hover { background: #ff5e1a; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gerenciar Mídias Principais</h1>

        <?php if ($status === 'success_upload'): ?>
            <div class="message success">Mídia atualizada com sucesso!</div>
        <?php elseif (isset($error)): ?>
            <div class="message error">Erro: <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="nav-menu">
            <a href="index.php">Textos e Configurações</a>
            <a href="midias.php">Imagens e Vídeos Principais</a>
            <a href="depoimentos_video.php">Depoimentos em Vídeo</a>
            <a href="depoimentos_texto.php">Depoimentos em Texto</a>
        </div>

        <div class="media-section">
            <div class="media-card">
                <h2>Imagem Principal (Hero)</h2>
                <div class="media-preview">
                    <?php if ($heroImage): ?>
                        <img src="../<?= htmlspecialchars($heroImage['caminho']) ?>" alt="Imagem Principal">
                        <small>Caminho: <?= htmlspecialchars($heroImage['caminho']) ?></small>
                    <?php else: ?>
                        <p>Nenhuma imagem principal definida.</p>
                    <?php endif; ?>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="media_key" value="hero_image">
                    <label for="hero_image_file">Upload Nova Imagem (JPG, PNG, WEBP):</label>
                    <input type="file" name="media_file" id="hero_image_file" accept="image/*" required>
                    <button type="submit">Atualizar Imagem</button>
                </form>
            </div>

            <div class="media-card">
                <h2>Vídeo Principal</h2>
                <div class="media-preview">
                    <?php if ($videoPrincipal): ?>
                        <video controls src="../<?= htmlspecialchars($videoPrincipal['caminho']) ?>"></video>
                        <small>Caminho: <?= htmlspecialchars($videoPrincipal['caminho']) ?></small>
                    <?php else: ?>
                        <p>Nenhum vídeo principal definido.</p>
                    <?php endif; ?>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="media_key" value="video_principal">
                    <label for="video_principal_file">Upload Novo Vídeo (MP4, WEBM, MOV):</label>
                    <input type="file" name="media_file" id="video_principal_file" accept="video/*" required>
                    <button type="submit">Atualizar Vídeo</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
