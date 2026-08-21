@extends('layouts.app')

@section('title', 'Acceso administrativo | DONA ÓRGANOS PANAMÁ')

@section('content')
<main class="auth-page">
    <div class="auth-shell">
        <section class="auth-card" aria-labelledby="loginTitle">
            <a class="auth-brand" href="{{ route('home') }}">DONA ÓRGANOS PANAMÁ</a>
            <p class="auth-eyebrow">Área protegida</p>
            <h1 id="loginTitle">Acceso administrativo</h1>
            <p class="auth-intro">Ingresa con las credenciales autorizadas para continuar.</p>

            @if (session('status'))
                <div class="alert alert-warning" role="status">{{ session('status') }}</div>
            @endif

            @if (session('login_retry_after'))
                <div class="login-countdown" data-login-countdown="{{ (int) session('login_retry_after') }}" role="status" aria-live="polite">
                    <strong>Acceso pausado temporalmente</strong>
                    <span>Podrás volver a intentar en <b data-countdown-value>{{ (int) session('login_retry_after') }}</b> segundos.</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" data-login-form novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">Correo electrónico</label>
                    <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" autofocus required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label" for="password">Contraseña</label>
                    <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" autocomplete="current-password" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="auth-form-actions">
                    <button class="btn btn-primary auth-submit" type="submit" data-login-submit>Ingresar</button>
                    <a class="btn btn-primary auth-return" href="{{ route('home') }}">Volver al inicio</a>
                </div>
            </form>
        </section>
        <p class="auth-security-note">El acceso y los intentos fallidos pueden ser registrados por motivos de seguridad.</p>
    </div>
</main>
@endsection
