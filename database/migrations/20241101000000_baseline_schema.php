<?php

declare(strict_types=1);

/**
 * Schema inicial das tabelas de negócio, sem dados.
 * Em bancos já existentes use: php bin/migrate.php baseline --confirm
 * Em bancos vazios use: php bin/migrate.php migrate
 */
return new class extends Migration {
    public function up(PDO $pdo): void
    {
        foreach (SchemaInspector::createStatements() as $sql) {
            $pdo->exec($sql);
        }
    }
};
