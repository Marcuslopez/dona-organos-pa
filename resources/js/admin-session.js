import { Modal } from 'bootstrap';

const sessionRoot = document.querySelector('[data-session-timeout]');

if (sessionRoot) {
    const idleTimeout = Math.max(1, Number(sessionRoot.dataset.idleTimeout) || 120);
    const warningTime = Math.min(idleTimeout, Math.max(1, Number(sessionRoot.dataset.idleWarning) || 30));
    const heartbeatUrl = sessionRoot.dataset.heartbeatUrl;
    const redirectUrl = sessionRoot.dataset.expiredRedirectUrl;
    const expiredMessage = sessionRoot.dataset.expiredMessage;
    const modalElement = document.querySelector('#sessionTimeoutModal');
    const title = modalElement?.querySelector('[data-session-title]');
    const message = modalElement?.querySelector('[data-session-message]');
    const remaining = modalElement?.querySelector('[data-session-remaining]');
    const continueButton = modalElement?.querySelector('[data-session-continue]');
    const redirectButton = modalElement?.querySelector('[data-session-redirect]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const modal = modalElement ? Modal.getOrCreateInstance(modalElement) : null;
    let deadline = Date.now() + (idleTimeout * 1000);
    let hasActivity = false;
    let heartbeatPending = false;
    let sessionExpired = false;

    const showExpired = () => {
        if (sessionExpired) return;
        sessionExpired = true;
        title.textContent = 'Sesión finalizada';
        message.textContent = expiredMessage;
        remaining.textContent = '';
        continueButton.classList.add('d-none');
        redirectButton.classList.remove('d-none');
        redirectButton.href = redirectUrl;
        modal?.show();
    };

    const renewSession = async () => {
        if (heartbeatPending || sessionExpired) return;
        heartbeatPending = true;

        try {
            const response = await fetch(heartbeatUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                showExpired();
                return;
            }

            const payload = await response.json();
            deadline = Date.now() + (Math.max(1, Number(payload.expires_in) || idleTimeout) * 1000);
            hasActivity = false;
            modal?.hide();
        } catch {
            // La sesión no se extiende si el servidor no puede confirmar la actividad.
        } finally {
            heartbeatPending = false;
        }
    };

    const registerActivity = () => {
        if (!sessionExpired) hasActivity = true;
    };

    ['click', 'keydown', 'pointerdown', 'scroll', 'touchstart'].forEach((eventName) => {
        document.addEventListener(eventName, registerActivity, { passive: true });
    });

    continueButton?.addEventListener('click', () => {
        hasActivity = true;
        renewSession();
    });

    window.setInterval(() => {
        if (hasActivity && document.visibilityState === 'visible') renewSession();
    }, Math.min(15000, Math.max(5000, idleTimeout * 250)));

    window.setInterval(() => {
        if (sessionExpired) return;
        const secondsLeft = Math.max(0, Math.ceil((deadline - Date.now()) / 1000));

        if (secondsLeft === 0) {
            renewSession().then(() => {
                if (Date.now() >= deadline) showExpired();
            });
            return;
        }

        if (secondsLeft <= warningTime) {
            title.textContent = 'Tu sesión está por finalizar';
            message.textContent = 'Por seguridad, la sesión finalizará si no confirmas que deseas continuar.';
            remaining.textContent = `${secondsLeft} segundos`;
            modal?.show();
        }
    }, 1000);
}
