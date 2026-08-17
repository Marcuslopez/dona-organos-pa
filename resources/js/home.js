const backToTop = document.querySelector('#backToTop');

if (backToTop) {
    const toggleBackToTop = () => backToTop.classList.toggle('visible', window.scrollY > 450);
    window.addEventListener('scroll', toggleBackToTop, { passive: true });
    backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    toggleBackToTop();
}

document.querySelectorAll('[data-collapsible-list]').forEach((list) => {
    const button = list.querySelector('.show-more');
    if (!button) return;

    button.addEventListener('click', () => {
        const expanded = button.getAttribute('aria-expanded') === 'true';
        list.querySelectorAll('.additional-content').forEach((item) => {
            item.hidden = expanded;
            if (expanded) item.removeAttribute('open');
        });
        button.setAttribute('aria-expanded', String(!expanded));
        button.querySelector('.show-more-label').textContent = expanded
            ? (list.closest('#mitos') ? 'Otros mitos' : 'Otras preguntas')
            : 'Ver menos';
        button.lastElementChild.textContent = expanded ? '＋' : '−';
    });
});

document.querySelectorAll('.legal-card').forEach((card) => {
    card.addEventListener('click', (event) => {
        if (event.target.closest('a')) return;
        card.classList.toggle('active');
    });

    card.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            card.classList.toggle('active');
        }
    });
});

document.querySelectorAll('#mainNavigation .nav-link').forEach((link) => {
    link.addEventListener('click', () => {
        const navigation = document.querySelector('#mainNavigation');
        if (navigation?.classList.contains('show')) {
            window.bootstrap?.Collapse.getOrCreateInstance(navigation).hide();
        }
    });
});
