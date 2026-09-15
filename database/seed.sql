-- Horizonte Imobiliária — dados de demonstração

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

-- login: admin@horizonte.com  senha: admin123
INSERT INTO admin_usuarios (nome, email, senha_hash) VALUES
('Arthur Sousa', 'admin@horizonte.com', '$2b$10$swr0Uv5vCoqpJVWGMXmAL.Gw3VVfxGzT6vTFKmeJ0Esv3cQfJn0I6');
