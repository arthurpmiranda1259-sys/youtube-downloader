<?php
$page_title = 'Usuários';
require_once '../includes/header.php';

if (!has_permission('admin')) {
    show_alert('Acesso negado!', 'error');
    redirect('dashboard.php');
}

$db = get_db();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Salvar
if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nome' => sanitize_input($_POST['nome']),
        'email' => sanitize_input($_POST['email']),
        'perfil' => $_POST['perfil']
    ];
    
    if ($id) {
        // Atualizar
        if (!empty($_POST['senha'])) {
            $data['senha'] = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE usuarios SET nome=?, email=?, perfil=?, senha=? WHERE id=?");
            $stmt->execute([$data['nome'], $data['email'], $data['perfil'], $data['senha'], $id]);
        } else {
            $stmt = $db->prepare("UPDATE usuarios SET nome=?, email=?, perfil=? WHERE id=?");
            $stmt->execute([$data['nome'], $data['email'], $data['perfil'], $id]);
        }
        show_alert('Usuário atualizado!', 'success');
    } else {
        // Inserir
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO usuarios (nome, email, senha, perfil) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['nome'], $data['email'], $senha, $data['perfil']]);
        show_alert('Usuário criado!', 'success');
    }
    
    redirect('modules/usuarios.php');
}

// Deletar
if ($action === 'delete' && $id) {
    if ($id == 1) {
        show_alert('Não é possível excluir o usuário administrador padrão!', 'error');
    } else {
        $stmt = $db->prepare("UPDATE usuarios SET ativo = 0 WHERE id = ?");
        $stmt->execute([$id]);
        show_alert('Usuário inativado!', 'success');
    }
    redirect('modules/usuarios.php');
}

// Listar
if ($action === 'list') {
    $stmt = $db->query("SELECT * FROM usuarios WHERE ativo = 1 ORDER BY nome");
    $usuarios = $stmt->fetchAll();
    ?>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Usuários do Sistema</h3>
            <a href="?action=new" class="btn btn-primary">➕ Novo Usuário</a>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Perfil</th>
                        <th>Último Acesso</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?= $u['nome'] ?></td>
                            <td><?= $u['email'] ?></td>
                            <td>
                                <span class="badge badge-<?= $u['perfil'] === 'admin' ? 'danger' : 'info' ?>">
                                    <?= ucfirst($u['perfil']) ?>
                                </span>
                            </td>
                            <td><?= format_datetime($u['ultimo_acesso']) ?></td>
                            <td>
                                <a href="?action=edit&id=<?= $u['id'] ?>" class="btn btn-sm btn-secondary">✏️</a>
                                <?php if ($u['id'] != 1): ?>
                                    <a href="?action=delete&id=<?= $u['id'] ?>" 
                                       onclick="return confirmDelete()" class="btn btn-sm btn-danger">🗑️</a>
                                <?php endif; ?>
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
    $usuario = null;
    if ($action === 'edit' && $id) {
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch();
    }
    ?>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= $action === 'edit' ? 'Editar' : 'Novo' ?> Usuário</h3>
            <a href="?" class="btn btn-secondary">← Voltar</a>
        </div>
        
        <form method="POST" action="?action=save<?= $id ? "&id=$id" : '' ?>">
            <div class="form-row">
                <div class="form-group" style="grid-column: span 2;">
                    <label for="nome">Nome Completo *</label>
                    <input type="text" id="nome" name="nome" required value="<?= $usuario['nome'] ?? '' ?>">
                </div>
                
                <div class="form-group" style="grid-column: span 2;">
                    <label for="email">E-mail *</label>
                    <input type="email" id="email" name="email" required value="<?= $usuario['email'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="perfil">Perfil *</label>
                    <select id="perfil" name="perfil" required>
                        <option value="recepcionista" <?= ($usuario['perfil'] ?? '') === 'recepcionista' ? 'selected' : '' ?>>Recepcionista</option>
                        <option value="dentista" <?= ($usuario['perfil'] ?? '') === 'dentista' ? 'selected' : '' ?>>Dentista</option>
                        <option value="admin" <?= ($usuario['perfil'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha <?= $action === 'edit' ? '(deixe em branco para não alterar)' : '*' ?></label>
                    <input type="password" id="senha" name="senha" <?= $action === 'new' ? 'required' : '' ?>>
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
