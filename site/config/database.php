<?php
/**
 * Configuração de conexão com o banco de dados usando PDO.
 *
 * Ajuste as variáveis abaixo de acordo com o seu ambiente (host, nome do banco,
 * usuário e senha). Esta função pode ser reutilizada em qualquer parte do
 * projeto para obter uma conexão com o banco de dados.
 */

/**
 * Retorna uma instância de PDO conectada ao banco de dados.
 *
 * @return PDO
 * @throws PDOException Em caso de falha na conexão.
 */
function getPDO(): PDO
{
    // Dados de conexão – ajuste conforme o seu ambiente.
    $dbHost = '127.0.0.1';      // Ex: 127.0.0.1 ou localhost
    $dbName = 'sua_base';       // Ex: vibecoding_um
    $dbUser = 'seu_usuario';    // Ex: root
    $dbPass = 'sua_senha';      // Ex: senha forte
    $dbCharset = 'utf8mb4';

    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";

    // Opções recomendadas para segurança e tratamento de erros.
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    return new PDO($dsn, $dbUser, $dbPass, $options);
}

