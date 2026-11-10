<?php

declare(strict_types=1);

/**
 * Contrato de um seeder. Arquivos em database/seeders/ devem retornar
 * uma instância desta classe.
 */
abstract class Seeder
{
    abstract public function name(): string;

    /**
     * @return list<string>
     */
    abstract public function requiredTables(): array;

    /**
     * Aplica dados iniciais de forma idempotente. Não apaga nem sobrescreve.
     *
     * @return string Resumo da ação (created|skipped|...)
     */
    abstract public function run(PDO $pdo): string;

    public function isDemo(): bool
    {
        return false;
    }
}
