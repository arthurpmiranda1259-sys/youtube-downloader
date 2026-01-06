-- Seleciona o banco de dados para garantir que os comandos sejam aplicados corretamente.
USE `revexa01`;

-- Atualizações do esquema para novas funcionalidades

-- Adiciona a associação de agendamento ao barbeiro, método de pagamento e preço final
ALTER TABLE appointments
ADD COLUMN barber_id INT,
ADD COLUMN payment_method ENUM('pix', 'dinheiro', 'cartao', 'nao_definido') DEFAULT 'nao_definido',
ADD COLUMN final_price DECIMAL(10, 2),
ADD COLUMN payment_status ENUM('pending', 'paid') DEFAULT 'pending',
ADD FOREIGN KEY (barber_id) REFERENCES barbers(id);

-- Adiciona informações de contato e pagamento ao barbeiro
ALTER TABLE barbers
ADD COLUMN pix_key VARCHAR(255),
ADD COLUMN whatsapp_number VARCHAR(25);

-- Adiciona configurações de funcionamento e link de agendamento para a barbearia
ALTER TABLE barbershops
ADD COLUMN operating_days VARCHAR(100), -- Ex: "1,2,3,4,5" para Seg-Sex
ADD COLUMN booking_link_slug VARCHAR(100) UNIQUE;

-- Cria a tabela para armazenar a disponibilidade (escala) dos barbeiros
CREATE TABLE IF NOT EXISTS barber_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barber_id INT NOT NULL,
    day_of_week INT NOT NULL, -- 0=Domingo, 1=Segunda, ..., 6=Sábado
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (barber_id) REFERENCES barbers(id) ON DELETE CASCADE
);

-- Garante que o campo 'email' na tabela 'clients' seja opcional e único
ALTER TABLE clients
MODIFY COLUMN email VARCHAR(100) NULL UNIQUE;

-- Melhora a tabela de serviços para desativação mais granular
ALTER TABLE services
ADD COLUMN is_bookable BOOLEAN DEFAULT TRUE;

-- Adiciona uma tabela para taxas ou ajustes financeiros, ajudando a corrigir relatórios
CREATE TABLE IF NOT EXISTS financial_adjustments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT,
    barbershop_id INT NOT NULL,
    type ENUM('fee', 'discount', 'bonus') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
    FOREIGN KEY (barbershop_id) REFERENCES barbershops(id) ON DELETE CASCADE
);