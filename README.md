# Horizonte Imobiliária

Site institucional de imobiliária com catálogo dinâmico de imóveis, captação de leads (interesse + agendamento de visita) persistida em banco de dados, e painel administrativo completo (CRUD de imóveis, gestão de clientes).

Convertido de um site estático (HTML/CSS puro, com imóveis fixos no markup e login decorativo) para uma aplicação PHP + PostgreSQL com backend funcional de verdade.

## Arquitetura

```
app/
├── Config/
│   └── Database.php     # conexão PDO em Singleton
└── helpers.php            # autenticação admin, CSRF, formatação

database/
├── schema.sql              # imoveis, leads, admin_usuarios
└── seed.sql                # imóveis e admin de demonstração

public/                     # document root
├── index.php                # catálogo + formulário de contato/interesse
├── imovel.php               # página de detalhe do imóvel, com formulário de visita
├── enviar-lead.php           # processa o formulário (POST + CSRF + validação)
├── admin/
│   ├── login.php, logout.php, dashboard.php
│   ├── imoveis.php, imovel-form.php   # CRUD de imóveis
│   └── leads.php                       # gestão de clientes/contatos
└── css/
```

## Decisões técnicas

- **PDO com prepared statements** em toda query, contra **PostgreSQL**.
- **Senha do admin em bcrypt** (`password_hash`/`password_verify`), com **bloqueio de conta** após 5 tentativas de login incorretas seguidas (15 minutos).
- **Cookies de sessão** com `httponly`, `samesite=Lax` e `secure` (quando em HTTPS).
- **CSRF** em todo formulário que altera dado (contato, login admin, CRUD de imóvel, exclusão, marcação de venda, mudança de status de cliente).
- **Exclusão lógica** de imóvel (`ativo = false`) em vez de `DELETE`, preservando o histórico de leads vinculados.
- **Dashboard com números reais**: os quatro indicadores (imóveis, clientes, visitas, vendas) que antes eram valores fixos no HTML agora vêm de consultas ao banco.
- **Página de detalhe por imóvel** com características, descrição e formulário de visita que já chega associado ao imóvel.
- **Aviso ao corretor por e-mail** a cada lead novo (`CORRETOR_EMAIL` no `.env`); falha de envio só vai para o log e nunca impede o lead de ser salvo.
- **Responsivo**: catálogo, página de detalhe e painel administrativo se reorganizam em telas de celular.
- **Formulário de contato de verdade**: a seção "Fale com a gente", que antes só mostrava dados estáticos de contato, agora inclui um formulário que grava o lead no banco, associado opcionalmente a um imóvel específico ("Tenho interesse" em cada card).

## Como rodar localmente

```bash
createdb horizonte
psql horizonte < database/schema.sql
psql horizonte < database/seed.sql

cp .env.example .env        # preencha DB_PASS; variáveis de ambiente do servidor têm prioridade

php -S localhost:8000 -t public
```

Acesse `http://localhost:8000`.

## Testes

```bash
composer install
createdb horizonte_test && psql horizonte_test < database/schema.sql && psql horizonte_test < database/seed.sql
DB_NAME=horizonte_test vendor/bin/phpunit
```

Cobrem a validação do formulário de contato (tamanhos, e-mail, datas inválidas ou no passado), o destino após o envio, a leitura de preços e o bloqueio de login após 5 senhas erradas. O GitHub Actions roda a suíte contra um PostgreSQL a cada push.

### Login administrativo de demonstração

| E-mail | Senha |
|---|---|
| admin@horizonte.com | admin123 |

## Roadmap

- [x] Página de detalhe individual para cada imóvel
- [ ] Galeria com várias fotos e mapa na página de detalhe
- [x] Notificação por e-mail ao corretor quando um lead novo chega
- [x] Responsividade completa para mobile

## Autor

Desenvolvido por **Arthur Sousa da Costa** — [LinkedIn](https://www.linkedin.com/in/arthur-sousa-ads/) · [GitHub](https://github.com/arthursousa-dev)
