<?php
$page_title = 'Procedimentos';
require_once '../includes/header.php';

if (!has_permission('dentista')) {
    show_alert('Acesso negado!', 'error');
    redirect('dashboard.php');
}

$db = get_db();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Salvar
if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'codigo' => sanitize_input($_POST['codigo'] ?? ''),
        'nome' => sanitize_input($_POST['nome']),
        'descricao' => sanitize_input($_POST['descricao'] ?? ''),
        'valor_particular' => floatval(str_replace(',', '.', $_POST['valor_particular'])),
        'valor_convenio' => floatval(str_replace(',', '.', $_POST['valor_convenio'] ?? 0))
    ];
    
    if ($id) {
        $stmt = $db->prepare("
            UPDATE procedimentos SET codigo=?, nome=?, descricao=?, valor_particular=?, valor_convenio=?
            WHERE id=?
        ");
        $stmt->execute([$data['codigo'], $data['nome'], $data['descricao'], 
                       $data['valor_particular'], $data['valor_convenio'], $id]);
        show_alert('Procedimento atualizado!', 'success');
    } else {
        $stmt = $db->prepare("
            INSERT INTO procedimentos (codigo, nome, descricao, valor_particular, valor_convenio)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$data['codigo'], $data['nome'], $data['descricao'], 
                       $data['valor_particular'], $data['valor_convenio']]);
        show_alert('Procedimento criado!', 'success');
    }
    
    redirect('modules/procedimentos.php');
}

// Deletar
if ($action === 'delete' && $id) {
    $stmt = $db->prepare("UPDATE procedimentos SET ativo = 0 WHERE id = ?");
    $stmt->execute([$id]);
    show_alert('Procedimento inativado!', 'success');
    redirect('modules/procedimentos.php');
}

// Listar
if ($action === 'list') {
    $stmt = $db->query("SELECT * FROM procedimentos WHERE ativo = 1 ORDER BY nome");
    $procedimentos = $stmt->fetchAll();
    ?>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tabela de Procedimentos</h3>
            <a href="?action=new" class="btn btn-primary">➕ Novo Procedimento</a>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nome</th>
                        <th>Valor Particular</th>
                        <th>Valor Convênio</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($procedimentos as $p): ?>
                        <tr>
                            <td><?= $p['codigo'] ?></td>
                            <td><?= $p['nome'] ?></td>
                            <td><?= format_currency($p['valor_particular']) ?></td>
                            <td><?= format_currency($p['valor_convenio']) ?></td>
                            <td>
                                <a href="?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-secondary">✏️</a>
                                <a href="?action=delete&id=<?= $p['id'] ?>" 
                                   onclick="return confirmDelete()" class="btn btn-sm btn-danger">🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php
}

// Formulário
if ($action === 'new' || $action === 'edit') {
    $proc = null;
    if ($action === 'edit' && $id) {
        $stmt = $db->prepare("SELECT * FROM procedimentos WHERE id = ?");
        $stmt->execute([$id]);
        $proc = $stmt->fetch();
    }
    ?>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= $action === 'edit' ? 'Editar' : 'Novo' ?> Procedimento</h3>
            <a href="?" class="btn btn-secondary">← Voltar</a>
        </div>
        
        <form method="POST" action="?action=save<?= $id ? "&id=$id" : '' ?>">
            <div class="form-row">
                <div class="form-group">
                    <label for="codigo">Código</label>
                    <input type="text" id="codigo" name="codigo" value="<?= $proc['codigo'] ?? '' ?>">
                </div>
                
                <div class="form-group" style="grid-column: span 3;">
                    <label for="nome">Nome *</label>
                    <input type="text" id="nome" name="nome" required value="<?= $proc['nome'] ?? '' ?>">
                </div>
                
                <div class="form-group" style="grid-column: span 4;">
                    <label for="descricao">Descrição</label>
                    <textarea id="descricao" name="descricao" rows="2"><?= $proc['descricao'] ?? '' ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="valor_particular">Valor Particular *</label>
                    <input type="text" id="valor_particular" name="valor_particular" required 
                           value="<?= $proc['valor_particular'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="valor_convenio">Valor Convênio</label>
                    <input type="text" id="valor_convenio" name="valor_convenio" 
                           value="<?= $proc['valor_convenio'] ?? '' ?>">
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">💾 Salvar</button>
                <a href="?" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
    
    <?php
}

require_once '../includes/footer.php';
?>
