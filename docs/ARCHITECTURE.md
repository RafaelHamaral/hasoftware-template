# Arquitetura

PHP puro, MVC próprio, PDO, MySQL/MariaDB. Sem Laravel e sem Composer.

O arquivo `docs/archtecture.md` é legado. Este é o documento vigente.

## Diretórios

```text
public/                 document root (index.php, bootstrap, assets, wrappers)
app/core/               Router
app/controllers/        Home, Auth, Dashboard, Config
app/models/             Config, Mensagem, Proposta, Dashboard
app/views/              HTML PHP + partials
app/console/            Migrator, Migration, SchemaInspector, Seeder, SeederRunner
app/helpers/            experience.php
bin/migrate.php         CLI de schema
bin/seed.php            CLI de dados iniciais
config/                 config.php, env.php, database.php, cli.php
database/migrations/    arquivos YYYYMMDDHHMMSS_slug.php
database/seeders/       arquivos NNN_slug.php
routes/web.php
docs/
```

Document root **não** deve ser a raiz do repositório. O `.htaccess` da raiz tenta reescrever para `public/` e negar `.env`, `config/`, `app/`, `routes/`. `bin/` e `database/` ainda não estão nesse deny; o CLI recusa SAPI diferente de `cli`.

## MVC

- **Router:** método HTTP + path → `[Classe, método]`. Sem `{id}` na URL; IDs de proposta vão no POST.
- **Controllers:** orquestram CSRF, auth e models; `require` da view.
- **Models:** SQL preparado. `DashboardModel` é exceção (stats parciais em PHP).
- **Views:** PHP; `header.php` / `footer.php` / flashes de config.

Não há camada de serviços nem ORM.

## Fluxo HTTP

1. Apache (`public/.htaccess`) ou `php -S -t public public/router.php`.
2. `public/index.php`: `BASE_URL = '/'`, `bootstrap.php`.
3. `config/config.php` define `BASE_PATH`, carrega `.env`.
4. Sessão (`httponly`, `SameSite=Lax`, `secure` se HTTPS).
5. Autoload: `app/core`, `app/controllers`, `app/models`.
6. Registro de rotas e `dispatch`.
7. 404 texto puro se a rota não existir.

## Bootstrap HTTP vs CLI

| | HTTP (`public/bootstrap.php`) | CLI (`config/cli.php`) |
| --- | --- | --- |
| SAPI | web | exige `cli` |
| Sessão | sim | não |
| Autoload | core, controllers, models | + `app/console` |
| PDO | sob demanda nos models | `getPDO()` em migrate/seed quando o comando precisa |
| `help` | — | não conecta ao banco (`migrate` e `seed`) |

## Autenticação e autorização

- Credenciais em `users` (`password` = hash).
- POST `/authenticate`: CSRF, rate limit, `password_verify`, `session_regenerate_id(true)`.
- Sessão: `user_id`, `user_name`, `user_email`, `user_type`.
- Tipo inválido → `viewer`.
- Logout GET destrói a sessão (sem token CSRF).
- Config: `checkAuth` / `requireContatoEditor` / `requireAdmin` no servidor.

## PDO

`getPDO()` (`config/database.php`):

- Variáveis `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_CHARSET` (default `utf8mb4`).
- `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, `EMULATE_PREPARES = false`.
- `loadEnvFile` **não** sobrescreve variável já definida no processo (`DB_NAME=outro php bin/migrate.php ...`).

Não versionar `.env`. Usar `.env.example` como modelo.

## Seis tabelas de negócio

Definidas em `SchemaInspector` e criadas pela migration `20241101000000_baseline_schema.php`. Engine InnoDB, `utf8mb4_general_ci`. Só PK em `id`. Sem FKs e sem UNIQUE em `email`.

| Tabela | Colunas |
| --- | --- |
| `users` | `id`, `name`, `email`, `password`, `type` |
| `dados_contato` | `id`, `titulo`, `descricao`, `telefone`, `whatsapp`, `email` |
| `dados_inicio` | `id`, `titulo`, `subtitulo`, `botao_inicio`, `imagem` |
| `sobre` | `id`, `titulo`, `descricao`, `subtitulo` |
| `mensagens_contato` | `id`, `nome`, `email`, `mensagem`, `criado_em` |
| `propostas_clientes` | `id`, `nome_cliente`, `estagio`, `valor_proposta`, `quantidade_horas`, `resumo`, `criado_em`, `prazo_inicio`, `prazo_final`, `titulo_projeto` |

`migrations` (controle): `id`, `migration` UNIQUE, `filename`, `batch`, `checksum` CHAR(64), `executed_at`.

## Migrations

Ver `docs/migrations.md`.

- Arquivos retornam instância de `Migration` (`up`, `down` opcional, `supportsDown`).
- `status`: leitura.
- `migrate`: lock, cria tabela de controle se preciso, aplica pendentes, registra **depois** de `up()` ok.
- `rollback --confirm`: último batch; exige `supportsDown` em todas; baseline não reverte.
- `baseline --confirm`: valida schema vs `SchemaInspector`; registra batch 0 **sem** `up()`.
- Lock: `GET_LOCK('vmig_' + md5(hostname + database))`, timeout 0, `RELEASE_LOCK` no `finally`.
- Checksum SHA-256; divergência bloqueia migrate/rollback/baseline.

## Baseline

- **Banco vazio:** `migrate` executa o `up()` da baseline (CREATE das seis tabelas, sem dados) e grava o arquivo no lote seguinte (na prática lote 1 se a tabela de controle estava vazia).
- **Banco que já tem as seis tabelas:** `baseline --confirm` (não recria tabelas). Depois `migrate` só para o que vier depois.
- Não inserir usuários na baseline.
- Não rodar `migrate` num banco já povoado sem baseline registrada: o `CREATE TABLE` falha.

## Segurança e implantação

- Document root = `public/`.
- Não servir `bin/migrate.php` pela web.
- Backup antes de `migrate` / `rollback` / `baseline`.
- DDL MySQL/MariaDB em geral **não** desfaz com transação. Falha no `up()`: registro não entra em `migrations`; operador corrige à mão.
- Sem `reset` / `fresh` / `drop-all`.
- Uploads em `public/uploads/`; garantir escrita pelo usuário do PHP só nessa pasta.
- Primeiro admin: criar pela tela de usuários (precisa de um admin já existente) ou inserção controlada fora da baseline — não há seed público.
- PHP observado: 8.2. Servidor embutido: `php -S localhost:8000 -t public public/router.php`.
- Seeders: `docs/seeders.md`. Não rodam migrations. Admin só com variáveis de ambiente.
