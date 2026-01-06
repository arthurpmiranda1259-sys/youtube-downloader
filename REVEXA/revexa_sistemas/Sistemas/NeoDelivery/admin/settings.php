<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$db = Database::getInstance()->getConnection();

// Salvar configurações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'business_name',
        'business_phone',
        'business_whatsapp',
        'business_address',
        'pix_key',
        'pix_key_type',
        'pix_holder_name',
        'minimum_order',
        'delivery_time_estimate',
        'is_open',
        'primary_color',
        'secondary_color'
    ];
    
    foreach ($settings as $setting) {
        if (isset($_POST[$setting])) {
            updateSetting($setting, sanitizeInput($_POST[$setting]));
        }
    }
    
    $success = 'Configurações salvas com sucesso!';
}

$businessName = getSetting('business_name', 'X Delivery');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - <?php echo htmlspecialchars($businessName); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
    <style>
        .quick-link-card {
            text-decoration: none;
        }
        .quick-link-card .admin-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
            box-shadow: 0 10px 30px rgba(108, 92, 231, 0.2);
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="admin-content">
            <header class="admin-header">
                <h1>Configurações do Sistema</h1>
            </header>
            
            <main class="admin-main">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <!-- Quick Links -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <a href="<?php echo BASE_URL; ?>admin/banners.php" class="quick-link-card">
                        <div class="admin-card" style="text-align: center; padding: 30px; cursor: pointer; transition: all 0.3s ease; border: 2px solid transparent;">
                            <div style="font-size: 48px; margin-bottom: 15px;">🖼️</div>
                            <h3 style="margin-bottom: 10px; color: var(--dark-color);">Gerenciar Banners</h3>
                            <p style="color: var(--text-muted); margin: 0;">Configure o carrossel da página inicial</p>
                        </div>
                    </a>
                    
                    <a href="<?php echo BASE_URL; ?>admin/kitchen.php" class="quick-link-card">
                        <div class="admin-card" style="text-align: center; padding: 30px; cursor: pointer; transition: all 0.3s ease; border: 2px solid transparent;">
                            <div style="font-size: 48px; margin-bottom: 15px;">🍳</div>
                            <h3 style="margin-bottom: 10px; color: var(--dark-color);">Modo Cozinha</h3>
                            <p style="color: var(--text-muted); margin: 0;">Visualização otimizada para cozinha</p>
                        </div>
                    </a>
                </div>
                
                <form method="POST">
                    <!-- Informações do Negócio -->
                    <div class="admin-card">
                        <h2 class="admin-card-title" style="margin-bottom: 20px;">Informações do Negócio</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nome do Estabelecimento</label>
                                <input type="text" name="business_name" class="form-control" 
                                       value="<?php echo htmlspecialchars(getSetting('business_name', '')); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Telefone</label>
                                <input type="tel" name="business_phone" class="form-control" 
                                       value="<?php echo htmlspecialchars(getSetting('business_phone', '')); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">WhatsApp (apenas números)</label>
                                <input type="text" name="business_whatsapp" class="form-control" 
                                       value="<?php echo htmlspecialchars(getSetting('business_whatsapp', '')); ?>"
                                       placeholder="5511999999999">
                                <small style="color: var(--text-muted);">Exemplo: 5511999999999 (código país + DDD + número)</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Endereço Completo</label>
                                <input type="text" name="business_address" class="form-control" 
                                       value="<?php echo htmlspecialchars(getSetting('business_address', '')); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informações PIX -->
                    <div class="admin-card">
                        <h2 class="admin-card-title" style="margin-bottom: 20px;">Informações PIX</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Chave PIX</label>
                                <input type="text" name="pix_key" class="form-control" 
                                       value="<?php echo htmlspecialchars(getSetting('pix_key', '')); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Tipo de Chave</label>
                                <select name="pix_key_type" class="form-control">
                                    <option value="CPF" <?php echo getSetting('pix_key_type') === 'CPF' ? 'selected' : ''; ?>>CPF</option>
                                    <option value="CNPJ" <?php echo getSetting('pix_key_type') === 'CNPJ' ? 'selected' : ''; ?>>CNPJ</option>
                                    <option value="Email" <?php echo getSetting('pix_key_type') === 'Email' ? 'selected' : ''; ?>>Email</option>
                                    <option value="Telefone" <?php echo getSetting('pix_key_type') === 'Telefone' ? 'selected' : ''; ?>>Telefone</option>
                                    <option value="Aleatória" <?php echo getSetting('pix_key_type') === 'Aleatória' ? 'selected' : ''; ?>>Aleatória</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Nome do Titular</label>
                            <input type="text" name="pix_holder_name" class="form-control" 
                                   value="<?php echo htmlspecialchars(getSetting('pix_holder_name', '')); ?>">
                        </div>
                    </div>
                    
                    <!-- Configurações de Entrega -->
                    <div class="admin-card">
                        <h2 class="admin-card-title" style="margin-bottom: 20px;">Configurações de Entrega</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Pedido Mínimo (R$)</label>
                                <input type="number" step="0.01" name="minimum_order" class="form-control" 
                                       value="<?php echo htmlspecialchars(getSetting('minimum_order', '0')); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Tempo Estimado de Entrega</label>
                                <input type="text" name="delivery_time_estimate" class="form-control" 
                                       value="<?php echo htmlspecialchars(getSetting('delivery_time_estimate', '40-50 minutos')); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" name="is_open" value="1" class="form-checkbox" 
                                       <?php echo getSetting('is_open', '1') == '1' ? 'checked' : ''; ?>>
                                <strong style="margin-left: 10px;">Loja Aberta para Pedidos</strong>
                            </label>
                            <small style="color: var(--text-muted);">Desmarque para pausar temporariamente os pedidos</small>
                        </div>
                    </div>
                    
                    <!-- Cores do Sistema -->
                    <div class="admin-card">
                        <h2 class="admin-card-title" style="margin-bottom: 20px;">Cores do Sistema</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Cor Primária</label>
                                <input type="color" name="primary_color" class="form-control" 
                                       value="<?php echo htmlspecialchars(getSetting('primary_color', '#6c5ce7')); ?>"
                                       style="height: 50px;">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Cor Secundária</label>
                                <input type="color" name="secondary_color" class="form-control" 
                                       value="<?php echo htmlspecialchars(getSetting('secondary_color', '#fdcb6e')); ?>"
                                       style="height: 50px;">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-block" style="font-size: 18px;">
                        Salvar Configurações
                    </button>
                </form>
            </main>
        </div>
    </div>
</body>
</html>
