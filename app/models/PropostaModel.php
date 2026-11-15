<?php

require_once BASE_PATH . '/config/database.php';

class PropostaModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getPDO();
    }

    public function allowedStages(): array
    {
        return [
            'aguardando_confirmacao' => 'Aguardando confirmação',
            'em_andamento' => 'Em andamento',
            'pausado' => 'Pausado',
            'fechado' => 'Fechado',
            'recusado' => 'Recusado',
        ];
    }

    public function listAll(): array
    {
        $stmt = $this->db->query(
            "SELECT id, nome_cliente, estagio, valor_proposta, quantidade_horas, resumo,
                    prazo_inicio, prazo_final, criado_em, titulo_projeto
             FROM propostas_clientes
             WHERE estagio <> 'excluido'
             ORDER BY criado_em DESC, id DESC"
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public function create(array $data): bool   
    {
        $sql = 'INSERT INTO propostas_clientes
                    (nome_cliente, estagio, valor_proposta, quantidade_horas, resumo, prazo_inicio, prazo_final, criado_em, titulo_projeto)
                VALUES
                    (:nome_cliente, :estagio, :valor_proposta, :quantidade_horas, :resumo, :prazo_inicio, :prazo_final, NOW(), :titulo_projeto)';

        return $this->db->prepare($sql)->execute($this->params($data));
    }

    public function updateResumo(int $id, string $resumo): bool
    {
        if ($id <= 0 || $resumo === '') {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE propostas_clientes SET resumo = :resumo WHERE id = :id AND estagio <> 'excluido'"
        );

        return $stmt->execute([
            'resumo' => $resumo,
            'id' => $id,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE propostas_clientes SET
                    nome_cliente = :nome_cliente,
                    estagio = :estagio,
                    valor_proposta = :valor_proposta,
                    quantidade_horas = :quantidade_horas,
                    resumo = :resumo,
                    prazo_inicio = :prazo_inicio,
                    prazo_final = :prazo_final,
                    titulo_projeto = :titulo_projeto
                WHERE id = :id';

        $params = $this->params($data);
        $params['id'] = $id;

        return $this->db->prepare($sql)->execute($params);
    }

    public function softDelete(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE propostas_clientes SET estagio = 'excluido' WHERE id = :id AND estagio <> 'excluido'"
        );

        return $stmt->execute(['id' => $id]);
    }

    private function params(array $data): array
    {
        return [
            'nome_cliente'     => $data['nome_cliente'],
            'estagio'          => $data['estagio'],
            'valor_proposta'   => $data['valor_proposta'],
            'quantidade_horas' => $data['quantidade_horas'],
            'resumo'           => $data['resumo'],
            'prazo_inicio'     => $data['prazo_inicio'],
            'prazo_final'      => $data['prazo_final'],
            'titulo_projeto'   => $data['titulo_projeto'],
        ];
    }
}
