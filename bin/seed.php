<?php

declare(strict_types=1);

/**
 * Ferramenta CLI de seeders. Não deve ser servida pelo document root.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este comando só pode ser executado em linha de comando.\n");
    exit(1);
}

require_once dirname(__DIR__) . '/config/cli.php';

const SEED_USAGE = <<<TXT
Uso:
  php bin/seed.php help
  php bin/seed.php status
  php bin/seed.php run
  php bin/seed.php run --demo

status    Somente leitura. Não insere dados.
run       Insere registros iniciais ausentes (idempotente).
run --demo
          Inclui dados demonstrativos. Exige SEED_ALLOW_DEMO=1
          e APP_ENV diferente de production/prod.

Administrador inicial (obrigatório se ainda não houver admin):
  SEED_ADMIN_NAME
  SEED_ADMIN_EMAIL
  SEED_ADMIN_PASSWORD   (mínimo 8 caracteres; nunca em texto puro no banco)

TXT;

/**
 * @param list<string> $argv
 * @return array{0: string, 1: list<string>}
 */
function seedParseCommand(array $argv): array
{
    $args = array_slice($argv, 1);
    $command = $args[0] ?? 'help';
    $flags = array_values(array_filter(
        array_slice($args, 1),
        static fn (string $arg): bool => str_starts_with($arg, '--')
    ));

    return [$command, $flags];
}

function seedMakeRunner(): SeederRunner
{
    return new SeederRunner(getPDO(), BASE_PATH . '/database/seeders');
}

[$command, $flags] = seedParseCommand($argv);

switch ($command) {
    case 'help':
    case '--help':
    case '-h':
        fwrite(STDOUT, SEED_USAGE);
        exit(0);

    case 'status':
        try {
            $status = seedMakeRunner()->status();
            fwrite(STDOUT, 'Banco: ' . $status['database'] . PHP_EOL);
            if ($status['schema_ok'] !== true) {
                fwrite(
                    STDOUT,
                    'Schema incompleto. Tabelas ausentes: '
                    . implode(', ', $status['missing_tables']) . PHP_EOL
                );
                exit(1);
            }
            fwrite(STDOUT, 'Schema: ok' . PHP_EOL);
            fwrite(STDOUT, 'Seeders (run padrão, sem demo):' . PHP_EOL);
            foreach ($status['seeders'] as $seeder) {
                fwrite(STDOUT, '  - ' . $seeder['file'] . ' (' . $seeder['name'] . ')' . PHP_EOL);
            }
            fwrite(STDOUT, 'Registros:' . PHP_EOL);
            foreach ($status['counts'] as $table => $count) {
                fwrite(STDOUT, '  ' . $table . ': ' . $count . PHP_EOL);
            }
            exit(0);
        } catch (Throwable $e) {
            fwrite(STDERR, 'Falha no status: ' . $e->getMessage() . PHP_EOL);
            exit(1);
        }

    case 'run':
        $includeDemo = in_array('--demo', $flags, true);
        try {
            $result = seedMakeRunner()->run($includeDemo);
            fwrite(STDOUT, 'Banco: ' . $result['database'] . PHP_EOL);
            foreach ($result['results'] as $row) {
                fwrite(STDOUT, $row['seeder'] . ': ' . $row['action'] . PHP_EOL);
            }
            exit(0);
        } catch (Throwable $e) {
            fwrite(STDERR, 'Falha no seed: ' . $e->getMessage() . PHP_EOL);
            exit(1);
        }

    default:
        fwrite(STDERR, 'Comando desconhecido: ' . $command . PHP_EOL . PHP_EOL);
        fwrite(STDERR, SEED_USAGE);
        exit(1);
}
