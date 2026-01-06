<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/utils.php';

$db = new Database();

if (!is_logged_in()) {
    redirect('login.php');
}

// --- Lógica de Processamento ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Adicionar Novo Depoimento
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $data = [
            'texto' => sanitize_input($_POST['texto']),
            'nome_cliente' => sanitize_input($_POST['nome_cliente'] ?? 'Cliente'),
            'cargo' => sanitize_input($_POST['cargo'] ?? ''),
            'empresa' => sanitize_input($_POST['empresa'] ?? ''),
            'localizacao' => sanitize_input($_POST['localizacao'] ?? ''),
            'ativo' => isset($_POST['ativo']) ? true : false,
        ];
        $db->addDepoimentoTexto($data);
        redirect('depoimentos_texto.php?status=success_add');
    }

    // Editar Depoimento Existente (simplificado, apenas para demonstração)
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id = sanitize_input($_POST['id']);
        $data = [
            'texto' => sanitize_input($_POST['texto']),
            'nome_cliente' => sanitize_input($_POST['nome_cliente'] ?? 'Cliente'),
            'cargo' => sanitize_input($_POST['cargo'] ?? ''),
            'empresa' => sanitize_input($_POST['empresa'] ?? ''),
            'localizacao' => sanitize_input($_POST['localizacao'] ?? ''),
            'ativo' => isset($_POST['ativo']) ? true : false,
        ];
        $db->updateDepoimentoTexto($id, $data);
        redirect('depoimentos_texto.php?status=success_edit');
    }
}

// Deletar Depoimento
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = sanitize_input($_GET['id']);
    $db->deleteDepoimentoTexto($id);
    redirect('depoimentos_texto.php?status=success_delete');
}

// --- Interface de Usuário ---

$depoimentos = $db->getDepoimentosTexto();
$status = $_GET['status'] ?? null;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Depoimentos em Texto</title>
    <style>
        body { font-family: 'IBM Plex Sans', Arial, sans-serif; background: linear-gradient(120deg, #181818 60%, #23272f 100%); color: #fff; margin: 0; padding: 0; }
        .container { max-width: 1000px; margin: 30px auto; background: #181818; padding: 32px 24px; border-radius: 18px; box-shadow: 0 4px 32px #0008; }
        h1 { color: #FF3B00; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; background: #23272f; color: #FF3B00; }
        .success { background: #1e2e1e; color: #5cb85c; border: 1px solid #5cb85c; }
        .error { background: #2e1e1e; color: #d9534f; border: 1px solid #d9534f; }
        form { background: #23272f; padding: 20px; margin-bottom: 20px; border-radius: 12px; box-shadow: 0 2px 8px #0004; }
        form label { display: block; margin-bottom: 5px; font-weight: bold; color: #FF3B00; }
        form input[type="text"], form textarea { width: 100%; padding: 10px; margin-bottom: 10px; border-radius: 6px; border: 1px solid #333; background: #181818; color: #fff; font-size: 1rem; }
        form textarea { resize: vertical; height: 100px; }
        form button { background-color: #FF3B00; color: white; padding: 12px 32px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 1.1rem; box-shadow: 0 2px 8px #0004; transition: background 0.2s; }
        form button:hover { background: #ff5e1a; }
        .depoimento-list { margin-top: 20px; }
        .depoimento-item { background: #23272f; border: 1px solid #333; padding: 15px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; border-radius: 8px; box-shadow: 0 2px 8px #0002; }
        .depoimento-info { flex-grow: 1; }
        .depoimento-actions a { margin-left: 10px; text-decoration: none; color: #FF3B00; font-weight: bold; }
        .depoimento-actions a.delete { color: #d9534f; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gerenciar Depoimentos em Texto</h1>

        <?php if ($status === 'success_add'): ?>
            <div class="message success">Depoimento em texto adicionado com sucesso!</div>
        <?php elseif ($status === 'success_edit'): ?>
            <div class="message success">Depoimento em texto atualizado com sucesso!</div>
        <?php elseif ($status === 'success_delete'): ?>
            <div class="message success">Depoimento em texto excluído com sucesso!</div>
        <?php endif; ?>

        <h2>Adicionar Novo Depoimento</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            
            <label for="texto">Texto do Depoimento:</label>
            <textarea name="texto" id="texto" required></textarea>

            <label for="nome_cliente">Nome do Cliente:</label>
            <input type="text" name="nome_cliente" id="nome_cliente" value="Cliente">

            <label for="cargo">Cargo:</label>
            <input type="text" name="cargo" id="cargo">

            <label for="empresa">Empresa:</label>
            <input type="text" name="empresa" id="empresa">

            <label for="localizacao">Localização:</label>
            <input type="text" name="localizacao" id="localizacao">

            <label>
                <input type="checkbox" name="ativo" checked> Ativo
            </label>
            
            <button type="submit">Adicionar Depoimento</button>
        </form>

        <h2>Depoimentos Existentes</h2>
        <div class="depoimento-list">
            <?php if (empty($depoimentos)): ?>
                <p>Nenhum depoimento em texto cadastrado.</p>
            <?php else: ?>
                <?php foreach ($depoimentos as $dep): ?>
                    <div class="depoimento-item">
                        <div class="depoimento-info">
                            <strong>"<?= htmlspecialchars(substr($dep['texto'], 0, 100)) ?>..."</strong> (<?= $dep['ativo'] ? 'Ativo' : 'Inativo' ?>)<br>
                            <?= htmlspecialchars($dep['nome_cliente']) ?>, <?= htmlspecialchars($dep['cargo']) ?> - <?= htmlspecialchars($dep['empresa']) ?>
                        </div>
                        <div class="depoimento-actions">
                            <!-- Ações de Edição seriam implementadas em uma página separada ou modal -->
                            <a href="depoimentos_texto.php?action=delete&id=<?= htmlspecialchars($dep['id']) ?>" onclick="return confirm('Tem certeza que deseja excluir este depoimento?');" class="delete">Excluir</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
