#!/bin/bash
# Script para criar admin e barbearia diretamente no banco MySQL
mysql -h mysql.revexa.com.br -u revexa01 -pmamaco12 revexa01 <<SQL
-- Cria usuário admin
INSERT INTO users (username, password_hash, role, full_name) VALUES ('admin', SHA2('admin1234', 256), 'admin', 'Administrador do Sistema');
SQL
