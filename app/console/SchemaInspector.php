<?php

declare(strict_types=1);

/**
 * Esquema das tabelas de negócio, copiado do banco local `vibecoding`
 * (information_schema + SHOW CREATE TABLE). Sem UNIQUE extra, FKs ou seeds.
 */
class SchemaInspector
{
    public const TABLES = [
        'users',
        'dados_contato',
        'dados_inicio',
        'sobre',
        'mensagens_contato',
        'propostas_clientes',
    ];

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return array<string, array{
     *     engine: string,
     *     collation: string,
     *     columns: list<array{name: string, type: string, nullable: bool, has_default: bool, default: ?string, extra: string}>,
     *     indexes: list<array{name: string, unique: bool, type: string, columns: list<string>}>
     * }>
     */
    public static function expected(): array
    {
        return [
            'users' => self::table(
                [
                    self::col('id', 'int(11)', false, false, null, 'auto_increment'),
                    self::col('name', 'varchar(100)', false, false, null, ''),
                    self::col('email', 'varchar(100)', false, false, null, ''),
                    self::col('password', 'varchar(100)', false, false, null, ''),
                    self::col('type', 'varchar(20)', false, false, null, ''),
                ],
                [self::primary('id')]
            ),
            'dados_contato' => self::table(
                [
                    self::col('id', 'int(11)', false, false, null, 'auto_increment'),
                    self::col('titulo', 'varchar(100)', true, true, null, ''),
                    self::col('descricao', 'text', true, true, null, ''),
                    self::col('telefone', 'varchar(14)', true, true, null, ''),
                    self::col('whatsapp', 'varchar(14)', true, true, null, ''),
                    self::col('email', 'varchar(100)', true, true, null, ''),
                ],
                [self::primary('id')]
            ),
            'dados_inicio' => self::table(
                [
                    self::col('id', 'int(11)', false, false, null, 'auto_increment'),
                    self::col('titulo', 'varchar(255)', false, false, null, ''),
                    self::col('subtitulo', 'text', false, false, null, ''),
                    self::col('botao_inicio', 'varchar(100)', true, true, null, ''),
                    self::col('imagem', 'varchar(255)', true, true, null, ''),
                ],
                [self::primary('id')]
            ),
            'sobre' => self::table(
                [
                    self::col('id', 'int(11)', false, false, null, 'auto_increment'),
                    self::col('titulo', 'varchar(255)', false, false, null, ''),
                    self::col('descricao', 'text', false, false, null, ''),
                    self::col('subtitulo', 'varchar(255)', true, true, null, ''),
                ],
                [self::primary('id')]
            ),
            'mensagens_contato' => self::table(
                [
                    self::col('id', 'int(11)', false, false, null, 'auto_increment'),
                    self::col('nome', 'varchar(255)', false, false, null, ''),
                    self::col('email', 'varchar(255)', false, false, null, ''),
                    self::col('mensagem', 'text', false, false, null, ''),
                    self::col('criado_em', 'datetime', false, false, null, ''),
                ],
                [self::primary('id')]
            ),
            'propostas_clientes' => self::table(
                [
                    self::col('id', 'int(11)', false, false, null, 'auto_increment'),
                    self::col('nome_cliente', 'varchar(255)', false, false, null, ''),
                    self::col('estagio', 'varchar(40)', false, false, null, ''),
                    self::col('valor_proposta', 'decimal(12,2)', false, false, null, ''),
                    self::col('quantidade_horas', 'decimal(8,2)', false, false, null, ''),
                    self::col('resumo', 'text', false, false, null, ''),
                    self::col('criado_em', 'datetime', false, false, null, ''),
                    self::col('prazo_inicio', 'date', true, true, null, ''),
                    self::col('prazo_final', 'date', true, true, null, ''),
                    self::col('titulo_projeto', 'varchar(250)', true, true, null, ''),
                ],
                [self::primary('id')]
            ),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function createStatements(): array
    {
        $statements = [];
        foreach (self::expected() as $table => $definition) {
            $lines = [];
            foreach ($definition['columns'] as $column) {
                $line = '`' . $column['name'] . '` ' . $column['type'];
                $line .= $column['nullable'] ? '' : ' NOT NULL';
                if ($column['has_default'] && $column['default'] === null) {
                    $line .= ' DEFAULT NULL';
                }
                if ($column['extra'] === 'auto_increment') {
                    $line .= ' AUTO_INCREMENT';
                }
                $lines[] = $line;
            }
            foreach ($definition['indexes'] as $index) {
                if ($index['name'] === 'PRIMARY') {
                    $quoted = array_map(
                        static fn (string $name): string => '`' . $name . '`',
                        $index['columns']
                    );
                    $lines[] = 'PRIMARY KEY (' . implode(', ', $quoted) . ')';
                }
            }
            $statements[$table] = 'CREATE TABLE `' . $table . '` ('
                . implode(', ', $lines)
                . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci';
        }

        return $statements;
    }

    /**
     * @return list<string>
     */
    public function diffAgainstExpected(): array
    {
        $expected = self::expected();
        $actual = $this->snapshot(array_keys($expected));
        $diffs = [];

        foreach ($expected as $table => $definition) {
            if (!isset($actual[$table])) {
                $diffs[] = 'Tabela ausente: ' . $table;
                continue;
            }
            $seen = $actual[$table];
            if (strcasecmp($seen['engine'], $definition['engine']) !== 0) {
                $diffs[] = $table . ': engine ' . $seen['engine'] . ' (esperado ' . $definition['engine'] . ')';
            }
            if (strcasecmp($seen['collation'], $definition['collation']) !== 0) {
                $diffs[] = $table . ': collation ' . $seen['collation'] . ' (esperado ' . $definition['collation'] . ')';
            }
            $diffs = array_merge($diffs, self::diffColumns($table, $definition['columns'], $seen['columns']));
            $diffs = array_merge($diffs, self::diffIndexes($table, $definition['indexes'], $seen['indexes']));
        }

        return $diffs;
    }

    /**
     * @param list<string> $tables
     * @return array<string, array{engine: string, collation: string, columns: list<array<string, mixed>>, indexes: list<array<string, mixed>>}>
     */
    public function snapshot(array $tables): array
    {
        $result = [];
        foreach ($tables as $table) {
            $meta = $this->tableMeta($table);
            if ($meta === null) {
                continue;
            }
            $result[$table] = [
                'engine' => $meta['engine'],
                'collation' => $meta['collation'],
                'columns' => $this->columnSnapshot($table),
                'indexes' => $this->indexSnapshot($table),
            ];
        }

        return $result;
    }

    /**
     * @return array{engine: string, collation: string}|null
     */
    private function tableMeta(string $table): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ENGINE, TABLE_COLLATION
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table
             LIMIT 1'
        );
        $stmt->execute(['table' => $table]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return null;
        }

        return [
            'engine' => (string) $row['ENGINE'],
            'collation' => (string) $row['TABLE_COLLATION'],
        ];
    }

    /**
     * @return list<array{name: string, type: string, nullable: bool, has_default: bool, default: ?string, extra: string}>
     */
    private function columnSnapshot(string $table): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table
             ORDER BY ORDINAL_POSITION ASC'
        );
        $stmt->execute(['table' => $table]);
        $columns = [];
        foreach ($stmt->fetchAll() as $row) {
            $nullable = strtoupper((string) $row['IS_NULLABLE']) === 'YES';
            $rawDefault = $row['COLUMN_DEFAULT'];
            $default = $rawDefault === null ? null : (string) $rawDefault;
            if ($default !== null && strcasecmp($default, 'NULL') === 0) {
                $default = null;
            }
            $columns[] = [
                'name' => (string) $row['COLUMN_NAME'],
                'type' => strtolower((string) $row['COLUMN_TYPE']),
                'nullable' => $nullable,
                'has_default' => $nullable || $default !== null,
                'default' => $default,
                'extra' => strtolower(trim((string) $row['EXTRA'])),
            ];
        }

        return $columns;
    }

    /**
     * @return list<array{name: string, unique: bool, type: string, columns: list<string>}>
     */
    private function indexSnapshot(string $table): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME, INDEX_TYPE
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table
             ORDER BY INDEX_NAME ASC, SEQ_IN_INDEX ASC'
        );
        $stmt->execute(['table' => $table]);
        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $name = (string) $row['INDEX_NAME'];
            if (!isset($grouped[$name])) {
                $grouped[$name] = [
                    'name' => $name,
                    'unique' => ((int) $row['NON_UNIQUE']) === 0,
                    'type' => strtoupper((string) $row['INDEX_TYPE']),
                    'columns' => [],
                ];
            }
            $grouped[$name]['columns'][] = (string) $row['COLUMN_NAME'];
        }

        return array_values($grouped);
    }

    /**
     * @param list<array{name: string, type: string, nullable: bool, has_default: bool, default: ?string, extra: string}> $expected
     * @param list<array{name: string, type: string, nullable: bool, has_default: bool, default: ?string, extra: string}> $actual
     * @return list<string>
     */
    private static function diffColumns(string $table, array $expected, array $actual): array
    {
        $diffs = [];
        $expectedNames = array_column($expected, 'name');
        $actualNames = array_column($actual, 'name');
        foreach (array_diff($expectedNames, $actualNames) as $missing) {
            $diffs[] = $table . ': coluna ausente ' . $missing;
        }
        foreach (array_diff($actualNames, $expectedNames) as $extra) {
            $diffs[] = $table . ': coluna extra ' . $extra;
        }
        if ($expectedNames !== $actualNames) {
            $diffs[] = $table . ': ordem das colunas diverge';
        }

        $actualByName = [];
        foreach ($actual as $column) {
            $actualByName[$column['name']] = $column;
        }

        foreach ($expected as $column) {
            if (!isset($actualByName[$column['name']])) {
                continue;
            }
            $seen = $actualByName[$column['name']];
            $label = $table . '.' . $column['name'];
            if (strtolower($seen['type']) !== strtolower($column['type'])) {
                $diffs[] = $label . ': tipo ' . $seen['type'] . ' (esperado ' . $column['type'] . ')';
            }
            if ($seen['nullable'] !== $column['nullable']) {
                $diffs[] = $label . ': nulabilidade diverge';
            }
            if ($seen['has_default'] !== $column['has_default'] || $seen['default'] !== $column['default']) {
                $diffs[] = $label . ': valor padrão diverge';
            }
            if ($seen['extra'] !== $column['extra']) {
                $diffs[] = $label . ': extra ' . $seen['extra'] . ' (esperado ' . $column['extra'] . ')';
            }
        }

        return $diffs;
    }

    /**
     * @param list<array{name: string, unique: bool, type: string, columns: list<string>}> $expected
     * @param list<array{name: string, unique: bool, type: string, columns: list<string>}> $actual
     * @return list<string>
     */
    private static function diffIndexes(string $table, array $expected, array $actual): array
    {
        $diffs = [];
        $expectedNames = array_column($expected, 'name');
        $actualNames = array_column($actual, 'name');
        foreach (array_diff($expectedNames, $actualNames) as $missing) {
            $diffs[] = $table . ': índice ausente ' . $missing;
        }
        foreach (array_diff($actualNames, $expectedNames) as $extra) {
            $diffs[] = $table . ': índice extra ' . $extra;
        }

        $actualByName = [];
        foreach ($actual as $index) {
            $actualByName[$index['name']] = $index;
        }

        foreach ($expected as $index) {
            if (!isset($actualByName[$index['name']])) {
                continue;
            }
            $seen = $actualByName[$index['name']];
            $label = $table . ' índice ' . $index['name'];
            if ($seen['unique'] !== $index['unique']) {
                $diffs[] = $label . ': unicidade diverge';
            }
            if (strcasecmp($seen['type'], $index['type']) !== 0) {
                $diffs[] = $label . ': tipo ' . $seen['type'] . ' (esperado ' . $index['type'] . ')';
            }
            if ($seen['columns'] !== $index['columns']) {
                $diffs[] = $label . ': colunas divergem';
            }
        }

        return $diffs;
    }

    /**
     * @param list<array{name: string, type: string, nullable: bool, has_default: bool, default: ?string, extra: string}> $columns
     * @param list<array{name: string, unique: bool, type: string, columns: list<string>}> $indexes
     * @return array{engine: string, collation: string, columns: list<array<string, mixed>>, indexes: list<array<string, mixed>>}
     */
    private static function table(array $columns, array $indexes): array
    {
        return [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci',
            'columns' => $columns,
            'indexes' => $indexes,
        ];
    }

    /**
     * @return array{name: string, type: string, nullable: bool, has_default: bool, default: ?string, extra: string}
     */
    private static function col(
        string $name,
        string $type,
        bool $nullable,
        bool $hasDefault,
        ?string $default,
        string $extra
    ): array {
        return [
            'name' => $name,
            'type' => $type,
            'nullable' => $nullable,
            'has_default' => $hasDefault,
            'default' => $default,
            'extra' => $extra,
        ];
    }

    /**
     * @return array{name: string, unique: bool, type: string, columns: list<string>}
     */
    private static function primary(string $column): array
    {
        return [
            'name' => 'PRIMARY',
            'unique' => true,
            'type' => 'BTREE',
            'columns' => [$column],
        ];
    }
}
