const countdown = document.querySelector('[data-login-countdown]');
const loginForm = document.querySelector('[data-login-form]');

document.querySelectorAll('[data-email-code-request]').forEach((form) => {
    const email = form.querySelector('input[type="email"]');
    const submit = form.querySelector('button[type="submit"]');

    if (!email || !submit) return;

    const refresh = () => {
        submit.disabled = !email.validity.valid;
    };

    email.addEventListener('input', refresh);
    form.addEventListener('submit', (event) => {
        refresh();
        if (submit.disabled) event.preventDefault();
    });
    refresh();
});

document.querySelectorAll('[data-verification-code]').forEach((form) => {
    const code = form.querySelector('input[name="code"]');
    const submit = form.querySelector('button[type="submit"]');

    if (!code || !submit) return;

    const refresh = () => {
        code.value = code.value.replace(/\D/g, '').slice(0, 6);
        submit.disabled = code.value.length !== 6;
    };

    code.addEventListener('input', refresh);
    code.addEventListener('paste', () => window.setTimeout(refresh, 0));
    form.addEventListener('submit', (event) => {
        refresh();
        if (submit.disabled) event.preventDefault();
    });
    refresh();
});

document.querySelectorAll('[data-password-reset]').forEach((toggle) => {
    const form = toggle.closest('form');
    const fields = form?.querySelectorAll('[data-temporary-password-fields]') ?? [];
    const password = form?.querySelector('input[name="password"]');
    const confirmation = form?.querySelector('input[name="password_confirmation"]');

    if (!form || !password || !confirmation) return;

    const randomPassword = () => {
        const groups = ['ABCDEFGHJKLMNPQRSTUVWXYZ', 'abcdefghijkmnopqrstuvwxyz', '23456789'];
        const characters = groups.join('');
        const values = new Uint32Array(16);
        window.crypto.getRandomValues(values);
        const result = [
            ...groups.map((group, index) => group[values[index] % group.length]),
            ...Array.from(values.slice(3), (value) => characters[value % characters.length]),
        ];

        for (let index = result.length - 1; index > 0; index -= 1) {
            const swapIndex = values[index] % (index + 1);
            [result[index], result[swapIndex]] = [result[swapIndex], result[index]];
        }

        return result.join('');
    };

    const refresh = () => {
        const enabled = toggle.checked;
        fields.forEach((field) => field.classList.toggle('d-none', !enabled));
        password.disabled = !enabled;
        confirmation.disabled = !enabled;

        if (enabled && password.value === '') {
            password.value = randomPassword();
            confirmation.value = password.value;
        }

        if (!enabled) {
            password.value = '';
            confirmation.value = '';
        }
    };

    toggle.addEventListener('change', refresh);
    refresh();
});

if (countdown && loginForm) {
    const countdownValue = countdown.querySelector('[data-countdown-value]');
    const controls = loginForm.querySelectorAll('input, button');
    let seconds = Number(countdown.dataset.loginCountdown) || 0;

    const renderCountdown = () => {
        countdownValue.textContent = String(seconds);
        controls.forEach((control) => {
            control.disabled = seconds > 0;
        });

        if (seconds <= 0) {
            countdown.classList.add('finished');
            countdown.querySelector('strong').textContent = 'Ya puedes intentarlo nuevamente';
            countdown.querySelector('span').textContent = 'El formulario está disponible.';
            loginForm.querySelector('#email')?.focus();
            return;
        }

        seconds -= 1;
        window.setTimeout(renderCountdown, 1000);
    };

    renderCountdown();
}
