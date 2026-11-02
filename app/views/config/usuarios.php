<?php require BASE_PATH . '/app/views/partials/header.php'; ?>
<?php require BASE_PATH . '/app/views/partials/config-flash.php'; ?>

<div class="config-container">
    <?php require BASE_PATH . '/app/views/partials/config-voltar.php'; ?>
    <h1>Configuração de Usuários</h1>

    <h2>Novo usuário</h2>
    <form class="config-form" method="POST" action="<?= BASE_URL ?>config/criar-usuario">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <label>Nome</label>
        <input class="config-input" type="text" name="name" required>

        <label>E-mail</label>
        <input class="config-input" type="email" name="email" required>

        <label>Senha</label>
        <input class="config-input" type="password" name="password" minlength="8" required>

        <label>Tipo</label>
        <select class="config-input config-select" name="type" required>
            <option value="viewer">viewer — só dashboard e mensagens</option>
            <option value="editor">editor — dashboard, mensagens e dados de contato</option>
            <option value="admin">admin — tudo, inclusive usuários</option>
        </select>

        <button class="config-button" type="submit">Criar usuário</button>
    </form>

    <h2>Usuários existentes</h2>
    <table class="config-table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Tipo</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['usuarios'] as $user): ?>
                <?php
                $userId = (int) ($user['id'] ?? 0);
                $isSelf = $userId === (int) ($data['currentUserId'] ?? 0);
                $currentType = (string) ($user['type'] ?? 'viewer');
                ?>
                <tr>
                    <td><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?php if ($isSelf): ?>
                            <?= htmlspecialchars($currentType, ENT_QUOTES, 'UTF-8') ?>
                        <?php else: ?>
                            <form class="config-inline-form" method="POST" action="<?= BASE_URL ?>config/salvar-usuario-tipo">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="user_id" value="<?= $userId ?>">
                                <select class="config-input config-select" name="type">
                                    <option value="viewer" <?= $currentType === 'viewer' ? 'selected' : '' ?>>viewer</option>
                                    <option value="editor" <?= $currentType === 'editor' ? 'selected' : '' ?>>editor</option>
                                    <option value="admin" <?= $currentType === 'admin' ? 'selected' : '' ?>>admin</option>
                                </select>
                                <button class="config-button" type="submit">Salvar</button>
                            </form>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($isSelf): ?>
                            Seu usuário
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require BASE_PATH . '/app/views/partials/footer.php'; ?>
