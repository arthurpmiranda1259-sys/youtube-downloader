<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Pegar o endpoint da URL
$path = $_GET['endpoint'] ?? '';

// Simular respostas enquanto o servidor Node.js não está rodando
switch ($path) {
    case 'status':
        echo json_encode([
            'connected' => false,
            'message' => '⚠️ Servidor WhatsApp não configurado ainda',
            'info' => 'Acesse /whatsapp-server/GUIA_RAPIDO.md no FTP para instruções'
        ]);
        break;
        
    case 'generate-qr':
        echo json_encode([
            'success' => false,
            'error' => 'Servidor WhatsApp não está rodando',
            'instructions' => [
                '1. Acesse o servidor via SSH',
                '2. cd /www/whatsapp-server',
                '3. npm install',
                '4. pm2 start server.js --name whatsapp-revexa',
                '5. pm2 save',
                '',
                'Ou configure pelo painel de controle do servidor',
                'Veja: /whatsapp-server/GUIA_RAPIDO.md'
            ]
        ]);
        break;
        
    case 'send-message':
        echo json_encode([
            'success' => false,
            'error' => 'Servidor WhatsApp não configurado'
        ]);
        break;
        
    default:
        http_response_code(404);
        echo json_encode([
            'error' => 'Endpoint não encontrado',
            'available_endpoints' => [
                '/status',
                '/generate-qr',
                '/send-message'
            ]
        ]);
}
?>
