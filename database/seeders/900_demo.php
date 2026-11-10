<?php

declare(strict_types=1);

return new class extends Seeder {
    public function name(): string
    {
        return 'dados_demonstrativos';
    }

    public function isDemo(): bool
    {
        return true;
    }

    public function requiredTables(): array
    {
        return ['mensagens_contato', 'propostas_clientes'];
    }

    public function run(PDO $pdo): string
    {
        $created = [];

        $msgMarker = 'seed-demo-mensagem';
        $checkMsg = $pdo->prepare(
            'SELECT 1 FROM mensagens_contato WHERE mensagem = :mensagem LIMIT 1'
        );
        $checkMsg->execute(['mensagem' => $msgMarker]);
        if ($checkMsg->fetchColumn() === false) {
            $stmt = $pdo->prepare(
                'INSERT INTO mensagens_contato (nome, email, mensagem, criado_em)
                 VALUES (:nome, :email, :mensagem, NOW())'
            );
            $stmt->execute([
                'nome' => 'Exemplo',
                'email' => 'exemplo@localhost.invalid',
                'mensagem' => $msgMarker,
            ]);
            $created[] = 'mensagens_contato';
        }

        $resumo = 'seed-demo-proposta';
        $checkProp = $pdo->prepare(
            'SELECT 1 FROM propostas_clientes WHERE resumo = :resumo LIMIT 1'
        );
        $checkProp->execute(['resumo' => $resumo]);
        if ($checkProp->fetchColumn() === false) {
            $stmt = $pdo->prepare(
                'INSERT INTO propostas_clientes
                    (nome_cliente, estagio, valor_proposta, quantidade_horas, resumo, criado_em, titulo_projeto)
                 VALUES
                    (:nome_cliente, :estagio, :valor_proposta, :quantidade_horas, :resumo, NOW(), :titulo_projeto)'
            );
            $stmt->execute([
                'nome_cliente' => 'Cliente exemplo',
                'estagio' => 'aguardando_confirmacao',
                'valor_proposta' => '0.00',
                'quantidade_horas' => '0.00',
                'resumo' => $resumo,
                'titulo_projeto' => 'Projeto exemplo',
            ]);
            $created[] = 'propostas_clientes';
        }

        return $created === [] ? 'skipped' : 'created:' . implode(',', $created);
    }
};
