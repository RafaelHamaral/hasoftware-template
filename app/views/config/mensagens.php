<?php require BASE_PATH . '/app/views/partials/header.php'; ?>
<?php require BASE_PATH . '/app/views/partials/config-flash.php'; ?>

<div class="config-container">
    <?php require BASE_PATH . '/app/views/partials/config-voltar.php'; ?>
    <h1>Mensagens de contato</h1>
    <p>Envios do formulário da página inicial (nome, e-mail e mensagem).</p>

    <?php if (empty($data['mensagens'])): ?>
        <p>Nenhuma mensagem recebida ainda.</p>
    <?php else: ?>
        <table class="config-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Mensagem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['mensagens'] as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) ($item['criado_em'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($item['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <a href="mailto:<?= htmlspecialchars((string) ($item['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars((string) ($item['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </td>
                        <td class="config-message-cell"><?= nl2br(htmlspecialchars((string) ($item['mensagem'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/app/views/partials/footer.php'; ?>
