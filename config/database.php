<?php
/**
 * Conexão PDO. Credenciais vêm apenas de variáveis de ambiente.
 */

require_once __DIR__ . '/env.php';
loadEnvFile((defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__)) . '/.env');

/**
 * @return PDO
 * @throws PDOException|RuntimeException
 */
function getPDO(): PDO
{
    $dbHost = envValue('DB_HOST');
    $dbName = envValue('DB_NAME');
    $dbUser = envValue('DB_USER');
    $dbPass = envValue('DB_PASS', '');
    $dbCharset = envValue('DB_CHARSET', 'utf8mb4');

    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    return new PDO($dsn, $dbUser, $dbPass, $options);
}
