<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/OrderProcessor.php';

// Log incoming webhook for debugging
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Mercado Pago sends a notification with 'type' or 'topic'
// Usually: ?topic=payment&id=123456789 or POST body with type=payment

$payment_id = $_GET['id'] ?? ($data['data']['id'] ?? null);
$topic = $_GET['topic'] ?? ($data['type'] ?? null);

// Sometimes MP sends just 'id' and 'topic' in query params
if (!$payment_id && isset($_GET['data_id'])) {
    $payment_id = $_GET['data_id'];
}

if (($topic === 'payment' || isset($data['action']) && $data['action'] == 'payment.created' || isset($data['action']) && $data['action'] == 'payment.updated') && $payment_id) {
    
    $db = StoreDatabase::getInstance();
    $mp_access_token = $db->getSetting('mp_access_token');

    if ($mp_access_token) {
        // Verify payment status with Mercado Pago API
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.mercadopago.com/v1/payments/$payment_id",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $mp_access_token
            ],
        ]);

        $response = curl_exec($curl);
        $payment = json_decode($response, true);
        curl_close($curl);

        if (isset($payment['status']) && $payment['status'] === 'approved') {
            $external_reference = $payment['external_reference'];
            
            // Find order by external_reference
            $order = $db->fetch("SELECT * FROM orders WHERE external_reference = ?", [$external_reference]);
            
            if ($order) {
                try {
                    OrderProcessor::approveOrder($order['id']);
                    http_response_code(200);
                    echo "Order Approved";
                    exit;
                } catch (Exception $e) {
                    error_log("Webhook Error: " . $e->getMessage());
                }
            } else {
                error_log("Webhook: Order not found for ref " . $external_reference);
            }
        }
    }
}

http_response_code(200);
?>