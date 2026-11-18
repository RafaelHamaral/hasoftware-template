<?php

/**
 * Conteúdo da página inicial (hero, sobre, serviços, contato, agendar).
 * Espera: $experienceLabel, $whatsAppUrl (e opcionalmente $title já definido para o header).
 */
require BASE_PATH . '/app/views/partials/header.php';
require BASE_PATH . '/app/views/partials/config-flash.php';
?>

<section class="hero" id="inicio">
    <div class="hero__overlay"></div>
    <div class="hero__bg"></div>
    <div class="container hero__content">
    <h1 class="hero__title">
        <span class="hero__heading<?= !empty($data['inicio']['imagem']) ? ' hero__heading--with-logo' : '' ?>">
            <?php if (!empty($data['inicio']['imagem'])): ?>
                <img
                    src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($data['inicio']['imagem'], ENT_QUOTES, 'UTF-8') ?>"
                    alt=""
                    width="100"
                    height="100"
                    class="hero__logo"
                    decoding="async"
                >
            <?php endif; ?>
            <span class="hero__heading-text"><?= htmlspecialchars(
                !empty($data['inicio']['titulo'])
                    ? $data['inicio']['titulo']
                    : 'H. A Consultoria & Desenvolvimento',
                ENT_QUOTES,
                'UTF-8'
            ) ?></span>
        </span>
    </h1>      
    <p class="hero__tagline"><?= htmlspecialchars(($data['inicio']['subtitulo'] ?? '') ?: 'Transformando ideias em resultados. Soluções que fazem a diferença para o seu negócio.', ENT_QUOTES, 'UTF-8') ?></p>
        <a href="#servicos" class="btn btn--primary hero__cta"><?= htmlspecialchars(($data['inicio']['botao_inicio'] ?? '') ?: 'Conheça nossos serviços', ENT_QUOTES, 'UTF-8') ?></a>
    </div>
    <div class="hero__scroll" aria-hidden="true">
        <span class="hero__scroll-line"></span>
    </div>
</section>

<section class="about section" id="sobre">
    <div class="container about__container">
        <!-- <div class="about__image-wrapper">
            <div class="about__image" role="img" aria-label="Foto da empresa ou profissional"></div>
        </div> -->
        <div class="about__text">
            <h2 class="section__title"><?= htmlspecialchars($data['sobre']['titulo'] ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="about__lead"><?= htmlspecialchars($data['sobre']['subtitulo'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            <p class="about__body"><?= htmlspecialchars($data['sobre']['descricao'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            <ul class="about__highlights">
                <li><strong><?= htmlspecialchars($experienceLabel ?? '', ENT_QUOTES, 'UTF-8') ?></strong> de experiência</li>
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5z" />
                        <path d="M2 17l10 5 10-5" />
                    </svg>
                </div>
                <h3 class="service-card__title"><a href="#consultoria">Consultoria</a></h3>
                <p class="service-card__desc">Análise estratégica e planejamento para o crescimento do seu negócio.</p>
            </article>
            <article class="service-card">
                <div class="service-card__icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" />
                        <path d="M3 9h18" />
                        <path d="M9 21V9" />
                    </svg>
                </div>
                <h3 class="service-card__title">Projetos</h3>
                <p class="service-card__desc">Desenvolvimento e gestão de projetos sob medida para sua necessidade.</p>
            </article>
            <article class="service-card">
                <div class="service-card__icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 6v6l4 2" />
                    </svg>
                </div>
                <h3 class="service-card__title">Suporte</h3>
                <p class="service-card__desc">Acompanhamento contínuo e suporte para garantir o sucesso.</p>
            </article>
        </div>

        <div class="task-calc" id="consultoria">
            <h3 class="task-calc__title">Quanto tempo essa tarefa consome?</h3>
            <p class="task-calc__lead">
                Informe com que frequência ela é feita e quanto dura cada execução.
                A estimativa usa 52 ÷ 12 semanas por mês.
            </p>
            <form class="task-calc__form" id="taskCalcForm" action="#consultoria" method="get">
                <label class="task-calc__label" for="taskCalcTimes">Vezes por semana</label>
                <input
                    class="contact__form-input"
                    type="number"
                    id="taskCalcTimes"
                    name="vezes"
                    step="any"
                    inputmode="decimal"
                    placeholder="Ex.: 12"
                >
                <label class="task-calc__label" for="taskCalcMinutes">Minutos por execução</label>
                <input
                    class="contact__form-input"
                    type="number"
                    id="taskCalcMinutes"
                    name="minutos"
                    step="any"
                    inputmode="decimal"
                    placeholder="Ex.: 15"
                >
                <button type="submit" class="btn btn--primary" id="taskCalcSubmit">Calcular</button>
            </form>
            <p class="task-calc__error" id="taskCalcError" role="alert" hidden></p>
            <p class="task-calc__result" id="taskCalcResult" role="status" hidden></p>
            <div class="cta-row">
                <a class="btn btn--primary" id="taskCalcCta" href="#contato">Quero automatizar essa tarefa</a>
            </div>
        </div>
    </div>
</section>

<section class="contact section" id="contato">
    <div class="container contact__container">
        <div class="contact__info">
            <h2 class="section__title"><?= htmlspecialchars($data['contato']['titulo'] ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="contact__lead"><?= htmlspecialchars($data['contato']['descricao'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            <!-- <ul class="contact__list">
                        <li>
                            <span class="contact__label"><?= htmlspecialchars($data['contato']['email_label'] ?? 'E-mail', ENT_QUOTES, 'UTF-8') ?></span>
                            <a href="mailto:<?= htmlspecialchars($data['contato']['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($data['contato']['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></a>
                        </li>
                        <li>
                            <span class="contact__label"><?= htmlspecialchars($data['contato']['whatsapp_label'] ?? 'WhatsApp', ENT_QUOTES, 'UTF-8') ?></span>
                            <a href="<?= htmlspecialchars($whatsAppUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><?= htmlspecialchars($data['contato']['whatsapp'] ?? '', ENT_QUOTES, 'UTF-8') ?></a>
                        </li>
                        <li>
                            <span class="contact__label"><?= htmlspecialchars($data['contato']['telefone_label'] ?? 'Telefone', ENT_QUOTES, 'UTF-8') ?></span>
                            <a href="tel:<?= htmlspecialchars($data['contato']['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($data['contato']['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?></a>
                        </li>
                    </ul> -->
            <form class="contact__form" id="contactForm" action="<?= BASE_URL ?>contato/enviar" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <label for="name" class="contact__form-label">Nome</label>
                <input type="text" id="name" name="name" class="contact__form-input" required placeholder="Seu nome">
                <label for="email" class="contact__form-label">E-mail</label>
                <input type="email" id="email" name="email" class="contact__form-input" required placeholder="seu@email.com">
                <label for="message" class="contact__form-label">Mensagem</label>
                <textarea
                    id="message"
                    name="message"
                    class="contact__form-input contact__form-textarea"
                    rows="4"
                    required
                    placeholder="Como podemos ajudar?"
                    data-draft-key="vibecoding_contato_message"
                    data-painel-message="<?= htmlspecialchars($painelCtaMessage ?? '', ENT_QUOTES, 'UTF-8') ?>"
                ><?= htmlspecialchars($contactMessage ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                <button type="submit" class="btn btn--primary contact__form-btn">Enviar mensagem</button>
            </form>
            <div class="contact__social">
                <a href="#" class="contact__social-link" aria-label="Instagram">Instagram</a>
                <a href="#" class="contact__social-link" aria-label="LinkedIn">LinkedIn</a>
                <a href="#" class="contact__social-link" aria-label="Facebook">Facebook</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--minimal" id="agendar" aria-label="Agendar consultoria">
    <div class="container">
        <p class="section__subtitle">Pronto para dar o próximo passo com o seu projeto?</p>
        <div class="cta-row">
            <a
                href="<?= htmlspecialchars($whatsAppUrl, ENT_QUOTES, 'UTF-8') ?>"
                class="btn btn--primary"
                target="_blank"
                rel="noopener noreferrer">
                Agendar consultoria pelo WhatsApp
            </a>
        </div>
    </div>
</section>

<?php require BASE_PATH . '/app/views/partials/footer.php'; ?>