<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configurações do Banco de Dados
$config = require __DIR__ . '/config.php';

// Conexão
try {
    $conn = new PDO(
        "mysql:host=" . $config['host'] . ";dbname=" . $config['db_name'],
        $config['username'],
        $config['password']
    );
    $conn->exec("set names utf8");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    echo json_encode(array("error" => "Connection error: " . $exception->getMessage()));
    exit();
}

// Helpers
function getAuthUser() {
    $headers = [];
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
    }
    
    // Merge with $_SERVER for fallback
    foreach ($_SERVER as $key => $value) {
        if (substr($key, 0, 5) <> 'HTTP_') {
            continue;
        }
        $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
        $headers[$header] = $value;
    }

    $auth = null;
    if (isset($headers['Authorization'])) $auth = $headers['Authorization'];
    elseif (isset($headers['authorization'])) $auth = $headers['authorization'];
    
    if (!$auth) return null;

    $token = str_replace('Bearer ', '', $auth);
    return json_decode(base64_decode($token));
}

function getBarbershopId($conn, $userId) {
    $stmt = $conn->prepare("SELECT id FROM barbershops WHERE owner_id = ?");
    $stmt->execute([$userId]);
    $shop = $stmt->fetch(PDO::FETCH_ASSOC);
    return $shop ? $shop['id'] : null;
}

// Roteamento
try {
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = basename($uri);
$data = json_decode(file_get_contents("php://input"));

// --- ENDPOINTS ---

// VERSION CHECK
if ($path == 'version') {
    echo json_encode([
        "version" => "2.0.0",
        "build" => 1,
        "apk_url" => "https://revexa.com.br/revexa_sistemas/Sistemas/Revexa_Barber/revexa-barber.apk",
        "force_update" => false,
        "message" => "Nova versão disponível!"
    ]);
    exit();
}

// PING
if ($path == 'ping') {
    echo json_encode(["status" => "ok", "message" => "API is working"]);
    exit();
}

// LOGIN
if ($path == 'login' && $method == 'POST') {
    $user = $data->username;
    $pass = $data->password;
    $passHash = hash('sha256', $pass);

    $stmt = $conn->prepare("SELECT id, role, full_name FROM users WHERE username = ? AND password_hash = ?");
    $stmt->execute([$user, $passHash]);
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $token = base64_encode(json_encode(["id" => $row['id'], "role" => $row['role'], "exp" => time() + 86400]));
        $barbershopId = ($row['role'] == 'owner') ? getBarbershopId($conn, $row['id']) : null;

        echo json_encode([
            "token" => $token,
            "user" => [
                "id" => $row['id'],
                "username" => $user,
                "role" => $row['role'],
                "fullName" => $row['full_name'],
                "barbershopId" => $barbershopId
            ]
        ]);
    } else {
        file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " [LOGIN FAILED] User: $user\n", FILE_APPEND);
        http_response_code(401);
        echo json_encode(["error" => "Invalid credentials"]);
    }
}

// DASHBOARD
elseif ($path == 'dashboard' && $method == 'GET') {
    $user = getAuthUser();
    if (!$user) { http_response_code(403); exit(); }
    
    $shopId = getBarbershopId($conn, $user->id);
    if (!$shopId) { echo json_encode([]); exit(); }

    // Stats
    $today = date('Y-m-d');
    
    // Appointments Today
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE barbershop_id = ? AND DATE(appointment_date) = ?");
    $stmt->execute([$shopId, $today]);
    $todayAppointments = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Revenue Today (Corrected)
    $stmt = $conn->prepare("
        SELECT SUM(final_price) as revenue 
        FROM appointments
        WHERE barbershop_id = ? 
        AND DATE(appointment_date) = ? 
        AND status = 'completed' 
        AND payment_status = 'paid'
    ");
    $stmt->execute([$shopId, $today]);
    $todayRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0;

    // Active Clients (last 30 days)
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT client_id) as count FROM appointments WHERE barbershop_id = ? AND appointment_date > DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->execute([$shopId]);
    $activeClients = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    echo json_encode([
        "todayAppointments" => (int)$todayAppointments,
        "todayRevenue" => (float)$todayRevenue,
        "activeClients" => (int)$activeClients,
        "averageRating" => 4.8, // Mock for now
        "totalReviews" => 0,
        "confirmedToday" => (int)$todayAppointments // Simplified
    ]);
}

// CLIENTS
elseif ($path == 'clients') {
    $user = getAuthUser();
    if (!$user) { http_response_code(403); exit(); }
    $shopId = getBarbershopId($conn, $user->id);

    if ($method == 'GET') {
        $stmt = $conn->prepare("SELECT * FROM clients WHERE barbershop_id = ? ORDER BY name");
        $stmt->execute([$shopId]);
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($clients);
    }
    elseif ($method == 'POST') {
        $stmt = $conn->prepare("INSERT INTO clients (barbershop_id, name, phone, email, birth_date, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$shopId, $data->name, $data->phone, $data->email, $data->birthDate, $data->notes]);
        echo json_encode(["id" => $conn->lastInsertId()]);
    }
    elseif ($method == 'DELETE') {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Missing client id"]);
            exit();
        }
        $stmt = $conn->prepare("DELETE FROM clients WHERE id = ? AND barbershop_id = ?");
        $stmt->execute([$id, $shopId]);
        echo json_encode(["success" => true]);
    }
}

// SERVICES
elseif ($path == 'services') {
    $user = getAuthUser();
    if (!$user) { http_response_code(403); exit(); }
    $shopId = getBarbershopId($conn, $user->id);

    if ($method == 'GET') {
        $stmt = $conn->prepare("SELECT * FROM services WHERE barbershop_id = ? AND is_active = 1");
        $stmt->execute([$shopId]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($method == 'POST') {
        $stmt = $conn->prepare("INSERT INTO services (barbershop_id, name, description, price, duration_minutes) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$shopId, $data->name, $data->description, $data->price, $data->durationMinutes]);
        echo json_encode(["id" => $conn->lastInsertId()]);
    }
    elseif ($method == 'DELETE') {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Missing service id"]);
            exit();
        }
        // Soft delete: set is_active = 0
        $stmt = $conn->prepare("UPDATE services SET is_active = 0 WHERE id = ? AND barbershop_id = ?");
        $stmt->execute([$id, $shopId]);
        echo json_encode(["success" => true]);
    }
}

// APPOINTMENTS
elseif ($path == 'appointments') {
    $user = getAuthUser();
    if (!$user) { http_response_code(403); exit(); }
    $shopId = getBarbershopId($conn, $user->id);

    if ($method == 'GET') {
        $date = $_GET['date'] ?? date('Y-m-d');
        $query = "
            SELECT a.*, c.name as client_name, s.name as service_name, s.price, s.duration_minutes, b.name as barber_name
            FROM appointments a
            JOIN clients c ON a.client_id = c.id
            JOIN services s ON a.service_id = s.id
            LEFT JOIN barbers b ON a.barber_id = b.id
            WHERE a.barbershop_id = ? AND DATE(a.appointment_date) = ?
            ORDER BY a.appointment_date
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute([$shopId, $date]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($method == 'POST') {
        try {
            $barberId = $data->barberId ?? null;
            $dateTime = $data->dateTime ?? $data->date ?? null;
            
            // Log para debug
            error_log("Creating appointment: barberId=$barberId, clientId={$data->clientId}, serviceId={$data->serviceId}, dateTime=$dateTime");
            
            if (!$dateTime || !isset($data->clientId) || !isset($data->serviceId)) {
                http_response_code(400);
                echo json_encode(["error" => "Missing required fields"]);
                exit();
            }
            
            $stmt = $conn->prepare("INSERT INTO appointments (barbershop_id, client_id, service_id, barber_id, appointment_date, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute([$shopId, $data->clientId, $data->serviceId, $barberId, $dateTime, 'scheduled', $data->notes ?? null]);
            
            if ($result) {
                echo json_encode(["success" => true, "id" => $conn->lastInsertId()]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Failed to create appointment"]);
            }
        } catch (Exception $e) {
            error_log("Error creating appointment: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
    elseif ($method == 'PUT') {
        $id = $data->id ?? null;
        $status = $data->status ?? null;
        
        if ($status) {
            // Update status only
            $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ? AND barbershop_id = ?");
            $stmt->execute([$status, $id, $shopId]);
        } else {
            // Update full appointment - support both camelCase and snake_case
            $clientId = $data->clientId ?? $data->client_id ?? null;
            $serviceId = $data->serviceId ?? $data->service_id ?? null;
            $barberId = $data->barberId ?? $data->barber_id ?? null;
            $dateTime = $data->dateTime ?? $data->date ?? null;
            $notes = $data->notes ?? null;
            
            if (!$dateTime || !$clientId || !$serviceId) {
                http_response_code(400);
                echo json_encode(["error" => "Missing required fields"]);
                exit();
            }
            
            $stmt = $conn->prepare("UPDATE appointments SET client_id = ?, service_id = ?, barber_id = ?, appointment_date = ?, notes = ? WHERE id = ? AND barbershop_id = ?");
            $stmt->execute([$clientId, $serviceId, $barberId, $dateTime, $notes, $id, $shopId]);
        }
        echo json_encode(["success" => true]);
    }
    elseif ($method == 'DELETE') {
        $id = $_GET['id'] ?? null;
        $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ? AND barbershop_id = ?");
        $stmt->execute([$id, $shopId]);
        echo json_encode(["success" => true]);
    }
}

// UPDATE CLIENT
elseif (preg_match('/clients\/(\d+)/', $path, $matches) && $method == 'PUT') {
    $user = getAuthUser();
    if (!$user) { http_response_code(403); exit(); }
    $shopId = getBarbershopId($conn, $user->id);
    $clientId = $matches[1];
    
    $stmt = $conn->prepare("UPDATE clients SET name = ?, phone = ?, email = ?, birth_date = ?, notes = ? WHERE id = ? AND barbershop_id = ?");
    $stmt->execute([$data->name, $data->phone, $data->email, $data->birthDate, $data->notes, $clientId, $shopId]);
    echo json_encode(["success" => true]);
}

// UPDATE SERVICE
elseif (preg_match('/services\/(\d+)/', $path, $matches) && $method == 'PUT') {
    $user = getAuthUser();
    if (!$user) { http_response_code(403); exit(); }
    $shopId = getBarbershopId($conn, $user->id);
    $serviceId = $matches[1];
    
    $stmt = $conn->prepare("UPDATE services SET name = ?, description = ?, price = ?, duration_minutes = ? WHERE id = ? AND barbershop_id = ?");
    $stmt->execute([$data->name, $data->description, $data->price, $data->durationMinutes, $serviceId, $shopId]);
    echo json_encode(["success" => true]);
}

// BARBEIROS (LISTA E CRIAÇÃO)
elseif ($path == 'barbers' && ($method == 'GET' || $method == 'POST')) {
    $user = getAuthUser();
    if (!$user) { 
        http_response_code(403);
        echo json_encode(["error" => "Unauthorized"]);
        exit();
    }
    $shopId = getBarbershopId($conn, $user->id);

    if ($method == 'GET') {
        $stmt = $conn->prepare("SELECT id, name, phone, commission_percentage, pix_key, whatsapp_number FROM barbers WHERE barbershop_id = ? AND is_active = 1 ORDER BY name");
        $stmt->execute([$shopId]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($method == 'POST') {
        $commission = $data->commission_percentage ?? $data->commissionPercentage ?? 50;
        $stmt = $conn->prepare("INSERT INTO barbers (barbershop_id, name, phone, commission_percentage, pix_key, whatsapp_number) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$shopId, $data->name, $data->phone, $commission, $data->pix_key ?? null, $data->whatsapp_number ?? null]);
        echo json_encode(["success" => true, "id" => $conn->lastInsertId()]);
    }
}

// BARBEIRO (INDIVIDUAL - GET, UPDATE, DELETE)
elseif (preg_match('/barbers\/(\d+)/', $uri, $matches) && !strpos($uri, 'availability')) {
    $user = getAuthUser();
    if (!$user) { http_response_code(403); exit(); }
    $shopId = getBarbershopId($conn, $user->id);
    $barberId = $matches[1];

    if ($method == 'GET') {
        $stmt = $conn->prepare("SELECT id, name, phone, commission_percentage, pix_key, whatsapp_number FROM barbers WHERE id = ? AND barbershop_id = ?");
        $stmt->execute([$barberId, $shopId]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    }
    elseif ($method == 'PUT') {
        $commission = $data->commission_percentage ?? $data->commissionPercentage ?? 50;
        $stmt = $conn->prepare("UPDATE barbers SET name = ?, phone = ?, commission_percentage = ?, pix_key = ?, whatsapp_number = ? WHERE id = ? AND barbershop_id = ?");
        $stmt->execute([$data->name, $data->phone, $commission, $data->pix_key ?? null, $data->whatsapp_number ?? null, $barberId, $shopId]);
        echo json_encode(["success" => true]);
    }
    elseif ($method == 'DELETE') {
        // Soft delete: set is_active = 0
        $stmt = $conn->prepare("UPDATE barbers SET is_active = 0 WHERE id = ? AND barbershop_id = ?");
        $stmt->execute([$barberId, $shopId]);
        echo json_encode(["success" => true]);
    }
}

// DISPONIBILIDADE DO BARBEIRO (GET, PUT)
elseif (preg_match('/barbers\/(\d+)\/availability/', $uri, $matches)) {
    $user = getAuthUser();
    if (!$user) { http_response_code(403); exit(); }
    $shopId = getBarbershopId($conn, $user->id);
    $barberId = $matches[1];

    // Checa se o barbeiro pertence à barbearia do usuário logado
    $stmt = $conn->prepare("SELECT id FROM barbers WHERE id = ? AND barbershop_id = ?");
    $stmt->execute([$barberId, $shopId]);
    if ($stmt->rowCount() == 0) {
        http_response_code(404);
        echo json_encode(["error" => "Barber not found or does not belong to this barbershop."]);
        exit();
    }

    if ($method == 'GET') {
        $stmt = $conn->prepare("SELECT day_of_week, start_time, end_time FROM barber_availability WHERE barber_id = ? ORDER BY day_of_week, start_time");
        $stmt->execute([$barberId]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($method == 'PUT') { // Usar PUT para substituir a agenda inteira
        $availabilityData = $data; // Espera um array de objetos: [{day_of_week, start_time, end_time}]

        try {
            $conn->beginTransaction();

            // 1. Deleta a agenda antiga
            $stmt = $conn->prepare("DELETE FROM barber_availability WHERE barber_id = ?");
            $stmt->execute([$barberId]);

            // 2. Insere a nova agenda
            $stmt = $conn->prepare("INSERT INTO barber_availability (barber_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)");
            foreach ($availabilityData as $slot) {
                if (isset($slot->day_of_week) && isset($slot->start_time) && isset($slot->end_time)) {
                    $stmt->execute([$barberId, $slot->day_of_week, $slot->start_time, $slot->end_time]);
                }
            }
            
            $conn->commit();
            echo json_encode(["success" => true, "message" => "Barber availability updated successfully."]);

        } catch (Exception $e) {
            $conn->rollBack();
            http_response_code(500);
            echo json_encode(["error" => "Failed to update availability", "message" => $e->getMessage()]);
        }
    }
}

// PAYMENTS
elseif ($path == 'payments') {
    $user = getAuthUser();
    if (!$user) { http_response_code(403); exit(); }
    $shopId = getBarbershopId($conn, $user->id);

    if ($method == 'GET') {
        $startDate = $_GET['start'] ?? date('Y-m-01');
        $endDate = $_GET['end'] ?? date('Y-m-d');
        
        $query = "SELECT p.*, a.appointment_date, c.name as client_name, s.name as service_name 
                  FROM payments p
                  JOIN appointments a ON p.appointment_id = a.id
                  JOIN clients c ON a.client_id = c.id
                  JOIN services s ON a.service_id = s.id
                  WHERE p.barbershop_id = ? AND DATE(p.payment_date) BETWEEN ? AND ?
                  ORDER BY p.payment_date DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute([$shopId, $startDate, $endDate]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($method == 'POST') {
        $stmt = $conn->prepare("INSERT INTO payments (barbershop_id, appointment_id, amount, payment_method, payment_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$shopId, $data->appointmentId, $data->amount, $data->paymentMethod, $data->paymentDate ?? date('Y-m-d H:i:s')]);
        
        // Update appointment status to completed
        $stmt = $conn->prepare("UPDATE appointments SET status = 'completed' WHERE id = ?");
        $stmt->execute([$data->appointmentId]);
        
        echo json_encode(["id" => $conn->lastInsertId()]);
    }
}

// REPORTS
elseif ($path == 'reports') {
    $user = getAuthUser();
    if (!$user) { http_response_code(403); exit(); }
    $shopId = getBarbershopId($conn, $user->id);
    
    $startDate = $_GET['start'] ?? date('Y-m-01');
    $endDate = $_GET['end'] ?? date('Y-m-d');
    
    // Correção Financeira: Usar a tabela 'appointments' como fonte da verdade.
    // 'final_price' deve ser preenchido no momento do checkout.
    // O "Valor Total" (total_gross_revenue) é a soma de todos os serviços concluídos.
    // Os "Pagamentos" (total_net_revenue) consideram ajustes.

    // Faturamento Bruto (baseado nos agendamentos concluídos e pagos)
    $stmt = $conn->prepare("
        SELECT 
            SUM(final_price) as total_gross_revenue,
            COUNT(id) as total_completed_appointments
        FROM appointments
        WHERE barbershop_id = ? 
        AND status = 'completed'
        AND payment_status = 'paid'
        AND DATE(appointment_date) BETWEEN ? AND ?
    ");
    $stmt->execute([$shopId, $startDate, $endDate]);
    $revenue = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ajustes financeiros no período
    $stmt = $conn->prepare("
        SELECT 
            type,
            SUM(amount) as total_amount
        FROM financial_adjustments
        WHERE barbershop_id = ? AND DATE(created_at) BETWEEN ? AND ?
        GROUP BY type
    ");
    $stmt->execute([$shopId, $startDate, $endDate]);
    $adjustmentsRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $adjustments = [
        'fee' => 0,
        'discount' => 0,
        'bonus' => 0
    ];
    foreach($adjustmentsRaw as $adj) {
        $adjustments[$adj['type']] = (float)$adj['total_amount'];
    }

    // Calcular Faturamento Líquido
    $netRevenue = (float)($revenue['total_gross_revenue'] ?? 0)
                - (float)($adjustments['fee'] ?? 0)
                - (float)($adjustments['discount'] ?? 0)
                + (float)($adjustments['bonus'] ?? 0);

    // Serviços mais vendidos
    $stmt = $conn->prepare("
        SELECT s.name, COUNT(a.id) as count, SUM(a.final_price) as revenue
        FROM appointments a
        JOIN services s ON a.service_id = s.id
        WHERE a.barbershop_id = ? AND DATE(a.appointment_date) BETWEEN ? AND ? AND a.status = 'completed'
        GROUP BY s.id, s.name
        ORDER BY count DESC
        LIMIT 5
    ");
    $stmt->execute([$shopId, $startDate, $endDate]);
    $topServices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Métodos de pagamento (dos agendamentos)
    $stmt = $conn->prepare("
        SELECT payment_method, COUNT(*) as count, SUM(final_price) as total
        FROM appointments
        WHERE barbershop_id = ? 
        AND status = 'completed' 
        AND payment_status = 'paid'
        AND payment_method != 'nao_definido'
        AND DATE(appointment_date) BETWEEN ? AND ?
        GROUP BY payment_method
    ");
    $stmt->execute([$shopId, $startDate, $endDate]);
    $paymentMethods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "summary" => [
            "total_gross_revenue" => (float)($revenue['total_gross_revenue'] ?? 0),
            "total_completed_appointments" => (int)($revenue['total_completed_appointments'] ?? 0),
            "adjustments" => $adjustments,
            "total_net_revenue" => $netRevenue
        ],
        "topServices" => $topServices,
        "paymentMethods" => $paymentMethods,
    ]);
}

// SETTINGS
elseif ($path == 'settings') {
    $user = getAuthUser();
    if (!$user) { http_response_code(403); exit(); }
    $shopId = getBarbershopId($conn, $user->id);
    
    if ($method == 'GET') {
        $stmt = $conn->prepare("SELECT name, address, phone, operating_days, booking_link_slug FROM barbershops WHERE id = ?");
        $stmt->execute([$shopId]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    }
    elseif ($method == 'PUT') {
        // Basic validation for the slug
        $slug = $data->booking_link_slug ?? null;
        if ($slug && !preg_match('/^[a-z0-9-]+$/', $slug)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid format for booking link slug. Use lowercase letters, numbers, and hyphens only."]);
            exit();
        }

        // Check if slug is unique before updating
        if ($slug) {
            $stmt = $conn->prepare("SELECT id FROM barbershops WHERE booking_link_slug = ? AND id != ?");
            $stmt->execute([$slug, $shopId]);
            if ($stmt->rowCount() > 0) {
                http_response_code(409); // Conflict
                echo json_encode(["error" => "This booking link is already in use by another barbershop."]);
                exit();
            }
        }

        $stmt = $conn->prepare(
            "UPDATE barbershops SET 
                name = :name, 
                phone = :phone, 
                address = :address, 
                operating_days = :operating_days, 
                booking_link_slug = :booking_link_slug 
            WHERE id = :id"
        );

        $stmt->execute([
            ':name' => $data->name ?? null, 
            ':phone' => $data->phone ?? null, 
            ':address' => $data->address ?? null, 
            ':operating_days' => $data->operating_days ?? null, // Expects a string like "1,2,3,4,5"
            ':booking_link_slug' => $slug,
            ':id' => $shopId
        ]);
        echo json_encode(["success" => true, "message" => "Settings updated successfully."]);
    }
}

// REVIEWS
elseif ($path == 'reviews') {
    $user = getAuthUser();
    if (!$user) { http_response_code(403); exit(); }
    $shopId = getBarbershopId($conn, $user->id);

    if ($method == 'GET') {
        $query = "SELECT r.*, c.name as client_name 
                  FROM reviews r
                  JOIN appointments a ON r.appointment_id = a.id
                  JOIN clients c ON r.client_id = c.id
                  WHERE a.barbershop_id = ?
                  ORDER BY r.created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute([$shopId]);
        
        $reviews = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $reviews[] = [
                "id" => $row['id'],
                "appointmentId" => $row['appointment_id'],
                "client" => ["id" => $row['client_id'], "name" => $row['client_name']],
                "rating" => (int)$row['rating'],
                "comment" => $row['comment'],
                "tags" => json_decode($row['tags']),
                "createdAt" => $row['created_at']
            ];
        }
        echo json_encode($reviews);
    }
    elseif ($method == 'POST') {
        $appointmentId = $data->appointmentId;
        $rating = $data->rating;
        $comment = $data->comment;
        $tags = json_encode($data->tags);

        $stmtAppt = $conn->prepare("SELECT client_id FROM appointments WHERE id = ?");
        $stmtAppt->execute([$appointmentId]);
        
        if ($stmtAppt->rowCount() > 0) {
            $appt = $stmtAppt->fetch(PDO::FETCH_ASSOC);
            $clientId = $appt['client_id'];
            $stmt = $conn->prepare("INSERT INTO reviews (appointment_id, client_id, rating, comment, tags) VALUES (?, ?, ?, ?, ?)");
            if($stmt->execute([$appointmentId, $clientId, $rating, $comment, $tags])) {
                echo json_encode(["message" => "Review created"]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Failed"]);
            }
        }
    }
}

// USERS (Admin only)
elseif ($path == 'users' && $method == 'POST') {
    $currentUser = getAuthUser();
    if (!$currentUser || $currentUser->role !== 'admin') {
        http_response_code(403);
        echo json_encode(["error" => "Unauthorized"]);
        exit();
    }

    $name = $data->name ?? '';
    $password = $data->password ?? '';
    $shopName = $data->barbershopName ?? 'Minha Barbearia';
    $shopPhone = $data->barbershopPhone ?? '';

    if (empty($name) || empty($password) || empty($shopName) || empty($shopPhone)) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields"]);
        exit();
    }

    // Generate unique username from barbershop name
    $baseUsername = preg_replace('/[^a-z0-9]/', '', strtolower($shopName));
    $username = $baseUsername;
    $counter = 1;
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    while ($stmt->rowCount() > 0) {
        $username = $baseUsername . $counter;
        $counter++;
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
    }

    try {
        $conn->beginTransaction();

        // Create User
        $passHash = hash('sha256', $password);
        $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role, full_name) VALUES (?, ?, 'owner', ?)");
        $stmt->execute([$username, $passHash, $name]);
        $userId = $conn->lastInsertId();

        // Create Barbershop
        $stmt = $conn->prepare("INSERT INTO barbershops (name, owner_id, phone) VALUES (?, ?, ?)");
        $stmt->execute([$shopName, $userId, $shopPhone]);
        $shopId = $conn->lastInsertId();

        $conn->commit();
        echo json_encode([
            "id" => $userId, 
            "barbershopId" => $shopId,
            "username" => $username,
            "message" => "Barbearia criada! Usuário: $username"
        ]);
    } catch (Exception $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

// --- PUBLIC ENDPOINTS (NO AUTH REQUIRED) ---
elseif (preg_match('/\/public\/(\w+)/', $uri, $matches)) {
    $publicPath = $matches[1];
    $slug = $_GET['slug'] ?? null;

    if (!$slug) {
        http_response_code(400);
        echo json_encode(["error" => "Barbershop slug is required."]);
        exit();
    }

    // Get barbershop_id from slug
    $stmt = $conn->prepare("SELECT id, operating_days FROM barbershops WHERE booking_link_slug = ?");
    $stmt->execute([$slug]);
    $barbershop = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$barbershop) {
        http_response_code(404);
        echo json_encode(["error" => "Barbershop not found."]);
        exit();
    }
    $shopId = $barbershop['id'];

    // PUBLIC: GET SERVICES
    if ($publicPath == 'services' && $method == 'GET') {
        $stmt = $conn->prepare("SELECT id, name, description, price, duration_minutes FROM services WHERE barbershop_id = ? AND is_active = 1 AND is_bookable = 1");
        $stmt->execute([$shopId]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // PUBLIC: GET AVAILABILITY
    elseif ($publicPath == 'availability' && $method == 'GET') {
        $serviceId = $_GET['serviceId'] ?? null;
        $date = $_GET['date'] ?? null;

        if (!$serviceId || !$date) {
            http_response_code(400);
            echo json_encode(["error" => "serviceId and date are required."]);
            exit();
        }

        // Get service duration
        $stmt = $conn->prepare("SELECT duration_minutes FROM services WHERE id = ? AND barbershop_id = ?");
        $stmt->execute([$serviceId, $shopId]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$service) {
            http_response_code(404);
            echo json_encode(["error" => "Service not found."]);
            exit();
        }
        $duration = (int)$service['duration_minutes'];
        $dayOfWeek = date('w', strtotime($date));

        // Get all appointments for the day
        $stmt = $conn->prepare("SELECT barber_id, appointment_date, TIME(appointment_date) as start_time, TIME(ADDTIME(appointment_date, SEC_TO_TIME(? * 60))) as end_time FROM appointments WHERE barbershop_id = ? AND DATE(appointment_date) = ? AND status != 'cancelled'");
        $stmt->execute([$duration, $shopId, $date]);
        $todaysAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get all active barbers' availability for that day
        $stmt = $conn->prepare("SELECT barber_id, start_time, end_time FROM barber_availability WHERE barber_id IN (SELECT id FROM barbers WHERE barbershop_id = ? AND is_active = 1) AND day_of_week = ?");
        $stmt->execute([$shopId, $dayOfWeek]);
        $barberSchedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $availableSlots = [];
        
        foreach ($barberSchedules as $schedule) {
            $barberId = $schedule['barber_id'];
            $start = new DateTime($schedule['start_time']);
            $end = new DateTime($schedule['end_time']);

            $slot = clone $start;

            while ($slot < $end) {
                $potentialSlotEnd = (clone $slot)->add(new DateInterval("PT{$duration}M"));
                if ($potentialSlotEnd > $end) break; // Slot doesn't fit in the schedule

                $isAvailable = true;
                // Check against existing appointments for this barber
                foreach ($todaysAppointments as $appt) {
                    if ($appt['barber_id'] == $barberId) {
                        $apptStart = new DateTime($appt['start_time']);
                        $apptEnd = new DateTime($appt['end_time']);
                        // Check for overlap
                        if ($slot < $apptEnd && $potentialSlotEnd > $apptStart) {
                            $isAvailable = false;
                            break;
                        }
                    }
                }

                if ($isAvailable) {
                    $availableSlots[] = [
                        "barber_id" => $barberId,
                        "time" => $slot->format('H:i')
                    ];
                }

                $slot->add(new DateInterval('PT15M')); // Check every 15 minutes
            }
        }
        
        // Add barber details to the slots
        if (!empty($availableSlots)) {
            $barberIds = array_unique(array_column($availableSlots, 'barber_id'));
            $stmt = $conn->prepare("SELECT id, name, pix_key, whatsapp_number FROM barbers WHERE id IN (" . implode(',', array_map('intval', $barberIds)) . ")");
            $stmt->execute();
            // Fetch into an associative array keyed by barber ID
            $barbers = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE);

            foreach ($availableSlots as &$slot) {
                $barberDetails = $barbers[$slot['barber_id']] ?? null;
                if ($barberDetails) {
                    $slot['barber_name'] = $barberDetails['name'];
                    $slot['barber_pix_key'] = $barberDetails['pix_key'];
                    $slot['barber_whatsapp'] = $barberDetails['whatsapp_number'];
                } else {
                    // This case should ideally not happen if data is consistent
                    $slot['barber_name'] = 'Desconhecido';
                    $slot['barber_pix_key'] = null;
                    $slot['barber_whatsapp'] = null;
                }
            }
        }
        
        echo json_encode($availableSlots);
    }

    // PUBLIC: CREATE APPOINTMENT
    elseif ($publicPath == 'appointments' && $method == 'POST') {
        try {
            $conn->beginTransaction();

            // 1. Find or create client
            $clientId = null;
            if (!empty($data->phone)) {
                 $stmt = $conn->prepare("SELECT id FROM clients WHERE phone = ? AND barbershop_id = ?");
                 $stmt->execute([$data->phone, $shopId]);
                 $client = $stmt->fetch(PDO::FETCH_ASSOC);
                 if ($client) {
                     $clientId = $client['id'];
                 }
            }
            if (!$clientId) {
                $stmt = $conn->prepare("INSERT INTO clients (barbershop_id, name, phone, email) VALUES (?, ?, ?, ?)");
                $stmt->execute([$shopId, $data->name, $data->phone, $data->email ?? null]);
                $clientId = $conn->lastInsertId();
            }

            // 2. Get service price
            $stmt = $conn->prepare("SELECT price FROM services WHERE id = ?");
            $stmt->execute([$data->serviceId]);
            $service = $stmt->fetch(PDO::FETCH_ASSOC);
            $finalPrice = $service ? $service['price'] : 0;

            // 3. Create appointment
            $stmt = $conn->prepare(
                "INSERT INTO appointments (barbershop_id, client_id, service_id, barber_id, appointment_date, status, payment_status, final_price, payment_method, notes) 
                 VALUES (?, ?, ?, ?, ?, 'scheduled', 'pending', ?, ?, ?)"
            );
            $stmt->execute([
                $shopId, 
                $clientId, 
                $data->serviceId, 
                $data->barberId, 
                $data->dateTime, 
                $finalPrice,
                $data->payment_method ?? 'nao_definido',
                'Agendado pelo cliente via link.'
            ]);
            $appointmentId = $conn->lastInsertId();
            
            $conn->commit();
            echo json_encode(["success" => true, "appointment_id" => $appointmentId]);

        } catch (Exception $e) {
            $conn->rollBack();
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
}

else {
    echo json_encode(["status" => "API Working", "path" => $path]);
}
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Internal server error",
        "message" => $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ]);
}
?>
