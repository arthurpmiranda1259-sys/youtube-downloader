-- Atualiza status do pedido para aprovado
UPDATE orders 
SET status = 'approved', 
    updated_at = datetime('now') 
WHERE id = 11;

-- Atualiza status da licenca para ativa
UPDATE licenses 
SET status = 'active', 
    provisioned_at = datetime('now'),
    access_url = 'https://revexa.com.br/revexa_sistemas/lojas/store-427cd6be/'
WHERE id = 3;

-- Verifica resultado
SELECT 'PEDIDO #11:' as info, id, status, total_amount, created_at FROM orders WHERE id = 11
UNION ALL
SELECT 'LICENCA #3:', id, status, license_key, access_url FROM licenses WHERE id = 3;
