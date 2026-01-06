<?php
/**
 * REVEXA Sistemas - Database Connection Class
 * Handles SQLite database connection and initialization
 */

class Database {
    private static $instance = null;
    private $pdo;
    private $dbPath;

    private function __construct() {
        $this->dbPath = __DIR__ . '/../database/revexa.db';
        $this->connect();
        $this->initTables();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
        try {
            // Create database directory if not exists
            $dbDir = dirname($this->dbPath);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }

            $this->pdo = new PDO('sqlite:' . $this->dbPath);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->pdo;
    }

    private function initTables() {
        // Services table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS services (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                icon VARCHAR(100),
                features TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Portfolio table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS portfolio (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                category VARCHAR(100),
                image_url VARCHAR(500),
                project_url VARCHAR(500),
                client_name VARCHAR(255),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Contacts table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS contacts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(50),
                subject VARCHAR(255),
                message TEXT NOT NULL,
                status VARCHAR(50) DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Differentials table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS differentials (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                icon VARCHAR(100),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Check if data exists, if not, seed initial data
        $this->seedData();
    }

    private function seedData() {
        // Check if services exist
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM services");
        $result = $stmt->fetch();
        
        if ($result['count'] == 0) {
            // Seed services
            $services = [
                [
                    'title' => 'Sistemas Personalizados',
                    'description' => 'Desenvolvemos sistemas sob medida para automatizar processos, gerenciar dados e aumentar a produtividade do seu negócio.',
                    'icon' => 'fas fa-cogs',
                    'features' => 'Gestão de Estoque,Controle Financeiro,CRM,Automação de Processos'
                ],
                [
                    'title' => 'Sites Profissionais',
                    'description' => 'Criamos websites modernos, responsivos e otimizados para converter visitantes em clientes.',
                    'icon' => 'fas fa-globe',
                    'features' => 'Landing Pages,Sites Institucionais,E-commerce,Blogs'
                ],
                [
                    'title' => 'Aplicativos Mobile',
                    'description' => 'Desenvolvemos aplicativos nativos e híbridos para iOS e Android que conectam você aos seus clientes.',
                    'icon' => 'fas fa-mobile-alt',
                    'features' => 'Apps Nativos,Apps Híbridos,PWAs,Integrações'
                ],
                [
                    'title' => 'Design & Identidade Visual',
                    'description' => 'Criamos identidades visuais únicas que transmitem a essência da sua marca e conectam com seu público.',
                    'icon' => 'fas fa-palette',
                    'features' => 'Logotipos,Branding,UI/UX Design,Material Gráfico'
                ]
            ];

            $stmt = $this->pdo->prepare("INSERT INTO services (title, description, icon, features) VALUES (?, ?, ?, ?)");
            foreach ($services as $service) {
                $stmt->execute([$service['title'], $service['description'], $service['icon'], $service['features']]);
            }

            // Seed portfolio
            $portfolios = [
                [
                    'title' => 'Sistema de Gestão - Ótica em Foco',
                    'description' => 'Sistema completo para gestão de óticas com controle de estoque, vendas e clientes.',
                    'category' => 'Sistemas',
                    'image_url' => 'https://images.unsplash.com/photo-1556742049-0cfed4f7a07d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                    'client_name' => 'Ótica em Foco'
                ],
                [
                    'title' => 'E-commerce - Loja Virtual',
                    'description' => 'Plataforma de vendas online com carrinho, pagamentos e gestão de pedidos.',
                    'category' => 'Sites',
                    'image_url' => 'https://images.unsplash.com/photo-1556740738-b6a63e27c4df?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                    'client_name' => 'Cliente Varejo'
                ],
                [
                    'title' => 'App de Delivery',
                    'description' => 'Aplicativo mobile para delivery de restaurantes com rastreamento em tempo real.',
                    'category' => 'Apps',
                    'image_url' => 'https://images.unsplash.com/photo-1526304640152-d4619684e484?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                    'client_name' => 'Restaurante XYZ'
                ],
                [
                    'title' => 'Identidade Visual - Startup Tech',
                    'description' => 'Criação completa de identidade visual para startup de tecnologia.',
                    'category' => 'Design',
                    'image_url' => 'https://images.unsplash.com/photo-1600607686527-6fb886090705?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                    'client_name' => 'Startup Tech'
                ],
                [
                    'title' => 'Sistema Financeiro',
                    'description' => 'Controle financeiro completo com fluxo de caixa, contas e relatórios.',
                    'category' => 'Sistemas',
                    'image_url' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                    'client_name' => 'Empresa ABC'
                ],
                [
                    'title' => 'Site Institucional',
                    'description' => 'Website moderno e responsivo para empresa de consultoria.',
                    'category' => 'Sites',
                    'image_url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                    'client_name' => 'Consultoria XYZ'
                ]
            ];

            $stmt = $this->pdo->prepare("INSERT INTO portfolio (title, description, category, image_url, client_name) VALUES (?, ?, ?, ?, ?)");
            foreach ($portfolios as $project) {
                $stmt->execute([$project['title'], $project['description'], $project['category'], $project['image_url'], $project['client_name']]);
            }

            // Seed differentials
            $differentials = [
                [
                    'title' => 'Sistemas Intuitivos',
                    'description' => 'Desenvolvemos interfaces simples e fáceis de usar, para que qualquer pessoa da sua equipe possa operar sem dificuldades.',
                    'icon' => 'fas fa-hand-pointer'
                ],
                [
                    'title' => 'Soluções Completas',
                    'description' => 'Oferecemos tudo que sua empresa precisa em um só lugar: sistemas, sites, apps e design. Sem precisar de múltiplos fornecedores.',
                    'icon' => 'fas fa-puzzle-piece'
                ],
                [
                    'title' => 'Foco no Cliente',
                    'description' => 'Nossa metodologia coloca você no centro. Entendemos suas necessidades antes de desenvolver qualquer solução.',
                    'icon' => 'fas fa-users'
                ],
                [
                    'title' => 'Suporte Dedicado',
                    'description' => 'Estamos sempre disponíveis para ajudar. Suporte rápido e eficiente para resolver qualquer questão.',
                    'icon' => 'fas fa-headset'
                ]
            ];

            $stmt = $this->pdo->prepare("INSERT INTO differentials (title, description, icon) VALUES (?, ?, ?)");
            foreach ($differentials as $diff) {
                $stmt->execute([$diff['title'], $diff['description'], $diff['icon']]);
            }
        }
    }

    // Generic query methods
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

    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        
        return $this->pdo->lastInsertId();
    }

    public function update($table, $data, $where, $whereParams = []) {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        
        return $this->query($sql, array_merge(array_values($data), $whereParams));
    }

    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params);
    }
}
