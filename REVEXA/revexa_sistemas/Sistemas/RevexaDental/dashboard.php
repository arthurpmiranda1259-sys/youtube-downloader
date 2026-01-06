<?php
$page_title = 'Dashboard';
require_once 'includes/header.php';

$db = get_db();
$hoje = date('Y-m-d');

// Estatísticas
$stats = [];

// Agendamentos de hoje
$stmt = $db->prepare("
    SELECT COUNT(*) as total 
    FROM agendamentos 
    WHERE data_agendamento = ? AND status NOT IN ('cancelado', 'faltou')
");
$stmt->execute([$hoje]);
$stats['agendamentos_hoje'] = $stmt->fetch()['total'];

// Pacientes ativos
$stmt = $db->query("SELECT COUNT(*) as total FROM pacientes WHERE ativo = 1");
$stats['pacientes_ativos'] = $stmt->fetch()['total'];

// Contas a receber pendentes
$stmt = $db->query("SELECT COUNT(*) as total, SUM(valor - valor_pago) as valor_total FROM contas_receber WHERE status = 'pendente'");
$result = $stmt->fetch();
$stats['contas_pendentes'] = $result['total'];
$stats['valor_receber'] = $result['valor_total'] ?? 0;

// Faturamento do mês
$primeiro_dia_mes = date('Y-m-01');
$stmt = $db->prepare("
    SELECT SUM(valor_pago) as total 
    FROM contas_receber 
    WHERE data_pagamento >= ? AND status = 'pago'
");
$stmt->execute([$primeiro_dia_mes]);
$stats['faturamento_mes'] = $stmt->fetch()['total'] ?? 0;

// Próximos agendamentos
$stmt = $db->prepare("
    SELECT a.*, p.nome as paciente_nome, u.nome as dentista_nome, pr.nome as procedimento_nome
    FROM agendamentos a
    LEFT JOIN pacientes p ON a.paciente_id = p.id
    LEFT JOIN usuarios u ON a.dentista_id = u.id
    LEFT JOIN procedimentos pr ON a.procedimento_id = pr.id
    WHERE a.data_agendamento = ? 
    AND a.status NOT IN ('cancelado', 'faltou')
    ORDER BY a.hora_inicio
    LIMIT 10
");
$stmt->execute([$hoje]);
$proximos_agendamentos = $stmt->fetchAll();

// Contas a receber vencidas
$stmt = $db->prepare("
    SELECT cr.*, p.nome as paciente_nome
    FROM contas_receber cr
    LEFT JOIN pacientes p ON cr.paciente_id = p.id
    WHERE cr.status = 'pendente' 
    AND cr.data_vencimento < ?
    ORDER BY cr.data_vencimento DESC
    LIMIT 5
");
$stmt->execute([$hoje]);
$contas_vencidas = $stmt->fetchAll();
?>

<div class="dashboard">
    <!-- Cards de Estatísticas -->
    <div class="stats-grid">
        <div class="stat-card card-blue">
            <div class="stat-icon">📅</div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['agendamentos_hoje'] ?></div>
                <div class="stat-label">Agendamentos Hoje</div>
            </div>
        </div>
        
        <div class="stat-card card-green">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['pacientes_ativos'] ?></div>
                <div class="stat-label">Pacientes Ativos</div>
            </div>
        </div>
        
        <div class="stat-card card-orange">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <div class="stat-value"><?= format_currency($stats['faturamento_mes']) ?></div>
                <div class="stat-label">Faturamento do Mês</div>
            </div>
        </div>
        
        <div class="stat-card card-red">
            <div class="stat-icon">⚠️</div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['contas_pendentes'] ?></div>
                <div class="stat-label">Contas Pendentes</div>
            </div>
        </div>
    </div>
    
    <!-- Duas colunas -->
    <div class="dashboard-grid">
        <!-- Agendamentos de Hoje -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Agendamentos de Hoje</h2>
                <a href="<?= BASE_URL ?>modules/agenda.php" class="btn btn-sm btn-primary">Ver Agenda</a>
            </div>
            
            <?php if (count($proximos_agendamentos) > 0): ?>
                <div class="agenda-list">
                    <?php foreach ($proximos_agendamentos as $ag): ?>
                        <div class="agenda-item status-<?= $ag['status'] ?>">
                            <div class="agenda-time"><?= substr($ag['hora_inicio'], 0, 5) ?></div>
                            <div class="agenda-details">
                                <div class="agenda-patient"><?= $ag['paciente_nome'] ?></div>
                                <div class="agenda-info">
                                    <?= $ag['dentista_nome'] ?>
                                    <?php if ($ag['procedimento_nome']): ?>
                                        • <?= $ag['procedimento_nome'] ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="agenda-status">
                                <?php
                                $status_labels = [
                                    'agendado' => 'Agendado',
                                    'confirmado' => 'Confirmado',
                                    'em_atendimento' => 'Em Atendimento',
                                    'realizado' => 'Realizado'
                                ];
                                echo $status_labels[$ag['status']] ?? $ag['status'];
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>Nenhum agendamento para hoje.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Contas Vencidas -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Contas Vencidas</h2>
                <a href="<?= BASE_URL ?>modules/financeiro.php" class="btn btn-sm btn-primary">Ver Financeiro</a>
            </div>
            
            <?php if (count($contas_vencidas) > 0): ?>
                <div class="contas-list">
                    <?php foreach ($contas_vencidas as $conta): ?>
                        <div class="conta-item">
                            <div class="conta-patient"><?= $conta['paciente_nome'] ?></div>
                            <div class="conta-info">
                                <span><?= $conta['descricao'] ?></span>
                                <span class="conta-vencimento text-danger">
                                    Venceu em <?= format_date($conta['data_vencimento']) ?>
                                </span>
                            </div>
                            <div class="conta-valor">
                                <?= format_currency($conta['valor'] - $conta['valor_pago']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>✅ Nenhuma conta vencida!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Atalhos Rápidos -->
    <div class="quick-actions">
        <h2>Ações Rápidas</h2>
        <div class="action-buttons">
            <a href="<?= BASE_URL ?>modules/pacientes.php?action=new" class="action-btn">
                <span class="action-icon">➕</span>
                <span>Novo Paciente</span>
            </a>
            <a href="<?= BASE_URL ?>modules/agenda.php?action=new" class="action-btn">
                <span class="action-icon">📅</span>
                <span>Novo Agendamento</span>
            </a>
            <a href="<?= BASE_URL ?>modules/financeiro.php?action=new_receber" class="action-btn">
                <span class="action-icon">💵</span>
                <span>Registrar Recebimento</span>
            </a>
            <?php if (has_permission('admin')): ?>
            <a href="<?= BASE_URL ?>modules/relatorios.php" class="action-btn">
                <span class="action-icon">📊</span>
                <span>Relatórios</span>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
