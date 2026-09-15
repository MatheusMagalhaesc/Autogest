-- ============================================================
-- AutoGest — Sistema de Gestão para Oficina Mecânica
-- Schema MySQL — Sprints 1 a 5
-- ============================================================

CREATE DATABASE IF NOT EXISTS autogest
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE autogest;

-- --------------------------------------------------------------
-- Sprint 1 — Usuários (Administrador, Recepcionista, Mecânico)
-- --------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150),
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('administrador', 'recepcionista', 'mecanico') NOT NULL DEFAULT 'recepcionista',
    especialidade VARCHAR(100) NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tokens de recuperação de senha (HU3 - Sprint 1)
CREATE TABLE IF NOT EXISTS senha_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expira_em DATETIME NOT NULL,
    usado TINYINT(1) NOT NULL DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------------
-- Sprint 2 — Clientes
-- --------------------------------------------------------------
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    cpf_cnpj VARCHAR(20),
    telefone VARCHAR(20) NOT NULL,
    email VARCHAR(150),
    endereco VARCHAR(200),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------------
-- Sprint 3 — Veículos
-- --------------------------------------------------------------
CREATE TABLE IF NOT EXISTS veiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    placa VARCHAR(8) NOT NULL,
    marca VARCHAR(60),
    modelo VARCHAR(60) NOT NULL,
    ano VARCHAR(4),
    km INT DEFAULT 0,
    cor VARCHAR(40),
    combustivel ENUM('flex', 'gasolina', 'etanol', 'diesel', 'hibrido', 'eletrico') DEFAULT 'flex',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------------
-- Catálogo mínimo de serviços (será expandido na Sprint 7)
-- Necessário já agora para permitir Agendamentos e Ordens de Serviço
-- --------------------------------------------------------------
CREATE TABLE IF NOT EXISTS servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------------
-- Sprint 4 — Agenda / Agendamentos
-- --------------------------------------------------------------
CREATE TABLE IF NOT EXISTS agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    veiculo_id INT NOT NULL,
    servico_id INT NOT NULL,
    mecanico_id INT NULL,
    data_agendamento DATE NOT NULL,
    hora_agendamento TIME NOT NULL,
    status ENUM('agendado', 'concluido', 'cancelado') NOT NULL DEFAULT 'agendado',
    observacoes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (veiculo_id) REFERENCES veiculos(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE CASCADE,
    FOREIGN KEY (mecanico_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------------
-- Sprint 5 — Ordem de Serviço
-- --------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ordens_servico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agendamento_id INT NULL,
    cliente_id INT NOT NULL,
    veiculo_id INT NOT NULL,
    mecanico_id INT NULL,
    km_atual INT DEFAULT 0,
    problema_relatado TEXT,
    diagnostico TEXT,
    status ENUM('agendado', 'em_atendimento', 'concluido', 'cancelado') NOT NULL DEFAULT 'agendado',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id) ON DELETE SET NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (veiculo_id) REFERENCES veiculos(id) ON DELETE CASCADE,
    FOREIGN KEY (mecanico_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Peças e serviços usados na OS
CREATE TABLE IF NOT EXISTS os_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    os_id INT NOT NULL,
    tipo ENUM('servico', 'peca') NOT NULL DEFAULT 'servico',
    servico_id INT NULL,
    descricao VARCHAR(150) NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    valor_unitario DECIMAL(10,2) NOT NULL DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Fotos do veículo dentro da OS
CREATE TABLE IF NOT EXISTS os_fotos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    os_id INT NOT NULL,
    caminho_arquivo VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------------
-- Usuário inicial (Administrador) — login: admin / senha: admin123
-- --------------------------------------------------------------
INSERT INTO usuarios (nome, usuario, email, senha, perfil)
VALUES (
    'Administrador',
    'admin',
    'admin@autogest.com',
    '$2y$10$JWPpKMdlfalv6V4BryH1PuAo62LgS6dRiGx9y9cZqT6nFZ/RGlWPe',
    'administrador'
)
ON DUPLICATE KEY UPDATE usuario = usuario;

-- Alguns serviços iniciais de exemplo (edite/expanda na tela Serviços)
INSERT INTO servicos (nome, descricao, preco) VALUES
    ('Troca de óleo', 'Troca de óleo e filtro', 120.00),
    ('Alinhamento', 'Alinhamento de direção', 80.00),
    ('Balanceamento', 'Balanceamento das 4 rodas', 60.00),
    ('Revisão completa', 'Revisão geral do veículo', 250.00),
    ('Troca de freios', 'Troca de pastilhas e discos', 180.00)
ON DUPLICATE KEY UPDATE nome = nome;
