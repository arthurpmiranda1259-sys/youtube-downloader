<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$db = Database::getInstance()->getConnection();

// Excluir área
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->exec("DELETE FROM delivery_areas WHERE id = {$id}");
    header('Location: ' . BASE_URL . 'admin/delivery_areas.php');
    exit;
}

// Salvar/Atualizar área
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $neighborhood = sanitizeInput($_POST['neighborhood']);
    $deliveryFee = (float)$_POST['delivery_fee'];
    $estimatedTime = sanitizeInput($_POST['estimated_time']);
    $active = isset($_POST['active']) ? 1 : 0;
    
    if ($id > 0) {
        // Atualizar
        $stmt = $db->prepare("UPDATE delivery_areas SET neighborhood = :neighborhood, delivery_fee = :delivery_fee, estimated_time = :estimated_time, active = :active WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    } else {
        // Inserir
        $stmt = $db->prepare("INSERT INTO delivery_areas (neighborhood, delivery_fee, estimated_time, active) VALUES (:neighborhood, :delivery_fee, :estimated_time, :active)");
    }
    
    $stmt->bindValue(':neighborhood', $neighborhood, SQLITE3_TEXT);
    $stmt->bindValue(':delivery_fee', $deliveryFee, SQLITE3_FLOAT);
    $stmt->bindValue(':estimated_time', $estimatedTime, SQLITE3_TEXT);
    $stmt->bindValue(':active', $active, SQLITE3_INTEGER);
    $stmt->execute();
    
    header('Location: ' . BASE_URL . 'admin/delivery_areas.php');
    exit;
}

// Buscar áreas
$areasQuery = $db->query("SELECT * FROM delivery_areas ORDER BY neighborhood");
$areas = [];
while ($row = $areasQuery->fetchArray(SQLITE3_ASSOC)) {
    $areas[] = $row;
}

$businessName = getSetting('business_name', 'X Delivery');

// Área sendo editada
$editArea = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM delivery_areas WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $editArea = $result->fetchArray(SQLITE3_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Áreas de Entrega - <?php echo htmlspecialchars($businessName); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="admin-content">
            <header class="admin-header">
                <h1>Gerenciar Áreas de Entrega</h1>
            </header>
            
            <main class="admin-main">
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
                    <!-- Form -->
                    <div class="admin-card">
                        <h2 class="admin-card-title" style="margin-bottom: 20px;">
                            <?php echo $editArea ? 'Editar' : 'Nova'; ?> Área
                        </h2>
                        
                        <form method="POST">
                            <?php if ($editArea): ?>
                                <input type="hidden" name="id" value="<?php echo $editArea['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label class="form-label">Bairro *</label>
                                <input type="text" name="neighborhood" class="form-control" required
                                       value="<?php echo $editArea ? htmlspecialchars($editArea['neighborhood']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Taxa de Entrega (R$) *</label>
                                <input type="number" step="0.01" name="delivery_fee" class="form-control" required
                                       value="<?php echo $editArea ? $editArea['delivery_fee'] : '0.00'; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Tempo Estimado</label>
                                <input type="text" name="estimated_time" class="form-control"
                                       value="<?php echo $editArea ? htmlspecialchars($editArea['estimated_time']) : ''; ?>"
                                       placeholder="Ex: 40-50 minutos">
                            </div>
                            
                            <div class="form-group">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="checkbox" name="active" value="1" class="form-checkbox"
                                           <?php echo (!$editArea || $editArea['active']) ? 'checked' : ''; ?>>
                                    <strong style="margin-left: 10px;">Área Ativa</strong>
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-success btn-block">
                                <?php echo $editArea ? 'Atualizar' : 'Cadastrar'; ?> Área
                            </button>
                            
                            <?php if ($editArea): ?>
                                <a href="<?php echo BASE_URL; ?>admin/delivery_areas.php" class="btn btn-outline btn-block" style="margin-top: 10px;">
                                    Cancelar Edição
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                    
                    <!-- List -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h2 class="admin-card-title">Áreas Cadastradas (<?php echo count($areas); ?>)</h2>
                        </div>
                        
                        <?php if (empty($areas)): ?>
                            <p style="text-align: center; color: var(--text-muted); padding: 40px 0;">
                                Nenhuma área cadastrada ainda
                            </p>
                        <?php else: ?>
                            <div style="overflow-x: auto;">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Bairro</th>
                                            <th>Taxa</th>
                                            <th>Tempo Estimado</th>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($areas as $area): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($area['neighborhood']); ?></strong></td>
                                                <td><strong><?php echo formatMoney($area['delivery_fee']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($area['estimated_time']); ?></td>
                                                <td>
                                                    <?php if ($area['active']): ?>
                                                        <span class="badge badge-ready">Ativa</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-cancelled">Inativa</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="?edit=<?php echo $area['id']; ?>" class="btn btn-primary btn-sm">Editar</a>
                                                        <a href="?delete=<?php echo $area['id']; ?>" 
                                                           class="btn btn-danger btn-sm"
                                                           onclick="return confirm('Tem certeza que deseja excluir esta área?')">
                                                            Excluir
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
