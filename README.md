# hasoftware-template

Template PHP para site institucional: landing pública, área restrita e configuração persistida em MySQL. Cada instalação usa o próprio código e o próprio banco. **Não** é SaaS multi-tenant.

Textos, imagem de capa, destinatário de notificação e usuários internos são definidos no painel ou no `.env` local — não há credenciais nem e-mails reais versionados neste repositório.

## Stack

| Item | Uso |
| --- | --- |
| PHP 8.2+ | MVC próprio, sem framework e sem Composer |
| MySQL / MariaDB | PDO (`utf8mb4`) |
| Front | Views PHP, `public/css/style.css`, `public/js/script.js` |
| Schema | Migrations CLI (`php bin/migrate.php`) |
| Dados iniciais | Seeders CLI (`php bin/seed.php`) |

## O que o site faz

### Público

- **Início** — hero, sobre, serviços (texto estático na landing), calculadora de tempo de tarefa, formulário de contato e CTA de agendamento.
- **Projetos** — página de demonstração de um painel de vendas (dados fictícios, não persistidos).
- **Proposta compartilhada** — URL com token; o destinatário consulta a proposta e pode pedir ajuste. Links expiram ou podem ser revogados no painel.
- **Contato** — grava a mensagem na área restrita e tenta notificar o e-mail cadastrado em Dados de contato. O visitante vai no `Reply-To`. Falha de envio **não** apaga a mensagem gravada.

### Área restrita (após login)

| Menu | Quem acessa | Função |
| --- | --- | --- |
| Dashboard | autenticados | atalhos; mensagens do dia reais; acessos do dia ainda simulados |
| Solicitações | `admin`, `editor`, `viewer` | mensagens do formulário, status, anotações e histórico |
| Dados de início | `admin`, `editor` | título, subtítulo, botão e imagem do hero |
| Dados de contato | `admin`, `editor` | textos do bloco de contato e **e-mail de notificação** |
| Sobre nós | `admin`, `editor` | textos da seção sobre |
| Propostas de clientes | `admin`, `editor` | funil, PDF, link privado, revogação |
| Configuração de usuários | só `admin` | criar, alterar tipo, excluir |

Papéis: `admin`, `editor`, `viewer`. Tipo inválido na sessão vira `viewer`. A autorização vale no servidor; o menu só esconde links.

Não há cadastro público, API REST nem configuração de API no painel.

## Estrutura

```text
public/                 document root (index.php, assets, uploads, .htaccess)
app/core/               Router e PDF simples
app/controllers/        Home, Projetos, Auth, Dashboard, Config, proposta pública
app/models/             Config, Mensagem, Proposta, Dashboard
app/views/              páginas e partials (header, footer, flashes)
app/helpers/            contato/e-mail, experiência, demo de vendas
app/console/            migrator, seeders, inspeção de schema
bin/                    migrate.php, seed.php
config/                 config.php, env.php, database.php, cli.php
database/migrations/    YYYYMMDDHHMMSS_slug.php
database/seeders/       NNN_slug.php
routes/web.php          mapa HTTP
docs/                   arquitetura, migrations, seeders, escopo
```

O document root **deve** ser `public/`. Não servir `app/`, `config/`, `database/`, `bin/` nem `.env`.

## Requisitos

- PHP 8.2+ com PDO MySQL
- MySQL ou MariaDB
- Apache (rewrite) **ou** servidor embutido:  
  `php -S localhost:8000 -t public public/router.php`

## Instalação

1. Copie `.env.example` para `.env` e preencha o banco:

```env
DB_HOST=localhost
DB_NAME=seu_banco
DB_USER=seu_usuario
DB_PASS=
DB_CHARSET=utf8mb4
```

2. Aplique o schema:

```bash
php bin/migrate.php migrate
```

Banco que já tem as tabelas da baseline, sem registro em `migrations`:

```bash
php bin/migrate.php baseline --confirm
php bin/migrate.php migrate
```

3. Conteúdo inicial e primeiro administrador (valores só no ambiente, nunca no Git):

```bash
SEED_ADMIN_NAME='Administrador' \
SEED_ADMIN_EMAIL='admin@localhost' \
SEED_ADMIN_PASSWORD='********' \
php bin/seed.php run
```

`run --demo` acrescenta mensagem e proposta de exemplo. Exige `SEED_ALLOW_DEMO=1` e `APP_ENV` diferente de `production` / `prod`.

## E-mail de notificação

- **Destino (To):** Área restrita → Dados de contato → E-mail de notificação.
- **Remetente (From):** `MAIL_FROM` no `.env` (endereço autorizado pelo servidor de e-mail, em geral do próprio domínio).
- **Resposta:** `Reply-To` com o e-mail informado pelo visitante.

```env
MAIL_FROM=noreply@seudominio.com
MAIL_FROM_NAME=Site
MAIL_TRANSPORT=log
```

`MAIL_TRANSPORT`: `log` (desenvolvimento, não dispara), `fail` (simula falha), `mail` (`mail()` do PHP). SMTP externo ainda não está implementado. Em hospedagens que bloqueiam `mail()`, a mensagem continua na lista de solicitações.

Não coloque senhas, chaves SMTP nem caixas pessoais no código. Use apenas `.env` (já no `.gitignore`).

## Segurança (resumo)

- CSRF nos POSTs autenticados e no formulário público
- Senhas com `password_hash` / `password_verify`
- Rate limit no login e no contato
- Sessão `httponly`, `SameSite=Lax`, `secure` em HTTPS
- Uploads só em `public/uploads/`

## Documentação

| Arquivo | Conteúdo |
| --- | --- |
| `docs/PROJECT_SCOPE.md` | escopo do produto |
| `docs/ARCHITECTURE.md` | MVC, HTTP, CLI, PDO |
| `docs/migrations.md` | `status`, `migrate`, `rollback`, `baseline` |
| `docs/seeders.md` | `run` e `--demo` |

## Licença

MIT. Ver `LICENSE`.
