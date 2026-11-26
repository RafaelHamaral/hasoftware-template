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

    var contactMessage = document.getElementById('message');
    var draftKey = contactMessage && contactMessage.getAttribute('data-draft-key')
        ? contactMessage.getAttribute('data-draft-key')
        : 'hasoftware_contato_message';

    if (contactMessage) {
        try {
            var storedMessage = sessionStorage.getItem(draftKey);
            if (storedMessage !== null && storedMessage.trim() !== '') {
                contactMessage.value = storedMessage;
            }
        } catch (err) {
            // sessionStorage pode estar indisponível
        }

        contactMessage.addEventListener('input', function () {
            try {
                sessionStorage.setItem(draftKey, contactMessage.value);
            } catch (err) {
                // ignora falha de armazenamento
            }
        });
    }

    var WEEKS_PER_MONTH = 52 / 12;

    function parseNonNegativeNumber(raw) {
        var text = String(raw == null ? '' : raw).trim();
        if (text === '') {
            return { error: 'empty' };
        }
        var normalized = text.replace(',', '.');
        var value = Number(normalized);
        if (!isFinite(value)) {
            return { error: 'invalid' };
        }
        if (value < 0) {
            return { error: 'negative' };
        }
        return { value: value };
    }

    function monthlyHoursFromTask(timesPerWeek, minutesEach) {
        return timesPerWeek * minutesEach * WEEKS_PER_MONTH / 60;
    }

    function formatMonthlyHours(hours) {
        var rounded = Math.round(hours * 10) / 10;
        if (Math.abs(rounded - Math.round(rounded)) < 0.05) {
            return String(Math.round(rounded)).replace('.', ',') + ' horas';
        }
        return String(rounded).replace('.', ',') + ' horas';
    }

    function buildTaskCalcSummary(times, minutes, hoursLabel) {
        return 'Olá, vi a calculadora de consultoria. A tarefa é feita '
            + String(times).replace('.', ',')
            + ' vez(es) por semana, com '
            + String(minutes).replace('.', ',')
            + ' minuto(s) por execução. Estimativa do tempo gasto atualmente: '
            + hoursLabel
            + ' por mês (52 ÷ 12 semanas). Gostaria de automatizar essa tarefa.';
    }

    var taskCalcForm = document.getElementById('taskCalcForm');
    var taskCalcTimes = document.getElementById('taskCalcTimes');
    var taskCalcMinutes = document.getElementById('taskCalcMinutes');
    var taskCalcError = document.getElementById('taskCalcError');
    var taskCalcResult = document.getElementById('taskCalcResult');
    var taskCalcCta = document.getElementById('taskCalcCta');
    var lastTaskSummary = '';

    function showTaskCalcError(message) {
        lastTaskSummary = '';
        if (taskCalcError) {
            taskCalcError.hidden = false;
            taskCalcError.textContent = message;
        }
        if (taskCalcResult) {
            taskCalcResult.hidden = true;
            taskCalcResult.textContent = '';
        }
    }

    function runTaskCalc() {
        if (!taskCalcTimes || !taskCalcMinutes) {
            return false;
        }
        var timesParsed = parseNonNegativeNumber(taskCalcTimes.value);
        var minutesParsed = parseNonNegativeNumber(taskCalcMinutes.value);
        if (timesParsed.error === 'empty' || minutesParsed.error === 'empty') {
            showTaskCalcError('Preencha as vezes por semana e os minutos por execução.');
            return false;
        }
        if (timesParsed.error === 'negative' || minutesParsed.error === 'negative') {
            showTaskCalcError('Use apenas números zero ou positivos.');
            return false;
        }
        if (timesParsed.error || minutesParsed.error) {
            showTaskCalcError('Informe números válidos.');
            return false;
        }
        var hours = monthlyHoursFromTask(timesParsed.value, minutesParsed.value);
        var hoursLabel = formatMonthlyHours(hours);
        lastTaskSummary = buildTaskCalcSummary(timesParsed.value, minutesParsed.value, hoursLabel);
        if (taskCalcError) {
            taskCalcError.hidden = true;
            taskCalcError.textContent = '';
        }
        if (taskCalcResult) {
            taskCalcResult.hidden = false;
            taskCalcResult.textContent = 'Estimativa do tempo gasto atualmente: ' + hoursLabel + ' por mês.';
        }
        return true;
    }

    if (taskCalcForm) {
        taskCalcForm.addEventListener('submit', function (e) {
            e.preventDefault();
            runTaskCalc();
        });
    }

    if (taskCalcCta) {
        taskCalcCta.addEventListener('click', function (e) {
            if (!runTaskCalc()) {
                e.preventDefault();
                return;
            }
            var summary = lastTaskSummary;
            var hasTyped = false;
            if (contactMessage && contactMessage.value.trim() !== '') {
                hasTyped = true;
            } else {
                try {
                    var stored = sessionStorage.getItem(draftKey);
                    hasTyped = stored !== null && stored.trim() !== '';
                } catch (err) {
                    hasTyped = false;
                }
            }
            if (!hasTyped) {
                if (contactMessage) {
                    contactMessage.value = summary;
                }
                try {
                    sessionStorage.setItem(draftKey, summary);
                } catch (err) {
                    // segue para o formulário
                }
            }
            if (contactMessage && header) {
                e.preventDefault();
                var contact = document.getElementById('contato');
                if (contact) {
                    var headerHeight = header.offsetHeight || 0;
                    var top = contact.getBoundingClientRect().top + window.scrollY - headerHeight;
                    window.scrollTo({ top: top, behavior: 'smooth' });
                    window.location.hash = 'contato';
                }
            }
        });
    }

    var demoPanelCta = document.getElementById('demoPanelCta');
    if (demoPanelCta) {
        demoPanelCta.addEventListener('click', function () {
            try {
                var existing = sessionStorage.getItem(draftKey);
                if (existing !== null && existing.trim() !== '') {
                    return;
                }
                var preset = demoPanelCta.getAttribute('data-painel-message') || '';
                if (preset !== '') {
                    sessionStorage.setItem(draftKey, preset);
                }
            } catch (err) {
                // segue o href com o parâmetro interesse=painel
            }
        });
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
