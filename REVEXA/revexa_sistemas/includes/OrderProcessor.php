<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/StoreProvisioner.php';

class OrderProcessor {
    
    public static function approveOrder($orderId) {
        $db = StoreDatabase::getInstance();
        $pdo = $db->getConnection();
        
        try {
            $pdo->beginTransaction();
            
            // 1. Fetch Order
            $order = $db->fetch("SELECT * FROM orders WHERE id = ?", [$orderId]);
            if (!$order) {
                throw new Exception("Pedido não encontrado.");
            }
            
            // Check if already approved
            if ($order['status'] === 'approved') {
                $pdo->commit();
                return true;
            }
            
            // 2. Fetch Product
            $product = $db->fetch("SELECT * FROM products WHERE id = ?", [$order['product_id']]);
            if (!$product) {
                throw new Exception("Produto não encontrado.");
            }
            
            // 3. Check if License already exists or is a Renewal
            $existingLicense = $db->fetch("SELECT id FROM licenses WHERE order_id = ?", [$orderId]);
            
            $license_key = '';
            $delivery_method = $order['delivery_method'] ?? $product['delivery_method'];
            $is_renewal = false;

            // Check for Renewal in External Reference
            if (preg_match('/-R(\d+)$/', $order['external_reference'], $matches)) {
                $renewLicenseId = (int)$matches[1];
                $originalLicense = $db->fetch("SELECT * FROM licenses WHERE id = ?", [$renewLicenseId]);
                
                if ($originalLicense) {
                    $is_renewal = true;
                    $license_key = $originalLicense['license_key'];
                    
                    // Calculate New Expiration (Add 30 days to current expiry or now)
                    $baseDate = (strtotime($originalLicense['expires_at']) > time()) ? strtotime($originalLicense['expires_at']) : time();
                    $expires_at = date('Y-m-d H:i:s', strtotime('+30 days', $baseDate));
                    
                    // Update License
                    $stmt = $pdo->prepare("UPDATE licenses SET expires_at = ?, status = 'active' WHERE id = ?");
                    $stmt->execute([$expires_at, $renewLicenseId]);
                }
            }
            
            if (!$existingLicense && !$is_renewal) {
                // Generate License Key
                $license_key = strtoupper(md5(uniqid(rand(), true)));
                
                // Calculate Expiration
                $expires_at = null;
                if ($product['billing_cycle'] === 'monthly') {
                    $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
                } elseif ($product['billing_cycle'] === 'yearly') {
                    $expires_at = date('Y-m-d H:i:s', strtotime('+1 year'));
                }
                
                // Create License
                $created_at = date('Y-m-d H:i:s');
                $stmt = $pdo->prepare("INSERT INTO licenses (order_id, customer_email, product_id, license_key, status, expires_at, delivery_method, created_at) VALUES (?, ?, ?, ?, 'active', ?, ?, ?)");
                $stmt->execute([$order['id'], $order['customer_email'], $product['id'], $license_key, $expires_at, $delivery_method, $created_at]);
            } elseif ($existingLicense) {
                // If license exists (e.g. re-approving same order), get the key
                $lic = $db->fetch("SELECT license_key FROM licenses WHERE order_id = ?", [$orderId]);
                $license_key = $lic['license_key'];
            }
            
            // 4. Update Order Status
            $stmt = $pdo->prepare("UPDATE orders SET status = 'approved' WHERE id = ?");
            $stmt->execute([$orderId]);
            
            $pdo->commit();
            
            // 5. Auto-Provision SaaS if Hosted (After commit to ensure DB is consistent)
            // Only provision if it's NOT a renewal (or if we want to ensure it exists, but let's assume renewal implies existence)
            if ($delivery_method == 'hosted' && !$is_renewal) {
                try {
                    $slug = 'store-' . substr($license_key, 0, 8);
                    
                    // Detect system type from product name
                    $sourceName = 'NeoDelivery'; // Default
                    if (stripos($product['name'], 'Dental') !== false || stripos($product['name'], 'Dentista') !== false) {
                        $sourceName = 'RevexaDental';
                    } elseif (stripos($product['name'], 'Delivery') !== false) {
                        $sourceName = 'NeoDelivery';
                    }
                    
                    $sourcePath = __DIR__ . '/../Sistemas/' . $sourceName;
                    $destRoot = __DIR__ . '/../lojas';
                    
                    if (is_dir($sourcePath)) {
                        StoreProvisioner::createInstance($slug, $sourcePath, $destRoot);
                        $storeUrl = SITE_URL . '/lojas/' . $slug;
                        $db->query("UPDATE licenses SET domain = ? WHERE license_key = ?", [$storeUrl, $license_key]);
                    }
                } catch (Exception $e) {
                    error_log("Provisioning Error: " . $e->getMessage());
                }
            }
            
            return true;
            
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
