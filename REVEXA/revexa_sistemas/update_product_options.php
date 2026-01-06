<?php
require_once __DIR__ . '/includes/Database.php';
$db = StoreDatabase::getInstance();

// Update NeoDelivery to have both options
$db->query("UPDATE products SET delivery_options = 'both' WHERE name LIKE '%NeoDelivery%'");

echo "Produto atualizado para ter ambas as opções de entrega.";
?>