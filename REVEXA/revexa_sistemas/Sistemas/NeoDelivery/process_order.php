<?php
require_once __DIR__ . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL);
    exit;
}

$db = Database::getInstance()->getConnection();

// Buscar dados do formulário
$customerName = sanitizeInput($_POST['customer_name']);
$customerPhone = sanitizeInput($_POST['customer_phone']);
$deliveryType = sanitizeInput($_POST['delivery_type']);
$paymentMethod = sanitizeInput($_POST['payment_method']);
$notes = sanitizeInput($_POST['notes'] ?? '');

$customerAddress = '';
$customerComplement = '';
$customerNeighborhood = '';
$customerReference = '';
$deliveryFee = 0;

if ($deliveryType === 'delivery') {
    $customerAddress = sanitizeInput($_POST['customer_address']);
    $customerComplement = sanitizeInput($_POST['customer_complement'] ?? '');
    $customerNeighborhood = sanitizeInput($_POST['customer_neighborhood']);
    $customerReference = sanitizeInput($_POST['customer_reference'] ?? '');
    
    // Buscar taxa de entrega
    $stmt = $db->prepare("SELECT delivery_fee, estimated_time FROM delivery_areas WHERE neighborhood = :neighborhood AND active = 1");
    $stmt->bindValue(':neighborhood', $customerNeighborhood, SQLITE3_TEXT);
    $result = $stmt->execute();
    $area = $result->fetchArray(SQLITE3_ASSOC);
    if ($area) {
        $deliveryFee = $area['delivery_fee'];
    }
}

// Buscar carrinho do localStorage (enviado via JS)
$cartJson = $_POST['cart_data'] ?? '[]';
$cart = json_decode($cartJson, true);

if (empty($cart)) {
    header('Location: ' . BASE_URL . 'cardapio.php');
    exit;
}

// Calcular valores
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$total = $subtotal + $deliveryFee;

// Gerar número do pedido e senha
$orderNumber = generateOrderNumber();
$password = generateOrderPassword();

// Inserir pedido
$estimatedTime = getSetting('delivery_time_estimate', '40-50 minutos');

$stmt = $db->prepare("INSERT INTO orders (
    order_number, password, customer_name, customer_phone, customer_address, 
    customer_complement, customer_neighborhood, customer_reference, 
    delivery_type, payment_method, subtotal, delivery_fee, total, 
    status, estimated_time, notes
) VALUES (
    :order_number, :password, :customer_name, :customer_phone, :customer_address,
    :customer_complement, :customer_neighborhood, :customer_reference,
    :delivery_type, :payment_method, :subtotal, :delivery_fee, :total,
    'pending', :estimated_time, :notes
)");

$stmt->bindValue(':order_number', $orderNumber, SQLITE3_TEXT);
$stmt->bindValue(':password', $password, SQLITE3_TEXT);
$stmt->bindValue(':customer_name', $customerName, SQLITE3_TEXT);
$stmt->bindValue(':customer_phone', $customerPhone, SQLITE3_TEXT);
$stmt->bindValue(':customer_address', $customerAddress, SQLITE3_TEXT);
$stmt->bindValue(':customer_complement', $customerComplement, SQLITE3_TEXT);
$stmt->bindValue(':customer_neighborhood', $customerNeighborhood, SQLITE3_TEXT);
$stmt->bindValue(':customer_reference', $customerReference, SQLITE3_TEXT);
$stmt->bindValue(':delivery_type', $deliveryType, SQLITE3_TEXT);
$stmt->bindValue(':payment_method', $paymentMethod, SQLITE3_TEXT);
$stmt->bindValue(':subtotal', $subtotal, SQLITE3_FLOAT);
$stmt->bindValue(':delivery_fee', $deliveryFee, SQLITE3_FLOAT);
$stmt->bindValue(':total', $total, SQLITE3_FLOAT);
$stmt->bindValue(':estimated_time', $estimatedTime, SQLITE3_TEXT);
$stmt->bindValue(':notes', $notes, SQLITE3_TEXT);

if (!$stmt->execute()) {
    die('Erro ao criar pedido');
}

$orderId = $db->lastInsertRowID();

// Inserir itens do pedido
foreach ($cart as $item) {
    $stmt = $db->prepare("INSERT INTO order_items (
        order_id, product_id, product_name, quantity, unit_price, options, subtotal
    ) VALUES (
        :order_id, :product_id, :product_name, :quantity, :unit_price, :options, :subtotal
    )");
    
    $stmt->bindValue(':order_id', $orderId, SQLITE3_INTEGER);
    $stmt->bindValue(':product_id', $item['id'], SQLITE3_INTEGER);
    $stmt->bindValue(':product_name', $item['name'], SQLITE3_TEXT);
    $stmt->bindValue(':quantity', $item['quantity'], SQLITE3_INTEGER);
    $stmt->bindValue(':unit_price', $item['price'], SQLITE3_FLOAT);
    $stmt->bindValue(':options', $item['options'] ?? '', SQLITE3_TEXT);
    $stmt->bindValue(':subtotal', $item['price'] * $item['quantity'], SQLITE3_FLOAT);
    $stmt->execute();
}

// Redirecionar para página de confirmação
header('Location: ' . BASE_URL . 'order_confirmation.php?order=' . $orderNumber);
exit;
