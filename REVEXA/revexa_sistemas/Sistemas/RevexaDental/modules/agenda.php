<?php
$page_title = 'Agenda';
require_once '../includes/header.php';

$db = get_db();
$action = $_GET['action'] ?? 'calendar';
$id = $_GET['id'] ?? null;

// SALVAR Agendamento
if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'paciente_id' => $_POST['paciente_id'],
        'dentista_id' => $_POST['dentista_id'],
        'data_agendamento' => $_POST['data_agendamento'],
        'hora_inicio' => $_POST['hora_inicio'],
        'hora_fim' => $_POST['hora_fim'],
        'procedimento_id' => $_POST['procedimento_id'] ?? null,
        'status' => $_POST['status'] ?? 'agendado',
        'observacoes' => sanitize_input($_POST['observacoes'] ?? '')
    ];
    
    if ($id) {
        // Atualizar
        $stmt = $db->prepare("
            UPDATE agendamentos SET 
                paciente_id=?, dentista_id=?, data_agendamento=?, hora_inicio=?, hora_fim=?,
                procedimento_id=?, status=?, observacoes=?
            WHERE id=?
        ");
        
        $stmt->execute([
            $data['paciente_id'], $data['dentista_id'], $data['data_agendamento'],
            $data['hora_inicio'], $data['hora_fim'], $data['procedimento_id'],
            $data['status'], $data['observacoes'], $id
        ]);
        
        log_audit('Agendamento atualizado', 'agendamentos', $id);
        show_alert('Agendamento atualizado com sucesso!', 'success');
    } else {
        // Inserir
        $stmt = $db->prepare("
            INSERT INTO agendamentos (
                paciente_id, dentista_id, data_agendamento, hora_inicio, hora_fim,
                procedimento_id, status, observacoes, criado_por
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['paciente_id'], $data['dentista_id'], $data['data_agendamento'],
            $data['hora_inicio'], $data['hora_fim'], $data['procedimento_id'],
            $data['status'], $data['observacoes'], $_SESSION['user_id']
        ]);
        
        log_audit('Agendamento criado', 'agendamentos', $db->lastInsertId());
        show_alert('Agendamento criado com sucesso!', 'success');
    }
    
    redirect('modules/agenda.php');
}

// DELETAR Agendamento
if ($action === 'delete' && $id) {
    $stmt = $db->prepare("UPDATE agendamentos SET status = 'cancelado' WHERE id = ?");
    $stmt->execute([$id]);
    
    log_audit('Agendamento cancelado', 'agendamentos', $id);
    show_alert('Agendamento cancelado!', 'success');
    redirect('modules/agenda.php');
}

// ATUALIZAR STATUS
if ($action === 'update_status' && $id && isset($_GET['status'])) {
    $stmt = $db->prepare("UPDATE agendamentos SET status = ? WHERE id = ?");
    $stmt->execute([$_GET['status'], $id]);
    
    log_audit('Status do agendamento atualizado', 'agendamentos', $id);
    show_alert('Status atualizado!', 'success');
    redirect('modules/agenda.php');
}

// VISUALIZAÇÃO CALENDÁRIO
if ($action === 'calendar') {
    $data_selecionada = $_GET['data'] ?? date('Y-m-d');
    $dentista_filtro = $_GET['dentista'] ?? '';
    
    // Buscar dentistas para filtro
    $stmt = $db->query("SELECT id, nome FROM usuarios WHERE perfil IN ('dentista', 'admin') AND ativo = 1 ORDER BY nome");
    $dentistas = $stmt->fetchAll();
    
    // Buscar agendamentos do dia
    $where = "WHERE a.data_agendamento = ?";
    $params = [$data_selecionada];
    
    if ($dentista_filtro) {
        $where .= " AND a.dentista_id = ?";
        $params[] = $dentista_filtro;
    }
    
    $stmt = $db->prepare("
        SELECT a.*, 
            p.nome as paciente_nome, 
            p.telefone as paciente_telefone,
            p.celular as paciente_celular,
            u.nome as dentista_nome,
            pr.nome as procedimento_nome
        FROM agendamentos a
        LEFT JOIN pacientes p ON a.paciente_id = p.id
        LEFT JOIN usuarios u ON a.dentista_id = u.id
        LEFT JOIN procedimentos pr ON a.procedimento_id = pr.id
        $where
        ORDER BY a.hora_inicio
    ");
    $stmt->execute($params);
    $agendamentos = $stmt->fetchAll();
    
    // Estatísticas do dia
    $total = count($agendamentos);
    $confirmados = count(array_filter($agendamentos, fn($a) => $a['status'] === 'confirmado'));
    $realizados = count(array_filter($agendamentos, fn($a) => $a['status'] === 'realizado'));
    $cancelados = count(array_filter($agendamentos, fn($a) => $a['status'] === 'cancelado'));
    ?>
    
    <div class="mb-2">
        <div class="d-flex justify-between align-center">
            <div class="d-flex gap-1">
                <input type="date" id="data_agenda" value="<?= $data_selecionada ?>" 
                       onchange="window.location.href='?data=' + this.value">
                
                <select id="dentista_filtro" onchange="window.location.href='?data=<?= $data_selecionada ?>&dentista=' + this.value">
                    <option value="">Todos os dentistas</option>
                    <?php foreach ($dentistas as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= $dentista_filtro == $d['id'] ? 'selected' : '' ?>>
                            <?= $d['nome'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <a href="?action=new&data=<?= $data_selecionada ?>" class="btn btn-primary">➕ Novo Agendamento</a>
        </div>
    </div>
    
    <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
        <div class="stat-card card-blue">
            <div class="stat-icon">📅</div>
            <div class="stat-content">
                <div class="stat-value"><?= $total ?></div>
                <div class="stat-label">Total</div>
            </div>
        </div>
        
        <div class="stat-card card-green">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
                <div class="stat-value"><?= $confirmados ?></div>
                <div class="stat-label">Confirmados</div>
            </div>
        </div>
        
        <div class="stat-card card-orange">
            <div class="stat-icon">✔️</div>
            <div class="stat-content">
                <div class="stat-value"><?= $realizados ?></div>
                <div class="stat-label">Realizados</div>
            </div>
        </div>
        
        <div class="stat-card card-red">
            <div class="stat-icon">❌</div>
            <div class="stat-content">
                <div class="stat-value"><?= $cancelados ?></div>
                <div class="stat-label">Cancelados</div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Agendamentos - <?= format_date($data_selecionada) ?></h2>
        </div>
        
        <?php if (count($agendamentos) > 0): ?>
            <div class="agenda-timeline">
                <?php foreach ($agendamentos as $ag): ?>
                    <div class="agenda-card status-<?= $ag['status'] ?>">
                        <div class="agenda-time-col">
                            <div class="time-badge"><?= substr($ag['hora_inicio'], 0, 5) ?></div>
                            <div class="time-separator">até</div>
                            <div class="time-badge"><?= substr($ag['hora_fim'], 0, 5) ?></div>
                        </div>
                        
                        <div class="agenda-content">
                            <div class="agenda-header">
                                <h3><?= $ag['paciente_nome'] ?></h3>
                                <div class="status-badge status-<?= $ag['status'] ?>">
                                    <?php
                                    $status_labels = [
                                        'agendado' => 'Agendado',
                                        'confirmado' => 'Confirmado',
                                        'em_atendimento' => 'Em Atendimento',
                                        'realizado' => 'Realizado',
                                        'cancelado' => 'Cancelado',
                                        'faltou' => 'Faltou'
                                    ];
                                    echo $status_labels[$ag['status']] ?? $ag['status'];
                                    ?>
                                </div>
                            </div>
                            
                            <div class="agenda-details-row">
                                <span>👨‍⚕️ <?= $ag['dentista_nome'] ?></span>
                                <?php if ($ag['procedimento_nome']): ?>
                                    <span>🔧 <?= $ag['procedimento_nome'] ?></span>
                                <?php endif; ?>
                                <?php if ($ag['paciente_celular'] || $ag['paciente_telefone']): ?>
                                    <span>📱 <?= $ag['paciente_celular'] ?: $ag['paciente_telefone'] ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($ag['observacoes']): ?>
                                <div class="agenda-obs">
                                    <strong>Obs:</strong> <?= $ag['observacoes'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="agenda-actions">
                            <?php if ($ag['status'] !== 'realizado' && $ag['status'] !== 'cancelado'): ?>
                                <a href="?action=update_status&id=<?= $ag['id'] ?>&status=confirmado" 
                                   class="btn btn-sm btn-success" title="Confirmar">✅</a>
                                <a href="?action=update_status&id=<?= $ag['id'] ?>&status=em_atendimento" 
                                   class="btn btn-sm btn-warning" title="Em Atendimento">🕐</a>
                                <a href="?action=update_status&id=<?= $ag['id'] ?>&status=realizado" 
                                   class="btn btn-sm btn-info" title="Realizado">✔️</a>
                            <?php endif; ?>
                            
                            <a href="../modules/prontuario.php?id=<?= $ag['paciente_id'] ?>" 
                               class="btn btn-sm btn-primary" title="Prontuário">📋</a>
                            <a href="?action=edit&id=<?= $ag['id'] ?>" 
                               class="btn btn-sm btn-secondary" title="Editar">✏️</a>
                            
                            <?php if ($ag['status'] !== 'cancelado'): ?>
                                <a href="?action=delete&id=<?= $ag['id'] ?>" 
                                   onclick="return confirmDelete('Cancelar este agendamento?')" 
                                   class="btn btn-sm btn-danger" title="Cancelar">❌</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>Nenhum agendamento para esta data.</p>
                <a href="?action=new&data=<?= $data_selecionada ?>" class="btn btn-primary mt-2">
                    Criar Primeiro Agendamento
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <style>
    .agenda-timeline {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .agenda-card {
        display: flex;
        gap: 20px;
        padding: 20px;
        background: var(--bg-color);
        border-radius: 8px;
        border-left: 4px solid var(--border-color);
    }
    
    .agenda-card.status-confirmado { border-left-color: var(--success-color); }
    .agenda-card.status-em_atendimento { border-left-color: var(--warning-color); }
    .agenda-card.status-realizado { border-left-color: var(--info-color); }
    .agenda-card.status-cancelado { opacity: 0.6; }
    
    .agenda-time-col {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
        min-width: 70px;
    }
    
    .time-badge {
        background: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 14px;
        color: var(--primary-color);
    }
    
    .time-separator {
        font-size: 10px;
        color: var(--text-muted);
    }
    
    .agenda-content {
        flex: 1;
    }
    
    .agenda-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .agenda-header h3 {
        font-size: 16px;
        margin: 0;
    }
    
    .status-badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .status-badge.status-agendado { background: #e0e7ff; color: #3730a3; }
    .status-badge.status-confirmado { background: #d1fae5; color: #065f46; }
    .status-badge.status-em_atendimento { background: #fef3c7; color: #92400e; }
    .status-badge.status-realizado { background: #dbeafe; color: #1e40af; }
    .status-badge.status-cancelado { background: #fee2e2; color: #991b1b; }
    
    .agenda-details-row {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 8px;
    }
    
    .agenda-obs {
        font-size: 13px;
        padding: 8px;
        background: white;
        border-radius: 4px;
        margin-top: 8px;
    }
    
    .agenda-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    @media (max-width: 768px) {
        .agenda-card {
            flex-direction: column;
        }
        
        .agenda-time-col {
            flex-direction: row;
            width: 100%;
        }
        
        .agenda-actions {
            flex-direction: row;
            flex-wrap: wrap;
        }
    }
    </style>
    
    <?php
}

// FORMULÁRIO Novo/Editar
if ($action === 'new' || $action === 'edit') {
    $agendamento = null;
    $data_padrao = $_GET['data'] ?? date('Y-m-d');
    
    if ($action === 'edit' && $id) {
        $stmt = $db->prepare("SELECT * FROM agendamentos WHERE id = ?");
        $stmt->execute([$id]);
        $agendamento = $stmt->fetch();
    }
    
    // Buscar pacientes
    $stmt = $db->query("SELECT id, nome FROM pacientes WHERE ativo = 1 ORDER BY nome");
    $pacientes = $stmt->fetchAll();
    
    // Buscar dentistas
    $stmt = $db->query("SELECT id, nome FROM usuarios WHERE perfil IN ('dentista', 'admin') AND ativo = 1 ORDER BY nome");
    $dentistas = $stmt->fetchAll();
    
    // Buscar procedimentos
    $stmt = $db->query("SELECT id, nome FROM procedimentos WHERE ativo = 1 ORDER BY nome");
    $procedimentos = $stmt->fetchAll();
    ?>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><?= $action === 'edit' ? 'Editar Agendamento' : 'Novo Agendamento' ?></h2>
            <a href="?" class="btn btn-secondary">← Voltar</a>
        </div>
        
        <form method="POST" action="?action=save<?= $id ? "&id=$id" : '' ?>">
            <div class="form-row">
                <div class="form-group" style="grid-column: span 2;">
                    <label for="paciente_id">Paciente *</label>
                    <select id="paciente_id" name="paciente_id" required>
                        <option value="">Selecione um paciente</option>
                        <?php foreach ($pacientes as $p): ?>
                            <option value="<?= $p['id'] ?>" 
                                <?= ($agendamento['paciente_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                <?= $p['nome'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group" style="grid-column: span 2;">
                    <label for="dentista_id">Dentista *</label>
                    <select id="dentista_id" name="dentista_id" required>
                        <option value="">Selecione um dentista</option>
                        <?php foreach ($dentistas as $d): ?>
                            <option value="<?= $d['id'] ?>" 
                                <?= ($agendamento['dentista_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                                <?= $d['nome'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="data_agendamento">Data *</label>
                    <input type="date" id="data_agendamento" name="data_agendamento" required
                           value="<?= $agendamento['data_agendamento'] ?? $data_padrao ?>">
                </div>
                
                <div class="form-group">
                    <label for="hora_inicio">Hora Início *</label>
                    <input type="time" id="hora_inicio" name="hora_inicio" required
                           value="<?= $agendamento['hora_inicio'] ?? '08:00' ?>">
                </div>
                
                <div class="form-group">
                    <label for="hora_fim">Hora Fim *</label>
                    <input type="time" id="hora_fim" name="hora_fim" required
                           value="<?= $agendamento['hora_fim'] ?? '09:00' ?>">
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="agendado" <?= ($agendamento['status'] ?? 'agendado') === 'agendado' ? 'selected' : '' ?>>Agendado</option>
                        <option value="confirmado" <?= ($agendamento['status'] ?? '') === 'confirmado' ? 'selected' : '' ?>>Confirmado</option>
                        <option value="em_atendimento" <?= ($agendamento['status'] ?? '') === 'em_atendimento' ? 'selected' : '' ?>>Em Atendimento</option>
                        <option value="realizado" <?= ($agendamento['status'] ?? '') === 'realizado' ? 'selected' : '' ?>>Realizado</option>
                        <option value="cancelado" <?= ($agendamento['status'] ?? '') === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                        <option value="faltou" <?= ($agendamento['status'] ?? '') === 'faltou' ? 'selected' : '' ?>>Faltou</option>
                    </select>
                </div>
                
                <div class="form-group" style="grid-column: span 4;">
                    <label for="procedimento_id">Procedimento</label>
                    <select id="procedimento_id" name="procedimento_id">
                        <option value="">Selecione (opcional)</option>
                        <?php foreach ($procedimentos as $pr): ?>
                            <option value="<?= $pr['id'] ?>" 
                                <?= ($agendamento['procedimento_id'] ?? '') == $pr['id'] ? 'selected' : '' ?>>
                                <?= $pr['nome'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="observacoes">Observações</label>
                <textarea id="observacoes" name="observacoes" rows="3"><?= $agendamento['observacoes'] ?? '' ?></textarea>
            </div>
            
            <div class="mt-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary">💾 Salvar</button>
                <a href="?" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
    
    <?php
}

require_once '../includes/footer.php';
?>
