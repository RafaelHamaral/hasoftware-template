<?php

require_once __DIR__ . '/../config/database.php';

$pdo = getPDO();

$name = 'Rafael Teste';
$email = 'rafael@vibecoding.com.br';
$type = 'admin';
$password = password_hash('Teste@123', PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
    INSERT INTO users (name, email_login, type, password)
    VALUES (:name, :email_login, :type, :password)
");

$stmt->execute([
    'name' => $name,
    'email_login' => $email,
    'type' => $type,
    'password' => $password
]);

echo "Usuário criado com sucesso!";
