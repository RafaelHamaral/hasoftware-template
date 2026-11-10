<?php

declare(strict_types=1);

/**
 * Ferramenta CLI de migrations. Não deve ser servida pelo document root.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este comando só pode ser executado em linha de comando.\n");
    exit(1);
}

require_once dirname(__DIR__) . '/config/cli.php';

const MIGRATE_USAGE = <<<TXT
Uso:
  php bin/migrate.php status
  php bin/migrate.php migrate
  php bin/migrate.php rollback --confirm
  php bin/migrate.php baseline --confirm

status    Somente leitura. Não cria tabelas.
migrate   Aplica pendentes e cria a tabela de controle se necessário.
rollback  Reverte o último lote (exige --confirm e down() explícito).
baseline  Valida o schema existente e registra a baseline (exige --confirm).

TXT;

/**
 * @param list<string> $argv
 * @return array{0: string, 1: list<string>}
 */
function migrateParseCommand(array $argv): array
{
    $args = array_slice($argv, 1);
    $command = $args[0] ?? 'help';
    $flags = array_values(array_filter(
        array_slice($args, 1),
        static fn (string $arg): bool => str_starts_with($arg, '--')
    ));

    return [$command, $flags];
}

function migratePrintStatus(array $status): int
{
    fwrite(STDOUT, 'Banco: ' . $status['database'] . PHP_EOL);
    fwrite(STDOUT, $status['message'] . PHP_EOL);

    if ($status['initialized'] !== true) {
        fwrite(STDOUT, PHP_EOL . 'Pendentes no disco (não aplicadas; tabela de controle ausente):' . PHP_EOL);
        if ($status['pending'] === []) {
            fwrite(STDOUT, "  (nenhum arquivo de migration)" . PHP_EOL);
        } else {
            foreach ($status['pending'] as $file) {
                fwrite(STDOUT, '  - ' . $file['filename'] . PHP_EOL);
            }
        }

        return 0;
    }

    fwrite(STDOUT, PHP_EOL . 'Aplicadas:' . PHP_EOL);
    if ($status['applied'] === []) {
        fwrite(STDOUT, "  (nenhuma)" . PHP_EOL);
    } else {
        foreach ($status['applied'] as $row) {
            fwrite(
                STDOUT,
                sprintf(
                    "  [batch %s] %s  %s\n",
                    (string) ($row['batch'] ?? ''),
                    (string) ($row['migration'] ?? ''),
                    (string) ($row['executed_at'] ?? '')
                )
            );
        }
    }

    fwrite(STDOUT, PHP_EOL . 'Pendentes:' . PHP_EOL);
    if ($status['pending'] === []) {
        fwrite(STDOUT, "  (nenhuma)" . PHP_EOL);
    } else {
        foreach ($status['pending'] as $file) {
            fwrite(STDOUT, '  - ' . $file['filename'] . PHP_EOL);
        }
    }

    if ($status['diverged'] !== []) {
        fwrite(STDERR, PHP_EOL . 'Checksum divergente (arquivo alterado após a execução):' . PHP_EOL);
        foreach ($status['diverged'] as $row) {
            fwrite(STDERR, '  - ' . $row['filename'] . PHP_EOL);
        }
    }

    if ($status['missing_files'] !== []) {
        fwrite(STDERR, PHP_EOL . 'Registradas no banco sem arquivo no disco:' . PHP_EOL);
        foreach ($status['missing_files'] as $row) {
            fwrite(STDERR, '  - ' . (string) ($row['migration'] ?? '') . PHP_EOL);
        }
    }

    return ($status['diverged'] === [] && $status['missing_files'] === []) ? 0 : 2;
}

function migrateMakeRunner(): Migrator
{
    return new Migrator(getPDO(), BASE_PATH . '/database/migrations');
}

[$command, $flags] = migrateParseCommand($argv);

switch ($command) {
    case 'help':
    case '--help':
    case '-h':
        fwrite(STDOUT, MIGRATE_USAGE);
        exit(0);

    case 'baseline':
        if (!in_array('--confirm', $flags, true)) {
            fwrite(STDERR, "Baseline recusada: informe --confirm. Nenhuma alteração foi feita.\n");
            exit(1);
        }
        try {
            $result = migrateMakeRunner()->baseline();
            fwrite(
                STDOUT,
                'Banco: ' . $result['database'] . PHP_EOL
                . 'Baseline registrada sem executar up(): ' . $result['filename'] . PHP_EOL
            );
            exit(0);
        } catch (Throwable $e) {
            fwrite(STDERR, 'Falha na baseline: ' . $e->getMessage() . PHP_EOL);
            exit(1);
        }

    case 'rollback':
        if (!in_array('--confirm', $flags, true)) {
            fwrite(STDERR, "Rollback recusado: informe --confirm. Nenhuma alteração foi feita.\n");
            exit(1);
        }
        try {
            $result = migrateMakeRunner()->rollback();
            fwrite(
                STDOUT,
                'Banco: ' . $result['database'] . PHP_EOL
                . 'Lote revertido: ' . $result['batch'] . PHP_EOL
            );
            foreach ($result['reverted'] as $filename) {
                fwrite(STDOUT, '  - ' . $filename . PHP_EOL);
            }
            exit(0);
        } catch (Throwable $e) {
            fwrite(STDERR, 'Falha no rollback: ' . $e->getMessage() . PHP_EOL);
            exit(1);
        }

    case 'migrate':
        try {
            $result = migrateMakeRunner()->migrate();
            fwrite(STDOUT, 'Banco: ' . $result['database'] . PHP_EOL);
            if ($result['applied'] === []) {
                fwrite(STDOUT, "Nenhuma migration pendente.\n");
                exit(0);
            }
            fwrite(STDOUT, 'Lote: ' . (string) $result['batch'] . PHP_EOL);
            foreach ($result['applied'] as $filename) {
                fwrite(STDOUT, '  - ' . $filename . PHP_EOL);
            }
            exit(0);
        } catch (Throwable $e) {
            fwrite(STDERR, 'Falha no migrate: ' . $e->getMessage() . PHP_EOL);
            exit(1);
        }

    case 'status':
        try {
            $code = migratePrintStatus(migrateMakeRunner()->status());
            exit($code);
        } catch (Throwable $e) {
            fwrite(STDERR, 'Falha no status: ' . $e->getMessage() . PHP_EOL);
            exit(1);
        }

    default:
        fwrite(STDERR, 'Comando desconhecido: ' . $command . PHP_EOL . PHP_EOL);
        fwrite(STDERR, MIGRATE_USAGE);
        exit(1);
}
