/**
 * SITE INSTITUCIONAL - SCRIPT PRINCIPAL
 * Responsável por: menu mobile, scroll suave, animações ao rolar e interações.
 */

(function () {
    'use strict';

    // ========== ELEMENTOS DO DOM ==========
    const header = document.getElementById('header');
    const menuToggle = document.getElementById('menuToggle');
    const mainNav = document.getElementById('mainNav');
    const navLinks = document.querySelectorAll('.nav__link');
    const currentYearEl = document.getElementById('currentYear');

    // ========== MENU MOBILE ==========
    /**
     * Abre ou fecha o menu em telas pequenas.
     * Atualiza aria-expanded para acessibilidade.
     */
    function toggleMenu() {
        const isOpen = mainNav.classList.toggle('is-open');
        menuToggle.setAttribute('aria-expanded', isOpen);
        menuToggle.setAttribute('aria-label', isOpen ? 'Fechar menu' : 'Abrir menu');
        document.body.style.overflow = isOpen ? 'hidden' : '';
    }

    /**
     * Fecha o menu ao clicar em um link (útil em mobile).
     */
    function closeMenu() {
        mainNav.classList.remove('is-open');
        menuToggle.setAttribute('aria-expanded', 'false');
        menuToggle.setAttribute('aria-label', 'Abrir menu');
        document.body.style.overflow = '';
    }

    var headerVisibilityToggle = document.getElementById('headerVisibilityToggle');
    if (headerVisibilityToggle) {
        headerVisibilityToggle.addEventListener('click', function () {
            document.body.classList.toggle('is-header-hidden');
            var hidden = document.body.classList.contains('is-header-hidden');
            headerVisibilityToggle.setAttribute('aria-expanded', hidden ? 'false' : 'true');
            headerVisibilityToggle.setAttribute('aria-label', hidden ? 'Mostrar menu' : 'Ocultar menu');
            headerVisibilityToggle.textContent = hidden ? 'Mostrar menu' : 'Ocultar menu';
        });
    }

    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', toggleMenu);
        navLinks.forEach(function (link) {
            link.addEventListener('click', closeMenu);
        });
    }

    // ========== SCROLL SUAVE PARA ÂNCORAS ==========
    /**
     * Garante que links internos (#inicio, #sobre, etc.) rolem suavemente.
     * O CSS já define scroll-behavior: smooth no html; aqui tratamos o offset do header fixo.
     */
    navLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href && href.startsWith('#')) {
                const id = href.slice(1);
                const target = document.getElementById(id);
                if (target) {
                    e.preventDefault();
                    const headerHeight = header ? header.offsetHeight : 0;
                    const top = target.getBoundingClientRect().top + window.scrollY - headerHeight;
                    window.scrollTo({ top: top, behavior: 'smooth' });
                }
            }
        });
    });

    // ========== HEADER COM EFEITO AO ROLAR ==========
    /**
     * Adiciona classe 'scrolled' no header após rolar um pouco, para sombra/destaque.
     */
    function updateHeaderOnScroll() {
        if (!header) return;
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    }

    window.addEventListener('scroll', updateHeaderOnScroll, { passive: true });
    updateHeaderOnScroll(); // estado inicial

    // ========== ANIMAÇÕES AO ROLAR A PÁGINA (REVEAL) ==========
    /**
     * Elementos com classe .reveal são animados quando entram na viewport.
     * Usa IntersectionObserver para performance.
     */
    const revealElements = document.querySelectorAll('.section__title, .section__subtitle, .about__container, .service-card, .contact__info, .contact__form');

    function addRevealClass() {
        revealElements.forEach(function (el) {
            el.classList.add('reveal');
        });
    }

    addRevealClass();

    const observerOptions = {
        root: null,
        rootMargin: '0px 0px -60px 0px', // dispara um pouco antes de entrar na tela
        threshold: 0.1
    };

    const revealObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
            }
        });
    }, observerOptions);

    revealElements.forEach(function (el) {
        revealObserver.observe(el);
    });

    // ========== ANO ATUAL NO FOOTER ==========
    if (currentYearEl) {
        currentYearEl.textContent = new Date().getFullYear();
    }

    var configFlash = document.getElementById('configFlash');
    if (configFlash && configFlash.open && configFlash.classList.contains('config-popup--success')) {
        setTimeout(function () {
            if (typeof configFlash.close === 'function') {
                configFlash.close();
            }
        }, 4000);
    }

})();
