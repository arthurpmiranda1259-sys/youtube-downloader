<?php
require_once __DIR__ . '/../config/config.php';

class StoreDatabase {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $dbDir = dirname(DB_PATH);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
            
            $this->pdo = new PDO('sqlite:' . DB_PATH);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            $this->initTables();
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    private function initTables() {
        // Admins Table
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS admins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Customers Table
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS customers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Products Table
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            price REAL NOT NULL,
            type TEXT NOT NULL, -- 'system', 'site', 'app'
            delivery_method TEXT DEFAULT 'file', -- 'hosted', 'file' (Default if options not set)
            delivery_options TEXT DEFAULT 'file', -- 'file', 'hosted', 'both'
            billing_cycle TEXT DEFAULT 'one_time', -- 'one_time', 'monthly', 'yearly'
            image_url TEXT,
            active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Licenses Table
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS licenses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER,
            customer_email TEXT NOT NULL,
            product_id INTEGER,
            license_key TEXT UNIQUE NOT NULL,
            domain TEXT,
            status TEXT DEFAULT 'active', -- 'active', 'blocked', 'expired'
            delivery_method TEXT, -- 'file' or 'hosted' (Snapshot of what was bought)
            expires_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(order_id) REFERENCES orders(id),
            FOREIGN KEY(product_id) REFERENCES products(id)
        )");

        // Orders Table
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER,
            customer_name TEXT NOT NULL,
            customer_email TEXT NOT NULL,
            amount REAL NOT NULL,
            status TEXT DEFAULT 'pending', -- 'pending', 'approved', 'rejected'
            delivery_method TEXT, -- 'file' or 'hosted'
            payment_id TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(product_id) REFERENCES products(id)
        )");

        // Settings Table
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // MIGRATION: Check for billing_cycle in products (for existing databases)
        try {
            $cols = $this->pdo->query("PRAGMA table_info(products)")->fetchAll();
            $hasBillingCycle = false;
            foreach ($cols as $col) {
                if ($col['name'] === 'billing_cycle') {
                    $hasBillingCycle = true;
                    break;
                }
            }
            if (!$hasBillingCycle) {
                $this->pdo->exec("ALTER TABLE products ADD COLUMN billing_cycle TEXT DEFAULT 'one_time'");
            }
        } catch (Exception $e) {
            // Ignore if already exists or error
        }

        // MIGRATION: Add delivery_options and delivery_method
        try {
            $this->pdo->exec("ALTER TABLE products ADD COLUMN delivery_options TEXT DEFAULT 'file'");
        } catch (Exception $e) {}
        
        try {
            $this->pdo->exec("ALTER TABLE orders ADD COLUMN delivery_method TEXT");
        } catch (Exception $e) {}

        try {
            $this->pdo->exec("ALTER TABLE orders ADD COLUMN external_reference TEXT");
        } catch (Exception $e) {}
        
        try {
            $this->pdo->exec("ALTER TABLE licenses ADD COLUMN delivery_method TEXT");
        } catch (Exception $e) {}

        // FORCE UPDATE: Ensure NeoDelivery has both options enabled for testing
        try {
            $this->pdo->exec("UPDATE products SET delivery_options = 'both' WHERE name LIKE '%NeoDelivery%'");
        } catch (Exception $e) {}

        try {
            $this->pdo->exec("ALTER TABLE products ADD COLUMN monthly_price REAL DEFAULT 0");
        } catch (Exception $e) {}

        // Seed Default Admin (admin / admin123)
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM admins");
        if ($stmt->fetch()['count'] == 0) {
            $pass = password_hash('admin123', PASSWORD_DEFAULT);
            $this->pdo->exec("INSERT INTO admins (username, password) VALUES ('admin', '$pass')");
        }

        // Seed Default Settings
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM settings");
        if ($stmt->fetch()['count'] == 0) {
            $settings = [
                'mp_access_token' => '',
                'mp_public_key' => '',
                'site_name' => 'RevexaSistemas Store',
                'currency' => 'BRL'
            ];
            $insert = $this->pdo->prepare("INSERT INTO settings (key, value) VALUES (?, ?)");
            foreach ($settings as $k => $v) {
                $insert->execute([$k, $v]);
            }
        }
        
        // Seed Sample Products
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM products");
        if ($stmt->fetch()['count'] == 0) {
            $products = [
                ['Sistema de Gestão Empresarial (ERP)', 'Sistema completo para gestão de pequenas e médias empresas.', 1500.00, 'system', 'hosted', 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800'],
                ['Website Institucional Premium', 'Site responsivo, otimizado para SEO e com painel administrativo.', 800.00, 'site', 'file', 'https://images.unsplash.com/photo-1547658719-da2b51169166?w=800'],
                ['App de Delivery', 'Aplicativo Android e iOS para restaurantes e entregas.', 2500.00, 'app', 'file', 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=800']
            ];
            
            $insert = $this->pdo->prepare("INSERT INTO products (name, description, price, type, delivery_method, image_url) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($products as $p) {
                $insert->execute($p);
            }
        }
    }
    
    // Helper methods
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function getSetting($key) {
        $stmt = $this->pdo->prepare("SELECT value FROM settings WHERE key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['value'] : null;
    }

    public function saveSetting($key, $value) {
        $stmt = $this->pdo->prepare("INSERT OR REPLACE INTO settings (key, value, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP)");
        return $stmt->execute([$key, $value]);
    }
}
