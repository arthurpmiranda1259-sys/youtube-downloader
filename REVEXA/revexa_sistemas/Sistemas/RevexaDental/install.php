<?php
/**
 * Script de Instalação do REVEXA DENTAL
 * Execute este arquivo UMA VEZ após fazer upload do sistema
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>REVEXA DENTAL - Instalação</h1>";
echo "<hr>";

$base_path = __DIR__ . '/';
$db_path = $base_path . 'config/dentista.db';
$sql_path = $base_path . 'config/database.sql';

// Verificar se já foi instalado
if (file_exists($db_path)) {
    echo "<p style='color: orange;'>⚠️ O banco de dados já existe!</p>";
    echo "<p>Se deseja reinstalar, delete o arquivo <code>config/dentista.db</code> e execute novamente.</p>";
    echo "<hr>";
    echo "<a href='index.php'>→ Acessar o Sistema</a>";
    exit;
}

echo "<h2>1. Verificando Requisitos</h2>";

// Verificar PHP
$php_version = phpversion();
echo "<p>✓ PHP versão: $php_version</p>";

if (version_compare($php_version, '7.4.0', '<')) {
    die("<p style='color: red;'>✗ PHP 7.4 ou superior é necessário!</p>");
}

// Verificar extensão PDO SQLite
if (!extension_loaded('pdo_sqlite')) {
    die("<p style='color: red;'>✗ Extensão PDO SQLite não está habilitada!</p>");
}
echo "<p>✓ PDO SQLite habilitado</p>";

// Verificar permissões
echo "<h2>2. Verificando Permissões</h2>";

$dirs_to_check = [
    'config' => $base_path . 'config/',
    'uploads' => $base_path . 'uploads/'
];

foreach ($dirs_to_check as $name => $path) {
    if (!is_writable($path)) {
        echo "<p style='color: red;'>✗ Diretório '$name' não tem permissão de escrita!</p>";
        echo "<p>Execute: <code>chmod 777 " . basename($path) . "</code></p>";
    } else {
        echo "<p>✓ Diretório '$name' com permissão OK</p>";
    }
}

// Criar banco de dados
echo "<h2>3. Criando Banco de Dados</h2>";

try {
    // Ler SQL
    if (!file_exists($sql_path)) {
        die("<p style='color: red;'>✗ Arquivo database.sql não encontrado!</p>");
    }
    
    $sql = file_get_contents($sql_path);
    
    // Criar conexão
    $db = new PDO('sqlite:' . $db_path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Executar SQL
    $db->exec($sql);
    
    echo "<p>✓ Banco de dados criado com sucesso!</p>";
    echo "<p>✓ Tabelas criadas</p>";
    echo "<p>✓ Dados iniciais inseridos</p>";
    echo "<p>✓ Usuário administrador criado</p>";
    
    // Verificar criação
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>✓ " . count($tables) . " tabelas criadas: " . implode(', ', $tables) . "</p>";
    
} catch (PDOException $e) {
    die("<p style='color: red;'>✗ Erro ao criar banco: " . $e->getMessage() . "</p>");
}

// Sucesso!
echo "<hr>";
echo "<h2 style='color: green;'>✓ Instalação Concluída com Sucesso!</h2>";
echo "<p><strong>Dados de acesso inicial:</strong></p>";
echo "<ul>";
echo "<li><strong>URL:</strong> <a href='index.php'>" . (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/index.php</a></li>";
echo "<li><strong>Usuário:</strong> admin@revexa.com.br</li>";
echo "<li><strong>Senha:</strong> admin123</li>";
echo "</ul>";
echo "<p style='color: red;'><strong>⚠️ IMPORTANTE: Altere a senha padrão após o primeiro acesso!</strong></p>";
echo "<p style='color: orange;'><strong>⚠️ SEGURANÇA: Delete ou renomeie este arquivo (install.php) após a instalação!</strong></p>";
echo "<hr>";
echo "<a href='index.php' style='display: inline-block; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px;'>→ Acessar o Sistema</a>";
?>
