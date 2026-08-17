const countdown = document.getElementById('identitySessionCountdown');
const expiredModalElement = document.getElementById('identitySessionExpired');
const expiredButton = document.getElementById('identitySessionExpiredButton');

if (countdown && expiredModalElement && expiredButton) {
    const expiresAt = Number(countdown.dataset.expiresAt) * 1000;
    let intervalId;
    let hasExpired = false;

    const finishSession = () => {
        if (hasExpired) return;

        hasExpired = true;
        countdown.textContent = '00:00';
        window.clearInterval(intervalId);
        window.bootstrap.Modal.getOrCreateInstance(expiredModalElement, {
            backdrop: 'static',
            keyboard: false,
        }).show();
    };

    const updateCountdown = () => {
        const remainingSeconds = Math.max(0, Math.ceil((expiresAt - Date.now()) / 1000));
        const minutes = Math.floor(remainingSeconds / 60);
        const seconds = remainingSeconds % 60;

        countdown.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

        if (remainingSeconds === 0) finishSession();
    };

    expiredButton.addEventListener('click', () => {
        window.location.assign(expiredModalElement.dataset.redirectUrl);
    });

    updateCountdown();
    if (!hasExpired) intervalId = window.setInterval(updateCountdown, 1000);
}
