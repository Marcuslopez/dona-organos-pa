@extends('layouts.app')

@section('title', 'Identidad validada | DONA ÓRGANOS PANAMÁ')

@section('content')
<main class="registration-page">
    <div class="registration-shell {{ ($verification['donor_status'] ?? null) === 'active' ? 'registration-shell-card' : '' }}">
        <section class="registration-card verified-card" aria-labelledby="verifiedTitle">
            <a class="auth-brand verified-card-brand" href="{{ route('home') }}">DONA ÓRGANOS PANAMÁ</a>
            <span class="verified-mark" aria-hidden="true">✓</span>
            @if (($verification['donor_status'] ?? null) === 'active')
                <h1 id="verifiedTitle">Hola, {{ $card && $card['record']->first_name ? $card['record']->first_name : 'donante' }}. Gracias por ser donante activo</h1>
                <p>Tu voluntad de donar permanece vigente. Conserva tu carné y comparte esta importante decisión con tu familia.</p>
            @elseif (($verification['donor_status'] ?? null) === 'withdrawn')
                @php
                    $withdrawnAt = $donor?->withdrawn_at
                        ? \Carbon\Carbon::parse($donor->withdrawn_at)->timezone('America/Panama')
                        : null;
                    $withdrawnPeriod = $withdrawnAt?->format('A') === 'AM' ? 'a. m.' : 'p. m.';
                @endphp
                <h1 id="verifiedTitle">Hola, {{ $donor?->first_name ?: 'donante' }}</h1>
                <p>Tu registro de consentimiento para donar órganos y el carné asociado se encuentran dados de baja.</p>
                <dl class="withdrawal-summary">
                    <div>
                        <dt>Folio:</dt>
                        <dd>{{ $withdrawnCard?->folio ?: 'No disponible' }}</dd>
                    </div>
                    <div>
                        <dt>Fecha:</dt>
                        <dd>{{ $withdrawnAt ? $withdrawnAt->format('d/m/Y') : 'No disponible' }}</dd>
                    </div>
                    <div>
                        <dt>Hora:</dt>
                        <dd>{{ $withdrawnAt ? $withdrawnAt->format('h:i:s').' '.$withdrawnPeriod : 'No disponible' }}</dd>
                    </div>
                </dl>
                <p>Puedes registrar nuevamente tu voluntad. Revisarás tus datos, aceptarás un nuevo consentimiento y recibirás un carné nuevo.</p>
            @else
                <h1 id="verifiedTitle">Identidad validada correctamente</h1>
                <p>La identidad puede continuar con un nuevo registro de donante.</p>
            @endif
            <dl class="verification-summary {{ empty($verification['expires_at']) ? 'verification-summary-single' : '' }}">
                <div><dt>Documento:</dt><dd>{{ $verification['document_number'] }}</dd></div>
                @if (! empty($verification['expires_at']))
                    <div>
                        <dt>Sesión activa por:</dt>
                        <dd id="identitySessionCountdown" data-expires-at="{{ $verification['expires_at'] }}">--:--</dd>
                    </div>
                @endif
            </dl>
            @if (($verification['donor_status'] ?? null) === 'active')
                @if ($card && $cardPrintUrl)
                    <section class="verified-donor-card" aria-labelledby="currentCardTitle">
                        <div class="verified-donor-card-heading">
                            <div>
                                <p class="auth-eyebrow mb-1">Carné vigente</p>
                                <h2 id="currentCardTitle">{{ $card['record']->folio }}</h2>
                            </div>
                        </div>
                        @include('cards.partials.card', ['card' => $card])
                    </section>
                @endif
                <nav class="verified-donor-actions" aria-label="Acciones del donante">
                    @if ($card && $cardPrintUrl)
                        <a class="btn btn-primary" href="{{ $cardPrintUrl }}" target="_blank" rel="noopener">Imprimir / Descargar PDF</a>
                    @endif
                    <a class="btn btn-outline-primary" href="{{ route('registration.update.form') }}">Actualizar datos</a>
                    <button class="btn btn-danger" type="button" data-bs-toggle="modal" data-bs-target="#withdrawalConfirmation">Darme de baja</button>
                    <a class="btn btn-outline-secondary" href="{{ route('home') }}">Volver al inicio</a>
                </nav>
            @elseif (($verification['donor_status'] ?? null) !== 'withdrawn')
                <a class="btn btn-primary w-100" href="{{ route('registration.form') }}">Continuar al formulario</a>
            @else
                <a class="btn btn-primary w-100" href="{{ route('registration.reactivation.form') }}">Registrar nuevamente mi voluntad</a>
            @endif
            @if (($verification['donor_status'] ?? null) !== 'active')
                <a class="registration-cancel" href="{{ route('home') }}">Volver al portal</a>
            @endif
        </section>
    </div>

    @if (($verification['donor_status'] ?? null) === 'active')
        <div class="modal fade withdrawal-modal" id="withdrawalConfirmation" tabindex="-1" aria-labelledby="withdrawalConfirmationTitle" aria-describedby="withdrawalConfirmationDescription" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="withdrawal-modal-brand">DONA ÓRGANOS PANAMÁ</div>
                    <div class="modal-body">
                        <span class="withdrawal-modal-icon" aria-hidden="true">!</span>
                        <p class="auth-eyebrow">Confirmación de baja</p>
                        <h2 id="withdrawalConfirmationTitle">¿Deseas dar de baja tu registro?</h2>
                        <p id="withdrawalConfirmationDescription">Tu voluntad dejará de figurar como activa y el carné vigente será revocado. Para volver a ser donante tendrás que realizar un nuevo proceso de activación.</p>
                        <div class="withdrawal-modal-document"><span>Documento</span><strong>{{ $verification['document_number'] }}</strong></div>
                        <div class="form-check withdrawal-check withdrawal-modal-check text-start">
                            <input class="form-check-input" id="confirmWithdrawal" name="confirm_withdrawal" type="checkbox" value="1" form="withdrawalForm" required>
                            <label class="form-check-label" for="confirmWithdrawal">Confirmo voluntariamente que deseo dar de baja mi registro.</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-danger" id="confirmWithdrawalButton" type="submit" form="withdrawalForm" disabled>Confirmar baja</button>
                        <button class="btn btn-success" type="button" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
        <form id="withdrawalForm" method="POST" action="{{ route('registration.withdraw') }}">@csrf</form>
    @endif

    <div class="modal fade session-expired-modal" id="identitySessionExpired" tabindex="-1" aria-labelledby="identitySessionExpiredTitle" aria-hidden="true" data-redirect-url="{{ route('registration.identity') }}">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="withdrawal-modal-brand">DONA ÓRGANOS PANAMÁ</div>
                <div class="modal-body text-center">
                    <span class="withdrawal-modal-icon session-expired-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" role="presentation">
                            <circle cx="12" cy="12" r="8.5"></circle>
                            <path d="M12 7.5v5l3.25 2"></path>
                        </svg>
                    </span>
                    <h2 id="identitySessionExpiredTitle">Sesión finalizada</h2>
                    <p>Por seguridad, el tiempo disponible terminó. Debes validar nuevamente tu identidad para continuar.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button class="btn btn-primary px-5" id="identitySessionExpiredButton" type="button">OK</button>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
