<?php
/**
 * Conteúdo da página inicial (hero, sobre, serviços, contato, agendar).
 * Espera: $experienceLabel, $whatsAppUrl (e opcionalmente $title já definido para o header).
 */
require BASE_PATH . '/app/views/partials/header.php';
?>

        <section class="hero" id="inicio">
            <div class="hero__overlay"></div>
            <div class="hero__bg"></div>
            <div class="container hero__content">
                <h1 class="hero__title">H. A Consultoria & Desenvolvimento</h1>
                <p class="hero__tagline">Transformando ideias em resultados. Soluções que fazem a diferença para o seu negócio.</p>
                <a href="#servicos" class="btn btn--primary hero__cta">Conheça nossos serviços</a>
            </div>
            <div class="hero__scroll" aria-hidden="true">
                <span class="hero__scroll-line"></span>
            </div>
        </section>

        <section class="about section" id="sobre">
            <div class="container about__container">
                <div class="about__image-wrapper">
                    <div class="about__image" role="img" aria-label="Foto da empresa ou profissional"></div>
                </div>
                <div class="about__text">
                    <h2 class="section__title">Sobre nós</h2>
                    <p class="about__lead">Somos uma empresa focada em entregar excelência e resultados.</p>
                    <p class="about__body">Com anos de experiência no mercado, ajudamos nossos clientes a alcançar seus objetivos com soluções personalizadas e um atendimento próximo. Nossa missão é criar parcerias duradouras baseadas em confiança e qualidade.</p>
                    <ul class="about__highlights">
                        <li><strong><?= htmlspecialchars($experienceLabel, ENT_QUOTES, 'UTF-8') ?></strong> de experiência</li>
                        <!-- <li><strong>Clientes satisfeitos</strong> em todo o Brasil</li> -->
                        <li><strong>Atendimento humanizado</strong> e dedicado</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="services section section--alt" id="servicos">
            <div class="container">
                <h2 class="section__title section__title--center">Nossos serviços</h2>
                <p class="section__subtitle">Conheça as soluções que oferecemos para o seu negócio.</p>
                <div class="services__grid">
                    <article class="service-card">
                        <div class="service-card__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/></svg>
                        </div>
                        <h3 class="service-card__title">Consultoria</h3>
                        <p class="service-card__desc">Análise estratégica e planejamento para o crescimento do seu negócio.</p>
                    </article>
                    <article class="service-card">
                        <div class="service-card__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                        </div>
                        <h3 class="service-card__title">Projetos</h3>
                        <p class="service-card__desc">Desenvolvimento e gestão de projetos sob medida para sua necessidade.</p>
                    </article>
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
                <form class="contact__form" id="contactForm" action="#" method="post">
                    <label for="name" class="contact__form-label">Nome</label>
                    <input type="text" id="name" name="name" class="contact__form-input" required placeholder="Seu nome">
                    <label for="email_login" class="contact__form-label">E-mail</label>
                    <input type="email" id="email_login" name="email_login" class="contact__form-input" required placeholder="seu@email.com">
                    <label for="message" class="contact__form-label">Mensagem</label>
                    <textarea id="message" name="message" class="contact__form-input contact__form-textarea" rows="4" required placeholder="Como podemos ajudar?"></textarea>
                    <button type="submit" class="btn btn--primary contact__form-btn">Enviar mensagem</button>
                </form>
            </div>
        </section>

        <section class="section section--minimal" id="agendar" aria-label="Agendar consultoria">
            <div class="container">
                <p class="section__subtitle">Pronto para dar o próximo passo com o seu projeto?</p>
                <div style="margin-top: 1.5rem; text-align: center;">
                    <a
                        href="<?= htmlspecialchars($whatsAppUrl, ENT_QUOTES, 'UTF-8') ?>"
                        class="btn btn--primary"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Agendar consultoria pelo WhatsApp
                    </a>
                </div>
            </div>
        </section>

<?php require BASE_PATH . '/app/views/partials/footer.php'; ?>
