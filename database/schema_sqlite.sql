-- ============================================================
-- AutoGest — versão SQLite (padrão, criação automática)
-- ============================================================

CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150),
    senha VARCHAR(255) NOT NULL,
    perfil VARCHAR(20) NOT NULL DEFAULT 'recepcionista' CHECK (perfil IN ('administrador','recepcionista','mecanico')),
    especialidade VARCHAR(100),
    ativo INTEGER NOT NULL DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS senha_resets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expira_em DATETIME NOT NULL,
    usado INTEGER NOT NULL DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS clientes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(150) NOT NULL,
    cpf_cnpj VARCHAR(20),
    telefone VARCHAR(20) NOT NULL,
    email VARCHAR(150),
    endereco VARCHAR(200),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS veiculos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cliente_id INTEGER NOT NULL,
    placa VARCHAR(8) NOT NULL,
    marca VARCHAR(60),
    modelo VARCHAR(60) NOT NULL,
    ano VARCHAR(4),
    km INTEGER DEFAULT 0,
    cor VARCHAR(40),
    combustivel VARCHAR(20) DEFAULT 'flex' CHECK (combustivel IN ('flex','gasolina','etanol','diesel','hibrido','eletrico')),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS servicos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) DEFAULT 0,
    ativo INTEGER NOT NULL DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS agendamentos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cliente_id INTEGER NOT NULL,
    veiculo_id INTEGER NOT NULL,
    servico_id INTEGER NOT NULL,
    mecanico_id INTEGER NULL,
    data_agendamento DATE NOT NULL,
    hora_agendamento TIME NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'agendado' CHECK (status IN ('agendado','concluido','cancelado')),
    observacoes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (veiculo_id) REFERENCES veiculos(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE CASCADE,
    FOREIGN KEY (mecanico_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS ordens_servico (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    agendamento_id INTEGER NULL,
    cliente_id INTEGER NOT NULL,
    veiculo_id INTEGER NOT NULL,
    mecanico_id INTEGER NULL,
    km_atual INTEGER DEFAULT 0,
    problema_relatado TEXT,
    diagnostico TEXT,
    status VARCHAR(20) NOT NULL DEFAULT 'agendado' CHECK (status IN ('agendado','em_atendimento','concluido','cancelado')),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id) ON DELETE SET NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (veiculo_id) REFERENCES veiculos(id) ON DELETE CASCADE,
    FOREIGN KEY (mecanico_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS os_itens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    os_id INTEGER NOT NULL,
    tipo VARCHAR(10) NOT NULL DEFAULT 'servico' CHECK (tipo IN ('servico','peca')),
    servico_id INTEGER NULL,
    descricao VARCHAR(150) NOT NULL,
    quantidade INTEGER NOT NULL DEFAULT 1,
    valor_unitario DECIMAL(10,2) NOT NULL DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS os_fotos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    os_id INTEGER NOT NULL,
    caminho_arquivo VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE CASCADE
);

INSERT OR IGNORE INTO usuarios (nome, usuario, email, senha, perfil)
VALUES (
    'Administrador',
    'admin',
    'admin@autogest.com',
    '$2y$10$JWPpKMdlfalv6V4BryH1PuAo62LgS6dRiGx9y9cZqT6nFZ/RGlWPe',
    'administrador'
);

INSERT OR IGNORE INTO servicos (nome, descricao, preco) VALUES
    ('Troca de óleo', 'Troca de óleo e filtro', 120.00),
    ('Alinhamento', 'Alinhamento de direção', 80.00),
    ('Balanceamento', 'Balanceamento das 4 rodas', 60.00),
    ('Revisão completa', 'Revisão geral do veículo', 250.00),
    ('Troca de freios', 'Troca de pastilhas e discos', 180.00);
