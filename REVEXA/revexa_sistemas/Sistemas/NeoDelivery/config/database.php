<?php
class Database {
    private $db;
    private static $instance = null;
    
    private function __construct() {
        $dbPath = __DIR__ . '/../data/neodelivery.db';
        $dbDir = dirname($dbPath);
        
        if (!file_exists($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        
        $this->db = new SQLite3($dbPath);
        $this->db->busyTimeout(5000);
        $this->db->exec('PRAGMA journal_mode = WAL');
        $this->initDatabase();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->db;
    }
    
    private function initDatabase() {
        // Tabela de usuários (Funcionários/Admin)
        $this->db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT DEFAULT 'employee', -- 'admin' or 'employee'
            active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Tabela de configurações
        $this->db->exec("CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT UNIQUE NOT NULL,
            value TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Tabela de categorias
        $this->db->exec("CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            image TEXT,
            display_order INTEGER DEFAULT 0,
            active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Tabela de produtos
        $this->db->exec("CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            description TEXT,
            price REAL NOT NULL,
            image TEXT,
            active INTEGER DEFAULT 1,
            featured INTEGER DEFAULT 0,
            display_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
        )");
        
        // Tabela de variações/opções de produtos
        $this->db->exec("CREATE TABLE IF NOT EXISTS product_options (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            price_modifier REAL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )");
        
        // Tabela de pedidos
        $this->db->exec("CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_number TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            customer_name TEXT NOT NULL,
            customer_phone TEXT NOT NULL,
            customer_address TEXT,
            customer_complement TEXT,
            customer_neighborhood TEXT,
            customer_reference TEXT,
            delivery_type TEXT NOT NULL,
            payment_method TEXT NOT NULL,
            subtotal REAL NOT NULL,
            delivery_fee REAL DEFAULT 0,
            discount REAL DEFAULT 0,
            total REAL NOT NULL,
            status TEXT DEFAULT 'pending',
            estimated_time TEXT,
            notes TEXT,
            delivery_person_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Tabela de itens do pedido
        $this->db->exec("CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            product_name TEXT NOT NULL,
            quantity INTEGER NOT NULL,
            unit_price REAL NOT NULL,
            options TEXT,
            subtotal REAL NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id)
        )");
        
        // Tabela de horários de funcionamento
        $this->db->exec("CREATE TABLE IF NOT EXISTS business_hours (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            day_of_week INTEGER NOT NULL,
            opening_time TEXT NOT NULL,
            closing_time TEXT NOT NULL,
            is_open INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Tabela de taxas de entrega por bairro
        $this->db->exec("CREATE TABLE IF NOT EXISTS delivery_areas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            neighborhood TEXT NOT NULL,
            delivery_fee REAL NOT NULL,
            estimated_time TEXT,
            active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Tabela de entregadores
        $this->db->exec("CREATE TABLE IF NOT EXISTS delivery_persons (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            phone TEXT,
            active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Tabela de banners do carrossel
        $this->db->exec("CREATE TABLE IF NOT EXISTS banners (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT,
            description TEXT,
            image TEXT NOT NULL,
            link TEXT,
            display_order INTEGER DEFAULT 0,
            active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Inserir configurações padrão
        $this->insertDefaultSettings();
        $this->insertDefaultUser();
    }

    private function insertDefaultUser() {
        $count = $this->db->querySingle("SELECT COUNT(*) FROM users");
        if ($count == 0) {
            // Default Admin: admin / admin123
            $stmt = $this->db->prepare("INSERT INTO users (name, username, password, role) VALUES (:name, :username, :password, :role)");
            $stmt->bindValue(':name', 'Administrador', SQLITE3_TEXT);
            $stmt->bindValue(':username', 'admin', SQLITE3_TEXT);
            $stmt->bindValue(':password', password_hash('admin123', PASSWORD_DEFAULT), SQLITE3_TEXT);
            $stmt->bindValue(':role', 'admin', SQLITE3_TEXT);
            $stmt->execute();
        }
    }
    
    private function insertDefaultSettings() {
        $defaults = [
            ['business_name', 'X Delivery'],
            ['business_phone', ''],
            ['business_whatsapp', ''],
            ['business_address', ''],
            ['business_logo', ''],
            ['pix_key', ''],
            ['pix_key_type', 'CPF'],
            ['pix_holder_name', ''],
            ['minimum_order', '0'],
            ['delivery_time_estimate', '40-50 minutos'],
            ['is_open', '1'],
            ['primary_color', '#6c5ce7'],
            ['secondary_color', '#fdcb6e']
        ];
        
        foreach ($defaults as $setting) {
            $stmt = $this->db->prepare("INSERT OR IGNORE INTO settings (name, value) VALUES (:name, :value)");
            $stmt->bindValue(':name', $setting[0], SQLITE3_TEXT);
            $stmt->bindValue(':value', $setting[1], SQLITE3_TEXT);
            $stmt->execute();
        }
        
        // Inserir dias da semana padrão
        for ($i = 0; $i <= 6; $i++) {
            $stmt = $this->db->prepare("INSERT OR IGNORE INTO business_hours (day_of_week, opening_time, closing_time, is_open) VALUES (:day, :open, :close, :is_open)");
            $stmt->bindValue(':day', $i, SQLITE3_INTEGER);
            $stmt->bindValue(':open', '17:00', SQLITE3_TEXT);
            $stmt->bindValue(':close', '23:59', SQLITE3_TEXT);
            $stmt->bindValue(':is_open', 1, SQLITE3_INTEGER);
            $stmt->execute();
        }
    }
}
