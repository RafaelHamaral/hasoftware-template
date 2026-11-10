# Seeders

Dados iniciais para uma instalação nova. **Não** alteram o schema. Somente CLI (`PHP_SAPI === 'cli'`).

Migrations criam tabelas. Seeders inserem registros mínimos. Ordem:

```bash
php bin/migrate.php migrate
php bin/seed.php run
```

O seeder **não** dispara migrations.

## Comandos

```bash
php bin/seed.php help
php bin/seed.php status
php bin/seed.php run
php bin/seed.php run --demo
```

| Comando | Banco | Efeito |
| --- | --- | --- |
| `help` | não conecta | texto de uso |
| `status` | só leitura | schema e contagens; exit 1 se faltar tabela |
| `run` | escrita | conteúdo institucional (id=1) se ausente; admin se as variáveis existirem e o e-mail ainda não estiver em `users` |
| `run --demo` | escrita | como `run`, mais mensagem/proposta de exemplo. Exige `SEED_ALLOW_DEMO=1` e `APP_ENV` ≠ `production`/`prod` |

`run` é idempotente: não duplica, não apaga, não sobrescreve linhas já existentes (incluindo admin já criado).

`DB_NAME` do processo prevalece sobre o `.env`.

## Variáveis de ambiente

Não versionar senhas. Não há valor padrão de senha no código.

| Variável | Uso |
| --- | --- |
| `SEED_ADMIN_NAME` | Nome do administrador inicial |
| `SEED_ADMIN_EMAIL` | E-mail (login) |
| `SEED_ADMIN_PASSWORD` | Senha em texto só no ambiente; gravada com `password_hash` / `PASSWORD_DEFAULT` (compatível com `password_verify`) |
| `SEED_ALLOW_DEMO` | Deve ser `1` para `--demo` |
| `APP_ENV` | `production` ou `prod` bloqueia `--demo` |

Se ainda não houver nenhum `type = admin` e as três variáveis do admin faltarem, `run` aborta com instrução. Se já existir admin, a ausência das variáveis **não** falha o seed de conteúdo.

A senha precisa de no mínimo 8 caracteres (mesma regra da tela de usuários). A senha **não** é impressa.

Exemplo (não gravar no Git):

```bash
SEED_ADMIN_NAME='Administrador' \
SEED_ADMIN_EMAIL='admin@localhost' \
SEED_ADMIN_PASSWORD='********' \
php bin/seed.php run
```

## Computador novo

1. Copiar o código e criar o database vazio.
2. `.env` com `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_CHARSET` (sem commitar).
3. `php bin/migrate.php migrate`
4. Definir `SEED_ADMIN_*` no ambiente (ou no `.env` local).
5. `php bin/seed.php run`
6. Entrar em `/login` com o e-mail e a senha definidos.
7. Completar contato, sobre e início na área restrita. O seed só coloca textos neutros, sem telefone, e-mail comercial ou marca fictícia.

## Migrations vs seeders

| | Migrations | Seeders |
| --- | --- | --- |
| Objeto | Tabelas, colunas, índices | Linhas |
| Comando | `php bin/migrate.php` | `php bin/seed.php` |
| Produção | aplicar pendentes após backup | `run` só para instalação; não usa `--demo` |

## Produção

- Backup antes de qualquer `run`.
- Confirme o banco (`status` imprime `Banco:`).
- Não use `--demo`.
- Não rode seed para “resetar” conteúdo: registros já personalizados são preservados.
- Não coloque `SEED_ADMIN_PASSWORD` no repositório.
