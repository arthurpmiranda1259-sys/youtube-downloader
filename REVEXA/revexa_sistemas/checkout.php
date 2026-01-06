<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';

session_start();

if (!isset($_GET['id'])) {
    die('Produto não especificado.');
}

$id = (int)$_GET['id'];
$renew_license_id = isset($_GET['renew']) ? (int)$_GET['renew'] : null;

// Require Login
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php?redirect=" . urlencode("checkout.php?id=" . $id . ($renew_license_id ? "&renew=" . $renew_license_id : "")));
    exit;
}

$db = StoreDatabase::getInstance();
$product = $db->fetch("SELECT * FROM products WHERE id = ?", [$id]);

if (!$product) {
    die('Produto não encontrado.');
}

// Validate Renewal
if ($renew_license_id) {
    $license = $db->fetch("SELECT * FROM licenses WHERE id = ? AND customer_email = ? AND product_id = ?", 
        [$renew_license_id, $_SESSION['customer_email'], $id]);
    
    if (!$license) {
        die('Licença inválida para renovação.');
    }
    
    // Force Hosted/Monthly for renewal
    $product['delivery_options'] = 'hosted'; 
    $product['delivery_method'] = 'hosted';
    // Ensure monthly price is used
    if (!empty($product['monthly_price']) && $product['monthly_price'] > 0) {
        $product['price'] = $product['monthly_price'];
    }
}

// Get Settings
$mp_access_token = $db->getSetting('mp_access_token');
$mp_public_key = $db->getSetting('mp_public_key');

$error = '';

// Handle Payment Initiation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay'])) {
    $delivery_method = isset($_POST['delivery_method']) ? $_POST['delivery_method'] : $product['delivery_method'];
    
    // Validate delivery method if options are 'both'
    if (isset($product['delivery_options']) && $product['delivery_options'] == 'both') {
        if (!in_array($delivery_method, ['file', 'hosted'])) {
            $error = 'Selecione uma opção de entrega.';
        }
    }

    if (!$error) {
        if (empty($mp_access_token)) {
            $error = 'Erro: Token de acesso do Mercado Pago não configurado no Admin.';
        } else {
            $curl = curl_init();

            // Determine Price
            $unit_price = (float)$product['price'];
            if ($delivery_method == 'hosted' && !empty($product['monthly_price']) && $product['monthly_price'] > 0) {
                $unit_price = (float)$product['monthly_price'];
            }

            // Generate Unique Reference
            $external_reference = 'ORD-' . uniqid() . '-' . time();
            if ($renew_license_id) {
                $external_reference .= '-R' . $renew_license_id;
            }

            $preference_data = [
                "items" => [
                    [
                        "title" => $product['name'] . ($delivery_method == 'hosted' ? ' (Hospedado - Mensal)' : ' (Download)') . ($renew_license_id ? ' - Renovação' : ''),
                        "quantity" => 1,
                        "currency_id" => "BRL",
                        "unit_price" => $unit_price
                    ]
                ],
                "back_urls" => [
                    "success" => SITE_URL . "/success.php",
                    "failure" => SITE_URL . "/failure.php",
                    "pending" => SITE_URL . "/pending.php"
                ],
                "notification_url" => SITE_URL . "/webhook.php",
                "auto_return" => "approved",
                "external_reference" => $external_reference,
                "metadata" => [
                    "delivery_method" => $delivery_method
                ]
            ];

            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.mercadopago.com/checkout/preferences",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($preference_data),
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer " . $mp_access_token,
                    "Content-Type: application/json"
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                $error = "Erro na comunicação com Mercado Pago: " . $err;
            } else {
                $preference = json_decode($response, true);
                if (isset($preference['init_point'])) {
                    // Create a pending order
                    $db->query("INSERT INTO orders (product_id, customer_name, customer_email, amount, status, payment_id, delivery_method, external_reference) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", 
                        [$product['id'], $_SESSION['customer_name'], $_SESSION['customer_email'], $unit_price, 'pending', $preference['id'], $delivery_method, $external_reference]);
                    
                    header("Location: " . $preference['init_point']);
                    exit;
                } else {
                    // Debug info if preference creation fails
                    $error = "Erro ao criar preferência. MP Respondeu: " . print_r($preference, true);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | <?= htmlspecialchars($product['name']) ?></title>
    <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .checkout-container {
            max-width: 600px;
            margin: 100px auto;
            background: var(--dark-lighter);
            padding: 40px;
            border-radius: var(--radius);
            text-align: center;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .price-tag {
            font-size: 36px;
            color: var(--primary);
            font-weight: 700;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <h1 style="color: var(--white);">Checkout</h1>
        <p style="color: var(--gray-400); margin-top: 10px;">Você está adquirindo:</p>
        <h2 style="color: var(--white); margin: 10px 0;"><?= htmlspecialchars($product['name']) ?></h2>
        
        <div class="price-tag" id="displayPrice">R$ <?= number_format($product['price'], 2, ',', '.') ?></div>
        
        <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: left; font-size: 14px; word-break: break-word;">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <?php if(isset($product['delivery_options']) && $product['delivery_options'] == 'both'): ?>
                <div style="margin-bottom: 25px; text-align: left;">
                    <label style="color: var(--white); display: block; margin-bottom: 10px; font-weight: 500;">Escolha o formato de entrega:</label>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <label style="cursor: pointer;">
                            <input type="radio" name="delivery_method" value="file" checked style="display: none;" onchange="updateSelection(this)">
                            <div class="option-card selected" style="background: var(--gray-800); border: 2px solid var(--primary); padding: 15px; border-radius: 8px; height: 100%;">
                                <div style="color: var(--primary); font-size: 24px; margin-bottom: 10px;"><i class="fas fa-download"></i></div>
                                <div style="color: var(--white); font-weight: 600; margin-bottom: 5px;">Download (Código Fonte)</div>
                                <div style="color: var(--gray-400); font-size: 12px;">Baixe os arquivos e instale no seu próprio servidor.</div>
                            </div>
                        </label>
                        
                        <label style="cursor: pointer;">
                            <input type="radio" name="delivery_method" value="hosted" style="display: none;" onchange="updateSelection(this)">
                            <div class="option-card" style="background: var(--gray-800); border: 2px solid transparent; padding: 15px; border-radius: 8px; height: 100%;">
                                <div style="color: var(--secondary); font-size: 24px; margin-bottom: 10px;"><i class="fas fa-cloud"></i></div>
                                <div style="color: var(--white); font-weight: 600; margin-bottom: 5px;">SaaS (Hospedado)</div>
                                <div style="color: var(--gray-400); font-size: 12px;">Nós hospedamos e configuramos tudo para você.</div>
                            </div>
                        </label>
                    </div>
                </div>
                
                <script>
                    const filePrice = <?= json_encode((float)$product['price']) ?>;
                    const hostedPrice = <?= json_encode((float)($product['monthly_price'] > 0 ? $product['monthly_price'] : $product['price'])) ?>;

                    function updateSelection(radio) {
                        document.querySelectorAll('.option-card').forEach(card => {
                            card.style.borderColor = 'transparent';
                            card.classList.remove('selected');
                        });
                        if (radio.checked) {
                            radio.nextElementSibling.style.borderColor = radio.value == 'file' ? 'var(--primary)' : 'var(--secondary)';
                            radio.nextElementSibling.classList.add('selected');
                            
                            // Update Price Display
                            const displayPrice = document.getElementById('displayPrice');
                            if (radio.value == 'hosted') {
                                displayPrice.innerHTML = `
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 5px;">
                                        <div style="font-size: 16px; color: var(--gray-400); font-weight: 500;">
                                            Ativação: <span style="color: var(--secondary);">Grátis</span>
                                        </div>
                                        <div>
                                            ${hostedPrice.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })} / mês
                                        </div>
                                    </div>
                                `;
                            } else {
                                displayPrice.innerHTML = filePrice.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                            }
                        }
                    }
                </script>
            <?php elseif(isset($product['delivery_method']) && $product['delivery_method'] == 'hosted'): ?>
                <div style="background: rgba(20, 184, 166, 0.1); color: var(--primary); padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: left;">
                    <h4 style="margin-bottom: 5px;"><i class="fas fa-cloud"></i> Produto Hospedado</h4>
                    <p style="font-size: 14px;">Este sistema será configurado em nossos servidores. Após o pagamento, nossa equipe entrará em contato para realizar a configuração e enviar suas credenciais de acesso.</p>
                </div>
            <?php else: ?>
                <div style="background: rgba(255, 255, 255, 0.05); color: var(--gray-300); padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: left;">
                    <h4 style="margin-bottom: 5px;"><i class="fas fa-download"></i> Produto Digital</h4>
                    <p style="font-size: 14px;">Após a confirmação do pagamento, você receberá um link para download dos arquivos do código fonte e instruções de instalação.</p>
                </div>
            <?php endif; ?>

            <button type="submit" name="pay" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 18px;">
                <i class="fas fa-lock"></i> Pagar com Mercado Pago
            </button>
        </form>
        
        <a href="index.php" style="display: block; margin-top: 20px; color: var(--gray-400);">Cancelar</a>
    </div>
</body>
</html>
