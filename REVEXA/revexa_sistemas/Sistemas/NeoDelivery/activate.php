<?php
require_once __DIR__ . '/config/config.php';

$error = '';
$success = '';

// Handle Auto-Activation via GET
if (isset($_GET['auto_key'])) {
    $_POST['license_key'] = $_GET['auto_key'];
    $_SERVER['REQUEST_METHOD'] = 'POST';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $license_key = trim($_POST['license_key']);
    
    if (empty($license_key)) {
        $error = 'Por favor, insira a chave de licença.';
    } else {
        // Verify with the licensing server
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local dev
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $data = json_decode($response, true);
            
            if ($data['status'] === 'active') {
                // Save license and verification time
                updateSetting('license_key', $license_key);
                updateSetting('license_last_check', time());
                updateSetting('license_data', json_encode($data));
                
                $success = 'Licença ativada com sucesso!';
                header("Location: index.php"); // Immediate redirect for auto-activation
                exit;
            } else {
                $error = 'Licença inválida ou expirada: ' . ($data['message'] ?? 'Erro desconhecido');
            }
        } else {
            $error = 'Erro ao conectar com o servidor de licenças. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ativação do Sistema | NeoDelivery</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background-color: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Inter', sans-serif;
        }
        .activation-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }
        .logo {
            font-size: 24px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 10px;
            display: block;
        }
        .subtitle {
            color: #6b7280;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .btn-primary {
            width: 100%;
            padding: 12px;
            background-color: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .alert-success {
            background-color: #dcfce7;
            color: #166534;
        }
    </style>
</head>
<body>
    <div class="activation-card">
        <span class="logo">NeoDelivery</span>
        <p class="subtitle">Este sistema requer uma licença válida para funcionar.</p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Chave de Licença</label>
                    <input type="text" name="license_key" class="form-control" placeholder="Cole sua chave aqui..." required>
                </div>
                <button type="submit" class="btn-primary">Ativar Sistema</button>
            </form>
        <?php endif; ?>
        
        <p style="margin-top: 20px; font-size: 12px; color: #9ca3af;">
            Não tem uma licença? <a href="#" style="color: #2563eb;">Compre agora</a>
        </p>
    </div>
</body>
</html>
