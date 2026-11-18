<?php
$base = defined('BASE_URL') ? BASE_URL : '';
?>
    </main>

    <footer class="footer">
        <div class="container footer__container">
            <div class="footer__links">
                <a href="<?= $base ? $base . '#inicio' : 'index.php#inicio' ?>">Início</a>
                <a href="<?= $base ? $base . '#sobre' : 'index.php#sobre' ?>">Sobre</a>
                <a href="<?= $base ? $base . '#servicos' : 'index.php#servicos' ?>">Serviços</a>
                <a href="<?= $base ?>projetos">Projetos</a>
                <a href="<?= $base ? $base . '#contato' : 'index.php#contato' ?>">Contato</a>
            </div>
            <div class="footer__social">
                <a href="#" aria-label="Instagram">Instagram</a>
                <a href="#" aria-label="LinkedIn">LinkedIn</a>
                <a href="#" aria-label="Facebook">Facebook</a>
            </div>
            <p class="footer__copy">&copy; <span id="currentYear"></span> Rafael H. Amaral. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="<?= $base ?>js/script.js"></script>
</body>
</html>
