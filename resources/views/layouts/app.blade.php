@php
    $sessionTimeout = null;

    if (auth()->check() && request()->routeIs('admin.*')) {
        $sessionTimeout = [
            'idle' => config('admin_session.idle_timeout'),
            'warning' => config('admin_session.idle_warning'),
            'heartbeat' => route('admin.session.activity'),
            'redirect' => route('login'),
            'expired_message' => 'La sesión administrativa finalizó por inactividad. Ingresa nuevamente para continuar.',
            'redirect_label' => 'Ir al inicio de sesión',
        ];
    } elseif (session()->has('identity_verification') && request()->routeIs('registration.*')) {
        $sessionTimeout = [
            'idle' => config('donor_session.idle_timeout'),
            'warning' => config('donor_session.idle_warning'),
            'heartbeat' => route('registration.session.activity'),
            'redirect' => route('registration.identity'),
            'expired_message' => 'La sesión del donante finalizó por inactividad. Valida nuevamente tu identidad para continuar.',
            'redirect_label' => 'Validar nuevamente mi identidad',
        ];
    }
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="DONA ÓRGANOS PANAMÁ: información y registro de voluntad para la donación de órganos y tejidos.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DONA ÓRGANOS PANAMÁ')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="@yield('body_class')" @if($sessionTimeout) data-session-timeout data-idle-timeout="{{ max(1, (int) $sessionTimeout['idle']) }}" data-idle-warning="{{ max(1, (int) $sessionTimeout['warning']) }}" data-heartbeat-url="{{ $sessionTimeout['heartbeat'] }}" data-expired-redirect-url="{{ $sessionTimeout['redirect'] }}" data-expired-message="{{ $sessionTimeout['expired_message'] }}" data-expired-redirect-label="{{ $sessionTimeout['redirect_label'] }}" @endif>
    @yield('content')
    @if($sessionTimeout)
        <div class="modal fade admin-session-modal" id="sessionTimeoutModal" tabindex="-1" aria-labelledby="sessionTimeoutModalTitle" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center">
                        <div class="admin-session-clock" aria-hidden="true">◷</div>
                        <h2 class="modal-title" id="sessionTimeoutModalTitle" data-session-title>Tu sesión está por finalizar</h2>
                        <p data-session-message>Por seguridad, la sesión finalizará por inactividad.</p>
                        <strong class="admin-session-remaining" data-session-remaining aria-live="polite"></strong>
                    </div>
                    <div class="modal-footer admin-session-actions">
                        <button class="btn btn-primary" type="button" data-session-continue>Continuar sesión</button>
                        <a class="btn btn-primary d-none" href="{{ $sessionTimeout['redirect'] }}" data-session-redirect>{{ $sessionTimeout['redirect_label'] }}</a>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @stack('scripts')
</body>
</html>
