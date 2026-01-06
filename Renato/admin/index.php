<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/utils.php';

// Redireciona para a página de login se não estiver logado (será implementado na fase 5)
if (!is_logged_in()) {
    redirect('login.php');
}

$db = new Database();
$config = $db->getAllConfig();

// --- Lógica de Processamento ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_config') {
    foreach ($_POST as $key => $value) {
        if ($key !== 'action') {
            $db->setConfig($key, sanitize_input($value));
        }
    }
    redirect('index.php?status=success_config');
}

// --- Interface de Usuário ---

$status = $_GET['status'] ?? null;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Administração</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <style>
        body { font-family: 'IBM Plex Sans', Arial, sans-serif; background: linear-gradient(120deg, #181818 60%, #23272f 100%); color: #fff; margin: 0; padding: 0; }
        .container { max-width: 1100px; margin: 30px auto; background: #181818; padding: 32px 24px; border-radius: 18px; box-shadow: 0 4px 32px #0008; }
        h1 { color: #FF3B00; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; background: #23272f; color: #FF3B00; }
        .success { background: #1e2e1e; color: #5cb85c; border: 1px solid #5cb85c; }
        .nav-menu { margin-bottom: 30px; }
        .nav-menu a { margin-right: 18px; text-decoration: none; color: #FF3B00; font-weight: bold; font-size: 1.1em; }
        .content-section { margin-top: 30px; padding: 24px; border-radius: 12px; background: #23272f; box-shadow: 0 2px 8px #0004; }
        .content-section h2 { margin-top: 0; color: #FF3B00; }
        form label { display: block; margin-top: 15px; font-weight: bold; color: #FF3B00; }
        form input[type="text"], form textarea { width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #333; background: #181818; color: #fff; font-size: 1rem; }
        form textarea { resize: vertical; height: 100px; }
        form button { background-color: #FF3B00; color: white; padding: 12px 32px; border: none; border-radius: 6px; cursor: pointer; margin-top: 20px; font-weight: bold; font-size: 1.1rem; box-shadow: 0 2px 8px #0004; transition: background 0.2s; }
        form button:hover { background: #ff5e1a; }
        .media-upload { margin-top: 20px; padding: 15px; border: 1px dashed #FF3B00; background: #23272f; border-radius: 8px; }
        .media-upload img, .media-upload video { max-width: 200px; height: auto; display: block; margin-top: 10px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Painel de Administração da Landing Page</h1>

        <?php if ($status === 'success_config'): ?>
            <div class="message success">Configurações de texto atualizadas com sucesso!</div>
        <?php endif; ?>

        <div class="nav-menu">
            <a href="index.php">Textos e Configurações</a>
            <a href="midias.php">Imagens e Vídeos Principais</a>
            <a href="depoimentos_video.php">Depoimentos em Vídeo</a>
            <a href="depoimentos_texto.php">Depoimentos em Texto</a>
            <a href="logout.php">Sair</a>
        </div>

        <div class="content-section">
            <h2>Edição de Textos e Configurações</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update_config">

                <h3>Configurações Gerais</h3>
                <label for="whatsapp">WhatsApp (apenas números):</label>
                <input type="text" name="whatsapp" id="whatsapp" value="<?= htmlspecialchars($config['whatsapp'] ?? '') ?>">

                <label for="cor_primaria">Cor Primária:</label>
                <input type="color" name="cor_primaria" id="cor_primaria" value="<?= htmlspecialchars($config['cor_primaria'] ?? '#FF3B00') ?>" style="width: 60px; height: 40px; padding: 0; border: none; background: none; cursor: pointer; vertical-align: middle;">
                <span style="margin-left:10px; font-size:0.95em; color:#FF3B00; vertical-align: middle;">Escolha a cor</span>

                <h3>Seção Hero</h3>
                <label for="hero_kicker">Kicker (Texto acima do título):</label>
                <input type="text" name="hero_kicker" id="hero_kicker" value="<?= htmlspecialchars($config['hero_kicker'] ?? '') ?>">

                <label for="hero_titulo">Título Principal:</label>
                <input type="text" name="hero_titulo" id="hero_titulo" value="<?= htmlspecialchars($config['hero_titulo'] ?? '') ?>">

                <label for="hero_subtitulo">Subtítulo (destacado):</label>
                <input type="text" name="hero_subtitulo" id="hero_subtitulo" value="<?= htmlspecialchars($config['hero_subtitulo'] ?? '') ?>">

                <label for="hero_deck">Deck (Texto abaixo do título):</label>
                <textarea name="hero_deck" id="hero_deck"><?= htmlspecialchars($config['hero_deck'] ?? '') ?></textarea>

                <h3>Estatísticas</h3>
                <label for="stat1_num">Estatística 1 - Número:</label>
                <input type="text" name="stat1_num" id="stat1_num" value="<?= htmlspecialchars($config['stat1_num'] ?? '') ?>">
                <label for="stat1_label">Estatística 1 - Rótulo:</label>
                <input type="text" name="stat1_label" id="stat1_label" value="<?= htmlspecialchars($config['stat1_label'] ?? '') ?>">
                
                <label for="stat2_num">Estatística 2 - Número:</label>
                <input type="text" name="stat2_num" id="stat2_num" value="<?= htmlspecialchars($config['stat2_num'] ?? '') ?>">
                <label for="stat2_label">Estatística 2 - Rótulo:</label>
                <input type="text" name="stat2_label" id="stat2_label" value="<?= htmlspecialchars($config['stat2_label'] ?? '') ?>">

                <label for="stat3_num">Estatística 3 - Número:</label>
                <input type="text" name="stat3_num" id="stat3_num" value="<?= htmlspecialchars($config['stat3_num'] ?? '') ?>">
                <label for="stat3_label">Estatística 3 - Rótulo:</label>
                <input type="text" name="stat3_label" id="stat3_label" value="<?= htmlspecialchars($config['stat3_label'] ?? '') ?>">

                <h3>Seção de Vídeo Principal</h3>
                <label for="video_texto">Título da Seção de Vídeo:</label>
                <input type="text" name="video_texto" id="video_texto" value="<?= htmlspecialchars($config['video_texto'] ?? '') ?>">

                <h3>Seção Metodologia</h3>
                <label for="meth_titulo">Título da Metodologia:</label>
                <input type="text" name="meth_titulo" id="meth_titulo" value="<?= htmlspecialchars($config['meth_titulo'] ?? '') ?>">
                <label for="meth_texto1">Texto 1 da Metodologia:</label>
                <textarea name="meth_texto1" id="meth_texto1"><?= htmlspecialchars($config['meth_texto1'] ?? '') ?></textarea>
                <label for="meth_texto2">Texto 2 da Metodologia:</label>
                <textarea name="meth_texto2" id="meth_texto2"><?= htmlspecialchars($config['meth_texto2'] ?? '') ?></textarea>

                <h3>Seção Depoimentos</h3>
                <label for="depoimentos_titulo">Título da Seção de Depoimentos:</label>
                <input type="text" name="depoimentos_titulo" id="depoimentos_titulo" value="<?= htmlspecialchars($config['depoimentos_titulo'] ?? '') ?>">

                <h3>Seção CTA (Call to Action)</h3>
                <label for="cta_titulo">Título do CTA:</label>
                <input type="text" name="cta_titulo" id="cta_titulo" value="<?= htmlspecialchars($config['cta_titulo'] ?? '') ?>">
                <label for="cta_texto">Texto do CTA:</label>
                <textarea name="cta_texto" id="cta_texto"><?= htmlspecialchars($config['cta_texto'] ?? '') ?></textarea>

                <button type="submit">Salvar Configurações de Texto</button>
            </form>
        </div>
    </div>
</body>
</html>
