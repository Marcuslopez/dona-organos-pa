const countdown = document.querySelector('[data-login-countdown]');
const loginForm = document.querySelector('[data-login-form]');

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
