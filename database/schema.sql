-- ============================================================
-- Horizonte Imobiliária
-- Schema MySQL
-- ============================================================

CREATE DATABASE IF NOT EXISTS horizonte
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE horizonte;

-- ------------------------------------------------------------
-- Tabela: admin_usuarios
-- ------------------------------------------------------------
CREATE TABLE admin_usuarios (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(120)  NOT NULL,
    email       VARCHAR(150)  NOT NULL UNIQUE,
    senha_hash  VARCHAR(255)  NOT NULL,
    criado_em   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabela: imoveis
-- ------------------------------------------------------------
CREATE TABLE imoveis (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    titulo      VARCHAR(150)  NOT NULL,
    tipo        ENUM('Casa','Apartamento','Cobertura','Terreno') NOT NULL,
    quartos     TINYINT UNSIGNED NOT NULL DEFAULT 0,
    suites      TINYINT UNSIGNED NOT NULL DEFAULT 0,
    vagas       TINYINT UNSIGNED NOT NULL DEFAULT 0,
    area_m2     SMALLINT UNSIGNED NOT NULL,
    preco       DECIMAL(12,2) NOT NULL,
    descricao   TEXT          NULL,
    imagem_url  VARCHAR(500)  NOT NULL,
    status      ENUM('disponivel','vendido') NOT NULL DEFAULT 'disponivel',
    vendido_em  DATE          NULL,
    ativo       TINYINT(1)    NOT NULL DEFAULT 1,
    criado_em   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabela: leads
-- Contatos recebidos pelo site (interesse em um imóvel e/ou
-- solicitação de visita).
-- ------------------------------------------------------------
CREATE TABLE leads (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    nome                  VARCHAR(150)  NOT NULL,
    email                 VARCHAR(150)  NOT NULL,
    telefone              VARCHAR(30)   NOT NULL,
    imovel_id             INT           NULL,
    mensagem              TEXT          NULL,
    data_visita_preferida DATE          NULL,
    status                ENUM('novo','contatado','visita_agendada','fechado') NOT NULL DEFAULT 'novo',
    criado_em             DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_leads_imovel FOREIGN KEY (imovel_id) REFERENCES imoveis(id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Dados de demonstração — imóveis
-- ------------------------------------------------------------
INSERT INTO imoveis (titulo, tipo, quartos, suites, vagas, area_m2, preco, descricao, imagem_url) VALUES
('Casa de Alto Padrão', 'Casa', 4, 4, 3, 420, 3500000.00,
 'Casa de alto padrão com acabamento de primeira linha, piscina e área gourmet completa.',
 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=600'),

('Residência Moderna', 'Casa', 3, 3, 2, 280, 2200000.00,
 'Arquitetura contemporânea, integração entre ambientes internos e externos.',
 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=600'),

('Apartamento Luxo', 'Apartamento', 2, 2, 1, 130, 1850000.00,
 'Apartamento de alto padrão em condomínio com infraestrutura completa de lazer.',
 'https://images.unsplash.com/photo-1600573472550-8090b5e0745e?w=600');

-- ------------------------------------------------------------
-- Dados de demonstração — usuário administrativo
-- login: admin@horizonte.com  senha: admin123
-- ------------------------------------------------------------
INSERT INTO admin_usuarios (nome, email, senha_hash) VALUES
('Arthur Sousa', 'admin@horizonte.com', '$2b$10$swr0Uv5vCoqpJVWGMXmAL.Gw3VVfxGzT6vTFKmeJ0Esv3cQfJn0I6');
