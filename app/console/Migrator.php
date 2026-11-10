<?php

declare(strict_types=1);

/**
 * Runner de migrations (CLI).
 * status: somente leitura.
 * migrate / rollback: lock, tabela de controle e execução de arquivos PHP.
 */
class Migrator
{
    public const TABLE = 'migrations';

    public const FILENAME_PATTERN = '/^[0-9]{14}_[a-z0-9_]+\\.php$/';

    public const LOCK_TIMEOUT_SECONDS = 0;

    public const CONTROL_COLUMNS = [
        'id',
        'migration',
        'filename',
        'batch',
        'checksum',
        'executed_at',
    ];

    public const BASELINE_FILENAME_PATTERN = '/^[0-9]{14}_baseline_schema\\.php$/';

    public const BASELINE_BATCH = 0;

    private const CREATE_CONTROL_TABLE_SQL = <<<'SQL'
CREATE TABLE `migrations` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `migration` VARCHAR(191) NOT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `batch` INT NOT NULL,
    `checksum` CHAR(64) NOT NULL,
    `executed_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `migrations_migration_unique` (`migration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
SQL;

    private PDO $pdo;

    private string $migrationsPath;

    public function __construct(PDO $pdo, string $migrationsPath)
    {
        $this->pdo = $pdo;
        $resolved = realpath($migrationsPath);
        if ($resolved === false || !is_dir($resolved)) {
            throw new RuntimeException('Diretório de migrations inválido.');
        }
        $this->migrationsPath = $resolved;
    }

    /**
     * Relatório somente leitura. Não cria a tabela `migrations` e não obtém lock.
     *
     * @return array{
     *     initialized: bool,
     *     database: string,
     *     pending: list<array{migration: string, filename: string, checksum: string}>,
     *     applied: list<array<string, mixed>>,
     *     diverged: list<array<string, mixed>>,
     *     missing_files: list<array<string, mixed>>,
     *     message: string
     * }
     */
    public function status(): array
    {
        $database = $this->currentDatabase();
        $files = $this->listMigrationFiles();

        if (!$this->controlTableExists()) {
            return [
                'initialized' => false,
                'database' => $database,
                'pending' => $files,
                'applied' => [],
                'diverged' => [],
                'missing_files' => [],
                'message' => 'Mecanismo de migrations ainda não foi inicializado.',
            ];
        }

        $comparison = $this->compareFilesToApplied($files);

        return [
            'initialized' => true,
            'database' => $database,
            'pending' => $comparison['pending'],
            'applied' => $comparison['applied'],
            'diverged' => $comparison['diverged'],
            'missing_files' => $comparison['missing_files'],
            'message' => 'Tabela de controle encontrada. Nenhuma alteração foi feita neste comando.',
        ];
    }

    /**
     * Aplica migrations pendentes. Cria a tabela de controle se ainda não existir.
     * DDL não é revertido por transação: cada arquivo só é registrado após up() ok.
     *
     * @return array{database: string, batch: int|null, applied: list<string>}
     */
    public function migrate(): array
    {
        return $this->withLock(function (): array {
            $this->ensureControlTable();
            $files = $this->listMigrationFiles();
            $comparison = $this->compareFilesToApplied($files);
            $this->assertSafeToRun($comparison);

            if ($comparison['pending'] === []) {
                return [
                    'database' => $this->currentDatabase(),
                    'batch' => null,
                    'applied' => [],
                ];
            }

            $pending = [];
            foreach ($comparison['pending'] as $file) {
                $pending[] = $file + ['instance' => $this->loadMigration($file['path'])];
            }

            $batch = $this->nextBatch();
            $applied = [];

            foreach ($pending as $file) {
                try {
                    $file['instance']->up($this->pdo);
                } catch (Throwable $e) {
                    throw new RuntimeException(
                        'Falha ao aplicar ' . $file['filename'] . '. '
                        . 'A migration não foi registrada. DDL do MySQL pode ter sido aplicado em parte; '
                        . 'corrija o banco manualmente a partir de backup se necessário. '
                        . $e->getMessage(),
                        0,
                        $e
                    );
                }

                $this->recordApplied($file, $batch);
                $applied[] = $file['filename'];
            }

            return [
                'database' => $this->currentDatabase(),
                'batch' => $batch,
                'applied' => $applied,
            ];
        });
    }

    /**
     * Reverte o último batch, somente se todas as migrations do lote implementam down().
     *
     * @return array{database: string, batch: int, reverted: list<string>}
     */
    public function rollback(): array
    {
        return $this->withLock(function (): array {
            if (!$this->controlTableExists()) {
                throw new RuntimeException('Mecanismo de migrations ainda não foi inicializado.');
            }
            $this->assertControlTableSchema();

            $files = $this->listMigrationFiles();
            $comparison = $this->compareFilesToApplied($files);
            $this->assertSafeToRun($comparison);

            $batch = $this->lastBatch();
            if ($batch === null) {
                throw new RuntimeException('Não há lote aplicado para reverter.');
            }

            $rows = $this->fetchAppliedByBatch($batch);
            if ($rows === []) {
                throw new RuntimeException('Não há lote aplicado para reverter.');
            }

            $filesByName = [];
            foreach ($files as $file) {
                $filesByName[$file['migration']] = $file;
            }

            $prepared = [];
            foreach ($rows as $row) {
                $name = (string) $row['migration'];
                if (!isset($filesByName[$name])) {
                    throw new RuntimeException(
                        'Não é possível reverter o lote ' . $batch
                        . ': arquivo ausente para ' . $name . '.'
                    );
                }
                $file = $filesByName[$name];
                $instance = $this->loadMigration($file['path']);
                if (!$instance->supportsDown()) {
                    throw new RuntimeException(
                        'Rollback recusado: ' . $file['filename']
                        . ' não implementa reversão explícita (supportsDown).'
                    );
                }
                $prepared[] = $file + ['instance' => $instance];
            }

            $reverted = [];
            foreach ($prepared as $file) {
                try {
                    $file['instance']->down($this->pdo);
                } catch (Throwable $e) {
                    throw new RuntimeException(
                        'Falha ao reverter ' . $file['filename'] . '. '
                        . 'O registro pode permanecer na tabela de controle. '
                        . $e->getMessage(),
                        0,
                        $e
                    );
                }
                $this->forgetApplied($file['migration']);
                $reverted[] = $file['filename'];
            }

            return [
                'database' => $this->currentDatabase(),
                'batch' => $batch,
                'reverted' => $reverted,
            ];
        });
    }

    /**
     * Valida o schema das tabelas de negócio e registra a baseline sem executar up().
     *
     * @return array{database: string, filename: string, checksum: string, diffs: list<string>}
     */
    public function baseline(): array
    {
        return $this->withLock(function (): array {
            $this->ensureControlTable();
            $files = $this->listMigrationFiles();
            $comparison = $this->compareFilesToApplied($files);
            $this->assertSafeToRun($comparison);

            $baseline = $this->baselineFile($files);
            foreach ($comparison['applied'] as $row) {
                if ((string) $row['migration'] === $baseline['migration']) {
                    throw new RuntimeException(
                        'A baseline já está registrada. Nenhuma alteração foi feita.'
                    );
                }
            }

            $this->loadMigration($baseline['path']);
            $diffs = (new SchemaInspector($this->pdo))->diffAgainstExpected();
            if ($diffs !== []) {
                throw new RuntimeException(
                    "Schema diverge da baseline. Nada foi registrado.\n - "
                    . implode("\n - ", $diffs)
                );
            }

            $this->recordApplied($baseline, self::BASELINE_BATCH);

            return [
                'database' => $this->currentDatabase(),
                'filename' => $baseline['filename'],
                'checksum' => $baseline['checksum'],
                'diffs' => [],
            ];
        });
    }

    /**
     * Nome do lock MySQL específico desta instalação (host + database).
     */
    public function lockName(): string
    {
        $stmt = $this->pdo->query('SELECT @@hostname AS host, DATABASE() AS db');
        $row = $stmt !== false ? $stmt->fetch() : false;
        $host = is_array($row) ? (string) ($row['host'] ?? '') : '';
        $db = is_array($row) ? (string) ($row['db'] ?? '') : '';

        return 'vmig_' . md5($host . "\0" . $db);
    }

    /**
     * @return list<array{migration: string, filename: string, checksum: string, path: string}>
     */
    public function listMigrationFiles(): array
    {
        $entries = scandir($this->migrationsPath);
        if ($entries === false) {
            throw new RuntimeException('Não foi possível ler o diretório de migrations.');
        }

        $files = [];
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || $entry === '.gitkeep') {
                continue;
            }
            if (!preg_match(self::FILENAME_PATTERN, $entry)) {
                throw new RuntimeException('Nome de migration inválido: ' . $entry);
            }

            $path = $this->migrationsPath . DIRECTORY_SEPARATOR . $entry;
            $real = realpath($path);
            if (
                $real === false
                || !is_file($real)
                || !str_starts_with($real, $this->migrationsPath . DIRECTORY_SEPARATOR)
            ) {
                throw new RuntimeException('Caminho de migration recusado: ' . $entry);
            }

            $files[] = [
                'migration' => substr($entry, 0, -4),
                'filename' => $entry,
                'checksum' => $this->checksumForFile($real),
                'path' => $real,
            ];
        }

        usort(
            $files,
            static fn (array $a, array $b): int => strcmp($a['migration'], $b['migration'])
        );

        return $files;
    }

    /**
     * @param list<array{migration: string, filename: string, checksum: string, path: string}> $files
     * @return array{migration: string, filename: string, checksum: string, path: string}
     */
    private function baselineFile(array $files): array
    {
        $matches = [];
        foreach ($files as $file) {
            if (preg_match(self::BASELINE_FILENAME_PATTERN, $file['filename'])) {
                $matches[] = $file;
            }
        }
        if ($matches === []) {
            throw new RuntimeException('Arquivo de baseline não encontrado em database/migrations/.');
        }
        if (count($matches) > 1) {
            throw new RuntimeException('Há mais de um arquivo de baseline. Nenhuma alteração foi feita.');
        }

        return $matches[0];
    }

    public function checksumForFile(string $path): string
    {
        $hash = hash_file('sha256', $path);
        if ($hash === false) {
            throw new RuntimeException('Não foi possível calcular o checksum da migration.');
        }

        return $hash;
    }

    public function controlTableExists(): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table
             LIMIT 1'
        );
        $stmt->execute(['table' => self::TABLE]);

        return $stmt->fetchColumn() !== false;
    }

    public function currentDatabase(): string
    {
        $value = $this->pdo->query('SELECT DATABASE()')->fetchColumn();

        return is_string($value) ? $value : '';
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
                'Outro processo de migration está em execução neste banco ('
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
        $result = $stmt->fetchColumn();

        return (int) $result === 1;
    }

    private function releaseLock(): void
    {
        $stmt = $this->pdo->prepare('SELECT RELEASE_LOCK(:name)');
        $stmt->execute(['name' => $this->lockName()]);
        $stmt->fetchColumn();
    }

    private function ensureControlTable(): void
    {
        if (!$this->controlTableExists()) {
            $this->pdo->exec(self::CREATE_CONTROL_TABLE_SQL);
        }

        $this->assertControlTableSchema();
    }

    private function assertControlTableSchema(): void
    {
        $stmt = $this->pdo->query('SHOW COLUMNS FROM `' . self::TABLE . '`');
        if ($stmt === false) {
            throw new RuntimeException('Não foi possível inspecionar a tabela de controle.');
        }

        $found = [];
        foreach ($stmt->fetchAll() as $column) {
            $found[] = (string) ($column['Field'] ?? '');
        }

        $missing = array_diff(self::CONTROL_COLUMNS, $found);
        if ($missing !== []) {
            throw new RuntimeException(
                'Tabela migrations existe, mas está incompleta. Colunas ausentes: '
                . implode(', ', $missing)
                . '. Corrija manualmente; o runner não altera essa tabela automaticamente.'
            );
        }
    }

    /**
     * @param list<array{migration: string, filename: string, checksum: string, path: string}> $files
     * @return array{
     *     pending: list<array{migration: string, filename: string, checksum: string, path: string}>,
     *     applied: list<array<string, mixed>>,
     *     diverged: list<array<string, mixed>>,
     *     missing_files: list<array<string, mixed>>
     * }
     */
    private function compareFilesToApplied(array $files): array
    {
        $appliedRows = $this->fetchApplied();
        $appliedByName = [];
        foreach ($appliedRows as $row) {
            $appliedByName[(string) $row['migration']] = $row;
        }

        $pending = [];
        $diverged = [];
        $seen = [];

        foreach ($files as $file) {
            $name = $file['migration'];
            $seen[$name] = true;
            if (!isset($appliedByName[$name])) {
                $pending[] = $file;
                continue;
            }
            $stored = (string) ($appliedByName[$name]['checksum'] ?? '');
            if ($stored !== '' && !hash_equals($stored, $file['checksum'])) {
                $diverged[] = [
                    'migration' => $name,
                    'filename' => $file['filename'],
                    'stored_checksum' => $stored,
                    'file_checksum' => $file['checksum'],
                ];
            }
        }

        $missingFiles = [];
        foreach ($appliedByName as $name => $row) {
            if (!isset($seen[$name])) {
                $missingFiles[] = $row;
            }
        }

        return [
            'pending' => $pending,
            'applied' => $appliedRows,
            'diverged' => $diverged,
            'missing_files' => $missingFiles,
        ];
    }

    /**
     * @param array{
     *     diverged: list<array<string, mixed>>,
     *     missing_files: list<array<string, mixed>>
     * } $comparison
     */
    private function assertSafeToRun(array $comparison): void
    {
        if ($comparison['diverged'] !== []) {
            $names = array_map(
                static fn (array $row): string => (string) $row['filename'],
                $comparison['diverged']
            );
            throw new RuntimeException(
                'Checksum divergente após a execução. Nenhuma migration foi aplicada. Arquivos: '
                . implode(', ', $names)
            );
        }

        if ($comparison['missing_files'] !== []) {
            $names = array_map(
                static fn (array $row): string => (string) ($row['migration'] ?? ''),
                $comparison['missing_files']
            );
            throw new RuntimeException(
                'Há migrations registradas sem arquivo no disco: ' . implode(', ', $names)
            );
        }
    }

    private function loadMigration(string $path): Migration
    {
        $loaded = require $path;
        if (!$loaded instanceof Migration) {
            throw new RuntimeException(
                'Arquivo de migration inválido (deve retornar instância de Migration): '
                . basename($path)
            );
        }

        return $loaded;
    }

    private function nextBatch(): int
    {
        $value = $this->pdo->query('SELECT MAX(batch) FROM `' . self::TABLE . '`')->fetchColumn();

        return $value === null || $value === false ? 1 : ((int) $value) + 1;
    }

    private function lastBatch(): ?int
    {
        $value = $this->pdo->query('SELECT MAX(batch) FROM `' . self::TABLE . '`')->fetchColumn();
        if ($value === null || $value === false) {
            return null;
        }

        return (int) $value;
    }

    /**
     * @param array{migration: string, filename: string, checksum: string} $file
     */
    private function recordApplied(array $file, int $batch): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO `' . self::TABLE . '`
                (migration, filename, batch, checksum, executed_at)
             VALUES
                (:migration, :filename, :batch, :checksum, NOW())'
        );
        $stmt->execute([
            'migration' => $file['migration'],
            'filename' => $file['filename'],
            'batch' => $batch,
            'checksum' => $file['checksum'],
        ]);
    }

    private function forgetApplied(string $migration): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM `' . self::TABLE . '` WHERE migration = :migration LIMIT 1'
        );
        $stmt->execute(['migration' => $migration]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchApplied(): array
    {
        $stmt = $this->pdo->query(
            'SELECT migration, filename, batch, checksum, executed_at
             FROM `' . self::TABLE . '`
             ORDER BY id ASC'
        );
        if ($stmt === false) {
            throw new RuntimeException('Não foi possível ler o registro de migrations.');
        }
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchAppliedByBatch(int $batch): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT migration, filename, batch, checksum, executed_at
             FROM `' . self::TABLE . '`
             WHERE batch = :batch
             ORDER BY id DESC'
        );
        $stmt->execute(['batch' => $batch]);
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }
}
