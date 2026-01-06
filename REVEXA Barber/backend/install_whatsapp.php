<?php
// Instalador Automático do Servidor WhatsApp REVEXA Barber
header("Content-Type: text/html; charset=UTF-8");

$isSharedHosting = function_exists('shell_exec') === false || 
                   strpos(ini_get('disable_functions'), 'shell_exec') !== false;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador WhatsApp - REVEXA Barber</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #fff;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #2a2a2a;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        h1 {
            color: #FFD700;
            font-size: 36px;
            margin-bottom: 10px;
        }
        h2 {
            color: #FFD700;
            font-size: 24px;
            margin: 30px 0 15px 0;
        }
        h3 {
            color: #fff;
            font-size: 18px;
            margin: 20px 0 10px 0;
        }
        .subtitle {
            color: #999;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .alert {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border-left: 4px solid;
        }
        .alert.warning {
            background: rgba(245, 158, 11, 0.1);
            border-color: #f59e0b;
            color: #fbbf24;
        }
        .alert.success {
            background: rgba(34, 197, 94, 0.1);
            border-color: #22c55e;
            color: #4ade80;
        }
        .alert.error {
            background: rgba(239, 68, 68, 0.1);
            border-color: #ef4444;
            color: #f87171;
        }
        .option-card {
            background: #333;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            border: 2px solid #444;
            transition: all 0.3s;
        }
        .option-card:hover {
            border-color: #FFD700;
            transform: translateY(-2px);
        }
        .option-card h3 {
            color: #FFD700;
            margin-top: 0;
        }
        ol {
            line-height: 2;
            margin-left: 25px;
            color: #ddd;
        }
        .command {
            background: #1a1a1a;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            margin: 15px 0;
            border-left: 3px solid #FFD700;
            color: #fbbf24;
        }
        .button {
            background: #FFD700;
            color: #1a1a1a;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin-top: 15px;
            transition: all 0.3s;
        }
        .button:hover {
            background: #ffc700;
            transform: translateY(-2px);
        }
        input[type="text"] {
            width: 100%;
            padding: 15px;
            background: #333;
            border: 2px solid #555;
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
            margin-bottom: 15px;
        }
        input[type="text"]:focus {
            outline: none;
            border-color: #FFD700;
        }
        a {
            color: #FFD700;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .download-links {
            background: #333;
            padding: 20px;
            border-radius: 12px;
            margin-top: 30px;
        }
        .download-links ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .download-links li {
            padding: 10px 0;
            border-bottom: 1px solid #444;
        }
        .download-links li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Instalador WhatsApp</h1>
        <p class="subtitle">REVEXA Barber - Configuração Automática</p>

        <?php if ($isSharedHosting): ?>
            
            <div class="alert warning">
                <strong>⚠️ Hospedagem Compartilhada Detectada</strong><br><br>
                Seu servidor não permite executar Node.js diretamente por questões de segurança.<br>
                <strong>Solução:</strong> Hospede o servidor WhatsApp gratuitamente em uma plataforma externa!
            </div>

            <h2>📋 Opções de Deploy Gratuito</h2>

            <div class="option-card">
                <h3>🚀 Opção 1: Render.com (Recomendado)</h3>
                <ol>
                    <li>Acesse <a href="https://render.com" target="_blank">render.com</a> e crie uma conta gratuita</li>
                    <li>Clique em <strong>"New +"</strong> → <strong>"Web Service"</strong></li>
                    <li>Conecte com GitHub ou faça upload manual dos arquivos</li>
                    <li>Configure:
                        <div class="command">Build Command: npm install<br>Start Command: node server.js<br>Port: 3001</div>
                    </li>
                    <li>Aguarde o deploy (leva ~2 minutos)</li>
                    <li>Copie a URL gerada (ex: <code>https://revexa-whatsapp.onrender.com</code>)</li>
                </ol>
            </div>

            <div class="option-card">
                <h3>⚡ Opção 2: Railway.app</h3>
                <ol>
                    <li>Acesse <a href="https://railway.app" target="_blank">railway.app</a> e crie conta</li>
                    <li>Clique em <strong>"New Project"</strong> → <strong>"Deploy from GitHub"</strong></li>
                    <li>Ou faça upload manual dos arquivos</li>
                    <li>Railway detecta Node.js automaticamente</li>
                    <li>Copie a URL pública gerada</li>
                </ol>
            </div>

            <div class="option-card">
                <h3>🔹 Opção 3: Replit.com</h3>
                <ol>
                    <li>Acesse <a href="https://replit.com" target="_blank">replit.com</a></li>
                    <li>Crie novo Repl → <strong>Node.js</strong></li>
                    <li>Faça upload dos arquivos (server.js, package.json)</li>
                    <li>Clique em <strong>"Run"</strong></li>
                    <li>Use a URL gerada automaticamente</li>
                </ol>
            </div>

            <h2>🔗 Configurar URL do Servidor</h2>
            
            <form method="POST">
                <p style="color: #999; margin-bottom: 15px;">
                    Após fazer o deploy, cole a URL pública do seu servidor WhatsApp aqui:
                </p>
                
                <input type="text" 
                       name="server_url" 
                       placeholder="https://seu-servidor.onrender.com" 
                       value="<?php echo htmlspecialchars($_POST['server_url'] ?? ''); ?>"
                       required>
                
                <?php if (isset($_POST['server_url']) && !empty($_POST['server_url'])): ?>
                    <?php
                    $url = trim($_POST['server_url']);
                    
                    // Testar conexão
                    $ch = curl_init($url . '/status');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    if ($httpCode == 200 || $httpCode == 404): ?>
                        <div class="alert success">
                            <strong>✅ Servidor acessível!</strong><br><br>
                            URL configurada: <strong><?php echo htmlspecialchars($url); ?></strong><br><br>
                            <strong>Próximo passo:</strong> Atualize o código do app Flutter para usar esta URL.
                        </div>
                        
                        <?php
                        // Salvar configuração
                        $configFile = __DIR__ . '/whatsapp_config.json';
                        file_put_contents($configFile, json_encode(['server_url' => $url], JSON_PRETTY_PRINT));
                        ?>
                        
                        <div class="command">
// Atualize em: lib/screens/whatsapp_config_screen.dart<br>
final String serverUrl = '<?php echo $url; ?>';
                        </div>
                        
                    <?php else: ?>
                        <div class="alert error">
                            <strong>❌ Não foi possível conectar ao servidor</strong><br><br>
                            Código HTTP: <?php echo $httpCode; ?><br>
                            Verifique se:<br>
                            • A URL está correta<br>
                            • O servidor está rodando<br>
                            • A porta está acessível
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <button type="submit" class="button">
                    🔗 Testar e Salvar URL
                </button>
            </form>

            <div class="download-links">
                <h3>📦 Arquivos para Deploy</h3>
                <p style="color: #999; margin-bottom: 15px;">
                    Baixe estes arquivos do FTP (<code>/whatsapp-server/</code>) e faça upload na plataforma:
                </p>
                <ul>
                    <li>📄 <strong>server.js</strong> - Código principal do servidor</li>
                    <li>📄 <strong>package.json</strong> - Dependências do Node.js</li>
                    <li>📄 <strong>README.md</strong> - Documentação</li>
                </ul>
                <p style="color: #999; margin-top: 15px; font-size: 14px;">
                    Os arquivos estão disponíveis em: <code>ftp.revexa.com.br/whatsapp-server/</code>
                </p>
            </div>

        <?php else: ?>
            
            <div class="alert success">
                <strong>✅ VPS/Servidor Dedicado Detectado</strong><br><br>
                Seu servidor suporta Node.js! Use os comandos SSH abaixo.
            </div>

            <div class="command">
cd /var/www/whatsapp-server<br>
npm install<br>
pm2 start server.js --name whatsapp-revexa<br>
pm2 save<br>
pm2 startup
            </div>

        <?php endif; ?>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #444; color: #999; font-size: 14px; text-align: center;">
            <strong>REVEXA Barber</strong> - Sistema de Gestão para Barbearias<br>
            © 2025 - Todos os direitos reservados
        </div>
    </div>
</body>
</html>
