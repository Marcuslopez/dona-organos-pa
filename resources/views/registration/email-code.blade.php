@extends('layouts.app')

@section('title', 'Verificar correo | DONA ÓRGANOS PANAMÁ')

@section('content')
<main class="registration-page">
    <div class="registration-shell">
        <section class="registration-card verified-card" aria-labelledby="emailCodeTitle">
            <a class="auth-brand verified-card-brand" href="{{ route('home') }}">DONA ÓRGANOS PANAMÁ</a>
            <span class="verified-mark" aria-hidden="true">✓</span>
            <h1 id="emailCodeTitle">Hola, {{ $verification['donor_name'] ?: 'donante' }}</h1>
            <p>Para proteger tu registro, enviamos un código de verificación a {{ $maskedEmail }}.</p>
            @if(session('status'))<div class="alert alert-warning">{{ session('status') }}</div>@endif
            <form method="POST" action="{{ route('registration.email-code.verify') }}" class="mx-auto text-start" style="max-width: 430px;">
                @csrf
                <label class="form-label" for="code">Código de verificación</label>
                <input class="form-control @error('code') is-invalid @enderror" id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" autofocus required>
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="registration-form-actions mt-4"><button class="btn btn-primary" type="submit">Verificar y continuar</button><a class="btn btn-primary" href="{{ route('registration.identity') }}">Volver al inicio</a></div>
            </form>
            <form method="POST" action="{{ route('registration.email-code.resend') }}" class="mt-3">@csrf<button class="btn btn-outline-primary" type="submit">Reenviar código</button></form>
        </section>
    </div>
</main>
@endsection
