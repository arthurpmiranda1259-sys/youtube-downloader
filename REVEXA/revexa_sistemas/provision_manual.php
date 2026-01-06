<?php
/**
 * Script Manual de Provisionamento
 * Cria a instancia do cliente diretamente copiando arquivos
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== PROVISIONAMENTO MANUAL ===\n\n";

// Configuracoes
$license_key = 'd3362fade295de66befaad45bb730db4';
$customer_email = 'arthurmiranda1259@gmail.com';
$store_folder = "store-427cd6be";
$base_path = __DIR__;
$template_path = $base_path . "/Sistemas/RevexaDental";
$lojas_path = $base_path . "/lojas";
$instance_path = $lojas_path . "/" . $store_folder;

echo "Caminhos:\n";
echo "  - Base: $base_path\n";
echo "  - Template: $template_path\n";
echo "  - Lojas: $lojas_path\n";
echo "  - Instancia: $instance_path\n\n";

// Verifica se o template existe
if (!is_dir($template_path)) {
    die("ERRO: Template nao encontrado em: $template_path\n");
}
echo "Template encontrado\n";

// Cria pasta /lojas se nao existir
if (!is_dir($lojas_path)) {
    echo "Criando pasta /lojas...\n";
    if (!mkdir($lojas_path, 0755, true)) {
        die("ERRO: Nao foi possivel criar pasta /lojas\n");
    }
    echo "Pasta /lojas criada\n";
} else {
    echo "Pasta /lojas ja existe\n";
}

// Remove instancia antiga se existir
if (is_dir($instance_path)) {
    echo "Removendo instancia antiga...\n";
    function deleteDirectory($dir) {
        if (!is_dir($dir)) return false;
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? deleteDirectory($path) : unlink($path);
        }
        return rmdir($dir);
    }
    deleteDirectory($instance_path);
    echo "Instancia antiga removida\n";
}

// Cria pasta da instancia
echo "Criando pasta da instancia...\n";
if (!mkdir($instance_path, 0755, true)) {
    die("ERRO: Nao foi possivel criar pasta da instancia\n");
}
echo "Pasta da instancia criada\n";

// Funcao para copiar recursivamente
function copyDirectory($src, $dst) {
    if (!is_dir($src)) return false;
    if (!is_dir($dst)) mkdir($dst, 0755, true);
    
    $items = scandir($src);
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;
        
        $src_path = $src . DIRECTORY_SEPARATOR . $item;
        $dst_path = $dst . DIRECTORY_SEPARATOR . $item;
        
        if (is_dir($src_path)) {
            copyDirectory($src_path, $dst_path);
        } else {
            copy($src_path, $dst_path);
        }
    }
    return true;
}

// Copia apenas os arquivos essenciais
echo "Copiando arquivos essenciais...\n";

$essential_files = [
    'config_minimal.php' => 'config/config.php',
    'login_simple.php' => 'login.php',
    'dashboard_simple.php' => 'dashboard.php',
    'logout_simple.php' => 'logout.php'
];

foreach ($essential_files as $source => $dest) {
    $source_file = $template_path . '/' . $source;
    $dest_file = $instance_path . '/' . $dest;
    
    if (!file_exists($source_file)) {
        echo "Arquivo nao encontrado: $source\n";
        continue;
    }
    
    // Cria diretorio de destino se necessario
    $dest_dir = dirname($dest_file);
    if (!is_dir($dest_dir)) {
        mkdir($dest_dir, 0755, true);
    }
    
    if (copy($source_file, $dest_file)) {
        echo "  Copiado: $source -> $dest\n";
    } else {
        echo "  Falha ao copiar: $source\n";
    }
}

// Copia pastas essenciais
echo "\nCopiando pastas essenciais...\n";
$essential_dirs = ['assets', 'includes', 'database'];

foreach ($essential_dirs as $dir) {
    $src_dir = $template_path . '/' . $dir;
    $dst_dir = $instance_path . '/' . $dir;
    
    if (is_dir($src_dir)) {
        if (copyDirectory($src_dir, $dst_dir)) {
            echo "  Copiado: $dir/\n";
        } else {
            echo "  Falha ao copiar: $dir/\n";
        }
    }
}

// Cria arquivo .htaccess personalizado (SEM rewrite problematico)
echo "\nCriando .htaccess...\n";
$htaccess_content = <<<HTACCESS
# Configuracao basica
Options -Indexes
DirectoryIndex login.php

# Protecao de arquivos sensiveis
<Files "config.php">
    Order allow,deny
    Deny from all
</Files>

<Files "*.db">
    Order allow,deny
    Deny from all
</Files>

# PHP settings
php_flag display_errors Off
php_value upload_max_filesize 10M
php_value post_max_size 10M
HTACCESS;

file_put_contents($instance_path . '/.htaccess', $htaccess_content);
echo ".htaccess criado\n";

// Atualiza configuracao com valores corretos
echo "\nAtualizando configuracao...\n";
$config_file = $instance_path . '/config/config.php';
if (file_exists($config_file)) {
    $config_content = file_get_contents($config_file);
    
    // Substitui valores de exemplo pelos reais
    $config_content = str_replace("'REVEXA-XXXXX'", "'$license_key'", $config_content);
    $config_content = str_replace("'cliente@exemplo.com'", "'$customer_email'", $config_content);
    
    file_put_contents($config_file, $config_content);
    echo "Configuracao atualizada\n";
}

// Verifica resultado
echo "\n=== VERIFICACAO FINAL ===\n";
if (is_dir($instance_path)) {
    $files = scandir($instance_path);
    echo "Instancia criada com sucesso!\n";
    echo "Arquivos na pasta: " . (count($files) - 2) . "\n";
    echo "\nURL de Acesso:\n";
    echo "   https://revexa.com.br/revexa_sistemas/lojas/$store_folder/\n\n";
    
    echo "Credenciais de Acesso:\n";
    echo "   Email: admin@admin.com\n";
    echo "   Senha: admin123\n\n";
    
    echo "PROVISIONAMENTO CONCLUIDO!\n";
} else {
    echo "ERRO: Instancia nao foi criada\n";
}
