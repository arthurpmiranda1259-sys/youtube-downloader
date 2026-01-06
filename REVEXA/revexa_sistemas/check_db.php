<?php
require_once 'config/config.php';
require_once 'includes/Database.php';

$db = StoreDatabase::getInstance();
$conn = $db->getConnection();

echo "--- Products ---\n";
$stmt = $conn->query("SELECT id, name, delivery_method, type FROM products");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

echo "\n--- License (Key ending in F491F27) ---\n";
$key = '2177AD4B21983C9715088B1F5F491F27';
$stmt = $conn->prepare("SELECT * FROM licenses WHERE license_key = ?");
$stmt->execute([$key]);
$license = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($license);
?>