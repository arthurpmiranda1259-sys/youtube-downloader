<?php
require_once __DIR__ . '/../config/config.php';
requireRole('admin');

$db = Database::getInstance()->getConnection();

// Delete User
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Prevent deleting self
    if ($id != $_SESSION['user_id']) {
        $db->exec("DELETE FROM users WHERE id = {$id}");
    }
    header('Location: ' . BASE_URL . 'admin/users.php');
    exit;
}

// Save/Update User
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = sanitizeInput($_POST['name']);
    $username = sanitizeInput($_POST['username']);
    $role = sanitizeInput($_POST['role']);
    $password = $_POST['password'];
    
    if ($id > 0) {
        // Update
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET name = :name, username = :username, password = :password, role = :role WHERE id = :id");
            $stmt->bindValue(':password', $hash, SQLITE3_TEXT);
        } else {
            $stmt = $db->prepare("UPDATE users SET name = :name, username = :username, role = :role WHERE id = :id");
        }
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    } else {
        // Insert
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (name, username, password, role) VALUES (:name, :username, :password, :role)");
        $stmt->bindValue(':password', $hash, SQLITE3_TEXT);
    }
    
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':role', $role, SQLITE3_TEXT);
    
    try {
        $stmt->execute();
    } catch (Exception $e) {
        // Handle duplicate username
    }
    
    header('Location: ' . BASE_URL . 'admin/users.php');
    exit;
}

$users = [];
$query = $db->query("SELECT * FROM users ORDER BY name");
while ($row = $query->fetchArray(SQLITE3_ASSOC)) {
    $users[] = $row;
}

$businessName = getSetting('business_name', 'X Delivery');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - <?php echo htmlspecialchars($businessName); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>Gerenciar Usuários</h1>
                <button onclick="openModal()" class="btn btn-primary">Novo Usuário</button>
            </div>
            
            <div class="card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Usuário</th>
                            <th>Função</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td>
                                <span class="badge <?php echo $user['role'] == 'admin' ? 'badge-completed' : 'badge-pending'; ?>">
                                    <?php echo $user['role'] == 'admin' ? 'Administrador' : 'Funcionário'; ?>
                                </span>
                            </td>
                            <td>Ativo</td>
                            <td>
                                <button onclick='editUser(<?php echo json_encode($user); ?>)' class="btn-icon">✏️</button>
                                <?php if($user['id'] != $_SESSION['user_id']): ?>
                                <a href="?delete=<?php echo $user['id']; ?>" onclick="return confirm('Tem certeza?')" class="btn-icon" style="color: var(--danger-color);">🗑️</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Novo Usuário</h2>
                <button onclick="closeModal()" class="close-modal">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="id" id="userId">
                <div class="form-group">
                    <label class="form-label">Nome</label>
                    <input type="text" name="name" id="userName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Usuário (Login)</label>
                    <input type="text" name="username" id="userUsername" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Senha</label>
                    <input type="password" name="password" id="userPassword" class="form-control" placeholder="Deixe em branco para manter a atual">
                    <small style="color: #666; display: block; margin-top: 5px;">Obrigatório para novos usuários</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Função</label>
                    <select name="role" id="userRole" class="form-control">
                        <option value="employee">Funcionário</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal()" class="btn btn-outline">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('userModal');
        
        function openModal() {
            document.getElementById('modalTitle').innerText = 'Novo Usuário';
            document.getElementById('userId').value = '';
            document.getElementById('userName').value = '';
            document.getElementById('userUsername').value = '';
            document.getElementById('userPassword').required = true;
            document.getElementById('userRole').value = 'employee';
            modal.style.display = 'flex';
        }
        
        function editUser(user) {
            document.getElementById('modalTitle').innerText = 'Editar Usuário';
            document.getElementById('userId').value = user.id;
            document.getElementById('userName').value = user.name;
            document.getElementById('userUsername').value = user.username;
            document.getElementById('userPassword').required = false;
            document.getElementById('userRole').value = user.role;
            modal.style.display = 'flex';
        }
        
        function closeModal() {
            modal.style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>