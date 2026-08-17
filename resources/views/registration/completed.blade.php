@extends('layouts.app')
@section('title', 'Registro completado | DONA ÓRGANOS PANAMÁ')
@section('content')
@php($isUpdate = ($registration['completion_type'] ?? null) === 'update')
<main class="registration-page card-completed-page"><div class="registration-shell registration-shell-card"><a class="auth-brand" href="{{ route('home') }}">DONA ÓRGANOS PANAMÁ</a><section class="registration-card verified-card"><span class="verified-mark" aria-hidden="true">✓</span><p class="auth-eyebrow">{{ $isUpdate ? 'Actualización completada' : 'Registro completado' }}</p><h1>{{ $isUpdate ? 'Tus datos fueron actualizados' : 'Tu voluntad fue registrada' }}</h1><p>{{ $isUpdate ? 'Los cambios quedaron registrados correctamente.' : 'Conserva tu carné y conversa con tus contactos sobre tu decisión.' }}</p>
    @if(!$isUpdate || $registration['card_reissued'])
        <div class="alert {{ $registration['email_sent'] ? 'alert-success' : 'alert-warning' }} text-start" role="status">{{ $registration['email_sent'] ? 'Enviamos una copia del carné a '.$registration['masked_email'].'.' : 'Los cambios se guardaron, pero el correo no pudo enviarse. Puedes imprimir o guardar el carné ahora.' }}</div>
    @else
        <div class="alert alert-success text-start" role="status">Tus cambios no afectaron el carné vigente, por lo que no fue necesario emitir uno nuevo.</div>
    @endif
    @if($card)
        @include('cards.partials.card', ['card' => $card])
    @else
        <div class="folio-display">{{ $registration['folio'] }}</div>
    @endif
    <nav class="completed-actions" aria-label="Acciones posteriores al registro">
        @if($card)
            <a class="btn btn-primary" href="{{ $cardPrintUrl }}" target="_blank" rel="noopener">Imprimir / Guardar PDF</a>
        @endif
        <a class="btn btn-primary" href="{{ route('home') }}">Volver al inicio</a>
    </nav>
</section></div></main>
@endsection
