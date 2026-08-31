@extends('layouts.app')

@section('title', 'Acceso administrativo | DONA ÓRGANOS PANAMÁ')

@section('content')
@php
    $challenge = session('admin_login');
    $codeVerified = is_array($challenge) && filled($challenge['code_verified_at'] ?? null);
    $codeSent = is_array($challenge) && ! $codeVerified;
@endphp
<main class="auth-page">
    <div class="auth-shell">
        <section class="auth-card" aria-labelledby="loginTitle">
            <a class="auth-brand" href="{{ route('home') }}">DONA ÓRGANOS PANAMÁ</a>
            <p class="auth-eyebrow">Área protegida</p>
            <h1 id="loginTitle">Acceso administrativo</h1>
            <p class="auth-intro">@if($codeVerified) Ingresa tu contraseña para completar el acceso. @elseif($codeSent) Revisa tu correo e ingresa el código de seis dígitos recibido. @else Ingresa tu correo autorizado para solicitar un código de verificación. @endif</p>
            @if (session('status'))<div class="alert alert-warning" role="status">{{ session('status') }}</div>@endif

            @if (! $codeSent && ! $codeVerified)
                <form method="POST" action="{{ route('login.code.send') }}" data-login-form data-email-code-request novalidate>
                    @csrf
                    <div class="mb-4"><label class="form-label" for="email">Correo electrónico</label><input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" autofocus required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="auth-form-actions"><button class="btn btn-primary auth-submit" type="submit" disabled>Enviar código</button><a class="btn btn-primary auth-return" href="{{ route('home') }}">Volver al inicio</a></div>
                </form>
            @elseif($codeSent)
                <form method="POST" action="{{ route('login.code.verify') }}" data-login-form data-verification-code novalidate>
                    @csrf
                    <p class="form-text mb-3">Código enviado a {{ \Illuminate\Support\Str::mask($challenge['email'], '*', 2, max(strlen($challenge['email']) - 6, 1)) }}.</p>
                    <div class="mb-4"><label class="form-label" for="code">Código de verificación</label><input class="form-control @error('code') is-invalid @enderror" id="code" name="code" type="tel" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" autofocus required><div class="form-text">Ingresa los seis dígitos recibidos.</div>@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="auth-form-actions"><button class="btn btn-primary auth-submit" type="submit" disabled>Verificar código</button><a class="btn btn-primary auth-return" href="{{ route('login', ['reiniciar' => 1]) }}">Usar otro correo</a></div>
                </form>
                <form method="POST" action="{{ route('login.code.resend') }}" class="mt-3 text-end">@csrf<button class="btn btn-outline-primary" type="submit">Reenviar código</button></form>
            @else
                <form method="POST" action="{{ route('login.store') }}" data-login-form novalidate>
                    @csrf
                    <input name="email" type="hidden" value="{{ $challenge['email'] }}">
                    <div class="mb-3"><label class="form-label">Correo electrónico</label><input class="form-control" value="{{ $challenge['email'] }}" disabled></div>
                    <div class="mb-4"><label class="form-label" for="password">Contraseña</label><input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" autocomplete="current-password" autofocus required>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="auth-form-actions"><button class="btn btn-primary auth-submit" type="submit">Ingresar</button><a class="btn btn-primary auth-return" href="{{ route('login', ['reiniciar' => 1]) }}">Volver</a></div>
                </form>
            @endif
        </section>
        <p class="auth-security-note">El acceso y los intentos fallidos se registran por motivos de seguridad.</p>
    </div>
</main>
@endsection
