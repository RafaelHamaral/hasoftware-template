# PROJECT_CONTEXT.md

Documentação de contexto do estado **atual** do código (outubro/2026).  
Fonte principal: o repositório. O `README.md` e `docs/` estão desatualizados ou incompletos em relação ao código.

---

## 1. Visão geral

- **Nome:** vibecoding-um
- **Objetivo:** site institucional da H. A Consultoria & Desenvolvimento (Rafael Holanda do Amaral), com login e área restrita. O README descreve intenção de evoluir para micro SaaS; isso **não** está implementado no código.
- **Público-alvo:** visitantes da landing; usuários cadastrados na tabela `users` (tipos efetivos no código: `admin` e demais tratados como `viewer`).
- **Funcionalidades confirmadas no código:**
  - Landing (início, sobre, serviços, contato, CTA WhatsApp).
  - Login / logout com sessão.
  - Dashboard (área restrita) com estatísticas mock.
  - Telas de configuração (template de e-mail, contato, usuários) **somente leitura / mock**, restritas a `admin`.
- **Estágio:** MVP em PHP puro (MVC próprio). Sem Composer, sem testes automatizados, sem migrations versionadas no repositório.

---

## 2. Stack tecnológica

| Item | Estado no código |
| --- | --- |
| Linguagem | PHP (sintaxe 8+: tipos em métodos, `str_starts_with`) |
| Versão exata do PHP | **PENDENTE DE CONFIRMAÇÃO** (README cita “PHP 8”) |
| Banco | MySQL via PDO (`mysql:` DSN, charset `utf8mb4`) |
| Front | HTML nas views, CSS em `public/css/style.css`, JS em `public/js/script.js` |
| Fontes / ícones | Google Fonts e Font Awesome via CDN no `header.php` |
| Framework PHP | Nenhum |
| Composer / vendor | Ausentes |
| Bibliotecas PHP | Nenhuma (loader de `.env` próprio em `config/env.php`) |
| Servidor | Front controller Apache (`.htaccess`) ou `php -S` com `public/router.php` |
| Licença | MIT (Rafael Holanda do Amaral, 2026) |

---

## 3. Arquitetura

**Padrão:** MVC próprio, sem framework.

**Fluxo de requisição (oficial):**

1. Document root deve ser `public/`.
2. `public/index.php` define `BASE_URL` como `'/'`, carrega `bootstrap.php`.
3. Bootstrap: `config/config.php` (inclui `.env`), sessão, autoload de classes em `app/core`, `app/controllers`, `app/models`.
4. Instancia `Router`, registra `routes/web.php`, faz `dispatch` do path + método HTTP.
5. Controller executa e `require` da view.

**Roteamento:** `app/core/Router.php` — mapa GET/POST path → `[Classe, método]`. Sem parâmetros de URL, sem middleware. 404 textual se a rota não existir.

**Autenticação:** `AuthController::login` (GET) e `authenticate` (POST). Consulta `users` por `email`, `password_verify`, CSRF (`hash_equals`), rate limit em sessão (5 tentativas / 300 s), `session_regenerate_id(true)` no sucesso. Logout em `AuthController::logout` (GET): limpa sessão, expira cookie, `session_destroy`.

**Autorização:** fail-closed. Sem `user_id` na sessão → redireciona para login. `user_type` na sessão só é `admin` se o campo `type` do banco for exatamente `admin`; qualquer outro valor vira `viewer`. `ConfigController` exige `admin` em todas as ações. Menu do dashboard esconde links de config para não-admin; a regra efetiva está no controller.

**Banco:** `getPDO()` em `config/database.php` lê `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_CHARSET` do ambiente. Prepared statements no login. Models de dashboard/config **não** usam PDO (dados fixos em PHP).

**Entradas de compatibilidade:** `public/login.php`, `logout.php`, `authenticate.php`, `area-restrita.php` e `public/auth/login-process.php` **não** implementam auth própria; encaminham aos controllers oficiais.

---

## 4. Estrutura de diretórios

| Caminho | Função |
| --- | --- |
| `public/` | Document root: front controller, assets, wrappers de compatibilidade |
| `public/index.php` | Entrada oficial |
| `public/bootstrap.php` | Config, cookie de sessão, autoload |
| `public/router.php` | Fallback para `php -S` (arquivos estáticos vs MVC) |
| `public/.htaccess` | Rewrite para `index.php` (Apache) |
| `app/core/Router.php` | Roteador |
| `app/controllers/` | Home, Auth, Dashboard, Config |
| `app/models/` | DashboardModel, ConfigModel (mocks) |
| `app/views/` | Templates PHP; `partials/header.php` e `footer.php` |
| `app/helpers/experience.php` | Cálculo de anos de experiência |
| `routes/web.php` | Declaração de rotas |
| `config/` | `config.php`, `env.php`, `database.php` |
| `.env` | Segredos locais (gitignored) |
| `.env.example` | Modelo sem credenciais reais |
| `docs/` | Notas antigas; `database.md` vazio; `archtecture.md` genérico |
| `site/` | Residual: só `site/js/script.js` (não é o asset servido pelo MVC) |
| `.htaccess` (raiz) | Nega `.env`, `config/`, `app/`, `routes/` se o docroot for a raiz do repo; reescreve para `public/` |

Não há `composer.json`, `vendor/`, pasta de testes nem arquivos `.sql` no repositório.

---

## 5. Módulos e funcionalidades

### 5.1 Institucional (landing)

- **Objetivo:** apresentar a empresa e gerar contato/agendamento.
- **Controller:** `HomeController`
- **Model:** nenhum
- **Views:** `app/views/home.php` + partials
- **Rotas GET:** ``, `/`, `/index.php`
- **Existente:** hero, sobre (anos via helper, ano inicial **2024** no controller), serviços estáticos, bloco contato, CTA WhatsApp (`wa.me`, número **hardcoded** `5511999999999` — placeholder).
- **Formulário de contato:** `action="#"`, envio interceptado no JS (feedback fake, sem backend).
- **Status:** Implementado (conteúdo estático). Envio de contato: **parcialmente implementado**.

### 5.2 Autenticação

- **Controller:** `AuthController`
- **Model:** nenhum (PDO direto)
- **Views:** `app/views/login.php`
- **Rotas:** GET `/login`, `/login.php`; POST `/authenticate`, `/authenticate.php`; GET `/logout`, `/logout.php`
- **Status:** Implementado (fluxo MVC). Logout CSRF: não existe (GET). Integração browser: **pendente de validação**.

### 5.3 Dashboard (área restrita)

- **Controller:** `DashboardController`
- **Model:** `DashboardModel` (`emails_hoje`, `acessos_hoje` mock)
- **View:** `app/views/dashboard/area-restrita.php`
- **Rotas GET:** `/area-restrita`, `/area-restrita.php`
- **Status:** Parcialmente implementado (auth real, dados simulados). `Cache-Control: no-store` no controller.

### 5.4 Configuração (admin)

- **Controller:** `ConfigController` (`requireAdmin()` em todas as ações)
- **Model:** `ConfigModel` (arrays fixos; lista de usuários **não** lê a tabela `users`)
- **Views:** `config/email-template.php`, `config/contato.php`, `config/usuarios.php`
- **Rotas GET:** `/config/email-template`, `/config/contato`, `/config/usuarios`
- **Formulário de template:** campos visíveis, botão Salvar **sem** POST/rota/CSRF/persistência.
- **Status:** Parcialmente implementado (UI + gate admin). Persistência: **planejado** (não há código).

### 5.5 Itens do README não encontrados no código

Configuração de imagens/texto da landing, visualização de e-mails reais, configuração de API: **não implementados**. Tratar o README como desatualizado.

---

## 6. Banco de dados

- **SGBD:** MySQL (PDO).
- **SQL no repositório:** nenhum. `docs/database.md` vazio.
- **Tabela confirmada (produto / sessão de desenvolvimento):** `users`
  - `id` — PK
  - `name`
  - `email` — usado no login (`WHERE email = :email`)
  - `password` — hash verificado com `password_verify`
  - `type` — `admin` concede acesso às rotas `/config/*`; resto → `viewer`
- **Relacionamentos:** nenhum outro confirmado no código.
- **Models associados:** nenhum model de `User`. Auth usa PDO no controller.
- **Inconsistências:**
  - Formulário de contato usa o name `email_login`; isso **não** é a coluna de login.
  - `ConfigModel::getUsuarios()` não consulta `users`.
  - Nome do banco e credenciais só no `.env` (não versionar).

Outras tabelas: **PENDENTE DE CONFIRMAÇÃO** (não há dump nem models adicionais).

---

## 7. Segurança

### Já tratado no código atual

- Um fluxo de login (`AuthController::authenticate`): CSRF, `password_verify`, prepared statements, regeneração de ID de sessão, tipo fail-closed.
- Área restrita e `/config/*` validados no servidor.
- Cookie de sessão: `httponly`, `samesite=Lax`, `secure` se HTTPS.
- Seed público `public/usuario-teste.php` removido.
- Wrappers em `public/` não autenticam sozinhos.
- Credenciais fora do PHP versionado (`.env` + `.gitignore`).
- Rewrite na raiz para não servir `config/`, `app/`, `routes/`, `.env` se o docroot estiver errado.

### Riscos / lacunas conhecidas

- Logout por GET (logout CSRF).
- Wrappers `.php` ainda acessíveis (delegam ao MVC; úteis sem rewrite).
- Sem HTTPS forçado no PHP.
- Rate limit só em sessão (não por IP).
- Formulário de contato sem backend e sem CSRF.
- Telas de config sem POST protegido; “Salvar” não persiste.
- Número/e-mail de WhatsApp e contato institucional hardcoded ou mock.
- Pasta `site/` residual.
- `.htaccess` só aplica no Apache; `php -S` **precisa** de `public/router.php`.
- Sem testes automatizados; login real no browser **pendente de validação**.

---

## 8. Regras de negócio (somente as do código)

1. Visitante não autenticado pode ver a landing e a tela de login.
2. Login exige e-mail válido, senha não vazia, CSRF válido e hash conferido na tabela `users`.
3. Após login, redireciona para `/area-restrita`.
4. Já autenticado que acessa `/login` vai para `/area-restrita`.
5. Sem `user_id` na sessão, dashboard e config redirecionam para `/login`.
6. Só `type === 'admin'` (string exata) acessa `/config/*`; senão redireciona para `/area-restrita`.
7. Ausência ou valor diferente de `type` na sessão **não** concede admin.
8. Cinco falhas de login na janela de 300 s bloqueiam novas tentativas (mensagem na sessão).
9. Anos de experiência = `max(0, anoAtual - 2024)` (ano inicial no `HomeController`).
10. Estatísticas do dashboard e dados de config **não** são regras de negócio persistidas; são constantes no PHP.

Não há regra de cadastro público, recuperação de senha, multi-tenant ou API.

---

## 9. Pendências conhecidas

| Prioridade | Item |
| --- | --- |
| Crítica | Validar login/logout/sessão no servidor real (HTTP local não foi confirmado nesta linha de trabalho). Document root = `public/` ou `php -S -t public public/router.php`. |
| Alta | Logout GET (CSRF de encerramento de sessão). |
| Alta | Persistência real de config e listagem de usuários a partir de `users`. |
| Alta | Backend do formulário de contato (hoje só JS). |
| Média | README e `docs/` desalinhados do código. |
| Média | WhatsApp/contato placeholder; `site/js` duplicado. |
| Média | Sem Composer, `.gitignore` só cobre `.env` (não cobre OS/IDE). |
| Baixa | Sem suíte de testes. |
| Baixa | Logout e outras ações mutáveis ainda sem POST+CSRF além do login. |

---

## 10. Roadmap

**Já feito (não reimplementar):** MVC + front controller; auth unificada; autorização fail-closed; rotas `/config/*`; wrappers seguros; `.env`; rewrite/`router.php`.

**Próximo (prioridade):**

1. Logout POST + CSRF.
2. Teste manual ponta a ponta (login admin, viewer, config, logout).
3. Persistência de configurações (e leitura real de `users` na tela de usuários).
4. POST do contato institucional.
5. Atualizar README/`docs` ou removê-los se forem legado.
6. Remover ou isolar `site/`.
7. Testes automatizados mínimos de auth.

**Planejado só no README (não existe código):** CMS da landing, módulo de e-mails, API.

---

## 11. Decisões arquiteturais (preservar)

Confirmadas pelo código:

1. PHP puro, sem framework.
2. MVC próprio (`controllers` / `models` / `views` + `Router`).
3. Entrada principal: `public/index.php` (front controller).
4. Rotas centralizadas em `routes/web.php`.
5. PDO com prepared statements para SQL.
6. Views PHP com `require`; header/footer em `partials`.
7. Autoload por convenção de nome de classe = arquivo.
8. Autenticação e autorização no backend, não só no menu.
9. Fail-closed para permissão administrativa.
10. Segredos de banco em variáveis de ambiente, não no Git.
11. Não introduzir segundo mecanismo de login.

Não há decisão confirmada de usar Composer, ORM ou RBAC completo.

---

## 12. Instruções para futuras sessões

- Ler este arquivo antes de nova funcionalidade.
- Respeitar o MVC e o Router existentes; não criar um segundo login.
- Reutilizar `partials`, `AuthController` e `requireAdmin()` em vez de copiar guards.
- Não criar camadas, pacotes ou arquivos sem necessidade.
- Não implementar fora do escopo pedido; não alterar o que já funciona sem motivo.
- Não inventar schema; mudanças de banco exigem confirmação.
- Não commitar `.env` nem credenciais.
- Priorizar segurança (CSRF em POST, prepared statements, fail-closed).
- Document root: `public/`. Servidor embutido: `php -S localhost:8000 -t public public/router.php`.
- Atualizar **este** `PROJECT_CONTEXT.md` quando mudarem arquitetura, regras de negócio, rotas ou módulos.

### Referências rápidas de rotas oficiais

| Método | Path | Destino |
| --- | --- | --- |
| GET | `/`, `/index.php` | `HomeController::index` |
| GET | `/login` | `AuthController::login` |
| POST | `/authenticate` | `AuthController::authenticate` |
| GET | `/logout` | `AuthController::logout` |
| GET | `/area-restrita` | `DashboardController::index` |
| GET | `/config/email-template` | `ConfigController::emailTemplate` |
| GET | `/config/contato` | `ConfigController::contato` |
| GET | `/config/usuarios` | `ConfigController::usuarios` |

Aliases `*.php` das mesmas ações existem para compatibilidade quando o rewrite não intercepta o arquivo físico.
