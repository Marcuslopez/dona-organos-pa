@extends('layouts.app')
@section('title', 'Baja completada | DONA ÓRGANOS PANAMÁ')
@section('content')
<main class="registration-page"><div class="registration-shell"><a class="auth-brand" href="{{ route('home') }}">DONA ÓRGANOS PANAMÁ</a><section class="registration-card verified-card"><span class="verified-mark withdrawal-mark" aria-hidden="true">✓</span><p class="auth-eyebrow">Solicitud completada</p><h1>Tu registro fue dado de baja</h1><p>La voluntad, el consentimiento y el carné asociados dejaron de estar activos.</p><a class="btn btn-primary w-100" href="{{ route('home') }}">Volver al portal</a></section></div></main>
@endsection
