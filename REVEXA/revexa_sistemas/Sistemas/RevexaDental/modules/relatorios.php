<?php
$page_title = 'Relatórios';
require_once '../includes/header.php';

if (!has_permission('admin')) {
    show_alert('Acesso negado!', 'error');
    redirect('dashboard.php');
}

$db = get_db();
$tipo = $_GET['tipo'] ?? 'financeiro';

// Filtros de data
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
$data_fim = $_GET['data_fim'] ?? date('Y-m-t');
?>

<div class="tabs mb-2">
    <a href="?tipo=financeiro&data_inicio=<?= $data_inicio ?>&data_fim=<?= $data_fim ?>" 
       class="tab <?= $tipo === 'financeiro' ? 'active' : '' ?>">
        💰 Financeiro
    </a>
    <a href="?tipo=producao&data_inicio=<?= $data_inicio ?>&data_fim=<?= $data_fim ?>" 
       class="tab <?= $tipo === 'producao' ? 'active' : '' ?>">
        📊 Produção
    </a>
    <a href="?tipo=pacientes&data_inicio=<?= $data_inicio ?>&data_fim=<?= $data_fim ?>" 
       class="tab <?= $tipo === 'pacientes' ? 'active' : '' ?>">
        👥 Pacientes
    </a>
</div>

<div class="card mb-2">
    <form method="GET" class="form-inline">
        <input type="hidden" name="tipo" value="<?= $tipo ?>">
        <div class="form-group">
            <label for="data_inicio">Data Início:</label>
            <input type="date" id="data_inicio" name="data_inicio" value="<?= $data_inicio ?>">
        </div>
        <div class="form-group">
            <label for="data_fim">Data Fim:</label>
            <input type="date" id="data_fim" name="data_fim" value="<?= $data_fim ?>">
        </div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>
</div>

<?php
// ============================================================
// RELATÓRIO FINANCEIRO
// ============================================================
if ($tipo === 'financeiro') {
    // Recebimentos
    $stmt = $db->prepare("
        SELECT SUM(valor_pago) as total
        FROM contas_receber
        WHERE status = 'pago' AND data_pagamento BETWEEN ? AND ?
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $total_recebido = $stmt->fetch()['total'] ?? 0;
    
    // Pendentes
    $stmt = $db->prepare("
        SELECT SUM(valor - valor_pago) as total
        FROM contas_receber
        WHERE status = 'pendente' AND data_vencimento BETWEEN ? AND ?
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $total_pendente = $stmt->fetch()['total'] ?? 0;
    
    // Pagamentos
    $stmt = $db->prepare("
        SELECT SUM(valor_pago) as total
        FROM contas_pagar
        WHERE status = 'pago' AND data_pagamento BETWEEN ? AND ?
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $total_pago = $stmt->fetch()['total'] ?? 0;
    
    $saldo = $total_recebido - $total_pago;
    ?>
    
    <div class="stats-grid">
        <div class="stat-card card-green">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <div class="stat-value"><?= format_currency($total_recebido) ?></div>
                <div class="stat-label">Total Recebido</div>
            </div>
        </div>
        
        <div class="stat-card card-orange">
            <div class="stat-icon">⏳</div>
            <div class="stat-content">
                <div class="stat-value"><?= format_currency($total_pendente) ?></div>
                <div class="stat-label">A Receber</div>
            </div>
        </div>
        
        <div class="stat-card card-red">
            <div class="stat-icon">📤</div>
            <div class="stat-content">
                <div class="stat-value"><?= format_currency($total_pago) ?></div>
                <div class="stat-label">Total Pago</div>
            </div>
        </div>
        
        <div class="stat-card card-blue">
            <div class="stat-icon">💵</div>
            <div class="stat-content">
                <div class="stat-value"><?= format_currency($saldo) ?></div>
                <div class="stat-label">Saldo</div>
            </div>
        </div>
    </div>
    
    <!-- Recebimentos por forma de pagamento -->
    <?php
    $stmt = $db->prepare("
        SELECT forma_pagamento, SUM(valor_pago) as total, COUNT(*) as quantidade
        FROM contas_receber
        WHERE status = 'pago' AND data_pagamento BETWEEN ? AND ?
        GROUP BY forma_pagamento
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $por_forma = $stmt->fetchAll();
    ?>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recebimentos por Forma de Pagamento</h3>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Forma de Pagamento</th>
                        <th>Quantidade</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($por_forma as $f): ?>
                        <tr>
                            <td><?= ucfirst(str_replace('_', ' ', $f['forma_pagamento'])) ?></td>
                            <td><?= $f['quantidade'] ?></td>
                            <td><?= format_currency($f['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php
}

// ============================================================
// RELATÓRIO DE PRODUÇÃO
// ============================================================
if ($tipo === 'producao') {
    // Consultas por dentista
    $stmt = $db->prepare("
        SELECT u.nome, COUNT(a.id) as total_consultas,
        SUM(CASE WHEN a.status = 'realizado' THEN 1 ELSE 0 END) as realizadas
        FROM usuarios u
        LEFT JOIN agendamentos a ON u.id = a.dentista_id AND a.data_agendamento BETWEEN ? AND ?
        WHERE u.perfil IN ('dentista', 'admin') AND u.ativo = 1
        GROUP BY u.id
        ORDER BY total_consultas DESC
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $por_dentista = $stmt->fetchAll();
    ?>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Produção por Dentista</h3>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Dentista</th>
                        <th>Total Agendamentos</th>
                        <th>Realizados</th>
                        <th>Taxa Realização</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($por_dentista as $d): ?>
                        <tr>
                            <td><?= $d['nome'] ?></td>
                            <td><?= $d['total_consultas'] ?></td>
                            <td><?= $d['realizadas'] ?></td>
                            <td>
                                <?php
                                $taxa = $d['total_consultas'] > 0 
                                    ? round(($d['realizadas'] / $d['total_consultas']) * 100, 1) 
                                    : 0;
                                echo $taxa . '%';
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php
}

// ============================================================
// RELATÓRIO DE PACIENTES
// ============================================================
if ($tipo === 'pacientes') {
    // Novos pacientes
    $stmt = $db->prepare("
        SELECT COUNT(*) as total
        FROM pacientes
        WHERE data_cadastro BETWEEN ? AND ?
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $novos_pacientes = $stmt->fetch()['total'];
    
    // Total de pacientes ativos
    $stmt = $db->query("SELECT COUNT(*) as total FROM pacientes WHERE ativo = 1");
    $total_pacientes = $stmt->fetch()['total'];
    ?>
    
    <div class="stats-grid" style="grid-template-columns: repeat(2, 1fr);">
        <div class="stat-card card-blue">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <div class="stat-value"><?= $total_pacientes ?></div>
                <div class="stat-label">Total de Pacientes</div>
            </div>
        </div>
        
        <div class="stat-card card-green">
            <div class="stat-icon">➕</div>
            <div class="stat-content">
                <div class="stat-value"><?= $novos_pacientes ?></div>
                <div class="stat-label">Novos no Período</div>
            </div>
        </div>
    </div>
    
    <!-- Pacientes mais atendidos -->
    <?php
    $stmt = $db->prepare("
        SELECT p.nome, COUNT(a.id) as total_consultas
        FROM pacientes p
        LEFT JOIN agendamentos a ON p.id = a.paciente_id 
            AND a.status = 'realizado' 
            AND a.data_agendamento BETWEEN ? AND ?
        GROUP BY p.id
        HAVING total_consultas > 0
        ORDER BY total_consultas DESC
        LIMIT 10
    ");
    $stmt->execute([$data_inicio, $data_fim]);
    $top_pacientes = $stmt->fetchAll();
    ?>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Top 10 Pacientes Mais Atendidos</h3>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Paciente</th>
                        <th>Consultas Realizadas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top_pacientes as $p): ?>
                        <tr>
                            <td><?= $p['nome'] ?></td>
                            <td><?= $p['total_consultas'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php
}

require_once '../includes/footer.php';
?>
