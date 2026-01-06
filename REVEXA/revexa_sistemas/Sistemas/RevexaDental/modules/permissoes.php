<?php
$page_title = 'Gerenciar Permissões';
require_once __DIR__ . '/../includes/header.php';

// Verificar se é admin
if (!has_permission('admin')) {
    show_alert('Acesso negado! Apenas administradores podem gerenciar permissões.', 'error');
    redirect('dashboard.php');
}

$db = get_db();

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'criar_perfil') {
        $nome = sanitize_input($_POST['nome']);
        $descricao = sanitize_input($_POST['descricao'] ?? '');
        $nivel = (int)($_POST['nivel_hierarquia'] ?? 1);
        $cor = sanitize_input($_POST['cor'] ?? '#64748b');
        
        try {
            $stmt = $db->prepare("INSERT INTO perfis (nome, descricao, nivel_hierarquia, cor) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $descricao, $nivel, $cor]);
            show_alert('Perfil criado com sucesso!', 'success');
        } catch (PDOException $e) {
            show_alert('Erro ao criar perfil: ' . $e->getMessage(), 'error');
        }
    }
    
    elseif ($action === 'atualizar_permissoes') {
        $perfil_id = (int)$_POST['perfil_id'];
        
        // Limpar permissões existentes
        $stmt = $db->prepare("DELETE FROM permissoes WHERE perfil_id = ?");
        $stmt->execute([$perfil_id]);
        
        // Inserir novas permissões
        $stmt = $db->prepare("
            INSERT INTO permissoes (perfil_id, modulo_id, pode_visualizar, pode_criar, pode_editar, pode_excluir) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($_POST['modulos'] ?? [] as $modulo_id => $perms) {
            $visualizar = isset($perms['visualizar']) ? 1 : 0;
            $criar = isset($perms['criar']) ? 1 : 0;
            $editar = isset($perms['editar']) ? 1 : 0;
            $excluir = isset($perms['excluir']) ? 1 : 0;
            
            // Só inserir se tiver pelo menos uma permissão marcada
            if ($visualizar || $criar || $editar || $excluir) {
                $stmt->execute([$perfil_id, $modulo_id, $visualizar, $criar, $editar, $excluir]);
            }
        }
        
        show_alert('Permissões atualizadas com sucesso!', 'success');
        log_audit('Atualização de permissões', 'permissoes', $perfil_id);
    }
    
    elseif ($action === 'excluir_perfil') {
        $perfil_id = (int)$_POST['perfil_id'];
        
        // Verificar se não é perfil padrão
        if ($perfil_id <= 3) {
            show_alert('Não é possível excluir perfis padrão do sistema!', 'error');
        } else {
            // Verificar se há usuários com este perfil
            $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE perfil_id = ?");
            $stmt->execute([$perfil_id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                show_alert("Não é possível excluir! Existem $count usuário(s) com este perfil.", 'error');
            } else {
                $stmt = $db->prepare("DELETE FROM permissoes WHERE perfil_id = ?");
                $stmt->execute([$perfil_id]);
                
                $stmt = $db->prepare("DELETE FROM perfis WHERE id = ?");
                $stmt->execute([$perfil_id]);
                
                show_alert('Perfil excluído com sucesso!', 'success');
                log_audit('Exclusão de perfil', 'perfis', $perfil_id);
            }
        }
    }
}

// Buscar perfis
$perfis = $db->query("SELECT * FROM perfis WHERE ativo = 1 ORDER BY nivel_hierarquia DESC")->fetchAll();

// Buscar módulos
$modulos = $db->query("SELECT * FROM modulos ORDER BY ordem")->fetchAll();

// Perfil selecionado
$perfil_selecionado = isset($_GET['perfil']) ? (int)$_GET['perfil'] : ($perfis[0]['id'] ?? 1);

// Buscar permissões do perfil selecionado
$stmt = $db->prepare("SELECT * FROM permissoes WHERE perfil_id = ?");
$stmt->execute([$perfil_selecionado]);
$permissoes_ativas = [];
foreach ($stmt->fetchAll() as $perm) {
    $permissoes_ativas[$perm['modulo_id']] = $perm;
}
?>

<style>
.permissions-container {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 30px;
    margin-top: 25px;
}

.perfis-sidebar {
    background: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 8px 20px rgba(212, 175, 55, 0.12);
    height: fit-content;
    border: 1px solid rgba(212, 175, 55, 0.1);
}

.perfis-sidebar h3 {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 20px;
    color: var(--text-color);
}

.perfil-item {
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 12px;
    border: 2px solid transparent;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.perfil-item:hover {
    background: linear-gradient(90deg, rgba(244, 228, 183, 0.2) 0%, rgba(244, 228, 183, 0.05) 100%);
}

.perfil-item.active {
    background: linear-gradient(90deg, rgba(244, 228, 183, 0.4) 0%, rgba(244, 228, 183, 0.1) 100%);
    border-color: var(--primary-gold);
}

.perfil-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.permissions-content {
    background: white;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 8px 20px rgba(212, 175, 55, 0.12);
    border: 1px solid rgba(212, 175, 55, 0.1);
}

.permissions-table {
    margin-top: 25px;
}

.permissions-table table {
    width: 100%;
}

.permission-checkbox {
    width: 20px;
    height: 20px;
    cursor: pointer;
    accent-color: var(--primary-gold);
}

.modulo-row {
    transition: all 0.2s;
}

.modulo-row:hover {
    background: linear-gradient(90deg, rgba(244, 228, 183, 0.15) 0%, rgba(244, 228, 183, 0.05) 100%);
}

.modulo-icon {
    font-size: 24px;
    margin-right: 12px;
}

.nivel-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.2) 0%, rgba(212, 175, 55, 0.1) 100%);
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 16px;
    padding: 35px;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 16px 40px rgba(212, 175, 55, 0.16);
    animation: fadeIn 0.3s ease-out;
}

.color-picker-options {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 10px;
    margin-top: 10px;
}

.color-option {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    border: 3px solid transparent;
    cursor: pointer;
    transition: all 0.2s;
}

.color-option:hover {
    transform: scale(1.1);
}

.color-option.selected {
    border-color: var(--primary-gold);
    box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.3);
}

@media (max-width: 968px) {
    .permissions-container {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="content-wrapper">
    <div class="d-flex justify-between align-center mb-3">
        <div>
            <h2 style="font-size: 28px; font-weight: 700; margin-bottom: 8px;">🔐 Gerenciar Permissões</h2>
            <p class="text-muted">Configure perfis de acesso e permissões personalizadas</p>
        </div>
        <button class="btn btn-primary" onclick="openModalNovoPerfil()">
            ➕ Novo Perfil
        </button>
    </div>

    <div class="permissions-container">
        <!-- Sidebar de Perfis -->
        <div class="perfis-sidebar">
            <h3>Perfis de Acesso</h3>
            <?php foreach ($perfis as $perfil): ?>
                <a href="?perfil=<?= $perfil['id'] ?>" style="text-decoration: none; color: inherit;">
                    <div class="perfil-item <?= $perfil['id'] == $perfil_selecionado ? 'active' : '' ?>">
                        <div>
                            <div style="font-weight: 600; margin-bottom: 4px;"><?= htmlspecialchars($perfil['nome']) ?></div>
                            <div class="nivel-badge">
                                <span>Nível <?= $perfil['nivel_hierarquia'] ?></span>
                            </div>
                        </div>
                        <div class="perfil-badge" style="background-color: <?= $perfil['cor'] ?>20; color: <?= $perfil['cor'] ?>;">
                            <?= substr($perfil['nome'], 0, 1) ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Conteúdo de Permissões -->
        <div class="permissions-content">
            <?php
            $perfil_atual = array_filter($perfis, fn($p) => $p['id'] == $perfil_selecionado)[0] ?? null;
            if ($perfil_atual):
            ?>
                <div class="d-flex justify-between align-center" style="margin-bottom: 25px;">
                    <div>
                        <h2 style="font-size: 22px; font-weight: 700; margin-bottom: 6px;">
                            <?= htmlspecialchars($perfil_atual['nome']) ?>
                        </h2>
                        <p class="text-muted"><?= htmlspecialchars($perfil_atual['descricao']) ?></p>
                    </div>
                    <?php if ($perfil_atual['id'] > 3): ?>
                        <button class="btn btn-danger btn-sm" onclick="confirmarExclusao(<?= $perfil_atual['id'] ?>)">
                            🗑️ Excluir Perfil
                        </button>
                    <?php endif; ?>
                </div>

                <form method="POST" class="permissions-table">
                    <input type="hidden" name="action" value="atualizar_permissoes">
                    <input type="hidden" name="perfil_id" value="<?= $perfil_selecionado ?>">

                    <table>
                        <thead>
                            <tr>
                                <th style="width: 40%;">Módulo</th>
                                <th class="text-center">Visualizar</th>
                                <th class="text-center">Criar</th>
                                <th class="text-center">Editar</th>
                                <th class="text-center">Excluir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modulos as $modulo): 
                                $perm = $permissoes_ativas[$modulo['id']] ?? null;
                            ?>
                                <tr class="modulo-row">
                                    <td>
                                        <span class="modulo-icon"><?= $modulo['icone'] ?></span>
                                        <strong><?= $modulo['nome'] ?></strong>
                                        <br>
                                        <small class="text-muted"><?= $modulo['descricao'] ?></small>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" class="permission-checkbox" 
                                               name="modulos[<?= $modulo['id'] ?>][visualizar]"
                                               <?= $perm && $perm['pode_visualizar'] ? 'checked' : '' ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" class="permission-checkbox"
                                               name="modulos[<?= $modulo['id'] ?>][criar]"
                                               <?= $perm && $perm['pode_criar'] ? 'checked' : '' ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" class="permission-checkbox"
                                               name="modulos[<?= $modulo['id'] ?>][editar]"
                                               <?= $perm && $perm['pode_editar'] ? 'checked' : '' ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" class="permission-checkbox"
                                               name="modulos[<?= $modulo['id'] ?>][excluir]"
                                               <?= $perm && $perm['pode_excluir'] ? 'checked' : '' ?>>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div style="margin-top: 30px; text-align: right;">
                        <button type="submit" class="btn btn-primary btn-lg">
                            💾 Salvar Permissões
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Novo Perfil -->
<div id="modalNovoPerfil" class="modal">
    <div class="modal-content">
        <h2 style="font-size: 22px; font-weight: 700; margin-bottom: 25px;">➕ Criar Novo Perfil</h2>
        
        <form method="POST">
            <input type="hidden" name="action" value="criar_perfil">
            
            <div class="form-group">
                <label>Nome do Perfil</label>
                <input type="text" name="nome" required placeholder="Ex: Auxiliar, Gerente...">
            </div>
            
            <div class="form-group">
                <label>Descrição</label>
                <textarea name="descricao" placeholder="Descreva as responsabilidades deste perfil"></textarea>
            </div>
            
            <div class="form-group">
                <label>Nível Hierárquico (1-10)</label>
                <input type="number" name="nivel_hierarquia" min="1" max="10" value="5" required>
                <small class="text-muted">Quanto maior, mais autoridade (Admin = 10)</small>
            </div>
            
            <div class="form-group">
                <label>Cor do Perfil</label>
                <input type="hidden" name="cor" id="corSelecionada" value="#64748b">
                <div class="color-picker-options">
                    <div class="color-option selected" style="background: #64748b;" onclick="selecionarCor('#64748b', this)"></div>
                    <div class="color-option" style="background: #D4AF37;" onclick="selecionarCor('#D4AF37', this)"></div>
                    <div class="color-option" style="background: #10b981;" onclick="selecionarCor('#10b981', this)"></div>
                    <div class="color-option" style="background: #3b82f6;" onclick="selecionarCor('#3b82f6', this)"></div>
                    <div class="color-option" style="background: #8b5cf6;" onclick="selecionarCor('#8b5cf6', this)"></div>
                    <div class="color-option" style="background: #ef4444;" onclick="selecionarCor('#ef4444', this)"></div>
                    <div class="color-option" style="background: #f59e0b;" onclick="selecionarCor('#f59e0b', this)"></div>
                    <div class="color-option" style="background: #ec4899;" onclick="selecionarCor('#ec4899', this)"></div>
                </div>
            </div>
            
            <div class="d-flex gap-2" style="margin-top: 30px;">
                <button type="button" class="btn btn-secondary" onclick="closeModalNovoPerfil()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Criar Perfil</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalNovoPerfil() {
    document.getElementById('modalNovoPerfil').classList.add('active');
}

function closeModalNovoPerfil() {
    document.getElementById('modalNovoPerfil').classList.remove('active');
}

function selecionarCor(cor, elemento) {
    document.querySelectorAll('.color-option').forEach(el => el.classList.remove('selected'));
    elemento.classList.add('selected');
    document.getElementById('corSelecionada').value = cor;
}

function confirmarExclusao(perfilId) {
    if (confirm('Tem certeza que deseja excluir este perfil? Esta ação não pode ser desfeita.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="excluir_perfil">
            <input type="hidden" name="perfil_id" value="${perfilId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Fechar modal ao clicar fora
document.getElementById('modalNovoPerfil').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModalNovoPerfil();
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
