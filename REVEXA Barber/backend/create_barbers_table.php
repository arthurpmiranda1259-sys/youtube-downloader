<?php
header("Content-Type: application/json");

$host = "mysql.revexa.com.br";
$db_name = "revexa01";
$username = "revexa01";
$password = "mamaco12";

try {
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
    $conn->exec("set names utf8");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Criar tabela barbers
    $sql = "CREATE TABLE IF NOT EXISTS barbers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        barbershop_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        commission_percentage DECIMAL(5, 2) DEFAULT 50.00,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (barbershop_id) REFERENCES barbershops(id) ON DELETE CASCADE
    )";
    
    $conn->exec($sql);
    
    // Criar tabela payments se não existir
    $sql2 = "CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        barbershop_id INT NOT NULL,
        appointment_id INT,
        barber_id INT,
        amount DECIMAL(10, 2) NOT NULL,
        payment_method ENUM('cash', 'card', 'pix') DEFAULT 'cash',
        notes TEXT,
        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (barbershop_id) REFERENCES barbershops(id) ON DELETE CASCADE
    )";
    
    $conn->exec($sql2);
    
    // Criar tabela settings se não existir
    $sql3 = "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        barbershop_id INT NOT NULL UNIQUE,
        barbershop_name VARCHAR(100),
        address VARCHAR(255),
        phone VARCHAR(20),
        opening_time TIME DEFAULT '09:00:00',
        closing_time TIME DEFAULT '19:00:00',
        work_days VARCHAR(50) DEFAULT '1,2,3,4,5,6',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (barbershop_id) REFERENCES barbershops(id) ON DELETE CASCADE
    )";
    
    $conn->exec($sql3);
    
    // Adicionar barber_id na tabela appointments se não existir
    try {
        $conn->exec("ALTER TABLE appointments ADD COLUMN barber_id INT AFTER service_id");
    } catch(Exception $e) {
        // Coluna já existe
    }
    
    echo json_encode([
        "success" => true,
        "message" => "Tables created successfully!",
        "tables" => ["barbers", "payments", "settings"]
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        "error" => $e->getMessage()
    ]);
}
?>
