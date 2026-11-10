<?php

declare(strict_types=1);

return new class extends Seeder {
    public function name(): string
    {
        return 'conteudo_inicial';
    }

    public function requiredTables(): array
    {
        return ['dados_inicio', 'dados_contato', 'sobre'];
    }

    public function run(PDO $pdo): string
    {
        $created = [];

        if (!$this->rowExists($pdo, 'dados_inicio')) {
            $stmt = $pdo->prepare(
                'INSERT INTO dados_inicio (id, titulo, subtitulo, botao_inicio, imagem)
                 VALUES (1, :titulo, :subtitulo, :botao_inicio, :imagem)'
            );
            $stmt->execute([
                'titulo' => 'Título',
                'subtitulo' => 'Subtítulo institucional.',
                'botao_inicio' => 'Saiba mais',
                'imagem' => '',
            ]);
            $created[] = 'dados_inicio';
        }

        if (!$this->rowExists($pdo, 'dados_contato')) {
            $stmt = $pdo->prepare(
                'INSERT INTO dados_contato (id, titulo, descricao, telefone, whatsapp, email)
                 VALUES (1, :titulo, :descricao, NULL, NULL, NULL)'
            );
            $stmt->execute([
                'titulo' => 'Contato',
                'descricao' => 'Preencha os dados de contato na área restrita.',
            ]);
            $created[] = 'dados_contato';
        }

        if (!$this->rowExists($pdo, 'sobre')) {
            $stmt = $pdo->prepare(
                'INSERT INTO sobre (id, titulo, descricao, subtitulo)
                 VALUES (1, :titulo, :descricao, :subtitulo)'
            );
            $stmt->execute([
                'titulo' => 'Sobre',
                'descricao' => 'Conteúdo institucional. Edite este texto na área restrita.',
                'subtitulo' => '',
            ]);
            $created[] = 'sobre';
        }

        return $created === [] ? 'skipped' : 'created:' . implode(',', $created);
    }

    private function rowExists(PDO $pdo, string $table): bool
    {
        $stmt = $pdo->query('SELECT 1 FROM `' . $table . '` WHERE id = 1 LIMIT 1');

        return $stmt !== false && $stmt->fetchColumn() !== false;
    }
};
