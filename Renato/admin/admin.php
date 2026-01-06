<?php
require_once __DIR__ . '/login.php';
require_once __DIR__ . '/database.php';
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}
$db = new Database();

// Salvar alterações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'texto_') === 0) {
            $db->setConfig(substr($key, 6), $value);
        }
        if ($key === 'whatsapp') {
            $db->setConfig('whatsapp', preg_replace('/\D/', '', $value));
        }
    }
    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['hero_image']['name'], PATHINFO_EXTENSION);
        $dest = '../uploads/imagens/hero_image.' . $ext;
        move_uploaded_file($_FILES['hero_image']['tmp_name'], $dest);
        $db->addImage('hero_image', $dest);
    }
    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
        $dest = '../uploads/videos/video_principal.' . $ext;
        move_uploaded_file($_FILES['video']['tmp_name'], $dest);
        $db->addVideo($dest, $_FILES['video']['type'], '', '', '');
    }
    echo '<div style="color:lime;">Alterações salvas!</div>';
}

$textos = [
    'hero_kicker' => 'Kicker do Hero',
    'hero_titulo' => 'Título do Hero',
    'hero_subtitulo' => 'Subtítulo do Hero',
    'hero_deck' => 'Deck do Hero',
    'stat1_num' => 'Stat 1 (número)',
    'stat1_label' => 'Stat 1 (label)',
    'stat2_num' => 'Stat 2 (número)',
    'stat2_label' => 'Stat 2 (label)',
    'stat3_num' => 'Stat 3 (número)',
    'stat3_label' => 'Stat 3 (label)',
    'video_texto' => 'Texto do Vídeo',
    'meth_titulo' => 'Título Metodologia',
    'meth_texto1' => 'Texto Metodologia 1',
    'meth_texto2' => 'Texto Metodologia 2',
    'cta_titulo' => 'Título CTA',
    'cta_texto' => 'Texto CTA',
    'depoimentos_titulo' => 'Título Depoimentos'
];
$dados = [];
foreach ($textos as $key => $label) {
    $dados[$key] = $db->getConfig($key, '');
}
$whatsapp = $db->getConfig('whatsapp', '');
$heroImage = $db->getImage('hero_image');
$videos = $db->getVideos();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Admin - Preview Editável</title>
<style>
body {
    background: linear-gradient(120deg, #181818 60%, #23272f 100%);
    color: #fff;
    font-family: 'IBM Plex Sans', Arial, sans-serif;
    margin: 0;
    min-height: 100vh;
}
.admin-bar {
    background: #181818;
    padding: 32px 0 24px 0;
    box-shadow: 0 2px 16px #0006;
    max-width: 700px;
    margin: 0 auto;
    border-radius: 0 0 18px 18px;
}
input, textarea {
    width: 100%;
    padding: 10px;
    margin: 8px 0 18px 0;
    border-radius: 6px;
    border: 1px solid #333;
    background: #23272f;
    color: #fff;
    font-size: 1rem;
}
label {
    font-weight: bold;
    color: #FF3B00;
    display: block;
    margin-top: 10px;
}
button, .btn-fullscreen {
    background: #FF3B00;
    color: #fff;
    border: none;
    padding: 12px 32px;
    border-radius: 6px;
    font-weight: bold;
    font-size: 1.1rem;
    cursor: pointer;
    margin-top: 18px;
    box-shadow: 0 2px 8px #0004;
    transition: background 0.2s;
}
button:hover, .btn-fullscreen:hover {
    background: #ff5e1a;
}
.preview {
    background: #111;
    padding: 40px 30px 30px 30px;
    margin: 40px auto 0 auto;
    border-radius: 18px;
    max-width: 700px;
    box-shadow: 0 4px 32px #0008;
    text-align: center;
}
img, video {
    max-width: 100%;
    border-radius: 12px;
    margin: 10px 0;
}
.carousel {
    display: flex;
    overflow-x: auto;
    gap: 24px;
    max-width: 100%;
    padding-bottom: 10px;
    margin: 0 auto 10px auto;
}
.carousel-item {
    min-width: 320px;
    max-width: 340px;
    background: #23272f;
    padding: 16px 10px 10px 10px;
    border-radius: 12px;
    box-shadow: 0 0 8px #0006;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: box-shadow 0.2s;
}
.carousel-item:hover {
    box-shadow: 0 0 24px #FF3B00aa;
}
.carousel video {
    background: #000;
    border-radius: 8px;
    outline: none;
}
.carousel small {
    color: #888;
    display: block;
    margin-top: 8px;
}
</style>
</head>
<body>
<div class="admin-bar">
    <form method="post" enctype="multipart/form-data">
        <h2>Preview Editável da Landing Page</h2>
        <label>WhatsApp:</label>
        <input type="text" name="whatsapp" value="<?= htmlspecialchars($whatsapp) ?>">
        <hr>
        <?php foreach($textos as $key => $label): ?>
            <label><?= htmlspecialchars($label) ?>:</label>
            <input type="text" name="texto_<?= $key ?>" value="<?= htmlspecialchars($dados[$key]) ?>">
        <?php endforeach; ?>
        <hr>
        <label>Imagem principal (Hero):</label>
        <input type="file" name="hero_image">
        <?php if($heroImage): ?><br><img src="<?= htmlspecialchars($heroImage) ?>" style="max-width:300px;"><?php endif; ?>
        <hr>
        <label>Vídeo principal:</label>
        <input type="file" name="video" accept="video/*">
        <?php if(!empty($videos)): ?>
            <br><video src="<?= htmlspecialchars($videos[0]['caminho']) ?>" width="300" controls></video>
        <?php endif; ?>
        <hr>
        <button type="submit">Salvar Alterações</button>
    </form>
</div>
<div class="preview">
    <h1><?= htmlspecialchars($dados['hero_titulo']) ?></h1>
    <h2><?= htmlspecialchars($dados['hero_subtitulo']) ?></h2>
    <p><?= htmlspecialchars($dados['hero_deck']) ?></p>
    <?php if($heroImage): ?><img src="<?= htmlspecialchars($heroImage) ?>"><?php endif; ?>
    <?php if(!empty($videos)): ?>
    <div style="margin: 30px 0;">
        <h3 style="color:#FF3B00;">Depoimentos em Vídeo (Carrossel)</h3>
        <div class="carousel" id="carousel">
            <?php foreach($videos as $v): ?>
                <div class="carousel-item">
                    <video src="<?= htmlspecialchars($v['caminho']) ?>" width="300" controls controlsList="nodownload" preload="metadata" allowfullscreen playsinline></video>
                    <button class="btn-fullscreen" onclick="this.previousElementSibling.requestFullscreen();return false;" style="margin-top:8px;background:#222;color:#FF3B00;font-size:0.95em;padding:6px 18px;">Tela cheia</button>
                    <?php if($v['nome_cliente']): ?><div style="margin-top:8px;font-weight:bold;">Cliente: <?= htmlspecialchars($v['nome_cliente']) ?></div><?php endif; ?>
                    <?php if($v['cargo'] || $v['empresa']): ?><div style="font-size:13px;opacity:.7;"> <?= htmlspecialchars($v['cargo']) ?> <?= $v['cargo'] && $v['empresa'] ? '—' : '' ?> <?= htmlspecialchars($v['empresa']) ?> </div><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <small>Arraste para o lado para ver todos os vídeos.</small>
    </div>
    <?php endif; ?>
    <p>WhatsApp: <?= htmlspecialchars($whatsapp) ?></p>
</div>
</body>
</html>
