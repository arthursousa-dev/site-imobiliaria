# Horizonte Imobiliária

Site institucional de imobiliária com catálogo dinâmico de imóveis, captação de leads (interesse + agendamento de visita) persistida em banco de dados, e painel administrativo completo (CRUD de imóveis, gestão de clientes).

Convertido de um site estático (HTML/CSS puro, com imóveis fixos no markup e login decorativo) para uma aplicação PHP + MySQL com backend funcional de verdade.

## Arquitetura

```
app/
├── Config/
│   └── Database.php     # conexão PDO em Singleton
└── helpers.php            # autenticação admin, CSRF, formatação

database/
└── schema.sql              # imoveis, leads, admin_usuarios + seed

public/                     # document root
├── index.php                # catálogo + formulário de contato/interesse
├── enviar-lead.php           # processa o formulário (POST + CSRF + validação)
├── admin/
│   ├── login.php, logout.php, dashboard.php
│   ├── imoveis.php, imovel-form.php   # CRUD de imóveis
│   └── leads.php                       # gestão de clientes/contatos
└── css/
```

## Decisões técnicas

- **PDO com prepared statements** em toda query.
- **Senha do admin em bcrypt** (`password_hash`/`password_verify`).
- **CSRF** em todo formulário que altera dado (contato, login admin, CRUD de imóvel, exclusão, marcação de venda, mudança de status de cliente).
- **Exclusão lógica** de imóvel (`ativo = 0`) em vez de `DELETE`, preservando o histórico de leads vinculados.
- **Dashboard com números reais**: os quatro indicadores (imóveis, clientes, visitas, vendas) que antes eram valores fixos no HTML agora vêm de consultas ao banco.
- **Formulário de contato de verdade**: a seção "Fale com a gente", que antes só mostrava dados estáticos de contato, agora inclui um formulário que grava o lead no banco, associado opcionalmente a um imóvel específico ("Tenho interesse" em cada card).

## Como rodar localmente

```bash
mysql -u root -p < database/schema.sql

export DB_HOST=localhost
export DB_NAME=horizonte
export DB_USER=root
export DB_PASS=

php -S localhost:8000 -t public
```

Acesse `http://localhost:8000`.

### Login administrativo de demonstração

| E-mail | Senha |
|---|---|
| admin@horizonte.com | admin123 |

## Roadmap

- [ ] Página de detalhe individual para cada imóvel (galeria de fotos, mapa)
- [ ] Notificação por e-mail ao corretor quando um lead novo chega
- [ ] Responsividade completa para mobile

## Autor

Desenvolvido por **Arthur Sousa da Costa** — [LinkedIn](https://www.linkedin.com/in/arthur-sousa-ads/) · [GitHub](https://github.com/arthursousa-dev)
