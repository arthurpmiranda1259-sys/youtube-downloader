<?php
$page_title = 'Prontuário';
require_once '../includes/header.php';

if (!has_permission('dentista')) {
    show_alert('Acesso negado!', 'error');
    redirect('dashboard.php');
}

$db = get_db();
$paciente_id = $_GET['id'] ?? null;

if (!$paciente_id) {
    show_alert('Paciente não especificado!', 'error');
    redirect('modules/pacientes.php');
}

// Buscar dados do paciente
$stmt = $db->prepare("SELECT * FROM pacientes WHERE id = ? AND ativo = 1");
$stmt->execute([$paciente_id]);
$paciente = $stmt->fetch();

if (!$paciente) {
    show_alert('Paciente não encontrado!', 'error');
    redirect('modules/pacientes.php');
}

$tab = $_GET['tab'] ?? 'resumo';

// Calcular idade
$idade = '';
if ($paciente['data_nascimento']) {
    $nasc = new DateTime($paciente['data_nascimento']);
    $hoje = new DateTime();
    $idade = $nasc->diff($hoje)->y . ' anos';
}
?>

<div class="prontuario-header">
    <div>
        <h1><?= $paciente['nome'] ?></h1>
        <div class="patient-info">
            <?php if ($paciente['data_nascimento']): ?>
                <span>📅 <?= format_date($paciente['data_nascimento']) ?> (<?= $idade ?>)</span>
            <?php endif; ?>
            <?php if ($paciente['cpf']): ?>
                <span>🆔 <?= $paciente['cpf'] ?></span>
            <?php endif; ?>
            <?php if ($paciente['celular'] || $paciente['telefone']): ?>
                <span>📱 <?= $paciente['celular'] ?: $paciente['telefone'] ?></span>
            <?php endif; ?>
        </div>
    </div>
    <a href="pacientes.php" class="btn btn-secondary">← Voltar</a>
</div>

<!-- Tabs de Navegação -->
<div class="tabs">
    <a href="?id=<?= $paciente_id ?>&tab=resumo" class="tab <?= $tab === 'resumo' ? 'active' : '' ?>">
        📋 Resumo
    </a>
    <a href="?id=<?= $paciente_id ?>&tab=anamnese" class="tab <?= $tab === 'anamnese' ? 'active' : '' ?>">
        📝 Anamnese
    </a>
    <a href="?id=<?= $paciente_id ?>&tab=odontograma" class="tab <?= $tab === 'odontograma' ? 'active' : '' ?>">
        🦷 Odontograma
    </a>
    <a href="?id=<?= $paciente_id ?>&tab=evolucao" class="tab <?= $tab === 'evolucao' ? 'active' : '' ?>">
        📖 Evoluções
    </a>
    <a href="?id=<?= $paciente_id ?>&tab=plano" class="tab <?= $tab === 'plano' ? 'active' : '' ?>">
        💼 Plano de Tratamento
    </a>
    <a href="?id=<?= $paciente_id ?>&tab=documentos" class="tab <?= $tab === 'documentos' ? 'active' : '' ?>">
        📎 Documentos
    </a>
</div>

<?php
// ============================================================
// TAB: RESUMO
// ============================================================
if ($tab === 'resumo') {
    // Últimas evoluções
    $stmt = $db->prepare("
        SELECT pr.*, u.nome as dentista_nome
        FROM prontuario pr
        LEFT JOIN usuarios u ON pr.dentista_id = u.id
        WHERE pr.paciente_id = ?
        ORDER BY pr.data_atendimento DESC
        LIMIT 5
    ");
    $stmt->execute([$paciente_id]);
    $ultimas_evolucoes = $stmt->fetchAll();
    
    // Próximo agendamento
    $stmt = $db->prepare("
        SELECT a.*, u.nome as dentista_nome, pr.nome as procedimento_nome
        FROM agendamentos a
        LEFT JOIN usuarios u ON a.dentista_id = u.id
        LEFT JOIN procedimentos pr ON a.procedimento_id = pr.id
        WHERE a.paciente_id = ? AND a.data_agendamento >= ? AND a.status NOT IN ('cancelado', 'realizado')
        ORDER BY a.data_agendamento, a.hora_inicio
        LIMIT 1
    ");
    $stmt->execute([$paciente_id, date('Y-m-d')]);
    $proximo_agendamento = $stmt->fetch();
    
    // Planos de tratamento
    $stmt = $db->prepare("
        SELECT * FROM planos_tratamento 
        WHERE paciente_id = ? 
        ORDER BY data_criacao DESC
        LIMIT 3
    ");
    $stmt->execute([$paciente_id]);
    $planos = $stmt->fetchAll();
    ?>
    
    <div class="dashboard-grid">
        <!-- Próximo Agendamento -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Próximo Agendamento</h3>
            </div>
            
            <?php if ($proximo_agendamento): ?>
                <div class="agenda-card">
                    <div class="agenda-time-col">
                        <div class="time-badge"><?= substr($proximo_agendamento['hora_inicio'], 0, 5) ?></div>
                    </div>
                    <div class="agenda-content">
                        <div><strong><?= format_date($proximo_agendamento['data_agendamento']) ?></strong></div>
                        <div class="text-muted"><?= $proximo_agendamento['dentista_nome'] ?></div>
                        <?php if ($proximo_agendamento['procedimento_nome']): ?>
                            <div class="text-muted"><?= $proximo_agendamento['procedimento_nome'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>Nenhum agendamento futuro</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Planos de Tratamento -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Planos de Tratamento</h3>
            </div>
            
            <?php if (count($planos) > 0): ?>
                <?php foreach ($planos as $plano): ?>
                    <div class="plano-item">
                        <div>
                            <strong><?= $plano['titulo'] ?: 'Plano #' . $plano['id'] ?></strong>
                            <div class="text-muted"><?= format_date($plano['data_criacao']) ?></div>
                        </div>
                        <div>
                            <span class="badge badge-<?= $plano['status'] === 'aprovado' ? 'success' : 'secondary' ?>">
                                <?= ucfirst($plano['status']) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>Nenhum plano de tratamento</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Últimas Evoluções -->
    <div class="card mt-2">
        <div class="card-header">
            <h3 class="card-title">Últimas Evoluções</h3>
            <a href="?id=<?= $paciente_id ?>&tab=evolucao&action=new" class="btn btn-sm btn-primary">
                ➕ Nova Evolução
            </a>
        </div>
        
        <?php if (count($ultimas_evolucoes) > 0): ?>
            <?php foreach ($ultimas_evolucoes as $ev): ?>
                <div class="evolucao-item">
                    <div class="evolucao-header">
                        <strong><?= format_datetime($ev['data_atendimento']) ?></strong>
                        <span class="text-muted"><?= $ev['dentista_nome'] ?></span>
                    </div>
                    <?php if ($ev['queixa_principal']): ?>
                        <div class="mt-1"><strong>Queixa:</strong> <?= $ev['queixa_principal'] ?></div>
                    <?php endif; ?>
                    <?php if ($ev['procedimentos_realizados']): ?>
                        <div class="mt-1"><strong>Procedimentos:</strong> <?= $ev['procedimentos_realizados'] ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <p>Nenhuma evolução registrada</p>
            </div>
        <?php endif; ?>
    </div>
    
    <?php
}

// ============================================================
// TAB: ANAMNESE
// ============================================================
if ($tab === 'anamnese') {
    // Buscar anamnese
    $stmt = $db->prepare("SELECT * FROM anamnese WHERE paciente_id = ? ORDER BY data_anamnese DESC LIMIT 1");
    $stmt->execute([$paciente_id]);
    $anamnese = $stmt->fetch();
    
    // Salvar anamnese
    if (isset($_POST['salvar_anamnese'])) {
        $data = [
            'esta_tratamento_medico' => $_POST['esta_tratamento_medico'] ?? 0,
            'descricao_tratamento' => sanitize_input($_POST['descricao_tratamento'] ?? ''),
            'toma_medicamentos' => $_POST['toma_medicamentos'] ?? 0,
            'lista_medicamentos' => sanitize_input($_POST['lista_medicamentos'] ?? ''),
            'alergias' => $_POST['alergias'] ?? 0,
            'lista_alergias' => sanitize_input($_POST['lista_alergias'] ?? ''),
            'problemas_cardiacos' => $_POST['problemas_cardiacos'] ?? 0,
            'problemas_respiratorios' => $_POST['problemas_respiratorios'] ?? 0,
            'diabetes' => $_POST['diabetes'] ?? 0,
            'hipertensao' => $_POST['hipertensao'] ?? 0,
            'hepatite' => $_POST['hepatite'] ?? 0,
            'dst' => $_POST['dst'] ?? 0,
            'gravida' => $_POST['gravida'] ?? 0,
            'fumante' => $_POST['fumante'] ?? 0,
            'etilista' => $_POST['etilista'] ?? 0,
            'outras_doencas' => sanitize_input($_POST['outras_doencas'] ?? ''),
            'observacoes_anamnese' => sanitize_input($_POST['observacoes_anamnese'] ?? '')
        ];
        
        $stmt = $db->prepare("
            INSERT INTO anamnese (
                paciente_id, esta_tratamento_medico, descricao_tratamento, toma_medicamentos,
                lista_medicamentos, alergias, lista_alergias, problemas_cardiacos, 
                problemas_respiratorios, diabetes, hipertensao, hepatite, dst, gravida,
                fumante, etilista, outras_doencas, observacoes_anamnese, atualizado_por
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $paciente_id, $data['esta_tratamento_medico'], $data['descricao_tratamento'],
            $data['toma_medicamentos'], $data['lista_medicamentos'], $data['alergias'],
            $data['lista_alergias'], $data['problemas_cardiacos'], $data['problemas_respiratorios'],
            $data['diabetes'], $data['hipertensao'], $data['hepatite'], $data['dst'],
            $data['gravida'], $data['fumante'], $data['etilista'], $data['outras_doencas'],
            $data['observacoes_anamnese'], $_SESSION['user_id']
        ]);
        
        log_audit('Anamnese atualizada', 'anamnese', $db->lastInsertId());
        show_alert('Anamnese salva com sucesso!', 'success');
        redirect("modules/prontuario.php?id=$paciente_id&tab=anamnese");
    }
    ?>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Anamnese</h3>
            <?php if ($anamnese): ?>
                <span class="text-muted">Última atualização: <?= format_datetime($anamnese['data_anamnese']) ?></span>
            <?php endif; ?>
        </div>
        
        <form method="POST">
            <div class="anamnese-section">
                <h4>Tratamento Médico</h4>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="esta_tratamento_medico" value="1" 
                               <?= ($anamnese['esta_tratamento_medico'] ?? 0) ? 'checked' : '' ?>>
                        Está em tratamento médico?
                    </label>
                </div>
                <div class="form-group">
                    <label for="descricao_tratamento">Descrição do tratamento:</label>
                    <textarea id="descricao_tratamento" name="descricao_tratamento" rows="2"><?= $anamnese['descricao_tratamento'] ?? '' ?></textarea>
                </div>
            </div>
            
            <div class="anamnese-section">
                <h4>Medicamentos</h4>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="toma_medicamentos" value="1" 
                               <?= ($anamnese['toma_medicamentos'] ?? 0) ? 'checked' : '' ?>>
                        Toma algum medicamento regularmente?
                    </label>
                </div>
                <div class="form-group">
                    <label for="lista_medicamentos">Lista de medicamentos:</label>
                    <textarea id="lista_medicamentos" name="lista_medicamentos" rows="2"><?= $anamnese['lista_medicamentos'] ?? '' ?></textarea>
                </div>
            </div>
            
            <div class="anamnese-section">
                <h4>Alergias</h4>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="alergias" value="1" 
                               <?= ($anamnese['alergias'] ?? 0) ? 'checked' : '' ?>>
                        Possui alguma alergia?
                    </label>
                </div>
                <div class="form-group">
                    <label for="lista_alergias">Lista de alergias:</label>
                    <textarea id="lista_alergias" name="lista_alergias" rows="2"><?= $anamnese['lista_alergias'] ?? '' ?></textarea>
                </div>
            </div>
            
            <div class="anamnese-section">
                <h4>Condições de Saúde</h4>
                <div class="checkbox-grid">
                    <label>
                        <input type="checkbox" name="problemas_cardiacos" value="1" 
                               <?= ($anamnese['problemas_cardiacos'] ?? 0) ? 'checked' : '' ?>>
                        Problemas Cardíacos
                    </label>
                    <label>
                        <input type="checkbox" name="problemas_respiratorios" value="1" 
                               <?= ($anamnese['problemas_respiratorios'] ?? 0) ? 'checked' : '' ?>>
                        Problemas Respiratórios
                    </label>
                    <label>
                        <input type="checkbox" name="diabetes" value="1" 
                               <?= ($anamnese['diabetes'] ?? 0) ? 'checked' : '' ?>>
                        Diabetes
                    </label>
                    <label>
                        <input type="checkbox" name="hipertensao" value="1" 
                               <?= ($anamnese['hipertensao'] ?? 0) ? 'checked' : '' ?>>
                        Hipertensão
                    </label>
                    <label>
                        <input type="checkbox" name="hepatite" value="1" 
                               <?= ($anamnese['hepatite'] ?? 0) ? 'checked' : '' ?>>
                        Hepatite
                    </label>
                    <label>
                        <input type="checkbox" name="dst" value="1" 
                               <?= ($anamnese['dst'] ?? 0) ? 'checked' : '' ?>>
                        DST
                    </label>
                    <label>
                        <input type="checkbox" name="gravida" value="1" 
                               <?= ($anamnese['gravida'] ?? 0) ? 'checked' : '' ?>>
                        Grávida
                    </label>
                    <label>
                        <input type="checkbox" name="fumante" value="1" 
                               <?= ($anamnese['fumante'] ?? 0) ? 'checked' : '' ?>>
                        Fumante
                    </label>
                    <label>
                        <input type="checkbox" name="etilista" value="1" 
                               <?= ($anamnese['etilista'] ?? 0) ? 'checked' : '' ?>>
                        Consome Álcool
                    </label>
                </div>
            </div>
            
            <div class="anamnese-section">
                <h4>Outras Informações</h4>
                <div class="form-group">
                    <label for="outras_doencas">Outras doenças:</label>
                    <textarea id="outras_doencas" name="outras_doencas" rows="2"><?= $anamnese['outras_doencas'] ?? '' ?></textarea>
                </div>
                <div class="form-group">
                    <label for="observacoes_anamnese">Observações gerais:</label>
                    <textarea id="observacoes_anamnese" name="observacoes_anamnese" rows="3"><?= $anamnese['observacoes_anamnese'] ?? '' ?></textarea>
                </div>
            </div>
            
            <button type="submit" name="salvar_anamnese" class="btn btn-primary">💾 Salvar Anamnese</button>
        </form>
    </div>
    
    <style>
    .anamnese-section {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--border-color);
    }
    
    .anamnese-section h4 {
        margin-bottom: 15px;
        color: var(--primary-color);
    }
    
    .checkbox-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
    }
    
    .checkbox-grid label {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    </style>
    
    <?php
}

// ============================================================
// TAB: ODONTOGRAMA
// ============================================================
if ($tab === 'odontograma') {
    // Buscar dados do odontograma
    $stmt = $db->prepare("SELECT * FROM odontograma WHERE paciente_id = ?");
    $stmt->execute([$paciente_id]);
    $odonto_data = $stmt->fetchAll(PDO::FETCH_GROUP);
    
    // Processar salvamento
    if (isset($_POST['salvar_odontograma'])) {
        foreach ($_POST['dentes'] as $dente => $data) {
            if (!empty($data['condicao'])) {
                // Verificar se já existe
                $stmt = $db->prepare("SELECT id FROM odontograma WHERE paciente_id = ? AND dente = ?");
                $stmt->execute([$paciente_id, $dente]);
                $existe = $stmt->fetch();
                
                if ($existe) {
                    $stmt = $db->prepare("
                        UPDATE odontograma SET condicao=?, faces=?, material=?, observacoes=?, profissional_id=?
                        WHERE paciente_id=? AND dente=?
                    ");
                    $stmt->execute([
                        $data['condicao'], $data['faces'] ?? '', $data['material'] ?? '',
                        $data['observacoes'] ?? '', $_SESSION['user_id'], $paciente_id, $dente
                    ]);
                } else {
                    $stmt = $db->prepare("
                        INSERT INTO odontograma (paciente_id, dente, condicao, faces, material, observacoes, profissional_id)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $paciente_id, $dente, $data['condicao'], $data['faces'] ?? '',
                        $data['material'] ?? '', $data['observacoes'] ?? '', $_SESSION['user_id']
                    ]);
                }
            }
        }
        
        log_audit('Odontograma atualizado', 'odontograma', null);
        show_alert('Odontograma atualizado com sucesso!', 'success');
        redirect("modules/prontuario.php?id=$paciente_id&tab=odontograma");
    }
    
    $dentes_superiores = [18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28];
    $dentes_inferiores = [48, 47, 46, 45, 44, 43, 42, 41, 31, 32, 33, 34, 35, 36, 37, 38];
    ?>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Odontograma</h3>
        </div>
        
        <form method="POST">
            <div class="odontograma-container">
                <!-- Dentes Superiores -->
                <div class="arcada">
                    <div class="arcada-label">Superior</div>
                    <div class="dentes-row">
                        <?php foreach ($dentes_superiores as $dente): 
                            $info = $odonto_data[$dente][0] ?? null;
                        ?>
                            <div class="dente-card">
                                <div class="dente-numero"><?= $dente ?></div>
                                <select name="dentes[<?= $dente ?>][condicao]" class="dente-select">
                                    <option value="">Hígido</option>
                                    <option value="cariado" <?= ($info['condicao'] ?? '') === 'cariado' ? 'selected' : '' ?>>Cariado</option>
                                    <option value="restaurado" <?= ($info['condicao'] ?? '') === 'restaurado' ? 'selected' : '' ?>>Restaurado</option>
                                    <option value="ausente" <?= ($info['condicao'] ?? '') === 'ausente' ? 'selected' : '' ?>>Ausente</option>
                                    <option value="protese" <?= ($info['condicao'] ?? '') === 'protese' ? 'selected' : '' ?>>Prótese</option>
                                    <option value="implante" <?= ($info['condicao'] ?? '') === 'implante' ? 'selected' : '' ?>>Implante</option>
                                    <option value="canal" <?= ($info['condicao'] ?? '') === 'canal' ? 'selected' : '' ?>>Canal</option>
                                </select>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Dentes Inferiores -->
                <div class="arcada">
                    <div class="dentes-row">
                        <?php foreach ($dentes_inferiores as $dente): 
                            $info = $odonto_data[$dente][0] ?? null;
                        ?>
                            <div class="dente-card">
                                <select name="dentes[<?= $dente ?>][condicao]" class="dente-select">
                                    <option value="">Hígido</option>
                                    <option value="cariado" <?= ($info['condicao'] ?? '') === 'cariado' ? 'selected' : '' ?>>Cariado</option>
                                    <option value="restaurado" <?= ($info['condicao'] ?? '') === 'restaurado' ? 'selected' : '' ?>>Restaurado</option>
                                    <option value="ausente" <?= ($info['condicao'] ?? '') === 'ausente' ? 'selected' : '' ?>>Ausente</option>
                                    <option value="protese" <?= ($info['condicao'] ?? '') === 'protese' ? 'selected' : '' ?>>Prótese</option>
                                    <option value="implante" <?= ($info['condicao'] ?? '') === 'implante' ? 'selected' : '' ?>>Implante</option>
                                    <option value="canal" <?= ($info['condicao'] ?? '') === 'canal' ? 'selected' : '' ?>>Canal</option>
                                </select>
                                <div class="dente-numero"><?= $dente ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="arcada-label">Inferior</div>
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" name="salvar_odontograma" class="btn btn-primary">💾 Salvar Odontograma</button>
            </div>
        </form>
    </div>
    
    <style>
    .odontograma-container {
        padding: 20px;
        background: var(--bg-color);
        border-radius: 8px;
    }
    
    .arcada {
        margin-bottom: 20px;
    }
    
    .arcada-label {
        text-align: center;
        font-weight: 600;
        color: var(--primary-color);
        margin-bottom: 10px;
    }
    
    .dentes-row {
        display: flex;
        justify-content: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .dente-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
    }
    
    .dente-numero {
        font-weight: 600;
        font-size: 12px;
        color: var(--text-muted);
    }
    
    .dente-select {
        width: 100px;
        padding: 6px;
        font-size: 11px;
        border: 1px solid var(--border-color);
        border-radius: 4px;
    }
    
    .dente-select option[value="cariado"] {
        color: #dc2626;
    }
    
    .dente-select option[value="restaurado"] {
        color: #2563eb;
    }
    
    .dente-select option[value="ausente"] {
        color: #64748b;
    }
    </style>
    
    <?php
}

// Continua com outras tabs no próximo arquivo...
require_once '../includes/footer.php';
?>

<style>
.prontuario-header {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.patient-info {
    display: flex;
    gap: 20px;
    margin-top: 8px;
    color: var(--text-muted);
    font-size: 14px;
}

.tabs {
    display: flex;
    gap: 5px;
    background: white;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 20px;
    overflow-x: auto;
}

.tab {
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    color: var(--text-color);
    font-weight: 500;
    white-space: nowrap;
    transition: all 0.2s;
}

.tab:hover {
    background: var(--bg-color);
}

.tab.active {
    background: var(--primary-color);
    color: white;
}

.plano-item,
.evolucao-item {
    padding: 15px;
    background: var(--bg-color);
    border-radius: 6px;
    margin-bottom: 10px;
}

.evolucao-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

@media (max-width: 768px) {
    .prontuario-header {
        flex-direction: column;
        gap: 15px;
    }
    
    .patient-info {
        flex-direction: column;
        gap: 5px;
    }
    
    .tabs {
        overflow-x: scroll;
    }
}
</style>
