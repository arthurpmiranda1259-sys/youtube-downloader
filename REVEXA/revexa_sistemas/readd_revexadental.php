<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';

$db = StoreDatabase::getInstance();
$conn = $db->getConnection();

echo "<h1>Restaurando RevexaDental</h1>";

// 1. Check if product exists
$stmt = $conn->query("SELECT * FROM products WHERE name LIKE '%Dental%' OR name LIKE '%Dentista%'");
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    echo "<p>⚠️ O produto já existe (ID: {$existing['id']}). Atualizando configurações...</p>";
    $stmt = $conn->prepare("UPDATE products SET 
        delivery_method = 'hosted', 
        delivery_options = 'hosted',
        billing_cycle = 'monthly',
        monthly_price = 99.90,
        active = 1
        WHERE id = ?");
    $stmt->execute([$existing['id']]);
    echo "<p>✅ Produto atualizado.</p>";
} else {
    echo "<p>➕ Produto não encontrado. Adicionando...</p>";
    $stmt = $conn->prepare("INSERT INTO products 
        (name, description, price, monthly_price, type, delivery_method, delivery_options, billing_cycle, image_url, active) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
    
    $productData = [
        'RevexaDental - Sistema de Gestão para Dentistas',
        'Sistema completo para gestão de clínicas odontológicas com controle de pacientes, agendamentos, prontuários e financeiro.',
        99.90, // Preço de ativação
        99.90, // Mensalidade
        'system',
        'hosted',
        'hosted',
        'monthly',
        'https://images.unsplash.com/photo-1588776814546-1ffcf47267a5?w=800'
    ];
    
    $stmt->execute($productData);
    echo "<p>✅ Produto RevexaDental readicionado com sucesso! (ID: " . $conn->lastInsertId() . ")</p>";
}

echo "<p><a href='index.php'>Voltar para a Loja</a></p>";
?>