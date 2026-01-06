<?php
$page_title = 'Financeiro';
require_once '../includes/header.php';

$db = get_db();
$tab = $_GET['tab'] ?? 'receber';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// CONTAS A RECEBER
if ($tab === 'receber') {
    // Salvar
    if ($action === 'save_receber' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'paciente_id' => $_POST['paciente_id'],
            'descricao' => sanitize_input($_POST['descricao']),
            'valor' => floatval(str_replace(',', '.', $_POST['valor'])),
            'data_vencimento' => $_POST['data_vencimento'],
            'status' => $_POST['status'] ?? 'pendente'
        ];
        
        if ($_POST['status'] === 'pago') {
            $data['data_pagamento'] = $_POST['data_pagamento'];
            $data['forma_pagamento'] = $_POST['forma_pagamento'];
            $data['valor_pago'] = $data['valor'];
        }
        
        if ($id) {
            $stmt = $db->prepare("
                UPDATE contas_receber SET paciente_id=?, descricao=?, valor=?, data_vencimento=?,
                data_pagamento=?, forma_pagamento=?, valor_pago=?, status=?
                WHERE id=?
            ");
            $stmt->execute([
                $data['paciente_id'], $data['descricao'], $data['valor'], $data['data_vencimento'],
                $data['data_pagamento'] ?? null, $data['forma_pagamento'] ?? null,
                $data['valor_pago'] ?? 0, $data['status'], $id
            ]);
            show_alert('Conta atualizada!', 'success');
        } else {
            $stmt = $db->prepare("
                INSERT INTO contas_receber (paciente_id, descricao, valor, data_vencimento,
                data_pagamento, forma_pagamento, valor_pago, status, criado_por)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['paciente_id'], $data['descricao'], $data['valor'], $data['data_vencimento'],
                $data['data_pagamento'] ?? null, $data['forma_pagamento'] ?? null,
                $data['valor_pago'] ?? 0, $data['status'], $_SESSION['user_id']
            ]);
            show_alert('Conta criada!', 'success');
        }
        
        redirect('modules/financeiro.php?tab=receber');
    }
    
    // Deletar
    if ($action === 'delete_receber' && $id) {
        $stmt = $db->prepare("DELETE FROM contas_receber WHERE id = ?");
        $stmt->execute([$id]);
        show_alert('Conta excluída!', 'success');
        redirect('modules/financeiro.php?tab=receber');
    }
    
    // Listar
    if ($action === 'list') {
        $filtro = $_GET['filtro'] ?? 'todas';
        
        $where = "WHERE 1=1";
        if ($filtro === 'pendentes') $where .= " AND cr.status = 'pendente'";
        if ($filtro === 'pagas') $where .= " AND cr.status = 'pago'";
        if ($filtro === 'vencidas') $where .= " AND cr.status = 'pendente' AND cr.data_vencimento < '" . date('Y-m-d') . "'";
        
        $stmt = $db->query("
            SELECT cr.*, p.nome as paciente_nome
            FROM contas_receber cr
            LEFT JOIN pacientes p ON cr.paciente_id = p.id
            $where
            ORDER BY cr.data_vencimento DESC
        ");
        $contas = $stmt->fetchAll();
        
        $total_pendente = array_sum(array_column(array_filter($contas, fn($c) => $c['status'] === 'pendente'), 'valor'));
        $total_recebido = array_sum(array_column(array_filter($contas, fn($c) => $c['status'] === 'pago'), 'valor_pago'));
        ?>
        
        <div class="stats-grid" style="grid-template-columns: repeat(2, 1fr);">
            <div class="stat-card card-orange">
                <div class="stat-icon">⏳</div>
                <div class="stat-content">
                    <div class="stat-value"><?= format_currency($total_pendente) ?></div>
                    <div class="stat-label">A Receber</div>
                </div>
            </div>
            
            <div class="stat-card card-green">
                <div class="stat-icon">💰</div>
                <div class="stat-content">
                    <div class="stat-value"><?= format_currency($total_recebido) ?></div>
                    <div class="stat-label">Recebido</div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Contas a Receber</h3>
                <a href="?tab=receber&action=new_receber" class="btn btn-primary">➕ Nova Conta</a>
            </div>
            
            <div class="mb-2">
                <div class="d-flex gap-1">
                    <a href="?tab=receber&filtro=todas" class="btn btn-sm <?= $filtro === 'todas' ? 'btn-primary' : 'btn-secondary' ?>">Todas</a>
                    <a href="?tab=receber&filtro=pendentes" class="btn btn-sm <?= $filtro === 'pendentes' ? 'btn-primary' : 'btn-secondary' ?>">Pendentes</a>
                    <a href="?tab=receber&filtro=vencidas" class="btn btn-sm <?= $filtro === 'vencidas' ? 'btn-primary' : 'btn-secondary' ?>">Vencidas</a>
                    <a href="?tab=receber&filtro=pagas" class="btn btn-sm <?= $filtro === 'pagas' ? 'btn-primary' : 'btn-secondary' ?>">Pagas</a>
                </div>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Descrição</th>
                            <th>Valor</th>
                            <th>Vencimento</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contas as $c): ?>
                            <tr>
                                <td><?= $c['paciente_nome'] ?></td>
                                <td><?= $c['descricao'] ?></td>
                                <td><?= format_currency($c['valor']) ?></td>
                                <td><?= format_date($c['data_vencimento']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $c['status'] === 'pago' ? 'success' : ($c['data_vencimento'] < date('Y-m-d') ? 'danger' : 'warning') ?>">
                                        <?= ucfirst($c['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?tab=receber&action=edit_receber&id=<?= $c['id'] ?>" class="btn btn-sm btn-secondary">✏️</a>
                                    <a href="?tab=receber&action=delete_receber&id=<?= $c['id'] ?>" 
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
    if ($action === 'new_receber' || $action === 'edit_receber') {
        $conta = null;
        if ($action === 'edit_receber' && $id) {
            $stmt = $db->prepare("SELECT * FROM contas_receber WHERE id = ?");
            $stmt->execute([$id]);
            $conta = $stmt->fetch();
        }
        
        $stmt = $db->query("SELECT id, nome FROM pacientes WHERE ativo = 1 ORDER BY nome");
        $pacientes = $stmt->fetchAll();
        ?>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?= $action === 'edit_receber' ? 'Editar' : 'Nova' ?> Conta a Receber</h3>
                <a href="?tab=receber" class="btn btn-secondary">← Voltar</a>
            </div>
            
            <form method="POST" action="?tab=receber&action=save_receber<?= $id ? "&id=$id" : '' ?>">
                <div class="form-row">
                    <div class="form-group" style="grid-column: span 2;">
                        <label for="paciente_id">Paciente *</label>
                        <select id="paciente_id" name="paciente_id" required>
                            <option value="">Selecione</option>
                            <?php foreach ($pacientes as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= ($conta['paciente_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                    <?= $p['nome'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="grid-column: span 2;">
                        <label for="descricao">Descrição *</label>
                        <input type="text" id="descricao" name="descricao" required value="<?= $conta['descricao'] ?? '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="valor">Valor *</label>
                        <input type="text" id="valor" name="valor" required value="<?= $conta['valor'] ?? '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="data_vencimento">Vencimento *</label>
                        <input type="date" id="data_vencimento" name="data_vencimento" required 
                               value="<?= $conta['data_vencimento'] ?? date('Y-m-d') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="pendente" <?= ($conta['status'] ?? 'pendente') === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                            <option value="pago" <?= ($conta['status'] ?? '') === 'pago' ? 'selected' : '' ?>>Pago</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="div_data_pagamento" style="display: <?= ($conta['status'] ?? '') === 'pago' ? 'block' : 'none' ?>;">
                        <label for="data_pagamento">Data Pagamento</label>
                        <input type="date" id="data_pagamento" name="data_pagamento" value="<?= $conta['data_pagamento'] ?? date('Y-m-d') ?>">
                    </div>
                    
                    <div class="form-group" id="div_forma_pagamento" style="display: <?= ($conta['status'] ?? '') === 'pago' ? 'block' : 'none' ?>;">
                        <label for="forma_pagamento">Forma Pagamento</label>
                        <select id="forma_pagamento" name="forma_pagamento">
                            <option value="dinheiro" <?= ($conta['forma_pagamento'] ?? '') === 'dinheiro' ? 'selected' : '' ?>>Dinheiro</option>
                            <option value="pix" <?= ($conta['forma_pagamento'] ?? '') === 'pix' ? 'selected' : '' ?>>PIX</option>
                            <option value="cartao_debito" <?= ($conta['forma_pagamento'] ?? '') === 'cartao_debito' ? 'selected' : '' ?>>Cartão Débito</option>
                            <option value="cartao_credito" <?= ($conta['forma_pagamento'] ?? '') === 'cartao_credito' ? 'selected' : '' ?>>Cartão Crédito</option>
                            <option value="transferencia" <?= ($conta['forma_pagamento'] ?? '') === 'transferencia' ? 'selected' : '' ?>>Transferência</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">💾 Salvar</button>
                    <a href="?tab=receber" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
        
        <script>
        document.getElementById('status').addEventListener('change', function() {
            const isPago = this.value === 'pago';
            document.getElementById('div_data_pagamento').style.display = isPago ? 'block' : 'none';
            document.getElementById('div_forma_pagamento').style.display = isPago ? 'block' : 'none';
        });
        </script>
        
        <?php
    }
}

// CONTAS A PAGAR (similar structure)
if ($tab === 'pagar') {
    echo '<div class="card"><div class="card-header"><h3>Contas a Pagar</h3></div>';
    echo '<div class="empty-state"><p>Funcionalidade similar a Contas a Receber - implemente conforme necessário</p></div>';
    echo '</div>';
}

require_once '../includes/footer.php';
?>

<div class="tabs mb-2">
    <a href="?tab=receber" class="tab <?= $tab === 'receber' ? 'active' : '' ?>">
        💵 Contas a Receber
    </a>
    <a href="?tab=pagar" class="tab <?= $tab === 'pagar' ? 'active' : '' ?>">
        📤 Contas a Pagar
    </a>
</div>
