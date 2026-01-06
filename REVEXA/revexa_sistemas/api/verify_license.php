<?php
require_once __DIR__ . '/../includes/Database.php';

header('Content-Type: application/json');

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$license_key = $input['license_key'] ?? '';
$domain = $input['domain'] ?? '';

if (empty($license_key)) {
    echo json_encode(['status' => 'error', 'message' => 'License key required']);
    exit;
}

$db = StoreDatabase::getInstance();
$license = $db->fetch("SELECT * FROM licenses WHERE license_key = ?", [$license_key]);

if (!$license) {
    echo json_encode(['status' => 'invalid', 'message' => 'License not found']);
    exit;
}

// Check Status
if ($license['status'] !== 'active') {
    echo json_encode(['status' => 'blocked', 'message' => 'License is blocked or inactive']);
    exit;
}

// Check Expiration
if ($license['expires_at'] && strtotime($license['expires_at']) < time()) {
    // Auto-expire if date passed
    $db->query("UPDATE licenses SET status = 'expired' WHERE id = ?", [$license['id']]);
    echo json_encode(['status' => 'expired', 'message' => 'License expired']);
    exit;
}

// Check Domain (Optional: Bind license to first domain used)
if (empty($license['domain']) && !empty($domain)) {
    $db->query("UPDATE licenses SET domain = ? WHERE id = ?", [$domain, $license['id']]);
} elseif (!empty($license['domain'])) {
    // Parse stored domain to get host
    $storedUrl = parse_url($license['domain']);
    $storedHost = $storedUrl['host'] ?? $license['domain']; // Fallback if not a URL
    
    // Normalize hosts (remove www.)
    $storedHost = str_replace('www.', '', $storedHost);
    $requestHost = str_replace('www.', '', $domain);
    
    if ($storedHost !== $requestHost) {
        echo json_encode(['status' => 'invalid_domain', 'message' => 'License domain mismatch: ' . $storedHost . ' vs ' . $requestHost]);
        exit;
    }
}

echo json_encode(['status' => 'active', 'message' => 'License active']);
