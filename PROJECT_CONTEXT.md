# PROJECT_CONTEXT.md

Documentação de contexto do estado **atual** do código (novembro/2026).
Fonte: o repositório. O `README.md`, `docs/database.md` e `docs/archtecture.md` estão desatualizados ou incompletos; a referência vigente em `docs/` é `PROJECT_SCOPE.md`, `ARCHITECTURE.md`, `migrations.md` e `seeders.md`.

---

## 1. Visão geral

- **Nome:** vibecoding-um
- **Objetivo:** site institucional da H. A Consultoria & Desenvolvimento (Rafael Holanda do Amaral), com login, área restrita e configuração persistida. Cada implantação usa o próprio código e o próprio banco. **Não** é SaaS multi-tenant.
- **Público-alvo:** visitantes da landing; usuários internos na tabela `users` (`admin`, `editor`, `viewer`).
- **Estágio:** MVP em PHP puro (MVC próprio). Sem Composer. Sem suíte PHPUnit. Schema versionado por migrations CLI.

**Confirmado no código:**

- Landing com hero, sobre, serviços (texto estático), contato e CTA.
- Login / logout com sessão.
- Dashboard autenticado (mensagens do dia reais; acessos ainda simulados).
- Configuração persistida: início (com upload), sobre, contato, usuários, propostas, listagem de mensagens.
- Formulário público de contato grava em `mensagens_contato` e tenta `mail()` para o e-mail configurado.
- CLI de migrations (`status`, `migrate`, `rollback --confirm`, `baseline --confirm`).
- CLI de seeders (`status`, `run`; `run --demo` opcional e protegido).

---

## 2. Stack tecnológica

| Item | Estado no código |
| --- | --- |
| Linguagem | PHP 8.2+ (tipos, `str_starts_with`, `declare(strict_types)` no CLI) |
| PHP local observado | 8.2.4 (XAMPP) |
| Banco | MySQL/MariaDB via PDO (`mysql:` DSN, `utf8mb4`) |
| Front | Views PHP, `public/css/style.css`, `public/js/script.js` |
| Fontes / ícones | Google Fonts e Font Awesome via CDN no `header.php` |
| Framework PHP | Nenhum |
| Composer / vendor | Ausentes |
| Bibliotecas PHP | Nenhuma (loader de `.env` em `config/env.php`) |
| Servidor | Document root `public/` (Apache `.htaccess` ou `php -S -t public public/router.php`) |
| CLI | `php bin/migrate.php`, `php bin/seed.php` (`PHP_SAPI === 'cli'`) |
| Licença | MIT (Rafael Holanda do Amaral, 2026) |

---

## 3. Arquitetura

**Padrão:** MVC próprio, sem framework.

**HTTP:**

1. Document root = `public/`.
2. `public/index.php` define `BASE_URL` `'/'`, carrega `bootstrap.php`.
3. Bootstrap: `config/config.php` (`.env`), sessão, autoload `app/core`, `app/controllers`, `app/models`.
4. `Router` + `routes/web.php`; `dispatch` do path e método HTTP.
5. Controller `require` da view.

**CLI:** `bin/migrate.php` → `config/cli.php` (sem sessão) + autoload também em `app/console/`. `help` não abre PDO.

**Roteamento:** `app/core/Router.php`. Sem parâmetros de URL, sem middleware. 404 textual.

**Autenticação:** `AuthController`. CSRF no POST de login, `password_verify`, rate limit em sessão (5 / 300 s), `session_regenerate_id(true)`. Logout GET (sem CSRF).

**Autorização:** fail-closed. Sem `user_id` → login. `user_type` só `admin` / `editor` / `viewer`; outro valor vira `viewer`.

| Área | Quem acessa |
| --- | --- |
| Mensagens | qualquer autenticado (`checkAuth`) |
| Início, contato, sobre, propostas | `admin` e `editor` (`requireContatoEditor`) |
| Usuários (listar, tipo, criar) | só `admin` (`requireAdmin`) |
| GET `/config/email-template` | redireciona para `/config/mensagens` |

A regra efetiva está no controller; o menu do dashboard só esconde links.

**Banco:** `getPDO()` em `config/database.php`. Models `ConfigModel`, `MensagemModel`, `PropostaModel` usam PDO. `DashboardModel` ainda devolve `acessos_hoje` simulado. Auth consulta `users` no controller.

**Wrappers:** `public/login.php`, `logout.php`, `authenticate.php`, `area-restrita.php`, `public/auth/login-process.php` apenas encaminham aos controllers.

---

## 4. Estrutura de diretórios

| Caminho | Função |
| --- | --- |
| `public/` | Document root |
| `public/index.php` | Front controller |
| `public/bootstrap.php` | HTTP: config, sessão, autoload |
| `public/router.php` | `php -S` |
| `app/core/Router.php` | Roteador |
| `app/controllers/` | Home, Auth, Dashboard, Config |
| `app/models/` | Config, Mensagem, Proposta, Dashboard |
| `app/views/` | Templates e `partials/` |
| `app/console/` | `Migrator`, `Migration`, `SchemaInspector`, `Seeder`, `SeederRunner` |
| `app/helpers/experience.php` | Anos de experiência |
| `bin/migrate.php` | CLI de schema |
| `bin/seed.php` | CLI de dados iniciais |
| `database/seeders/` | Seeders versionados |
| `config/cli.php` | Bootstrap CLI (sem sessão) |
| `database/migrations/` | Arquivos versionados |
| `routes/web.php` | Rotas |
| `config/` | `config.php`, `env.php`, `database.php` |
| `.env` | Segredos (gitignored) |
| `docs/` | Escopo, arquitetura, migrations |
| `site/` | Residual (`site/js/script.js`; não é o JS servido) |

Sem `composer.json` / `vendor/` / PHPUnit.

---

## 5. Módulos

### 5.1 Institucional

- **Controller:** `HomeController`
- **Models:** `ConfigModel` (início, sobre, contato), `MensagemModel` (POST)
- **View:** `app/views/home.php`
- **Rotas:** GET `/`; POST `/contato/enviar`
- **Status:** conteúdo de início/sobre/contato vem do banco. Serviços na view são estáticos. Experiência: helper com ano inicial **2024** no controller; a view **sobrescreve** com `company_experience_years(2024, 2026)`. WhatsApp no controller usa o número de `dados_contato`; a view **sobrescreve** com `https://wa.me/5511999999999`.
- Contato público: CSRF, rate limit, gravação, `mail()` opcional se houver e-mail válido em `dados_contato`.

### 5.2 Autenticação

- **Controller:** `AuthController` (PDO direto em `users`)
- **Rotas:** GET `/login`; POST `/authenticate`; GET `/logout`
- **Status:** implementado. Logout GET.

### 5.3 Dashboard

- **Controller:** `DashboardController`
- **View:** `app/views/dashboard/area-restrita.php`
- Mensagens hoje: `MensagemModel::countToday()`. Acessos hoje: mock `143`.
- Header da área restrita pode ser ocultado (`is-restricted`).

### 5.4 Configuração

Persistência real com POST + CSRF nas telas ativas.

- Início: título, subtítulo, botão, upload de imagem em `public/uploads/`
- Sobre nós
- Contato (e-mail, telefone, WhatsApp)
- Propostas (funil; soft-delete `estagio = 'excluido'`)
- Usuários: listar, alterar tipo, criar (`admin`/`editor`/`viewer`)
- Mensagens: listagem
- `email-template.php` existe, mas a rota GET redireciona; não há POST `salvar-email-template`

### 5.5 Migrations CLI

Ver `docs/migrations.md`. Baseline das seis tabelas em `database/migrations/20241101000000_baseline_schema.php`. Sem dados.

### 5.6 Seeders CLI

Ver `docs/seeders.md`. `php bin/seed.php run` após `migrate`. Conteúdo neutro em `dados_inicio` / `dados_contato` / `sobre` (id=1 se ausente). Admin via `SEED_ADMIN_*` (hash `PASSWORD_DEFAULT`). Demo só com `--demo` e `SEED_ALLOW_DEMO=1`.

### 5.7 README vs código

README ainda fala em “micro SaaS”, CMS e API. **Não implementados.** Não tratar como SaaS multi-tenant.

---

## 6. Banco de dados

SGBD: MySQL/MariaDB (PDO). Schema versionado na baseline (sem dados).

| Tabela | Uso |
| --- | --- |
| `users` | Login; `id`, `name`, `email`, `password`, `type`. Sem UNIQUE em `email` |
| `dados_contato` | Bloco contato / destino de `mail()` |
| `dados_inicio` | Hero; `imagem` = nome de arquivo em `public/uploads/` |
| `sobre` | Seção sobre |
| `mensagens_contato` | Formulário público |
| `propostas_clientes` | Funil; `estagio` inclui valor operacional `excluido` |
| `migrations` | Controle CLI (criada pelo runner; não é tabela de negócio) |

Sem FKs. Sem cadastro público. Credenciais só no `.env`.

Labels `email_label` / `whatsapp_label` / `telefone_label` existem só como default PHP, **não** como colunas.

---

## 7. Segurança

**Presente:** CSRF nos POST de login, contato público e config; `password_verify`; prepared statements; regeneração de sessão; fail-closed; cookie `httponly` / `SameSite=Lax` / `secure` se HTTPS; `.env` fora do Git; CLI recusa SAPI não-`cli`; lock `GET_LOCK` por host+database; checksum SHA-256 das migrations.

**Lacunas:** logout GET; `bin/` ainda não está no deny do `.htaccess` da raiz (depende do SAPI check); sem HTTPS forçado; rate limit só em sessão; `users.email` não é UNIQUE; `mail()` usa `From` do e-mail configurado; WhatsApp da landing hardcoded na view; `acessos_hoje` mock; pasta `site/`; sem PHPUnit; `home.php` ignora `$whatsAppUrl` e o ano corrente do helper.

---

## 8. Regras de negócio (código)

1. Visitante vê landing e login.
2. Login: e-mail, senha, CSRF, hash em `users`.
3. Sucesso → `/area-restrita`; já autenticado em `/login` → área restrita.
4. Sem sessão → dashboard/config → `/login`.
5. Tipos: `admin`, `editor`, `viewer` (inválido → `viewer`).
6. `admin` gerencia usuários; `admin`/`editor` editam conteúdo e propostas; autenticados leem mensagens.
7. Cinco falhas de login em 300 s bloqueiam tentativas.
8. Contato público: cinco envios em 300 s; mensagem ≤ 5000 caracteres.
9. Proposta “excluída” some das listagens (`estagio <> 'excluido'`), sem DELETE físico.
10. Baseline não cria usuário admin. O seeder cria um `admin` só com `SEED_ADMIN_*` e se o e-mail ainda não existir.
11. Anos de experiência: helper `max(0, anoAtual - 2024)`; a home fixa o ano final em 2026.

Sem recuperação de senha, cadastro público, API ou multi-tenant.

---

## 9. Pendências

| Prioridade | Item |
| --- | --- |
| Alta | Alinhar WhatsApp e anos de experiência da `home.php` ao controller/banco |
| Alta | Logout POST + CSRF |
| Alta | UNIQUE em `email` (migration futura, com autorização) |
| Média | `acessos_hoje` real ou remover o card |
| Média | README legado; `docs/database.md` e `docs/archtecture.md` obsoletos |
| Média | Isolar `site/`; deny `bin/` no Apache |
| Média | Endurecer `mail()` / From |
| Baixa | PHPUnit; logout e demais GET mutáveis |
| Baixa | Colunas de labels de contato, se forem desejadas de verdade |

---

## 10. Roadmap

**Já feito:** MVC; auth; fail-closed; config persistida; contato público; propostas; upload do início; `.env`; migrations CLI + baseline.

**Não reimplementar** o que já existe acima.

**Próximo (sugestão, não comprometido):** logout CSRF; corrigir divergências da home; UNIQUE de e-mail; limpar docs/assets legados.

**Só no README (sem código):** CMS genérico, API, micro SaaS.

---

## 11. Decisões a preservar

1. PHP puro, sem Laravel/Composer obrigatório.
2. MVC + Router + `routes/web.php`.
3. Front controller em `public/`.
4. CLI de schema separado do HTTP.
5. PDO + prepared statements.
6. Views PHP e partials.
7. Autoload por nome de classe.
8. Auth/autorização no servidor.
9. Fail-closed.
10. Segredos no ambiente, não no Git.
11. Um único fluxo de login.
12. Schema de negócio só via migration/baseline autorizada.
13. Uma instalação = um banco; não multi-tenant.

---

## 12. Instruções para futuras sessões

- Ler este arquivo e `docs/PROJECT_SCOPE.md` / `docs/ARCHITECTURE.md` / `docs/migrations.md`.
- Não criar segundo login nem ORM.
- Reutilizar guards do `ConfigController`.
- Não inventar colunas; mudanças de banco exigem migration e autorização.
- Não commitar `.env`.
- Document root: `public/`. CLI: `php bin/migrate.php`.
- Atualizar **este** arquivo quando mudarem arquitetura, rotas, schema ou regras.

### Rotas oficiais

| Método | Path | Destino |
| --- | --- | --- |
| GET | `/`, `/index.php` | `HomeController::index` |
| POST | `/contato/enviar` | `HomeController::enviarContato` |
| GET | `/login` | `AuthController::login` |
| POST | `/authenticate` | `AuthController::authenticate` |
| GET | `/logout` | `AuthController::logout` |
| GET | `/area-restrita` | `DashboardController::index` |
| GET | `/config/mensagens` | `ConfigController::mensagens` |
| GET | `/config/email-template` | redireciona para mensagens |
| GET/POST | `/config/contato`, `/config/salvar-contato` | contato |
| GET/POST | `/config/sobre`, `/config/salvar-sobre-nos` | sobre |
| GET/POST | `/config/inicio`, `/config/salvar-dados-inicio` | início |
| GET | `/config/usuarios` | usuários |
| POST | `/config/salvar-usuario-tipo`, `/config/criar-usuario` | usuários |
| GET | `/config/propostas` | propostas |
| POST | `/config/salvar-proposta`, `/config/atualizar-proposta`, `/config/excluir-proposta` | propostas |

Aliases `*.php` de login/logout/authenticate/área restrita existem por compatibilidade.
