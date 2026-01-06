<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Forcing Provisioning</h1>";

$source = __DIR__ . '/Sistemas/RevexaDental';
$destRoot = __DIR__ . '/lojas';
$slug = 'store-d3362fad';
$target = $destRoot . '/' . $slug;

echo "<p>Source: $source</p>";
echo "<p>Target: $target</p>";

if (!is_dir($source)) {
    die("❌ Source directory not found!");
}

if (!is_dir($destRoot)) {
    echo "<p>Creating /lojas...</p>";
    if (!mkdir($destRoot, 0755, true)) {
        die("❌ Failed to create /lojas directory. Check permissions.");
    }
}

function recursive_copy($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);
    
    echo "<ul>";
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                echo "<li>📂 Copying dir: $file</li>";
                recursive_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                echo "<li>📄 Copying file: $file</li>";
                if (!copy($src . '/' . $file,$dst . '/' . $file)) {
                    echo "❌ Failed to copy $file<br>";
                }
            }
        }
    }
    echo "</ul>";
    closedir($dir);
}

echo "<h3>Starting Copy...</h3>";
recursive_copy($source, $target);

echo "<h3>Post-Copy Setup</h3>";

// Reset DB
$dbFile = $target . '/config/dentista.db';
if (file_exists($dbFile)) {
    echo "<p>🗑️ Deleting existing DB template...</p>";
    unlink($dbFile);
}

// Create directories
$dirs = ['/data', '/config', '/uploads'];
foreach ($dirs as $d) {
    if (!is_dir($target . $d)) {
        echo "<p>📁 Creating $d...</p>";
        mkdir($target . $d, 0755, true);
    }
}

echo "<h2>✅ Done!</h2>";
echo "<p><a href='lojas/$slug/dashboard.php' target='_blank'>Open Dashboard</a></p>";
?>