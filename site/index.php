<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <!-- Configuração básica e meta tags para SEO e responsividade -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Site institucional - Conheça nossos serviços e entre em contato.">
    <title>Nome da Empresa | Soluções Profissionais</title>

    <!-- Google Fonts - Tipografia elegante (Outfit + DM Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Folha de estilos principal -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- ========== HEADER / MENU DE NAVEGAÇÃO ========== -->
    <header class="header" id="header">
        <div class="container header__container">
            <!-- Logo da empresa (substitua o texto pelo caminho da imagem quando tiver) -->
            <a href="#inicio" class="header__logo" aria-label="Ir para início">
                <span class="header__logo-text">Sua Empresa</span>
            </a>

            <!-- Botão do menu mobile (hambúrguer) -->
            <button class="header__toggle" id="menuToggle" aria-label="Abrir menu" aria-expanded="false">
                <span class="header__toggle-bar"></span>
                <span class="header__toggle-bar"></span>
                <span class="header__toggle-bar"></span>
            </button>

            <!-- Navegação principal -->
            <nav class="nav" id="mainNav">
                <ul class="nav__list">
                    <li><a href="#inicio" class="nav__link">Início</a></li>
                    <li><a href="#sobre" class="nav__link">Sobre</a></li>
                    <li><a href="#servicos" class="nav__link">Serviços</a></li>
                    <li><a href="#contato" class="nav__link">Contato</a></li>
                    <li><a href="#agendar" class="nav__link nav__link--cta">Agendar consultoria</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <!-- ========== HERO SECTION ========== -->
        <section class="hero" id="inicio">
            <!-- Overlay escuro sobre o background para legibilidade -->
            <div class="hero__overlay"></div>
            <!-- Background (imagem pode ser colocada em /images e referenciada aqui) -->
            <div class="hero__bg"></div>

            <div class="container hero__content">
                <h1 class="hero__title">Sua Empresa</h1>
                <p class="hero__tagline">Transformando ideias em resultados. Soluções que fazem a diferença para o seu negócio.</p>
                <a href="#servicos" class="btn btn--primary hero__cta">Conheça nossos serviços</a>
            </div>

            <!-- Indicador de scroll suave -->
            <div class="hero__scroll" aria-hidden="true">
                <span class="hero__scroll-line"></span>
            </div>
        </section>

        <!-- ========== SEÇÃO SOBRE ========== -->
        <section class="about section" id="sobre">
            <div class="container about__container">
                <!-- Imagem (placeholder - substitua por foto real em /images) -->
                <div class="about__image-wrapper">
                    <div class="about__image" role="img" aria-label="Foto da empresa ou profissional"></div>
                </div>

                <div class="about__text">
                    <h2 class="section__title">Sobre nós</h2>
                    <p class="about__lead">Somos uma empresa focada em entregar excelência e resultados.</p>
                    <p class="about__body">Com anos de experiência no mercado, ajudamos nossos clientes a alcançar seus objetivos com soluções personalizadas e um atendimento próximo. Nossa missão é criar parcerias duradouras baseadas em confiança e qualidade.</p>
                    <ul class="about__highlights">
                        <li><strong>+X anos</strong> de experiência</li>
                        <li><strong>Clientes satisfeitos</strong> em todo o Brasil</li>
                        <li><strong>Atendimento humanizado</strong> e dedicado</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- ========== SEÇÃO SERVIÇOS ========== -->
        <section class="services section section--alt" id="servicos">
            <div class="container">
                <h2 class="section__title section__title--center">Nossos serviços</h2>
                <p class="section__subtitle">Conheça as soluções que oferecemos para o seu negócio.</p>

                <div class="services__grid">
                    <!-- Card de serviço 1 -->
                    <article class="service-card">
                        <div class="service-card__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/></svg>
                        </div>
                        <h3 class="service-card__title">Consultoria</h3>
                        <p class="service-card__desc">Análise estratégica e planejamento para o crescimento do seu negócio.</p>
                    </article>

                    <!-- Card de serviço 2 -->
                    <article class="service-card">
                        <div class="service-card__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                        </div>
                        <h3 class="service-card__title">Projetos</h3>
                        <p class="service-card__desc">Desenvolvimento e gestão de projetos sob medida para sua necessidade.</p>
                    </article>

                    <!-- Card de serviço 3 -->
                    <article class="service-card">
                        <div class="service-card__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        </div>
                        <h3 class="service-card__title">Suporte</h3>
                        <p class="service-card__desc">Acompanhamento contínuo e suporte para garantir o sucesso.</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- ========== SEÇÃO CONTATO ========== -->
        <section class="contact section" id="contato">
            <div class="container contact__container">
                <div class="contact__info">
                    <h2 class="section__title">Entre em contato</h2>
                    <p class="contact__lead">Estamos prontos para atender você. Escolha o canal de sua preferência.</p>
                    <ul class="contact__list">
                        <li>
                            <span class="contact__label">E-mail</span>
                            <a href="mailto:contato@empresa.com">contato@empresa.com</a>
                        </li>
                        <li>
                            <span class="contact__label">WhatsApp</span>
                            <a href="https://wa.me/5511999999999" target="_blank" rel="noopener">(11) 99999-9999</a>
                        </li>
                        <li>
                            <span class="contact__label">Telefone</span>
                            <a href="tel:+5511999999999">(11) 99999-9999</a>
                        </li>
                    </ul>
                    <div class="contact__social">
                        <a href="#" class="contact__social-link" aria-label="Instagram">Instagram</a>
                        <a href="#" class="contact__social-link" aria-label="LinkedIn">LinkedIn</a>
                        <a href="#" class="contact__social-link" aria-label="Facebook">Facebook</a>
                    </div>
                </div>

                <!-- Formulário de contato -->
                <form class="contact__form" id="contactForm" action="#" method="post">
                    <label for="name" class="contact__form-label">Nome</label>
                    <input type="text" id="name" name="name" class="contact__form-input" required placeholder="Seu nome">

                    <label for="email" class="contact__form-label">E-mail</label>
                    <input type="email" id="email" name="email" class="contact__form-input" required placeholder="seu@email.com">

                    <label for="message" class="contact__form-label">Mensagem</label>
                    <textarea id="message" name="message" class="contact__form-input contact__form-textarea" rows="4" required placeholder="Como podemos ajudar?"></textarea>

                    <button type="submit" class="btn btn--primary contact__form-btn">Enviar mensagem</button>
                </form>
            </div>
        </section>

        <!-- Âncora para "Agendar consultoria" (pode apontar para um modal ou página futura) -->
        <section class="section section--minimal" id="agendar" aria-label="Agendar consultoria">
            <div class="container">
                <p class="section__subtitle">Em breve: agendamento online de consultoria.</p>
            </div>
        </section>
    </main>

    <!-- ========== FOOTER ========== -->
    <footer class="footer">
        <div class="container footer__container">
            <div class="footer__links">
                <a href="#inicio">Início</a>
                <a href="#sobre">Sobre</a>
                <a href="#servicos">Serviços</a>
                <a href="#contato">Contato</a>
            </div>
            <div class="footer__social">
                <a href="#" aria-label="Instagram">Instagram</a>
                <a href="#" aria-label="LinkedIn">LinkedIn</a>
                <a href="#" aria-label="Facebook">Facebook</a>
            </div>
            <p class="footer__copy">&copy; <span id="currentYear"></span> Sua Empresa. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- Script principal (carregado ao final para não bloquear a renderização) -->
    <script src="js/script.js"></script>
</body>
</html>
