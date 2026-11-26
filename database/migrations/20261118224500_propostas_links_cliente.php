<?php

declare(strict_types=1);

/**
 * Links privados para o cliente consultar a proposta
 * e pedidos de ajuste registrados nela.
 */
return new class extends Migration {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE `propostas_links` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `proposta_id` int(11) NOT NULL,
                `token_hash` char(64) NOT NULL,
                `expires_at` datetime NOT NULL,
                `revoked_at` datetime NULL,
                `criado_em` datetime NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_propostas_links_hash` (`token_hash`),
                KEY `idx_propostas_links_proposta` (`proposta_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
        );
        $pdo->exec(
            'CREATE TABLE `propostas_ajustes` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `proposta_id` int(11) NOT NULL,
                `link_id` int(11) NULL,
                `mensagem` text NOT NULL,
                `criado_em` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_propostas_ajustes_proposta` (`proposta_id`, `criado_em`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
        );
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `propostas_ajustes`');
        $pdo->exec('DROP TABLE IF EXISTS `propostas_links`');
    }

    public function supportsDown(): bool
    {
        return true;
    } 
};
