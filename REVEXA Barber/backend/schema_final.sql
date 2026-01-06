-- Cria o banco de dados se ele não existir, para garantir que o script não falhe.
CREATE DATABASE IF NOT EXISTS `revexa01`;

-- Seleciona o banco de dados para todas as operações subsequentes.
USE `revexa01`;

-- Tabela de Usuários (Donos de Barbearia e Admins)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'owner') NOT NULL DEFAULT 'owner',
    `full_name` VARCHAR(100),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Barbearias
CREATE TABLE IF NOT EXISTS `barbershops` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `owner_id` INT NOT NULL,
    `address` VARCHAR(255),
    `phone` VARCHAR(20),
    `operating_days` VARCHAR(100), -- Ex: "1,2,3,4,5" para Seg-Sex
    `booking_link_slug` VARCHAR(100) UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- Tabela de Clientes
CREATE TABLE IF NOT EXISTS `clients` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `barbershop_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20),
    `email` VARCHAR(100) NULL UNIQUE,
    `birth_date` DATE,
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`barbershop_id`) REFERENCES `barbershops`(`id`) ON DELETE CASCADE
);

-- Tabela de Barbeiros
CREATE TABLE IF NOT EXISTS `barbers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `barbershop_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20),
    `commission_percentage` DECIMAL(5, 2) DEFAULT 50.00,
    `pix_key` VARCHAR(255),
    `whatsapp_number` VARCHAR(25),
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`barbershop_id`) REFERENCES `barbershops`(`id`) ON DELETE CASCADE
);

-- Tabela de Disponibilidade dos Barbeiros
CREATE TABLE IF NOT EXISTS `barber_availability` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `barber_id` INT NOT NULL,
    `day_of_week` INT NOT NULL, -- 0=Domingo, 1=Segunda, ..., 6=Sábado
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`barber_id`) REFERENCES `barbers`(`id`) ON DELETE CASCADE
);

-- Tabela de Serviços
CREATE TABLE IF NOT EXISTS `services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `barbershop_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(10, 2) NOT NULL,
    `duration_minutes` INT NOT NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `is_bookable` BOOLEAN DEFAULT TRUE, -- Para agendamento online
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`barbershop_id`) REFERENCES `barbershops`(`id`) ON DELETE CASCADE
);

-- Tabela de Agendamentos
CREATE TABLE IF NOT EXISTS `appointments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `barbershop_id` INT NOT NULL,
    `client_id` INT NOT NULL,
    `barber_id` INT,
    `service_id` INT NOT NULL,
    `appointment_date` DATETIME NOT NULL,
    `status` ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
    `payment_method` ENUM('pix', 'dinheiro', 'cartao', 'nao_definido') DEFAULT 'nao_definido',
    `payment_status` ENUM('pending', 'paid') DEFAULT 'pending',
    `final_price` DECIMAL(10, 2),
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`barbershop_id`) REFERENCES `barbershops`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`barber_id`) REFERENCES `barbers`(`id`) ON DELETE SET NULL
);

-- Tabela de Avaliações
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `appointment_id` INT NOT NULL,
    `client_id` INT NOT NULL,
    `rating` INT NOT NULL,
    `comment` TEXT,
    `tags` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`appointment_id`) REFERENCES `appointments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE
);

-- Tabela para Ajustes Financeiros (taxas, descontos)
CREATE TABLE IF NOT EXISTS `financial_adjustments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `appointment_id` INT,
    `barbershop_id` INT NOT NULL,
    `type` ENUM('fee', 'discount', 'bonus') NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `description` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`appointment_id`) REFERENCES `appointments`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`barbershop_id`) REFERENCES `barbershops`(`id`) ON DELETE CASCADE
);

-- Insere o usuário administrador inicial (senha: admin123)
-- É altamente recomendável alterar esta senha após o primeiro login.
INSERT INTO `users` (`username`, `password_hash`, `role`, `full_name`) 
VALUES ('admin', '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 'admin', 'Administrador do Sistema')
ON DUPLICATE KEY UPDATE `id`=`id`;
