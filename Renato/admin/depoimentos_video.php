<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/utils.php';

$db = new Database();
$upload_dir = 'uploads/videos'; // Diretório de upload para vídeos

if (!is_logged_in()) {
    redirect('login.php');
}

// --- Lógica de Processamento ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Adicionar Novo Depoimento
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $upload_result = handle_upload('video_file', $upload_dir, ['mp4', 'webm', 'mov']);
        
        if ($upload_result['success']) {
            $data = [
                'caminho' => $upload_result['path'],
                'tipo' => $upload_result['type'],
                'nome_cliente' => sanitize_input($_POST['nome_cliente'] ?? 'Cliente'),
                'cargo' => sanitize_input($_POST['cargo'] ?? ''),
                'empresa' => sanitize_input($_POST['empresa'] ?? ''),
                'ativo' => isset($_POST['ativo']) ? true : false,
            ];
            $db->addDepoimentoVideo($data);
            redirect('depoimentos_video.php?status=success_add');
        } else {
            $error = $upload_result['message'];
        }
    }

    // Editar Depoimento Existente
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id = sanitize_input($_POST['id']);
        $data = [
            'nome_cliente' => sanitize_input($_POST['nome_cliente'] ?? 'Cliente'),
            'cargo' => sanitize_input($_POST['cargo'] ?? ''),
            'empresa' => sanitize_input($_POST['empresa'] ?? ''),
            'ativo' => isset($_POST['ativo']) ? true : false,
        ];

        // Se um novo arquivo foi enviado, processa o upload
        if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
            $upload_result = handle_upload('video_file', $upload_dir, ['mp4', 'webm', 'mov']);
            if ($upload_result['success']) {
                $data['caminho'] = $upload_result['path'];
                $data['tipo'] = $upload_result['type'];
                // TODO: Adicionar lógica para deletar o arquivo antigo
            } else {
                $error = $upload_result['message'];
            }
        }

        if (!isset($error)) {
            $db->updateDepoimentoVideo($id, $data);
            redirect('depoimentos_video.php?status=success_edit');
        }
    }
}

// Deletar Depoimento
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = sanitize_input($_GET['id']);
    // TODO: Adicionar lógica para deletar o arquivo físico
    $db->deleteDepoimentoVideo($id);
    redirect('depoimentos_video.php?status=success_delete');
}

// --- Interface de Usuário ---

$depoimentos = $db->getDepoimentosVideo();
$status = $_GET['status'] ?? null;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Depoimentos em Vídeo</title>
    <style>
        body { font-family: 'IBM Plex Sans', Arial, sans-serif; background: linear-gradient(120deg, #181818 60%, #23272f 100%); color: #fff; margin: 0; padding: 0; }
        .container { max-width: 1100px; margin: 30px auto; background: #181818; padding: 32px 24px; border-radius: 18px; box-shadow: 0 4px 32px #0008; }
        h1 { color: #FF3B00; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .nav-menu { margin-bottom: 30px; }
        .nav-menu a { margin-right: 18px; text-decoration: none; color: #FF3B00; font-weight: bold; font-size: 1.1em; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; background: #23272f; color: #FF3B00; }
        .success { background: #1e2e1e; color: #5cb85c; border: 1px solid #5cb85c; }
        .error { background: #2e1e1e; color: #d9534f; border: 1px solid #d9534f; }
        form { background: #23272f; padding: 20px; margin-bottom: 30px; border-radius: 12px; box-shadow: 0 2px 8px #0004; }
        form label { display: block; margin-bottom: 5px; font-weight: bold; color: #FF3B00; }
        form input[type="text"], form input[type="file"] { width: 100%; padding: 8px; margin-bottom: 12px; border-radius: 6px; border: 1px solid #333; background: #181818; color: #fff; }
        form button { background-color: #FF3B00; color: white; padding: 10px 18px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
.carousel {
    display: grid;
    grid-auto-flow: column;
    grid-auto-columns: minmax(300px, 1fr); /* Pelo menos 300px, mas pode expandir */
    gap: 24px;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    scroll-padding: 24px;
    padding-bottom: 10px; /* Espaço para a barra de rolagem */
    margin: 0 auto 10px auto;
    -webkit-overflow-scrolling: touch;
    max-width: 100%;
}
.carousel-item {
    scroll-snap-align: start;
    background: #23272f;
    padding: 16px 10px 10px 10px;
    border-radius: 12px;
    box-shadow: 0 0 8px #0006;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: box-shadow 0.2s;
}
        .carousel-item:hover { box-shadow: 0 0 24px #FF3B00aa; }
        .carousel video { background: #000; border-radius: 8px; outline: none; max-width: 100%; }
        .carousel small { color: #888; display: block; margin-top: 8px; }
        .carousel .btn-fullscreen { background: #222; color: #FF3B00; font-size: 0.95em; padding: 6px 18px; border-radius: 6px; margin-top: 8px; border: 1px solid #FF3B00; cursor: pointer; }
        .carousel .btn-fullscreen:hover { background: #FF3B00; color: #fff; }
        .carousel .info { margin-top: 8px; text-align: center; }
        .carousel .info strong { color: #FF3B00; }
        .actions { margin-top: 10px; }
        .actions a { color: #d9534f; margin-left: 10px; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Depoimentos em Vídeo</h1>
        <div class="nav-menu">
            <a href="index.php">Textos e Configurações</a>
            <a href="midias.php">Imagens e Vídeos Principais</a>
            <a href="depoimentos_video.php">Depoimentos em Vídeo</a>
            <a href="depoimentos_texto.php">Depoimentos em Texto</a>
            <a href="logout.php">Sair</a>
        </div>
        <?php if (isset($status) && $status === 'success_add'): ?>
            <div class="message success">Depoimento adicionado com sucesso!</div>
        <?php elseif (isset($status) && $status === 'success_edit'): ?>
            <div class="message success">Depoimento editado com sucesso!</div>
        <?php elseif (isset($status) && $status === 'success_delete'): ?>
            <div class="message success">Depoimento removido com sucesso!</div>
        <?php elseif (isset($error)): ?>
            <div class="message error">Erro: <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <label>Nome do Cliente:</label>
            <input type="text" name="nome_cliente" required>
            <label>Cargo:</label>
            <input type="text" name="cargo">
            <label>Empresa:</label>
            <input type="text" name="empresa">
            <label>Vídeo (mp4, webm, mov):</label>
            <input type="file" name="video_file" accept="video/*" required>
            <label><input type="checkbox" name="ativo" checked> Ativo</label>
            <button type="submit">Adicionar Depoimento</button>
        </form>

        <h2 style="color:#FF3B00;">Carrossel de Depoimentos</h2>
        <?php if (!empty($depoimentos)): ?>
        <div class="carousel">
            <?php foreach($depoimentos as $dep): ?>
                <div class="carousel-item">
                    <video src="/revexa_sistemas/Sistemas/RenatoCustom/<?= htmlspecialchars($dep['caminho']) ?>" width="300" controls controlsList="nodownload" preload="metadata" allowfullscreen playsinline></video>
                    <button class="btn-fullscreen" onclick="this.previousElementSibling.requestFullscreen();return false;">Tela cheia</button>
                    <div class="info">
                        <?php if($dep['nome_cliente']): ?><strong><?= htmlspecialchars($dep['nome_cliente']) ?></strong><br><?php endif; ?>
                        <?php if($dep['cargo'] || $dep['empresa']): ?>
                            <span><?= htmlspecialchars($dep['cargo']) ?><?= $dep['cargo'] && $dep['empresa'] ? ' — ' : '' ?><?= htmlspecialchars($dep['empresa']) ?></span><br>
                        <?php endif; ?>
                    </div>
                    <div class="actions">
                        <a href="?action=delete&id=<?= $dep['id'] ?>" onclick="return confirm('Remover este vídeo?')">Remover</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <small style="color:#888;">Arraste para o lado para ver todos os vídeos.</small>
        <?php else: ?>
            <p style="color:#888;">Nenhum depoimento cadastrado.</p>
        <?php endif; ?>
    </div>
</body>
</html>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Depoimentos em Vídeo</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        form { background: #f9f9f9; padding: 20px; margin-bottom: 20px; border: 1px solid #ddd; }
        form label { display: block; margin-bottom: 5px; font-weight: bold; }
        form input[type="text"], form input[type="file"] { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; box-sizing: border-box; }
        form button { background-color: #5cb85c; color: white; padding: 10px 15px; border: none; cursor: pointer; }
        .depoimento-list { margin-top: 20px; }
        .depoimento-item { background: #fff; border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .depoimento-info { flex-grow: 1; }
        .depoimento-actions a { margin-left: 10px; text-decoration: none; color: #337ab7; }
        .depoimento-actions a.delete { color: #d9534f; }
        video { max-width: 150px; height: auto; margin-right: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gerenciar Depoimentos em Vídeo</h1>

        <?php if ($status === 'success_add'): ?>
            <div class="message success">Depoimento em vídeo adicionado com sucesso!</div>
        <?php elseif ($status === 'success_edit'): ?>
            <div class="message success">Depoimento em vídeo atualizado com sucesso!</div>
        <?php elseif ($status === 'success_delete'): ?>
            <div class="message success">Depoimento em vídeo excluído com sucesso!</div>
        <?php elseif (isset($error)): ?>
            <div class="message error">Erro: <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>


    </div>
</body>
</html>
