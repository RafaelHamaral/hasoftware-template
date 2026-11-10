# Migrations

Ferramenta **somente CLI** (`PHP_SAPI === 'cli'`). PHP puro, PDO, MySQL/MariaDB. Não chamar via HTTP nem colocar `bin/` no document root.

Comandos reais em `bin/migrate.php` + `app/console/Migrator.php`.

## Comandos

```bash
php bin/migrate.php status
php bin/migrate.php migrate
php bin/migrate.php rollback --confirm
php bin/migrate.php baseline --confirm
php bin/migrate.php help
# ou: php bin/migrate.php --help
```

| Comando | Banco | Efeito |
| --- | --- | --- |
| `help` | não conecta | texto de uso |
| `status` | só leitura | não cria `migrations` |
| `migrate` | escrita | lock; cria `migrations` se faltar; aplica **pendentes** (`up()`); registra após sucesso |
| `rollback --confirm` | escrita | lock; reverte **somente o último lote** se todas tiverem `supportsDown()` |
| `baseline --confirm` | escrita | lock; **não executa `up()`**; valida schema; registra baseline em **batch 0** |
| `rollback` / `baseline` sem `--confirm` | não conecta | recusa |

Não existem `reset`, `fresh` nem `drop-all`.

`DB_NAME` (e demais `DB_*`) do processo prevalecem sobre o `.env`.

## Banco novo

Instalação vazia (database criado, sem as seis tabelas):

1. Backup não se aplica a dados, mas confira o `DB_NAME`.
2. `.env` apontando para esse database.
3. **`php bin/migrate.php migrate`** — cria a tabela de controle (se preciso), executa `20241101000000_baseline_schema.php` (`CREATE TABLE` das seis tabelas, **sem dados**) e grava o registro (lote **1** se a tabela de controle estava vazia).
4. `php bin/seed.php run` com `SEED_ADMIN_*` no ambiente (ver `docs/seeders.md`). A baseline **não** insere usuários.

Não use `baseline --confirm` num banco sem as seis tabelas: a validação aborta e não registra.

## Banco existente

Já possui as seis tabelas de negócio (instalação antiga):

1. **Backup.**
2. `php bin/migrate.php status` (leitura).
3. **`php bin/migrate.php baseline --confirm`** — compara engine, collation, colunas (tipo, nulabilidade, default, extra), PK e índices com `SchemaInspector`. Não roda `up()`. Se divergir, **aborta sem INSERT**. Se bater, registra a baseline com checksum, **batch 0**.
4. **`php bin/migrate.php migrate`** — só o que ainda não está em `migrations`.

Não rode `migrate` nesse cenário **antes** da baseline: o `up()` da baseline tentaria `CREATE TABLE` em tabelas que já existem e falharia.

`baseline --confirm` de novo, com a baseline já registrada, recusa e não altera nada.

## Atualizações futuras

Depois da baseline (lote 0 ou 1) estar registrada:

1. Backup.
2. `php bin/migrate.php status`
3. **`php bin/migrate.php migrate`** — apenas arquivos novos, em ordem do nome (`YYYYMMDDHHMMSS_...`).

Altere schema **só** com um arquivo novo. Não edite uma migration já aplicada (checksum SHA-256 bloqueia migrate, rollback e baseline).

## Rollback

```bash
php bin/migrate.php rollback --confirm
```

- Sem `--confirm`: nada é feito.
- Só o **último** `batch`.
- Todas as migrations desse lote precisam de `down()` **e** `supportsDown(): true`. Caso contrário o lote inteiro é recusado **antes** de qualquer `down()`.
- A baseline **não** implementa `down()` (não apaga tabelas de negócio). Não tente rollback da baseline.
- Após `down()` ok, o registro é apagado de `migrations`.

## Como criar uma migration

Arquivo `database/migrations/YYYYMMDDHHMMSS_slug.php` (`slug` = `[a-z0-9_]+`):

```php
<?php
declare(strict_types=1);

return new class extends Migration {
    public function up(PDO $pdo): void
    {
        $pdo->exec('ALTER TABLE ...');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('ALTER TABLE ...');
    }

    public function supportsDown(): bool
    {
        return true;
    }
};
```

Deve `return` instância de `Migration`. Sem `supportsDown` verdadeiro, `down()` padrão lança exceção.

## Lock, checksum, falhas de DDL

- Lock MySQL `GET_LOCK` derivado de `@@hostname` + `DATABASE()` (`vmig_` + md5). Timeout 0: segundo processo falha na hora. `RELEASE_LOCK` no `finally`.
- Checksum SHA-256 do arquivo. Divergência: nenhuma aplicação/reversão/baseline extra.
- **DDL no MySQL/MariaDB em geral não participa de transação.** Se `up()` quebrar no meio, a migration **não** é marcada como executada; pedaços de `ALTER`/`CREATE` podem ter ficado no banco. Restaure o backup ou desfaça o DDL manualmente e rode `migrate` de novo.
- Registro em `migrations` só depois de `up()` bem-sucedido (`INSERT`). `baseline` só depois da validação ok.

## Backup e produção

- Backup lógico/físico **antes** de `migrate`, `rollback` e `baseline` em qualquer ambiente com dados.
- Confirme `SELECT DATABASE()` / `status` (campo “Banco:”) antes de escrever.
- Não execute o runner contra o banco errado. Use `DB_NAME=...` no comando quando for banco descartável.
- Não há caminho automático de “desfazer tudo”.
