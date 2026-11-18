<?php

declare(strict_types=1);

/**
 * Acompanhamento de solicitações (mensagens de contato):
 * status, anotação interna, próximo contato e histórico.
 */
return new class extends Migration {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            'ALTER TABLE `mensagens_contato`
                ADD COLUMN `status` varchar(40) NOT NULL DEFAULT \'novo\',
                ADD COLUMN `anotacao_interna` text NULL,
                ADD COLUMN `proximo_contato` date NULL,
                ADD COLUMN `atualizado_em` datetime NULL'
        );
        $pdo->exec(
            'ALTER TABLE `mensagens_contato`
                ADD INDEX `idx_mensagens_status_retorno` (`status`, `proximo_contato`)'
        );
        $pdo->exec(
            'CREATE TABLE `mensagens_contato_historico` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `mensagem_id` int(11) NOT NULL,
                `usuario_id` int(11) NULL,
                `campo` varchar(40) NOT NULL,
                `valor_anterior` text NULL,
                `valor_novo` text NULL,
                `criado_em` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_historico_mensagem` (`mensagem_id`, `criado_em`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
        );
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `mensagens_contato_historico`');
        $pdo->exec(
            'ALTER TABLE `mensagens_contato`
                DROP INDEX `idx_mensagens_status_retorno`,
                DROP COLUMN `atualizado_em`,
                DROP COLUMN `proximo_contato`,
                DROP COLUMN `anotacao_interna`,
                DROP COLUMN `status`'
        );
    }

    public function supportsDown(): bool
    {
        return true;
    }
};
