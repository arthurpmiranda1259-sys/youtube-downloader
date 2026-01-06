<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

$host = "mysql.revexa.com.br";
$db_name = "revexa01";
$username = "revexa01";
$password = "mamaco12";

try {
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
    $conn->exec("set names utf8");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test insert
    $stmt = $conn->prepare("INSERT INTO barbers (barbershop_id, name, phone, commission_percentage) VALUES (?, ?, ?, ?)");
    $result = $stmt->execute([1, "Teste", "11999999999", 50]);
    
    echo json_encode(["success" => true, "id" => $conn->lastInsertId()]);
} catch(Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
