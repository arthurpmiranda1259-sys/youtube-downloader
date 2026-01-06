<?php
// Direct provisioning - no dependencies
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Criação Direta de Instância</h1>";

$sourceDir = __DIR__ . '/Sistemas/RevexaDental';
$targetDir = __DIR__ . '/lojas/store-427cd6be';

echo "<p>Origem: $sourceDir</p>";
echo "<p>Destino: $targetDir</p>";

// Check source
if (!is_dir($sourceDir)) {
    die("<p style='color:red'>❌ Pasta fonte não existe: $sourceDir</p>");
}

// Create lojas if not exists
if (!is_dir(__DIR__ . '/lojas')) {
    echo "<p>Criando pasta /lojas...</p>";
    mkdir(__DIR__ . '/lojas', 0755, true);
}

// Create target
if (is_dir($targetDir)) {
    echo "<p>⚠️ A pasta já existe. Ignorando...</p>";
} else {
    echo "<p>Copiando arquivos...</p>";
    
    function xcopy($source, $dest, $permissions = 0755) {
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }
        
        if (is_file($source)) {
            return copy($source, $dest);
        }
        
        if (!is_dir($dest)) {
            mkdir($dest, $permissions);
        }
        
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            xcopy("$source/$entry", "$dest/$entry", $permissions);
        }
        
        $dir->close();
        return true;
    }
    
    xcopy($sourceDir, $targetDir);
    echo "<p style='color:green'>✅ Arquivos copiados!</p>";
}

// Clean database
$dbFile = $targetDir . '/config/dentista.db';
if (file_exists($dbFile)) {
    unlink($dbFile);
    echo "<p>🗑️ Banco de dados resetado.</p>";
}

// Test access
$dashboardFile = $targetDir . '/dashboard.php';
if (file_exists($dashboardFile)) {
    echo "<p style='color:green'>✅ dashboard.php encontrado!</p>";
    echo "<p><a href='lojas/store-427cd6be/dashboard.php' target='_blank'>Abrir Sistema</a></p>";
} else {
    echo "<p style='color:red'>❌ dashboard.php não encontrado!</p>";
}

echo "<h2>Conteúdo da pasta criada:</h2>";
if (is_dir($targetDir)) {
    $files = scandir($targetDir);
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>$file</li>";
        }
    }
    echo "</ul>";
}

echo "<p><a href='my-account.php'>Voltar para Minha Conta</a></p>";
?>
