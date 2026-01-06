<?php
require_once __DIR__ . '/config/config.php';

$db = Database::getInstance()->getConnection();

// Buscar áreas de entrega
$areasQuery = $db->query("SELECT * FROM delivery_areas WHERE active = 1 ORDER BY neighborhood");
$deliveryAreas = [];
while ($row = $areasQuery->fetchArray(SQLITE3_ASSOC)) {
    $deliveryAreas[] = $row;
}

$businessName = getSetting('business_name', 'X Delivery');
$businessAddress = getSetting('business_address', '');
$businessLogo = getSetting('business_logo', '');
$deliveryEstimate = getSetting('delivery_time_estimate', '40-50 minutos');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo htmlspecialchars($businessName); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="<?php echo BASE_URL; ?>" class="logo">
                    <?php if ($businessLogo): ?>
                        <img src="<?php echo BASE_URL . $businessLogo; ?>" alt="<?php echo htmlspecialchars($businessName); ?>">
                    <?php else: ?>
                        <?php echo htmlspecialchars($businessName); ?>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </header>

    <div class="checkout-container">
        <!-- Checkout Steps -->
        <div class="checkout-steps">
            <div class="checkout-step active">
                <div class="step-circle">1</div>
                <div class="step-label">Carrinho</div>
            </div>
            <div class="checkout-step active">
                <div class="step-circle">2</div>
                <div class="step-label">Detalhes</div>
            </div>
            <div class="checkout-step">
                <div class="step-circle">3</div>
                <div class="step-label">Pedido Completo</div>
            </div>
        </div>

        <form id="checkoutForm" method="POST" action="<?php echo BASE_URL; ?>process_order.php">
            <div class="checkout-card">
                <h2 style="margin-bottom: 20px;">Informações de Contato</h2>
                
                <div class="form-group">
                    <label class="form-label">Nome Completo *</label>
                    <input type="text" name="customer_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Telefone/WhatsApp *</label>
                    <input type="tel" name="customer_phone" class="form-control" required placeholder="(00) 00000-0000">
                </div>
            </div>

            <div class="checkout-card">
                <h2 style="margin-bottom: 20px;">Tipo de Entrega</h2>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer; margin-bottom: 10px;">
                        <input type="radio" name="delivery_type" value="pickup" class="form-radio" required>
                        <div style="flex: 1;">
                            <strong>Retirar no Local</strong>
                            <div style="font-size: 14px; color: var(--text-muted);">Grátis</div>
                        </div>
                    </label>
                    
                    <label style="display: flex; align-items: center; padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer;">
                        <input type="radio" name="delivery_type" value="delivery" class="form-radio" required>
                        <div style="flex: 1;">
                            <strong>Entrega em Casa</strong>
                            <div style="font-size: 14px; color: var(--text-muted);">Taxa calculada pelo bairro</div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="checkout-card" id="addressFields" style="display: none;">
                <h2 style="margin-bottom: 20px;">Endereço de Entrega</h2>
                
                <div class="form-group">
                    <label class="form-label">Endereço *</label>
                    <input type="text" name="customer_address" class="form-control" placeholder="Rua, número">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Complemento</label>
                    <input type="text" name="customer_complement" class="form-control" placeholder="Apartamento, bloco, etc">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Bairro *</label>
                    <select name="customer_neighborhood" class="form-control">
                        <option value="">Selecione o bairro</option>
                        <?php foreach ($deliveryAreas as $area): ?>
                            <option value="<?php echo htmlspecialchars($area['neighborhood']); ?>" 
                                    data-fee="<?php echo $area['delivery_fee']; ?>"
                                    data-time="<?php echo htmlspecialchars($area['estimated_time']); ?>">
                                <?php echo htmlspecialchars($area['neighborhood']); ?> - <?php echo formatMoney($area['delivery_fee']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Ponto de Referência</label>
                    <input type="text" name="customer_reference" class="form-control" placeholder="Ex: Próximo ao mercado">
                </div>
            </div>

            <div class="checkout-card">
                <h2 style="margin-bottom: 20px;">Forma de Pagamento</h2>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer; margin-bottom: 10px;">
                        <input type="radio" name="payment_method" value="pix" class="form-radio" required checked>
                        <div style="flex: 1;">
                            <strong>PIX</strong>
                            <div style="font-size: 14px; color: var(--text-muted);">Chave exibida após confirmação</div>
                        </div>
                    </label>
                    
                    <label style="display: flex; align-items: center; padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer; margin-bottom: 10px;">
                        <input type="radio" name="payment_method" value="dinheiro" class="form-radio" required>
                        <div style="flex: 1;">
                            <strong>Dinheiro</strong>
                            <div style="font-size: 14px; color: var(--text-muted);">Pagamento na entrega</div>
                        </div>
                    </label>
                    
                    <label style="display: flex; align-items: center; padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer;">
                        <input type="radio" name="payment_method" value="cartao" class="form-radio" required>
                        <div style="flex: 1;">
                            <strong>Cartão na Entrega</strong>
                            <div style="font-size: 14px; color: var(--text-muted);">Débito ou crédito</div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="checkout-card">
                <h2 style="margin-bottom: 20px;">Observações</h2>
                <div class="form-group">
                    <textarea name="notes" class="form-control" rows="3" placeholder="Alguma observação sobre o pedido?"></textarea>
                </div>
            </div>

            <div class="checkout-card">
                <h2 style="margin-bottom: 20px;">Resumo do Pedido</h2>
                <div class="order-summary" id="orderSummary">
                    <div class="summary-item">
                        <span>Subtotal:</span>
                        <span id="orderSubtotal">R$ 0,00</span>
                    </div>
                    <div class="summary-item">
                        <span>Taxa de Entrega:</span>
                        <span id="deliveryFee">R$ 0,00</span>
                    </div>
                    <div class="summary-total">
                        <span>Total:</span>
                        <span id="orderTotal">R$ 0,00</span>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-success btn-block" style="margin-top: 20px; font-size: 18px;">
                    CONFIRMAR PEDIDO
                </button>
                
                <a href="<?php echo BASE_URL; ?>cardapio.php" class="btn btn-outline btn-block" style="margin-top: 10px;">
                    Continuar Comprando
                </a>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($businessName); ?>. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
    <script>
        // Atualizar resumo do pedido ao carregar
        document.addEventListener('DOMContentLoaded', function() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            
            if (cart.length === 0) {
                window.location.href = '<?php echo BASE_URL; ?>cardapio.php';
                return;
            }
            
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            document.getElementById('orderSubtotal').textContent = formatMoney(subtotal);
            document.getElementById('orderTotal').textContent = formatMoney(subtotal);
            
            // Atualizar quando mudar bairro
            document.querySelector('[name="customer_neighborhood"]').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const fee = parseFloat(selectedOption.dataset.fee) || 0;
                document.getElementById('deliveryFee').textContent = formatMoney(fee);
                document.getElementById('orderTotal').textContent = formatMoney(subtotal + fee);
            });
            
            // Resetar taxa quando mudar tipo de entrega
            document.querySelectorAll('[name="delivery_type"]').forEach(input => {
                input.addEventListener('change', function() {
                    if (this.value === 'pickup') {
                        document.getElementById('deliveryFee').textContent = 'R$ 0,00';
                        document.getElementById('orderTotal').textContent = formatMoney(subtotal);
                    }
                });
            });
        });
        
        // Validar form antes de enviar
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            if (!validateCheckoutForm()) {
                e.preventDefault();
                return;
            }
            
            // Adicionar dados do carrinho ao form
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const cartInput = document.createElement('input');
            cartInput.type = 'hidden';
            cartInput.name = 'cart_data';
            cartInput.value = JSON.stringify(cart);
            this.appendChild(cartInput);
        });
    </script>
</body>
</html>
