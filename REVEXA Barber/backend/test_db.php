<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP Version: " . phpversion() . "\n";
echo "Testing database connection...\n";

$host = "mysql.revexa.com.br";
$db_name = "revexa01";
$username = "revexa01";
$password = "mamaco12";

try {
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connected!\n";
    
    // Test barbers table
    $stmt = $conn->query("DESCRIBE barbers");
    echo "\nBarbers table structure:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
