<?php

declare(strict_types=1);

/**
 * Runner de seeders (CLI). Não executa migrations.
 */
class SeederRunner
{
    public const FILENAME_PATTERN = '/^[0-9]{3}_[a-z0-9_]+\\.php$/';

    public const LOCK_TIMEOUT_SECONDS = 0;

    private PDO $pdo;

    private string $seedersPath;

    public function __construct(PDO $pdo, string $seedersPath)
    {
        $this->pdo = $pdo;
        $resolved = realpath($seedersPath);
        if ($resolved === false || !is_dir($resolved)) {
            throw new RuntimeException('Diretório de seeders inválido.');
        }
        $this->seedersPath = $resolved;
    }

    public static function envOptional(string $name): ?string
    {
        $value = $_ENV[$name] ?? getenv($name);
        if ($value === false || $value === '') {
            return null;
        }

        return (string) $value;
    }

    /**
     * @return array{
     *     database: string,
     *     schema_ok: bool,
     *     missing_tables: list<string>,
     *     seeders: list<array{file: string, name: string, demo: bool}>,
     *     counts: array<string, int>
     * }
     */
    public function status(): array
    {
        $files = $this->listSeederFiles(false);
        $required = $this->collectRequiredTables($files);
        $missing = $this->missingTables($required);

        return [
            'database' => $this->currentDatabase(),
            'schema_ok' => $missing === [],
            'missing_tables' => $missing,
            'seeders' => array_map(
                static fn (array $file): array => [
                    'file' => $file['filename'],
                    'name' => $file['instance']->name(),
                    'demo' => $file['instance']->isDemo(),
                ],
                $files
            ),
            'counts' => $missing === [] ? $this->rowCounts() : [],
        ];
    }

    /**
     * @return array{database: string, results: list<array{seeder: string, action: string}>}
     */
    public function run(bool $includeDemo): array
    {
        return $this->withLock(function () use ($includeDemo): array {
            $files = $this->listSeederFiles($includeDemo);
            $required = $this->collectRequiredTables($files);
            $missing = $this->missingTables($required);
            if ($missing !== []) {
                throw new RuntimeException(
                    'Schema incompleto. Execute as migrations antes dos seeders. Tabelas ausentes: '
                    . implode(', ', $missing)
                );
            }

            if ($includeDemo) {
                $this->assertDemoAllowed();
            }

            $this->pdo->beginTransaction();
            try {
                $results = [];
                foreach ($files as $file) {
                    $action = $file['instance']->run($this->pdo);
                    $results[] = [
                        'seeder' => $file['instance']->name(),
                        'action' => $action,
                    ];
                }
                $this->pdo->commit();
            } catch (Throwable $e) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                throw $e;
            }

            return [
                'database' => $this->currentDatabase(),
                'results' => $results,
            ];
        });
    }

    public function currentDatabase(): string
    {
        $value = $this->pdo->query('SELECT DATABASE()')->fetchColumn();

        return is_string($value) ? $value : '';
    }

    public function lockName(): string
    {
        $stmt = $this->pdo->query('SELECT @@hostname AS host, DATABASE() AS db');
        $row = $stmt !== false ? $stmt->fetch() : false;
        $host = is_array($row) ? (string) ($row['host'] ?? '') : '';
        $db = is_array($row) ? (string) ($row['db'] ?? '') : '';

        return 'vseed_' . md5($host . "\0" . $db);
    }

    /**
     * @param list<array{instance: Seeder}> $files
     * @return list<string>
     */
    private function collectRequiredTables(array $files): array
    {
        $tables = [];
        foreach ($files as $file) {
            foreach ($file['instance']->requiredTables() as $table) {
                $tables[$table] = true;
            }
        }

        return array_keys($tables);
    }

    /**
     * @param list<string> $tables
     * @return list<string>
     */
    private function missingTables(array $tables): array
    {
        $missing = [];
        foreach ($tables as $table) {
            $stmt = $this->pdo->prepare(
                'SELECT 1
                 FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = :table
                 LIMIT 1'
            );
            $stmt->execute(['table' => $table]);
            if ($stmt->fetchColumn() === false) {
                $missing[] = $table;
            }
        }

        return $missing;
    }

    /**
     * @return array<string, int>
     */
    private function rowCounts(): array
    {
        $counts = [];
        foreach (['users', 'dados_inicio', 'dados_contato', 'sobre'] as $table) {
            $counts[$table] = (int) $this->pdo->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn();
        }
        $counts['admins'] = (int) $this->pdo->query(
            "SELECT COUNT(*) FROM users WHERE type = 'admin'"
        )->fetchColumn();

        return $counts;
    }

    /**
     * @return list<array{filename: string, path: string, instance: Seeder}>
     */
    private function listSeederFiles(bool $includeDemo): array
    {
        $entries = scandir($this->seedersPath);
        if ($entries === false) {
            throw new RuntimeException('Não foi possível ler o diretório de seeders.');
        }

        $files = [];
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || $entry === '.gitkeep') {
                continue;
            }
            if (!preg_match(self::FILENAME_PATTERN, $entry)) {
                throw new RuntimeException('Nome de seeder inválido: ' . $entry);
            }

            $path = $this->seedersPath . DIRECTORY_SEPARATOR . $entry;
            $real = realpath($path);
            if (
                $real === false
                || !is_file($real)
                || !str_starts_with($real, $this->seedersPath . DIRECTORY_SEPARATOR)
            ) {
                throw new RuntimeException('Caminho de seeder recusado: ' . $entry);
            }

            $loaded = require $real;
            if (!$loaded instanceof Seeder) {
                throw new RuntimeException('Arquivo de seeder inválido: ' . $entry);
            }
            if ($loaded->isDemo() && !$includeDemo) {
                continue;
            }

            $files[] = [
                'filename' => $entry,
                'path' => $real,
                'instance' => $loaded,
            ];
        }

        usort(
            $files,
            static fn (array $a, array $b): int => strcmp($a['filename'], $b['filename'])
        );

        return $files;
    }

    private function assertDemoAllowed(): void
    {
        $appEnv = strtolower((string) (self::envOptional('APP_ENV') ?? ''));
        if (in_array($appEnv, ['production', 'prod'], true)) {
            throw new RuntimeException(
                'Dados demonstrativos não são permitidos quando APP_ENV é production/prod.'
            );
        }
        if (self::envOptional('SEED_ALLOW_DEMO') !== '1') {
            throw new RuntimeException(
                'Dados demonstrativos exigem SEED_ALLOW_DEMO=1 e o flag --demo.'
            );
        }
    }

    /**
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    private function withLock(callable $callback): mixed
    {
        if (!$this->acquireLock()) {
            throw new RuntimeException(
                'Outro processo de seed está em execução neste banco ('
                . $this->currentDatabase() . ').'
            );
        }

        try {
            return $callback();
        } finally {
            $this->releaseLock();
        }
    }

    private function acquireLock(): bool
    {
        $stmt = $this->pdo->prepare('SELECT GET_LOCK(:name, :timeout)');
        $stmt->execute([
            'name' => $this->lockName(),
            'timeout' => self::LOCK_TIMEOUT_SECONDS,
        ]);

        return (int) $stmt->fetchColumn() === 1;
    }

    private function releaseLock(): void
    {
        $stmt = $this->pdo->prepare('SELECT RELEASE_LOCK(:name)');
        $stmt->execute(['name' => $this->lockName()]);
        $stmt->fetchColumn();
    }
}
