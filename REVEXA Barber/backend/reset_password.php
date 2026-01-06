<?php
header("Content-Type: application/json");

// Configurações do Banco de Dados
$host = "mysql.revexa.com.br";
$db_name = "revexa01";
$username = "revexa01";
$password = "mamaco12";

try {
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user = 'admin';
    $pass = 'admin123';
    $hash = hash('sha256', $pass);

    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$user]);
    
    if ($stmt->rowCount() > 0) {
        // Update
        $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
        $stmt->execute([$hash, $user]);
        echo json_encode(["status" => "success", "message" => "Password for 'admin' reset to 'admin123'"]);
    } else {
        // Create
        $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role, full_name) VALUES (?, ?, 'admin', 'System Admin')");
        $stmt->execute([$user, $hash]);
        echo json_encode(["status" => "success", "message" => "User 'admin' created with password 'admin123'"]);
    }

} catch(PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>