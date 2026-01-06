<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Debug Criação</title><style>body{font-family:monospace;padding:20px;background:#000;color:#0f0;}h1{color:#0ff;}</style></head><body>";
echo "<h1>DEBUG: Criação de Instância</h1>";

$source = realpath(__DIR__ . '/Sistemas/RevexaDental');
$dest = __DIR__ . '/lojas/store-427cd6be';

echo "<p>Source Path: " . ($source ? $source : 'NOT FOUND') . "</p>";
echo "<p>Dest Path: $dest</p>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Current Dir: " . getcwd() . "</p>";

if (!$source) {
    die("<p style='color:red'>FATAL: Source directory does not exist!</p></body></html>");
}

echo "<p>Source exists: YES</p>";
echo "<p>Source readable: " . (is_readable($source) ? 'YES' : 'NO') . "</p>";

// Create lojas dir
if (!is_dir(__DIR__ . '/lojas')) {
    echo "<p>Creating /lojas directory...</p>";
    if (mkdir(__DIR__ . '/lojas', 0777, true)) {
        echo "<p style='color:#0f0'>✓ /lojas created</p>";
    } else {
        die("<p style='color:red'>✗ Failed to create /lojas</p></body></html>");
    }
} else {
    echo "<p>/lojas already exists</p>";
}

// Copy using shell command (Windows)
echo "<h2>Attempting Copy...</h2>";
$cmd = 'xcopy /E /I /H /Y "' . $source . '" "' . $dest . '" 2>&1';
echo "<p>Command: $cmd</p>";
$output = [];
$return = 0;
exec($cmd, $output, $return);

echo "<p>Return Code: $return</p>";
echo "<p>Output:</p><pre>" . implode("\n", $output) . "</pre>";

// Verify
if (is_dir($dest)) {
    echo "<p style='color:#0f0'>✓ Destination created!</p>";
    $files = scandir($dest);
    echo "<p>Files copied: " . (count($files) - 2) . "</p>";
    
    // Check critical files
    $critical = ['dashboard.php', 'config/config.php', 'index.php'];
    foreach ($critical as $file) {
        $exists = file_exists($dest . '/' . $file);
        echo "<p>$file: " . ($exists ? '✓' : '✗') . "</p>";
    }
    
    echo "<h2>✓ SUCCESS!</h2>";
    echo "<p><a href='lojas/store-427cd6be/index.php' style='color:#0ff'>→ Try to Open System</a></p>";
} else {
    echo "<p style='color:red'>✗ Destination not created</p>";
}

echo "</body></html>";
?>
