<?php
$page_title = 'Pacientes';
require_once '../includes/header.php';

$db = get_db();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// CRIAR/EDITAR Paciente
if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nome' => sanitize_input($_POST['nome']),
        'cpf' => sanitize_input($_POST['cpf'] ?? ''),
        'rg' => sanitize_input($_POST['rg'] ?? ''),
        'data_nascimento' => $_POST['data_nascimento'] ?? null,
        'sexo' => $_POST['sexo'] ?? null,
        'telefone' => sanitize_input($_POST['telefone'] ?? ''),
        'celular' => sanitize_input($_POST['celular'] ?? ''),
        'email' => sanitize_input($_POST['email'] ?? ''),
        'cep' => sanitize_input($_POST['cep'] ?? ''),
        'endereco' => sanitize_input($_POST['endereco'] ?? ''),
        'numero' => sanitize_input($_POST['numero'] ?? ''),
        'complemento' => sanitize_input($_POST['complemento'] ?? ''),
        'bairro' => sanitize_input($_POST['bairro'] ?? ''),
        'cidade' => sanitize_input($_POST['cidade'] ?? ''),
        'estado' => sanitize_input($_POST['estado'] ?? ''),
        'nome_responsavel' => sanitize_input($_POST['nome_responsavel'] ?? ''),
        'telefone_emergencia' => sanitize_input($_POST['telefone_emergencia'] ?? ''),
        'convenio' => sanitize_input($_POST['convenio'] ?? ''),
        'numero_carteirinha' => sanitize_input($_POST['numero_carteirinha'] ?? ''),
        'observacoes' => sanitize_input($_POST['observacoes'] ?? '')
    ];
    
    if ($id) {
        // Atualizar
        $stmt = $db->prepare("
            UPDATE pacientes SET 
                nome=?, cpf=?, rg=?, data_nascimento=?, sexo=?, telefone=?, celular=?, email=?,
                cep=?, endereco=?, numero=?, complemento=?, bairro=?, cidade=?, estado=?,
                nome_responsavel=?, telefone_emergencia=?, convenio=?, numero_carteirinha=?, 
                observacoes=?, ultima_atualizacao=CURRENT_TIMESTAMP
            WHERE id=?
        ");
        
        $stmt->execute([
            $data['nome'], $data['cpf'], $data['rg'], $data['data_nascimento'], $data['sexo'],
            $data['telefone'], $data['celular'], $data['email'], $data['cep'], $data['endereco'],
            $data['numero'], $data['complemento'], $data['bairro'], $data['cidade'], $data['estado'],
            $data['nome_responsavel'], $data['telefone_emergencia'], $data['convenio'], 
            $data['numero_carteirinha'], $data['observacoes'], $id
        ]);
        
        log_audit('Paciente atualizado', 'pacientes', $id, null, $data);
        show_alert('Paciente atualizado com sucesso!', 'success');
    } else {
        // Inserir
        $stmt = $db->prepare("
            INSERT INTO pacientes (
                nome, cpf, rg, data_nascimento, sexo, telefone, celular, email,
                cep, endereco, numero, complemento, bairro, cidade, estado,
                nome_responsavel, telefone_emergencia, convenio, numero_carteirinha, observacoes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['nome'], $data['cpf'], $data['rg'], $data['data_nascimento'], $data['sexo'],
            $data['telefone'], $data['celular'], $data['email'], $data['cep'], $data['endereco'],
            $data['numero'], $data['complemento'], $data['bairro'], $data['cidade'], $data['estado'],
            $data['nome_responsavel'], $data['telefone_emergencia'], $data['convenio'], 
            $data['numero_carteirinha'], $data['observacoes']
        ]);
        
        $new_id = $db->lastInsertId();
        log_audit('Paciente criado', 'pacientes', $new_id, null, $data);
        show_alert('Paciente cadastrado com sucesso!', 'success');
    }
    
    redirect('modules/pacientes.php');
}

// DELETAR Paciente (soft delete)
if ($action === 'delete' && $id) {
    $stmt = $db->prepare("UPDATE pacientes SET ativo = 0 WHERE id = ?");
    $stmt->execute([$id]);
    
    log_audit('Paciente inativado', 'pacientes', $id);
    show_alert('Paciente inativado com sucesso!', 'success');
    redirect('modules/pacientes.php');
}

// LISTAR Pacientes
if ($action === 'list') {
    $search = $_GET['search'] ?? '';
    $page = $_GET['page'] ?? 1;
    $offset = ($page - 1) * ITEMS_PER_PAGE;
    
    $where = "WHERE p.ativo = 1";
    $params = [];
    
    if ($search) {
        $where .= " AND (p.nome LIKE ? OR p.cpf LIKE ? OR p.telefone LIKE ? OR p.celular LIKE ?)";
        $search_term = "%$search%";
        $params = [$search_term, $search_term, $search_term, $search_term];
    }
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM pacientes p $where");
    $stmt->execute($params);
    $total_records = $stmt->fetch()['total'];
    $total_pages = ceil($total_records / ITEMS_PER_PAGE);
    
    $stmt = $db->prepare("
        SELECT p.*, 
            (SELECT COUNT(*) FROM agendamentos WHERE paciente_id = p.id) as total_consultas
        FROM pacientes p 
        $where 
        ORDER BY p.nome 
        LIMIT " . ITEMS_PER_PAGE . " OFFSET $offset
    ");
    $stmt->execute($params);
    $pacientes = $stmt->fetchAll();
    ?>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Pacientes</h2>
            <a href="?action=new" class="btn btn-primary">➕ Novo Paciente</a>
        </div>
        
        <form method="GET" class="mb-2">
            <div class="form-inline">
                <input type="text" name="search" placeholder="Buscar por nome, CPF ou telefone..." 
                       value="<?= htmlspecialchars($search) ?>" class="form-control">
                <button type="submit" class="btn btn-secondary">🔍 Buscar</button>
                <?php if ($search): ?>
                    <a href="?" class="btn btn-secondary">Limpar</a>
                <?php endif; ?>
            </div>
        </form>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Telefone</th>
                        <th>Data Nasc.</th>
                        <th>Consultas</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pacientes as $p): ?>
                        <tr>
                            <td><strong><?= $p['nome'] ?></strong></td>
                            <td><?= $p['cpf'] ?></td>
                            <td><?= $p['celular'] ?: $p['telefone'] ?></td>
                            <td><?= format_date($p['data_nascimento']) ?></td>
                            <td><?= $p['total_consultas'] ?></td>
                            <td>
                                <a href="prontuario.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary" title="Prontuário">📋</a>
                                <a href="?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-secondary" title="Editar">✏️</a>
                                <a href="?action=delete&id=<?= $p['id'] ?>" 
                                   onclick="return confirmDelete('Deseja inativar este paciente?')" 
                                   class="btn btn-sm btn-danger" title="Inativar">🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <?php if (count($pacientes) === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center">
                                <?= $search ? 'Nenhum paciente encontrado.' : 'Nenhum paciente cadastrado.' ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($total_pages > 1): ?>
            <div class="mt-2 text-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?><?= $search ? "&search=$search" : '' ?>" 
                       class="btn btn-sm <?= $i == $page ? 'btn-primary' : 'btn-secondary' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php
}

// FORMULÁRIO Novo/Editar
if ($action === 'new' || $action === 'edit') {
    $paciente = null;
    
    if ($action === 'edit' && $id) {
        $stmt = $db->prepare("SELECT * FROM pacientes WHERE id = ?");
        $stmt->execute([$id]);
        $paciente = $stmt->fetch();
        
        if (!$paciente) {
            show_alert('Paciente não encontrado!', 'error');
            redirect('modules/pacientes.php');
        }
    }
    ?>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><?= $action === 'edit' ? 'Editar Paciente' : 'Novo Paciente' ?></h2>
            <a href="?" class="btn btn-secondary">← Voltar</a>
        </div>
        
        <form method="POST" action="?action=save<?= $id ? "&id=$id" : '' ?>">
            <h3 class="mb-2">Dados Pessoais</h3>
            
            <div class="form-row">
                <div class="form-group" style="grid-column: span 2;">
                    <label for="nome">Nome Completo *</label>
                    <input type="text" id="nome" name="nome" required 
                           value="<?= $paciente['nome'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="cpf">CPF</label>
                    <input type="text" id="cpf" name="cpf" data-mask="cpf" 
                           value="<?= $paciente['cpf'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="rg">RG</label>
                    <input type="text" id="rg" name="rg" 
                           value="<?= $paciente['rg'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="data_nascimento">Data de Nascimento</label>
                    <input type="date" id="data_nascimento" name="data_nascimento" 
                           value="<?= $paciente['data_nascimento'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="sexo">Sexo</label>
                    <select id="sexo" name="sexo">
                        <option value="">Selecione</option>
                        <option value="M" <?= ($paciente['sexo'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= ($paciente['sexo'] ?? '') === 'F' ? 'selected' : '' ?>>Feminino</option>
                    </select>
                </div>
            </div>
            
            <h3 class="mb-2 mt-3">Contato</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="text" id="telefone" name="telefone" data-mask="phone" 
                           value="<?= $paciente['telefone'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="celular">Celular</label>
                    <input type="text" id="celular" name="celular" data-mask="phone" 
                           value="<?= $paciente['celular'] ?? '' ?>">
                </div>
                
                <div class="form-group" style="grid-column: span 2;">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" 
                           value="<?= $paciente['email'] ?? '' ?>">
                </div>
            </div>
            
            <h3 class="mb-2 mt-3">Endereço</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="cep">CEP</label>
                    <input type="text" id="cep" name="cep" data-mask="cep" 
                           value="<?= $paciente['cep'] ?? '' ?>"
                           onblur="buscarCEP(this.value, preencherEndereco)">
                </div>
                
                <div class="form-group" style="grid-column: span 2;">
                    <label for="endereco">Endereço</label>
                    <input type="text" id="endereco" name="endereco" 
                           value="<?= $paciente['endereco'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="numero">Número</label>
                    <input type="text" id="numero" name="numero" 
                           value="<?= $paciente['numero'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="complemento">Complemento</label>
                    <input type="text" id="complemento" name="complemento" 
                           value="<?= $paciente['complemento'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="bairro">Bairro</label>
                    <input type="text" id="bairro" name="bairro" 
                           value="<?= $paciente['bairro'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="cidade">Cidade</label>
                    <input type="text" id="cidade" name="cidade" 
                           value="<?= $paciente['cidade'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <option value="">Selecione</option>
                        <?php
                        $estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                        foreach ($estados as $uf) {
                            $selected = ($paciente['estado'] ?? '') === $uf ? 'selected' : '';
                            echo "<option value='$uf' $selected>$uf</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <h3 class="mb-2 mt-3">Informações Adicionais</h3>
            
            <div class="form-row">
                <div class="form-group" style="grid-column: span 2;">
                    <label for="nome_responsavel">Nome do Responsável (se menor de idade)</label>
                    <input type="text" id="nome_responsavel" name="nome_responsavel" 
                           value="<?= $paciente['nome_responsavel'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="telefone_emergencia">Telefone de Emergência</label>
                    <input type="text" id="telefone_emergencia" name="telefone_emergencia" data-mask="phone" 
                           value="<?= $paciente['telefone_emergencia'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="convenio">Convênio</label>
                    <input type="text" id="convenio" name="convenio" 
                           value="<?= $paciente['convenio'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="numero_carteirinha">Número da Carteirinha</label>
                    <input type="text" id="numero_carteirinha" name="numero_carteirinha" 
                           value="<?= $paciente['numero_carteirinha'] ?? '' ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="observacoes">Observações</label>
                <textarea id="observacoes" name="observacoes" rows="3"><?= $paciente['observacoes'] ?? '' ?></textarea>
            </div>
            
            <div class="mt-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary">💾 Salvar</button>
                <a href="?" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
    
    <script>
    function preencherEndereco(data) {
        document.getElementById('endereco').value = data.logradouro;
        document.getElementById('bairro').value = data.bairro;
        document.getElementById('cidade').value = data.localidade;
        document.getElementById('estado').value = data.uf;
        document.getElementById('numero').focus();
    }
    </script>
    
    <?php
}

require_once '../includes/footer.php';
?>
