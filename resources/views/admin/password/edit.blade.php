@extends('layouts.app')

@section('title', 'Cambiar contraseña | DONA ÓRGANOS PANAMÁ')

@section('content')
<main class="auth-page">
    <div class="auth-shell">
    <section class="auth-card" aria-labelledby="passwordTitle">
        <a class="auth-brand" href="{{ route('home') }}">DONA ÓRGANOS PANAMÁ</a>
        <p class="auth-eyebrow">SEGURIDAD DE LA CUENTA</p>
        <h1 id="passwordTitle">Crea tu contraseña personal</h1>
        <p class="auth-intro">La contraseña temporal debe reemplazarse antes de acceder al panel administrativo.</p>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ route('admin.password.update') }}">
            @csrf
            @method('PUT')
            @unless(auth()->user()->must_change_password)<div class="mb-3"><label class="form-label" for="current_password">Contraseña actual</label><input class="form-control" id="current_password" name="current_password" type="password" autocomplete="current-password" required></div>@endunless
            <div class="mb-3"><label class="form-label" for="password">Nueva contraseña</label><input class="form-control" id="password" name="password" type="password" autocomplete="new-password" required></div>
            <div class="mb-3"><label class="form-label" for="password_confirmation">Confirmar contraseña</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
            <p class="form-text">Mínimo 12 caracteres, con mayúsculas, minúsculas y números.</p>
            <div class="auth-form-actions">
                <button class="btn btn-primary" type="submit">Guardar contraseña</button>
                <button class="btn btn-primary" form="logoutForm" type="submit">Cerrar sesión</button>
            </div>
        </form>
        <form id="logoutForm" method="POST" action="{{ route('admin.logout') }}">@csrf</form>
    </section>
    </div>
</main>
@endsection
