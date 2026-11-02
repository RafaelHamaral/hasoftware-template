<?php

require_once BASE_PATH . '/config/database.php';

class MensagemModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getPDO();
    }

    public function save(array $data): bool
    {
        $sql = 'INSERT INTO mensagens_contato (nome, email, mensagem, criado_em)
                VALUES (:nome, :email, :mensagem, NOW())';

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'nome'      => $data['nome'],
            'email'     => $data['email'],
            'mensagem'  => $data['mensagem'],
        ]);
    }

    public function listAll(): array
    {
        $stmt = $this->db->query(
            'SELECT id, nome, email, mensagem, criado_em
             FROM mensagens_contato
             ORDER BY criado_em DESC, id DESC'
        );
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
}
