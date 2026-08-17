@extends('layouts.app')

@section('title', 'Validación de identidad | DONA ÓRGANOS PANAMÁ')

@section('content')
<main class="registration-page">
    <div class="registration-shell">
        <section class="registration-card" aria-labelledby="identityTitle">
            <a class="auth-brand identity-card-brand" href="{{ route('home') }}">DONA ÓRGANOS PANAMÁ</a>
            <div class="step-indicator"><span>Paso 1</span> de 2</div>
            <h1 id="identityTitle">Validación de identidad</h1>
            <p class="registration-intro">Ingresa los datos tal como aparecen en tu cédula para continuar con el registro.</p>

            @if (session('identity_retry_after'))
                <div class="login-countdown" data-identity-countdown="{{ (int) session('identity_retry_after') }}" role="status" aria-live="polite">
                    <strong>Validación pausada temporalmente</strong>
                    <span>Podrás volver a intentar en <b data-countdown-value>{{ (int) session('identity_retry_after') }}</b> segundos.</span>
                </div>
            @endif

            <form method="POST" action="{{ route('registration.identity.store') }}" data-identity-form novalidate>
                @csrf
                <div class="mb-4">
                    <label class="form-label" for="document_number">Cédula de identidad <span aria-hidden="true">*</span></label>
                    <input class="form-control @error('document_number') is-invalid @enderror" id="document_number" name="document_number" type="text" value="{{ old('document_number') }}" placeholder="Ej: 8-1234-12345" maxlength="18" pattern="[A-Za-z0-9-]+" autocomplete="off" autocapitalize="characters" spellcheck="false" required>
                    <div class="form-text">Formatos admitidos: 1–13, PE, E o N. Incluye siempre los guiones.</div>
                    @error('document_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label" for="document_code">Código posterior de la cédula <span aria-hidden="true">*</span></label>
                    <input class="form-control @error('document_code') is-invalid @enderror" id="document_code" name="document_code" type="text" placeholder="Ej: 47KLMNP02861" minlength="9" maxlength="12" autocomplete="off" required>
                    <div class="form-text">Está al reverso de la cédula. Debe contener entre 9 y 12 letras y números, sin espacios ni guiones.</div>
                    @error('document_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label" for="captcha">Código de seguridad <span aria-hidden="true">*</span></label>
                    <div class="captcha-box">
                        <img src="{{ route('registration.captcha.image', ['v' => now()->timestamp]) }}" width="235" height="65" alt="Código CAPTCHA de seis caracteres" data-captcha-image>
                        <button class="btn btn-outline-primary captcha-refresh" type="button" data-captcha-refresh aria-label="Generar un nuevo código de seguridad">↻ <span>Otro código</span></button>
                    </div>
                    <input class="form-control @error('captcha') is-invalid @enderror" id="captcha" name="captcha" type="text" value="" minlength="6" maxlength="6" autocomplete="off" autocapitalize="none" spellcheck="false" aria-describedby="captchaHelp" required>
                    <div class="form-text" id="captchaHelp">Escribe los seis caracteres que aparecen en la imagen.</div>
                    @error('captcha')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <noscript><p class="form-text">Para generar otro código, actualiza esta página.</p></noscript>
                </div>
                <nav class="identity-form-actions" aria-label="Acciones de validación">
                    <button class="btn btn-primary" type="submit">Continuar</button>
                    <a class="btn btn-primary" href="{{ route('home') }}">Inicio</a>
                </nav>
            </form>
        </section>
        <p class="registration-security">Tus datos se utilizan únicamente para validar la identidad y no se muestran públicamente.</p>
    </div>
</main>
@endsection
