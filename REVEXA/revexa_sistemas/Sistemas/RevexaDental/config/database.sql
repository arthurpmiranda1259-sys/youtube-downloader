-- ============================================================
-- SISTEMA DE PRONTUÁRIO ODONTOLÓGICO - REVEXA DENTAL
-- Banco de Dados SQLite
-- ============================================================

-- Tabela de Usuários do Sistema
CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    perfil VARCHAR(20) NOT NULL DEFAULT 'recepcionista', -- admin, dentista, recepcionista
    ativo INTEGER DEFAULT 1,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_acesso DATETIME
);

-- Tabela de Pacientes
CREATE TABLE IF NOT EXISTS pacientes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(150) NOT NULL,
    cpf VARCHAR(14) UNIQUE,
    rg VARCHAR(20),
    data_nascimento DATE,
    sexo VARCHAR(1),
    telefone VARCHAR(20),
    celular VARCHAR(20),
    email VARCHAR(100),
    cep VARCHAR(10),
    endereco VARCHAR(200),
    numero VARCHAR(10),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    nome_responsavel VARCHAR(150),
    telefone_emergencia VARCHAR(20),
    convenio VARCHAR(100),
    numero_carteirinha VARCHAR(50),
    observacoes TEXT,
    ativo INTEGER DEFAULT 1,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultima_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Anamnese (Histórico Médico)
CREATE TABLE IF NOT EXISTS anamnese (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    paciente_id INTEGER NOT NULL,
    esta_tratamento_medico INTEGER DEFAULT 0,
    descricao_tratamento TEXT,
    toma_medicamentos INTEGER DEFAULT 0,
    lista_medicamentos TEXT,
    alergias INTEGER DEFAULT 0,
    lista_alergias TEXT,
    problemas_cardiacos INTEGER DEFAULT 0,
    problemas_respiratorios INTEGER DEFAULT 0,
    diabetes INTEGER DEFAULT 0,
    hipertensao INTEGER DEFAULT 0,
    hepatite INTEGER DEFAULT 0,
    dst INTEGER DEFAULT 0,
    gravida INTEGER DEFAULT 0,
    fumante INTEGER DEFAULT 0,
    etilista INTEGER DEFAULT 0,
    outras_doencas TEXT,
    observacoes_anamnese TEXT,
    data_anamnese DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_por INTEGER,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
    FOREIGN KEY (atualizado_por) REFERENCES usuarios(id)
);

-- Tabela de Odontograma
CREATE TABLE IF NOT EXISTS odontograma (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    paciente_id INTEGER NOT NULL,
    dente INTEGER NOT NULL, -- Numeração 11-18, 21-28, 31-38, 41-48
    condicao VARCHAR(50), -- hígido, cariado, restaurado, ausente, prótese, implante, etc
    faces VARCHAR(50), -- O, M, D, V, L (oclusal, mesial, distal, vestibular, lingual)
    material VARCHAR(50), -- amálgama, resina, porcelana, etc
    observacoes TEXT,
    data_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    profissional_id INTEGER,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
    FOREIGN KEY (profissional_id) REFERENCES usuarios(id)
);

-- Tabela de Procedimentos (Tabela de Preços)
CREATE TABLE IF NOT EXISTS procedimentos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    codigo VARCHAR(20),
    nome VARCHAR(200) NOT NULL,
    descricao TEXT,
    valor_particular DECIMAL(10,2) DEFAULT 0,
    valor_convenio DECIMAL(10,2) DEFAULT 0,
    ativo INTEGER DEFAULT 1,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Planos de Tratamento
CREATE TABLE IF NOT EXISTS planos_tratamento (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    paciente_id INTEGER NOT NULL,
    dentista_id INTEGER NOT NULL,
    titulo VARCHAR(200),
    descricao TEXT,
    valor_total DECIMAL(10,2) DEFAULT 0,
    desconto DECIMAL(10,2) DEFAULT 0,
    valor_final DECIMAL(10,2) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'orcamento', -- orcamento, aprovado, em_andamento, concluido, cancelado
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_aprovacao DATETIME,
    observacoes TEXT,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
    FOREIGN KEY (dentista_id) REFERENCES usuarios(id)
);

-- Tabela de Itens do Plano de Tratamento
CREATE TABLE IF NOT EXISTS plano_itens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    plano_id INTEGER NOT NULL,
    procedimento_id INTEGER NOT NULL,
    dente VARCHAR(10),
    faces VARCHAR(50),
    quantidade INTEGER DEFAULT 1,
    valor_unitario DECIMAL(10,2) DEFAULT 0,
    valor_total DECIMAL(10,2) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'pendente', -- pendente, em_andamento, concluido, cancelado
    ordem INTEGER DEFAULT 0,
    observacoes TEXT,
    FOREIGN KEY (plano_id) REFERENCES planos_tratamento(id),
    FOREIGN KEY (procedimento_id) REFERENCES procedimentos(id)
);

-- Tabela de Agendamentos
CREATE TABLE IF NOT EXISTS agendamentos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    paciente_id INTEGER NOT NULL,
    dentista_id INTEGER NOT NULL,
    data_agendamento DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    procedimento_id INTEGER,
    status VARCHAR(20) DEFAULT 'agendado', -- agendado, confirmado, em_atendimento, realizado, cancelado, faltou
    observacoes TEXT,
    confirmado INTEGER DEFAULT 0,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    criado_por INTEGER,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
    FOREIGN KEY (dentista_id) REFERENCES usuarios(id),
    FOREIGN KEY (procedimento_id) REFERENCES procedimentos(id),
    FOREIGN KEY (criado_por) REFERENCES usuarios(id)
);

-- Tabela de Prontuário (Evolução Clínica)
CREATE TABLE IF NOT EXISTS prontuario (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    paciente_id INTEGER NOT NULL,
    agendamento_id INTEGER,
    dentista_id INTEGER NOT NULL,
    data_atendimento DATETIME DEFAULT CURRENT_TIMESTAMP,
    queixa_principal TEXT,
    historico_doenca TEXT,
    exame_clinico TEXT,
    diagnostico TEXT,
    procedimentos_realizados TEXT,
    prescricoes TEXT,
    observacoes TEXT,
    proxima_consulta DATE,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
    FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id),
    FOREIGN KEY (dentista_id) REFERENCES usuarios(id)
);

-- Tabela de Imagens e Documentos
CREATE TABLE IF NOT EXISTS documentos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    paciente_id INTEGER NOT NULL,
    prontuario_id INTEGER,
    tipo VARCHAR(50), -- radiografia, foto, exame, documento
    titulo VARCHAR(200),
    descricao TEXT,
    arquivo VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100),
    tamanho INTEGER,
    data_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
    enviado_por INTEGER,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
    FOREIGN KEY (prontuario_id) REFERENCES prontuario(id),
    FOREIGN KEY (enviado_por) REFERENCES usuarios(id)
);

-- Tabela de Receitas
CREATE TABLE IF NOT EXISTS receitas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    paciente_id INTEGER NOT NULL,
    prontuario_id INTEGER,
    dentista_id INTEGER NOT NULL,
    medicamentos TEXT NOT NULL,
    instrucoes TEXT,
    data_receita DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
    FOREIGN KEY (prontuario_id) REFERENCES prontuario(id),
    FOREIGN KEY (dentista_id) REFERENCES usuarios(id)
);

-- Tabela de Atestados
CREATE TABLE IF NOT EXISTS atestados (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    paciente_id INTEGER NOT NULL,
    prontuario_id INTEGER,
    dentista_id INTEGER NOT NULL,
    dias_afastamento INTEGER NOT NULL,
    cid VARCHAR(10),
    observacoes TEXT,
    data_atestado DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
    FOREIGN KEY (prontuario_id) REFERENCES prontuario(id),
    FOREIGN KEY (dentista_id) REFERENCES usuarios(id)
);

-- Tabela de Contas a Receber
CREATE TABLE IF NOT EXISTS contas_receber (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    paciente_id INTEGER NOT NULL,
    plano_id INTEGER,
    agendamento_id INTEGER,
    descricao VARCHAR(200) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    valor_pago DECIMAL(10,2) DEFAULT 0,
    data_vencimento DATE NOT NULL,
    data_pagamento DATE,
    forma_pagamento VARCHAR(50), -- dinheiro, cartao_debito, cartao_credito, pix, transferencia
    status VARCHAR(20) DEFAULT 'pendente', -- pendente, pago, atrasado, cancelado
    observacoes TEXT,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    criado_por INTEGER,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
    FOREIGN KEY (plano_id) REFERENCES planos_tratamento(id),
    FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id),
    FOREIGN KEY (criado_por) REFERENCES usuarios(id)
);

-- Tabela de Contas a Pagar
CREATE TABLE IF NOT EXISTS contas_pagar (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    descricao VARCHAR(200) NOT NULL,
    categoria VARCHAR(50), -- salarios, aluguel, materiais, equipamentos, servicos, impostos
    valor DECIMAL(10,2) NOT NULL,
    valor_pago DECIMAL(10,2) DEFAULT 0,
    data_vencimento DATE NOT NULL,
    data_pagamento DATE,
    forma_pagamento VARCHAR(50),
    status VARCHAR(20) DEFAULT 'pendente',
    observacoes TEXT,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    criado_por INTEGER,
    FOREIGN KEY (criado_por) REFERENCES usuarios(id)
);

-- Tabela de Log de Auditoria
CREATE TABLE IF NOT EXISTS log_auditoria (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER NOT NULL,
    acao VARCHAR(100) NOT NULL,
    tabela VARCHAR(50),
    registro_id INTEGER,
    dados_anteriores TEXT,
    dados_novos TEXT,
    ip_address VARCHAR(45),
    data_acao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Inserir usuário administrador padrão (senha: admin123)
-- IMPORTANTE: A senha é "admin123" sem aspas
INSERT OR IGNORE INTO usuarios (id, nome, email, senha, perfil) 
VALUES (1, 'Administrador', 'admin@revexa.com.br', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'admin');

-- Inserir alguns procedimentos padrão
INSERT OR IGNORE INTO procedimentos (nome, descricao, valor_particular, valor_convenio) VALUES
('Consulta Inicial', 'Primeira consulta com anamnese completa', 80.00, 60.00),
('Limpeza (Profilaxia)', 'Limpeza e polimento dental', 120.00, 90.00),
('Restauração 1 Face', 'Restauração em resina - 1 face', 150.00, 120.00),
('Restauração 2 Faces', 'Restauração em resina - 2 faces', 200.00, 160.00),
('Restauração 3 Faces', 'Restauração em resina - 3 faces', 250.00, 200.00),
('Extração Simples', 'Extração dentária simples', 150.00, 100.00),
('Extração Complexa', 'Extração dentária complexa ou inclusa', 300.00, 250.00),
('Clareamento Caseiro', 'Moldeira + gel clareador', 400.00, 350.00),
('Clareamento Consultório', 'Clareamento a laser em consultório', 800.00, 700.00),
('Radiografia Periapical', 'Radiografia individual de dente', 40.00, 30.00),
('Radiografia Panorâmica', 'Radiografia panorâmica completa', 100.00, 80.00),
('Tratamento de Canal - Anterior', 'Endodontia de dente anterior', 400.00, 350.00),
('Tratamento de Canal - Pré-molar', 'Endodontia de pré-molar', 500.00, 450.00),
('Tratamento de Canal - Molar', 'Endodontia de molar', 700.00, 600.00),
('Coroa Provisória', 'Coroa provisória em resina', 150.00, 120.00),
('Coroa Porcelana', 'Coroa em porcelana pura', 1200.00, 1000.00),
('Implante Dentário', 'Implante de titânio + instalação', 2500.00, 2200.00),
('Prótese Total Superior', 'Dentadura completa superior', 1500.00, 1300.00),
('Prótese Total Inferior', 'Dentadura completa inferior', 1500.00, 1300.00),
('Aparelho Ortodôntico', 'Instalação de aparelho fixo completo', 2000.00, 1800.00),
('Manutenção Ortodôntica', 'Manutenção mensal do aparelho', 200.00, 180.00);

-- ============================================================
-- SISTEMA DE PERMISSÕES CUSTOMIZÁVEIS
-- ============================================================

-- Tabela de Perfis Customizáveis
CREATE TABLE IF NOT EXISTS perfis (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(50) UNIQUE NOT NULL,
    descricao TEXT,
    nivel_hierarquia INTEGER DEFAULT 1, -- 1=menor, 10=maior
    cor VARCHAR(20) DEFAULT '#64748b',
    ativo INTEGER DEFAULT 1,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Módulos do Sistema
CREATE TABLE IF NOT EXISTS modulos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(50) UNIQUE NOT NULL,
    descricao TEXT,
    icone VARCHAR(20),
    rota VARCHAR(100),
    ordem INTEGER DEFAULT 0
);

-- Tabela de Permissões (o que cada perfil pode fazer)
CREATE TABLE IF NOT EXISTS permissoes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    perfil_id INTEGER NOT NULL,
    modulo_id INTEGER NOT NULL,
    pode_visualizar INTEGER DEFAULT 0,
    pode_criar INTEGER DEFAULT 0,
    pode_editar INTEGER DEFAULT 0,
    pode_excluir INTEGER DEFAULT 0,
    FOREIGN KEY (perfil_id) REFERENCES perfis(id),
    FOREIGN KEY (modulo_id) REFERENCES modulos(id),
    UNIQUE(perfil_id, modulo_id)
);

-- Inserir Perfis Padrão
INSERT OR IGNORE INTO perfis (id, nome, descricao, nivel_hierarquia, cor) VALUES
(1, 'admin', 'Administrador do Sistema', 10, '#D4AF37'),
(2, 'dentista', 'Dentista - Acesso Clínico Completo', 7, '#10b981'),
(3, 'recepcionista', 'Recepcionista - Agenda e Cadastros', 3, '#3b82f6');

-- Inserir Módulos do Sistema
INSERT OR IGNORE INTO modulos (id, nome, descricao, icone, rota, ordem) VALUES
(1, 'Dashboard', 'Painel Principal', '📊', 'dashboard.php', 1),
(2, 'Agenda', 'Gerenciamento de Agendamentos', '📅', 'modules/agenda.php', 2),
(3, 'Pacientes', 'Cadastro de Pacientes', '👥', 'modules/pacientes.php', 3),
(4, 'Prontuário', 'Prontuário Eletrônico', '📋', 'modules/prontuario.php', 4),
(5, 'Procedimentos', 'Tabela de Procedimentos', '🔧', 'modules/procedimentos.php', 5),
(6, 'Financeiro', 'Gestão Financeira', '💰', 'modules/financeiro.php', 6),
(7, 'Relatórios', 'Relatórios Gerenciais', '📈', 'modules/relatorios.php', 7),
(8, 'Usuários', 'Gerenciar Usuários', '👤', 'modules/usuarios.php', 8),
(9, 'Permissões', 'Configurar Perfis e Permissões', '🔐', 'modules/permissoes.php', 9);

-- Permissões do Admin (acesso total)
INSERT OR IGNORE INTO permissoes (perfil_id, modulo_id, pode_visualizar, pode_criar, pode_editar, pode_excluir) VALUES
(1, 1, 1, 1, 1, 1), (1, 2, 1, 1, 1, 1), (1, 3, 1, 1, 1, 1), (1, 4, 1, 1, 1, 1),
(1, 5, 1, 1, 1, 1), (1, 6, 1, 1, 1, 1), (1, 7, 1, 1, 1, 1), (1, 8, 1, 1, 1, 1), (1, 9, 1, 1, 1, 1);

-- Permissões do Dentista
INSERT OR IGNORE INTO permissoes (perfil_id, modulo_id, pode_visualizar, pode_criar, pode_editar, pode_excluir) VALUES
(2, 1, 1, 0, 0, 0), -- Dashboard: apenas visualizar
(2, 2, 1, 1, 1, 1), -- Agenda: acesso completo
(2, 3, 1, 1, 1, 0), -- Pacientes: criar e editar (não excluir)
(2, 4, 1, 1, 1, 0), -- Prontuário: criar e editar (não excluir)
(2, 5, 1, 0, 0, 0), -- Procedimentos: apenas visualizar
(2, 6, 1, 0, 0, 0), -- Financeiro: apenas visualizar
(2, 7, 1, 0, 0, 0); -- Relatórios: apenas visualizar

-- Permissões do Recepcionista
INSERT OR IGNORE INTO permissoes (perfil_id, modulo_id, pode_visualizar, pode_criar, pode_editar, pode_excluir) VALUES
(3, 1, 1, 0, 0, 0), -- Dashboard: apenas visualizar
(3, 2, 1, 1, 1, 1), -- Agenda: acesso completo
(3, 3, 1, 1, 1, 0), -- Pacientes: criar e editar (não excluir)
(3, 6, 1, 1, 0, 0); -- Financeiro: visualizar e lançar (não editar/excluir)

-- Atualizar tabela de usuários para usar perfil_id
ALTER TABLE usuarios ADD COLUMN perfil_id INTEGER REFERENCES perfis(id);

-- Migrar perfis existentes para IDs
UPDATE usuarios SET perfil_id = 1 WHERE perfil = 'admin';
UPDATE usuarios SET perfil_id = 2 WHERE perfil = 'dentista';
UPDATE usuarios SET perfil_id = 3 WHERE perfil = 'recepcionista';
