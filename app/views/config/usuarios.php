<?php require BASE_PATH . '/app/views/partials/header.php'; ?>

<div class="config-container">
    <h1>Configuração de Usuários</h1>

    <table class="config-table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Tipo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['usuarios'] as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['type']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require BASE_PATH . '/app/views/partials/footer.php'; ?>