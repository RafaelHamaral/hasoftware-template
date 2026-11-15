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

    public function getUserById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare('SELECT id, name, email, type FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($user) ? $user : null;
    }

    public function countAdmins(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE type = 'admin'");

        return (int) $stmt->fetchColumn();
    }

    public function deleteUser(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id LIMIT 1');

        return $stmt->execute(['id' => $id]) && $stmt->rowCount() === 1;
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

    public function saveSobreNos(array $data): bool
    {
        $params = [
            'titulo'    => $data['titulo'],
            'subtitulo' => $data['subtitulo'],
            'descricao' => $data['descricao'],
        ];

        $stmt = $this->db->prepare('SELECT id FROM sobre WHERE id = 1 LIMIT 1');
        $stmt->execute();

        if ($stmt->fetch()) {
            $sql = 'UPDATE sobre SET titulo = :titulo, subtitulo = :subtitulo, descricao = :descricao WHERE id = 1';
        } else {
            $sql = 'INSERT INTO sobre (id, titulo, subtitulo, descricao) VALUES (1, :titulo, :subtitulo, :descricao)';
        }
        return $this->db->prepare($sql)->execute($params);
    }

    public function getSobreNos(): array
    {
        $stmt = $this->db->prepare('SELECT * FROM sobre WHERE id = 1 LIMIT 1');
        $stmt->execute();
        $sobreNos = $stmt->fetch(PDO::FETCH_ASSOC);
        return $sobreNos ? $sobreNos : [];
    }

    public function getDadosInicio(): array
    {
        $stmt = $this->db->prepare('SELECT * FROM dados_inicio WHERE id = 1 LIMIT 1');
        $stmt->execute();
        $dadosInicio = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dadosInicio ? $dadosInicio : [];
    }

    public function saveDadosInicio(array $data): bool
    {
        $params = [
            'titulo' => $data['titulo'],
            'subtitulo' => $data['subtitulo'],
            'botao_inicio' => $data['botao_inicio'],
            'imagem' => $data['imagem'] ?? '',
        ];
        $stmt = $this->db->prepare('SELECT id FROM dados_inicio WHERE id = 1 LIMIT 1');
        $stmt->execute();

        if ($stmt->fetch()) {
            $sql = 'UPDATE dados_inicio SET titulo = :titulo, subtitulo = :subtitulo, botao_inicio = :botao_inicio, imagem = :imagem WHERE id = 1';
        } else {
            $sql = 'INSERT INTO dados_inicio (id, titulo, subtitulo, botao_inicio, imagem) VALUES (1, :titulo, :subtitulo, :botao_inicio, :imagem)';
        }
        return $this->db->prepare($sql)->execute($params);
    }
}
