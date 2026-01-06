<?php
/**
 * RESET DE SENHA - REVEXA DENTAL
 * Use este script se não conseguir fazer login
 */

$db_path = __DIR__ . '/config/dentista.db';

if (!file_exists($db_path)) {
    die("❌ Banco de dados não encontrado! Execute install.php primeiro.");
}

try {
    $db = new PDO('sqlite:' . $db_path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Gerar nova senha
    $nova_senha = password_hash('admin123', PASSWORD_BCRYPT);
    
    // Atualizar senha do admin
    $stmt = $db->prepare("UPDATE usuarios SET senha = ? WHERE email = 'admin@revexa.com.br'");
    $stmt->execute([$nova_senha]);
    
    if ($stmt->rowCount() > 0) {
        echo "✅ Senha resetada com sucesso!<br><br>";
        echo "<strong>Login:</strong> admin@revexa.com.br<br>";
        echo "<strong>Senha:</strong> admin123<br><br>";
        echo "<a href='index.php'>→ Fazer Login</a>";
    } else {
        echo "❌ Usuário admin não encontrado! Execute install.php primeiro.";
    }
    
} catch (PDOException $e) {
    die("❌ Erro: " . $e->getMessage());
}
