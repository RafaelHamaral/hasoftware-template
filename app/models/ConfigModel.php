<?php

require_once BASE_PATH . '/config/database.php';

class ConfigModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getPDO();
    }

    public function getUsuarios(): array
    {
        $stmt = $this->db->query('SELECT id, name, email, type FROM users ORDER BY name ASC');
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($users) ? $users : [];
    }

    public function allowedUserTypes(): array
    {
        return ['admin', 'editor', 'viewer'];
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        if ($exceptId === null) {
            $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
        } else {
            $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
            $stmt->execute(['email' => $email, 'id' => $exceptId]);
        }

        return (bool) $stmt->fetch();
    }

    public function createUser(string $name, string $email, string $passwordHash, string $type): bool
    {
        if (!in_array($type, $this->allowedUserTypes(), true)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password, type) VALUES (:name, :email, :password, :type)'
        );

        return $stmt->execute([
            'name'     => $name,
            'email'    => $email,
            'password' => $passwordHash,
            'type'     => $type,
        ]);
    }

    public function updateUserType(int $id, string $type): bool
    {
        if (!in_array($type, $this->allowedUserTypes(), true)) {
            return false;
        }

        $stmt = $this->db->prepare('UPDATE users SET type = :type WHERE id = :id');

        return $stmt->execute([
            'type' => $type,
            'id'   => $id,
        ]);
    }

    public function getContato(): array
    {
        $defaults = [
            'id' => null,
            'titulo' => '',
            'descricao' => '',
            'email' => '',
            'telefone' => '',
            'whatsapp' => '',
            'email_label' => 'E-mail',
            'whatsapp_label' => 'WhatsApp',
            'telefone_label' => 'Telefone',
        ];

        $stmt = $this->db->prepare('SELECT * FROM dados_contato WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => 1]);
        $contato = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$contato) {
            return $defaults;
        }

        return array_merge($defaults, $contato);
    }

    public function saveContato(array $data): bool
    {
        $params = [
            'titulo'    => $data['titulo'],
            'descricao' => $data['descricao'],
            'email'     => $data['email'],
            'telefone'  => $data['telefone'],
            'whatsapp'  => $data['whatsapp'],
        ];

        $stmt = $this->db->prepare('SELECT id FROM dados_contato WHERE id = 1 LIMIT 1');
        $stmt->execute();

        if ($stmt->fetch()) {
            $sql = 'UPDATE dados_contato SET
                        titulo = :titulo,
                        descricao = :descricao,
                        email = :email,
                        telefone = :telefone,
                        whatsapp = :whatsapp
                    WHERE id = 1';
        } else {
            $sql = 'INSERT INTO dados_contato
                        (id, titulo, descricao, email, telefone, whatsapp)
                    VALUES
                        (1, :titulo, :descricao, :email, :telefone, :whatsapp)';
        }

        return $this->db->prepare($sql)->execute($params);
    }
}
