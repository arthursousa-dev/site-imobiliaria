-- ============================================================
-- Horizonte Imobiliária — Schema PostgreSQL
-- ============================================================

CREATE TYPE tipo_imovel AS ENUM ('Casa', 'Apartamento', 'Cobertura', 'Terreno');
CREATE TYPE status_imovel AS ENUM ('disponivel', 'vendido');
CREATE TYPE status_lead AS ENUM ('novo', 'contatado', 'visita_agendada', 'fechado');

-- ------------------------------------------------------------
-- admin_usuarios
-- ------------------------------------------------------------
CREATE TABLE admin_usuarios (
    id                SERIAL PRIMARY KEY,
    nome              VARCHAR(120) NOT NULL,
    email             VARCHAR(150) NOT NULL UNIQUE,
    senha_hash        VARCHAR(255) NOT NULL,
    tentativas_login  SMALLINT NOT NULL DEFAULT 0,
    bloqueado_ate     TIMESTAMP NULL,
    criado_em         TIMESTAMP NOT NULL DEFAULT now()
);

-- ------------------------------------------------------------
-- imoveis
-- ------------------------------------------------------------
CREATE TABLE imoveis (
    id          SERIAL PRIMARY KEY,
    titulo      VARCHAR(150) NOT NULL,
    tipo        tipo_imovel NOT NULL,
    quartos     SMALLINT NOT NULL DEFAULT 0,
    suites      SMALLINT NOT NULL DEFAULT 0,
    vagas       SMALLINT NOT NULL DEFAULT 0,
    area_m2     SMALLINT NOT NULL,
    preco       NUMERIC(12,2) NOT NULL,
    descricao   TEXT NULL,
    imagem_url  VARCHAR(500) NOT NULL,
    status      status_imovel NOT NULL DEFAULT 'disponivel',
    vendido_em  DATE NULL,
    ativo       BOOLEAN NOT NULL DEFAULT true,
    criado_em   TIMESTAMP NOT NULL DEFAULT now()
);

-- ------------------------------------------------------------
-- leads — contatos recebidos pelo site (interesse em um imóvel
-- e/ou solicitação de visita)
-- ------------------------------------------------------------
CREATE TABLE leads (
    id                    SERIAL PRIMARY KEY,
    nome                  VARCHAR(150) NOT NULL,
    email                 VARCHAR(150) NOT NULL,
    telefone              VARCHAR(30) NOT NULL,
    imovel_id             INT NULL REFERENCES imoveis(id) ON DELETE SET NULL,
    mensagem              TEXT NULL,
    data_visita_preferida DATE NULL,
    status                status_lead NOT NULL DEFAULT 'novo',
    criado_em             TIMESTAMP NOT NULL DEFAULT now()
);

CREATE INDEX idx_leads_imovel ON leads(imovel_id);
CREATE INDEX idx_imoveis_status ON imoveis(status);
