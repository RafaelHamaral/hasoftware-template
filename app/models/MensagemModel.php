<?php

require_once BASE_PATH . '/config/database.php';

class MensagemModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getPDO();
    }

    /**
     * @return array<string, string>
     */
    public function statusLabels(): array
    {
        return [
            'novo' => 'Novo',
            'em_conversa' => 'Em conversa',
            'proposta_enviada' => 'Proposta enviada',
            'fechado' => 'Fechado',
            'encerrado_sem_contratacao' => 'Encerrado sem contratação',
        ];
    }

    /**
     * @return list<string>
     */
    public function allowedStatuses(): array
    {
        return array_keys($this->statusLabels());
    }

    /**
     * Solicitações ainda em atendimento (não encerradas).
     *
     * @return list<string>
     */
    public function openStatuses(): array
    {
        return ['novo', 'em_conversa', 'proposta_enviada'];
    }

    public function isAllowedStatus(string $status): bool
    {
        return in_array($status, $this->allowedStatuses(), true);
    }

    public function isOpenStatus(string $status): bool
    {
        return in_array($status, $this->openStatuses(), true);
    }

    public function statusLabel(string $status): string
    {
        $labels = $this->statusLabels();

        return $labels[$status] ?? $status;
    }

    public function save(array $data): bool
    {
        $sql = 'INSERT INTO mensagens_contato (nome, email, mensagem, criado_em, status)
                VALUES (:nome, :email, :mensagem, NOW(), :status)';

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'nome'      => $data['nome'],
            'email'     => $data['email'],
            'mensagem'  => $data['mensagem'],
            'status'    => 'novo',
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listAll(): array
    {
        return $this->listFiltered('', false);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listFiltered(string $status, bool $pendingReturns): array
    {
        $sql = 'SELECT id, nome, email, mensagem, criado_em, status, anotacao_interna, proximo_contato, atualizado_em
                FROM mensagens_contato
                WHERE 1=1';
        $params = [];

        if ($status !== '') {
            if (!$this->isAllowedStatus($status)) {
                return [];
            }
            $sql .= ' AND status = :status';
            $params['status'] = $status;
        }

        if ($pendingReturns) {
            $placeholders = [];
            foreach ($this->openStatuses() as $i => $openStatus) {
                $key = 'open_' . $i;
                $placeholders[] = ':' . $key;
                $params[$key] = $openStatus;
            }
            $sql .= ' AND status IN (' . implode(', ', $placeholders) . ')
                      AND proximo_contato IS NOT NULL
                      AND proximo_contato <= CURDATE()';
        }

        $sql .= $pendingReturns
            ? ' ORDER BY proximo_contato ASC, id ASC'
            : ' ORDER BY criado_em DESC, id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public function getById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT id, nome, email, mensagem, criado_em, status, anotacao_interna, proximo_contato, atualizado_em
             FROM mensagens_contato
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    /**
     * @return array{ok: bool, error?: string}
     */
    public function updateAtendimento(int $id, array $payload, ?int $userId): array
    {
        $current = $this->getById($id);
        if ($current === null) {
            return ['ok' => false, 'error' => 'Solicitação não encontrada.'];
        }

        $status = (string) ($payload['status'] ?? '');
        if (!$this->isAllowedStatus($status)) {
            return ['ok' => false, 'error' => 'Selecione um status válido.'];
        }

        $note = trim((string) ($payload['anotacao_interna'] ?? ''));
        if (mb_strlen($note) > 5000) {
            return ['ok' => false, 'error' => 'A anotação interna é longa demais.'];
        }

        $nextRaw = trim((string) ($payload['proximo_contato'] ?? ''));
        $nextDate = null;
        if ($nextRaw !== '') {
            $parsed = DateTime::createFromFormat('Y-m-d', $nextRaw);
            $errors = DateTime::getLastErrors();
            $hasErrors = is_array($errors) && (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0);
            if (!$parsed instanceof DateTime || $parsed->format('Y-m-d') !== $nextRaw || $hasErrors) {
                return ['ok' => false, 'error' => 'Informe uma data válida para o próximo contato.'];
            }
            $nextDate = $nextRaw;
        }

        $changes = [];
        $currentStatus = (string) ($current['status'] ?? 'novo');
        if ($currentStatus !== $status) {
            $changes[] = ['campo' => 'status', 'de' => $currentStatus, 'para' => $status];
        }

        $currentNote = trim((string) ($current['anotacao_interna'] ?? ''));
        if ($currentNote !== $note) {
            $changes[] = ['campo' => 'anotacao_interna', 'de' => $currentNote, 'para' => $note];
        }

        $currentNext = $this->normalizeStoredDate($current['proximo_contato'] ?? null);
        if ($currentNext !== $nextDate) {
            $changes[] = ['campo' => 'proximo_contato', 'de' => $currentNext, 'para' => $nextDate];
        }

        if ($changes === []) {
            return ['ok' => true];
        }

        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare(
                'UPDATE mensagens_contato
                 SET status = :status,
                     anotacao_interna = :anotacao_interna,
                     proximo_contato = :proximo_contato,
                     atualizado_em = NOW()
                 WHERE id = :id
                 LIMIT 1'
            );
            $ok = $stmt->execute([
                'status' => $status,
                'anotacao_interna' => $note === '' ? null : $note,
                'proximo_contato' => $nextDate,
                'id' => $id,
            ]);
            if (!$ok || $stmt->rowCount() !== 1) {
                $this->db->rollBack();
                return ['ok' => false, 'error' => 'Não foi possível salvar o atendimento.'];
            }

            $hist = $this->db->prepare(
                'INSERT INTO mensagens_contato_historico
                    (mensagem_id, usuario_id, campo, valor_anterior, valor_novo, criado_em)
                 VALUES
                    (:mensagem_id, :usuario_id, :campo, :valor_anterior, :valor_novo, NOW())'
            );
            foreach ($changes as $change) {
                $hist->execute([
                    'mensagem_id' => $id,
                    'usuario_id' => $userId,
                    'campo' => $change['campo'],
                    'valor_anterior' => $change['de'],
                    'valor_novo' => $change['para'],
                ]);
            }
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Erro ao atualizar atendimento da solicitação: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'Não foi possível salvar o atendimento.'];
        }

        return ['ok' => true];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listHistory(int $id): array
    {
        if ($id <= 0) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT h.id, h.campo, h.valor_anterior, h.valor_novo, h.criado_em, u.name AS usuario_nome
             FROM mensagens_contato_historico h
             LEFT JOIN users u ON u.id = h.usuario_id
             WHERE h.mensagem_id = :id
             ORDER BY h.criado_em DESC, h.id DESC'
        );
        $stmt->execute(['id' => $id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public function countToday(): int
    {
        $stmt = $this->db->query(
            'SELECT COUNT(*) FROM mensagens_contato WHERE DATE(criado_em) = CURDATE()'
        );

        return (int) $stmt->fetchColumn();
    }

    private function normalizeStoredDate(mixed $value): ?string
    {
        if ($value === null || $value === '' || $value === '0000-00-00') {
            return null;
        }

        return substr((string) $value, 0, 10);
    }
}
