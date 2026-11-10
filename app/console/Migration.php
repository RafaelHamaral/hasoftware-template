<?php

declare(strict_types=1);

/**
 * Contrato de uma migration. Cada arquivo em database/migrations/ deve
 * retornar uma instância desta classe (return new class extends Migration { ... }).
 */
abstract class Migration
{
    /**
     * Aplica a alteração. Não deve ser chamado pelo front HTTP.
     */
    abstract public function up(PDO $pdo): void;

    /**
     * Reverte a alteração somente quando a migration o permitir de forma explícita.
     *
     * @throws RuntimeException quando a reversão não é suportada
     */
    public function down(PDO $pdo): void
    {
        throw new RuntimeException('Esta migration não implementa reversão.');
    }

    public function supportsDown(): bool
    {
        return false;
    }
}
