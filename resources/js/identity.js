const identityForm = document.querySelector('[data-identity-form]');
const identityCountdown = document.querySelector('[data-identity-countdown]');

if (identityForm) {
    const documentNumber = identityForm.querySelector('#document_number');
    const documentCode = identityForm.querySelector('#document_code');
    const captcha = identityForm.querySelector('#captcha');
    const captchaImage = identityForm.querySelector('[data-captcha-image]');
    const refreshCaptcha = identityForm.querySelector('[data-captcha-refresh]');

    documentNumber?.addEventListener('input', () => {
        documentNumber.value = documentNumber.value.toUpperCase().replace(/[^A-Z0-9-]/g, '');
    });
    documentCode?.addEventListener('input', () => {
        documentCode.value = documentCode.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    });
    captcha?.addEventListener('input', () => {
        captcha.value = captcha.value.toLowerCase().replace(/[^a-z0-9]/g, '').slice(0, 6);
    });
    refreshCaptcha?.addEventListener('click', async () => {
        refreshCaptcha.disabled = true;
        try {
            const response = await fetch('/registro/captcha/renovar', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': identityForm.querySelector('[name="_token"]').value },
            });
            if (!response.ok) throw new Error('No se pudo renovar el CAPTCHA');
            const data = await response.json();
            captchaImage.src = data.image_url;
            captcha.value = '';
            captcha.focus();
        } finally {
            refreshCaptcha.disabled = false;
        }
    });
}

if (identityForm && identityCountdown) {
    const value = identityCountdown.querySelector('[data-countdown-value]');
    const controls = identityForm.querySelectorAll('input, button');
    let seconds = Number(identityCountdown.dataset.identityCountdown) || 0;

    const render = () => {
        value.textContent = String(seconds);
        controls.forEach((control) => { control.disabled = seconds > 0; });

        if (seconds <= 0) {
            identityCountdown.classList.add('finished');
            identityCountdown.querySelector('strong').textContent = 'Ya puedes intentarlo nuevamente';
            identityCountdown.querySelector('span').textContent = 'El formulario está disponible.';
            identityForm.querySelector('#document_number')?.focus();
            return;
        }

        seconds -= 1;
        window.setTimeout(render, 1000);
    };

    render();
}
