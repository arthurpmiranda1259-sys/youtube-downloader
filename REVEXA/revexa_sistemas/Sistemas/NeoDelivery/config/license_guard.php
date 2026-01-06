<?php
// Prevent direct access
if (!defined('BASE_URL')) exit;

// Skip check if we are already on the activation page
if (basename($_SERVER['PHP_SELF']) === 'activate.php') {
    return;
}

$license_key = getSetting('license_key');
$last_check = getSetting('license_last_check', 0);
$check_interval = 86400; // 24 hours

if (empty($license_key)) {
    header('Location: ' . BASE_URL . 'activate.php');
    exit;
}

// Periodic Re-verification
if (time() - $last_check > $check_interval) {
    $api_url = LICENSE_SERVER_URL;
    $domain = $_SERVER['HTTP_HOST'];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'license_key' => $license_key,
        'domain' => $domain
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Short timeout to not block loading
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $data = json_decode($response, true);
        if ($data['status'] === 'active') {
            updateSetting('license_last_check', time());
            updateSetting('license_data', json_encode($data));
        } else {
            // License is no longer valid
            updateSetting('license_key', ''); // Clear license
            header('Location: ' . BASE_URL . 'activate.php?error=expired');
            exit;
        }
    }
    // If server is down, we allow access for now (grace period)
}
