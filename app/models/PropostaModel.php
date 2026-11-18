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

    public function getById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            "SELECT id, nome_cliente, estagio, valor_proposta, quantidade_horas, resumo,
                    prazo_inicio, prazo_final, criado_em, titulo_projeto
             FROM propostas_clientes
             WHERE id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    /**
     * @return list<int>
     */
    public function allowedLinkDays(): array
    {
        return [1, 7, 30];
    }

    public function hashShareToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * @return array{token: string, expires_at: string}|null
     */
    public function createShareLink(int $propostaId, int $days): ?array
    {
        $proposta = $this->getById($propostaId);
        if ($proposta === null || ($proposta['estagio'] ?? '') === 'excluido') {
            return null;
        }
        if (!in_array($days, $this->allowedLinkDays(), true)) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $hash = $this->hashShareToken($token);
        $expires = (new DateTimeImmutable('now'))->modify('+' . $days . ' days')->format('Y-m-d H:i:s');

        $stmt = $this->db->prepare(
            'INSERT INTO propostas_links (proposta_id, token_hash, expires_at, revoked_at, criado_em)
             VALUES (:proposta_id, :token_hash, :expires_at, NULL, NOW())'
        );
        $ok = $stmt->execute([
            'proposta_id' => $propostaId,
            'token_hash' => $hash,
            'expires_at' => $expires,
        ]);

        if (!$ok) {
            return null;
        }

        return [
            'token' => $token,
            'expires_at' => $expires,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listShareLinks(int $propostaId): array
    {
        if ($propostaId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT id, proposta_id, expires_at, revoked_at, criado_em
             FROM propostas_links
             WHERE proposta_id = :proposta_id
             ORDER BY criado_em DESC, id DESC'
        );
        $stmt->execute(['proposta_id' => $propostaId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public function revokeShareLink(int $linkId, int $propostaId): bool
    {
        if ($linkId <= 0 || $propostaId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE propostas_links
             SET revoked_at = NOW()
             WHERE id = :id AND proposta_id = :proposta_id AND revoked_at IS NULL'
        );

        return $stmt->execute([
            'id' => $linkId,
            'proposta_id' => $propostaId,
        ]) && $stmt->rowCount() === 1;
    }

    /**
     * @return array{status: string, link?: array<string, mixed>, proposta?: array<string, mixed>}
     */
    public function resolveShareToken(string $token): array
    {
        $token = trim($token);
        if ($token === '' || strlen($token) > 128) {
            return ['status' => 'invalid'];
        }

        $stmt = $this->db->prepare(
            'SELECT id, proposta_id, expires_at, revoked_at
             FROM propostas_links
             WHERE token_hash = :token_hash
             LIMIT 1'
        );
        $stmt->execute(['token_hash' => $this->hashShareToken($token)]);
        $link = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!is_array($link)) {
            return ['status' => 'invalid'];
        }

        if (!empty($link['revoked_at'])) {
            return ['status' => 'revoked'];
        }

        $expires = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) $link['expires_at']);
        if (!$expires instanceof DateTimeImmutable || $expires < new DateTimeImmutable('now')) {
            return ['status' => 'expired'];
        }

        $proposta = $this->getById((int) $link['proposta_id']);
        if ($proposta === null || ($proposta['estagio'] ?? '') === 'excluido') {
            return ['status' => 'invalid'];
        }

        return [
            'status' => 'ok',
            'link' => $link,
            'proposta' => $proposta,
        ];
    }

    public function addAdjustmentRequest(int $propostaId, int $linkId, string $mensagem): bool
    {
        $mensagem = trim($mensagem);
        if ($propostaId <= 0 || $linkId <= 0 || $mensagem === '' || mb_strlen($mensagem) > 2000) {
            return false;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO propostas_ajustes (proposta_id, link_id, mensagem, criado_em)
             VALUES (:proposta_id, :link_id, :mensagem, NOW())'
        );

        return $stmt->execute([
            'proposta_id' => $propostaId,
            'link_id' => $linkId,
            'mensagem' => $mensagem,
        ]);
    }

    /**
     * @param list<int> $propostaIds
     * @return array<int, list<array<string, mixed>>>
     */
    public function listAdjustmentsByProposals(array $propostaIds): array
    {
        if ($propostaIds === []) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach (array_values($propostaIds) as $i => $id) {
            $key = 'id' . $i;
            $placeholders[] = ':' . $key;
            $params[$key] = (int) $id;
        }

        $stmt = $this->db->prepare(
            'SELECT id, proposta_id, mensagem, criado_em
             FROM propostas_ajustes
             WHERE proposta_id IN (' . implode(', ', $placeholders) . ')
             ORDER BY criado_em DESC, id DESC'
        );
        $stmt->execute($params);
        $grouped = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $pid = (int) $row['proposta_id'];
            $grouped[$pid][] = $row;
        }

        return $grouped;
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
