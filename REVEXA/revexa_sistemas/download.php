<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';

session_start();

if (!isset($_SESSION['customer_id'])) {
    die('Acesso negado. Faça login.');
}

if (!isset($_GET['license_id'])) {
    die('Licença não especificada.');
}

$license_id = (int)$_GET['license_id'];
$db = StoreDatabase::getInstance();

// Verify ownership
$license = $db->fetch("SELECT * FROM licenses WHERE id = ? AND customer_email = ?", [$license_id, $_SESSION['customer_email']]);

if (!$license) {
    die('Licença não encontrada ou acesso negado.');
}

// Verify delivery method
if ($license['delivery_method'] == 'hosted') {
    die('Esta licença é para uso hospedado (SaaS). O download do código fonte não está incluído.');
}

// Determine file path
// For this demo, we'll assume the source is in Sistemas/NeoDelivery
// In a real app, this path would be in the products table
$sourceDir = __DIR__ . '/Sistemas/NeoDelivery';
$zipFile = __DIR__ . '/Sistemas/NeoDelivery.zip';

// If zip doesn't exist, try to create it (simple version)
if (!file_exists($zipFile)) {
    if (is_dir($sourceDir)) {
        // This is a placeholder. In production, you'd use ZipArchive to zip the folder.
        // For now, we'll just create a dummy zip if it doesn't exist to prevent errors
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
            $zip->addFromString('readme.txt', 'Obrigado por comprar o NeoDelivery! O código completo estaria aqui.');
            $zip->close();
        }
    } else {
        die('Arquivo fonte não encontrado no servidor.');
    }
}

if (file_exists($zipFile)) {
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="NeoDelivery_Source.zip"');
    header('Content-Length: ' . filesize($zipFile));
    readfile($zipFile);
    exit;
} else {
    die('Erro ao gerar arquivo de download.');
}
