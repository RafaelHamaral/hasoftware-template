<?php

declare(strict_types=1);

return new class extends Seeder {
    public function name(): string
    {
        return 'administrador_inicial';
    }

    public function requiredTables(): array
    {
        return ['users'];
    }

    public function run(PDO $pdo): string
    {
        $email = SeederRunner::envOptional('SEED_ADMIN_EMAIL');
        $password = SeederRunner::envOptional('SEED_ADMIN_PASSWORD');
        $name = SeederRunner::envOptional('SEED_ADMIN_NAME');

        $adminCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE type = 'admin'")->fetchColumn();

        if ($email === null && $password === null && $name === null) {
            if ($adminCount > 0) {
                return 'skipped';
            }
            throw new RuntimeException(
                'Nenhum administrador encontrado. Defina SEED_ADMIN_NAME, SEED_ADMIN_EMAIL e '
                . 'SEED_ADMIN_PASSWORD no ambiente (mínimo 8 caracteres na senha) e execute novamente. '
                . 'A senha não é gravada em texto puro.'
            );
        }

        if ($name === null || $email === null || $password === null) {
            throw new RuntimeException(
                'Credenciais de seed incompletas. Informe SEED_ADMIN_NAME, SEED_ADMIN_EMAIL e SEED_ADMIN_PASSWORD.'
            );
        }

        $name = trim($name);
        $email = trim($email);
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('SEED_ADMIN_NAME e SEED_ADMIN_EMAIL devem ser um nome e um e-mail válidos.');
        }
        if (strlen($password) < 8) {
            throw new RuntimeException('SEED_ADMIN_PASSWORD deve ter no mínimo 8 caracteres.');
        }

        $stmt = $pdo->prepare('SELECT id, type FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $existing = $stmt->fetch();
        if (is_array($existing)) {
            return 'skipped';
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insert = $pdo->prepare(
            'INSERT INTO users (name, email, password, type)
             VALUES (:name, :email, :password, :type)'
        );
        $insert->execute([
            'name' => $name,
            'email' => $email,
            'password' => $hash,
            'type' => 'admin',
        ]);

        return 'created';
    }
};
