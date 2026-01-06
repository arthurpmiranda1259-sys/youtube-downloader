<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico - REVEXA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #667eea; margin-bottom: 20px; }
        .check { background: #e8f5e9; border-left: 4px solid #4caf50; padding: 15px; margin: 10px 0; }
        .error { background: #ffebee; border-left: 4px solid #f44336; padding: 15px; margin: 10px 0; }
        .info { background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin: 10px 0; }
        code { background: #f5f5f5; padding: 2px 5px; border-radius: 3px; }
        .btn { display: inline-block; background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .btn:hover { background: #5568d3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico do Sistema</h1>
        
        <?php
        $checks = [];
        
        // Verifica PHP
        $checks[] = [
            'name' => 'PHP',
            'status' => true,
            'message' => 'PHP está funcionando! Versão: ' . phpversion()
        ];
        
        // Verifica arquivo de config
        $config_exists = file_exists('config/config.php');
        $checks[] = [
            'name' => 'Arquivo de Configuração',
            'status' => $config_exists,
            'message' => $config_exists ? 'config/config.php encontrado' : 'config/config.php NÃO encontrado'
        ];
        
        // Verifica permissões de escrita
        $writable = is_writable(__DIR__);
        $checks[] = [
            'name' => 'Permissões de Escrita',
            'status' => $writable,
            'message' => $writable ? 'Diretório tem permissão de escrita' : 'Diretório SEM permissão de escrita'
        ];
        
        // Verifica sessões
        $session_test = session_start();
        $checks[] = [
            'name' => 'Sessões PHP',
            'status' => $session_test,
            'message' => $session_test ? 'Sessões estão funcionando' : 'Problema com sessões'
        ];
        
        // Verifica PDO SQLite
        $pdo_available = extension_loaded('pdo_sqlite');
        $checks[] = [
            'name' => 'PDO SQLite',
            'status' => $pdo_available,
            'message' => $pdo_available ? 'Extensão PDO SQLite disponível' : 'PDO SQLite NÃO disponível'
        ];
        
        // Mostra resultados
        foreach ($checks as $check) {
            $class = $check['status'] ? 'check' : 'error';
            $icon = $check['status'] ? '✅' : '❌';
            echo "<div class='$class'>";
            echo "<strong>$icon {$check['name']}</strong><br>";
            echo $check['message'];
            echo "</div>";
        }
        ?>
        
        <div class="info">
            <strong>📂 Caminho Atual:</strong><br>
            <code><?php echo __DIR__; ?></code>
        </div>
        
        <div class="info">
            <strong>🌐 URL Atual:</strong><br>
            <code><?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?></code>
        </div>
        
        <div class="info">
            <strong>🔑 Informações da Instância:</strong><br>
            Licença: d3362fade295de66befaad45bb730db4<br>
            Cliente: arthurmiranda1259@gmail.com
        </div>
        
        <a href="login.php" class="btn">🚀 Ir para Login</a>
    </div>
</body>
</html>
