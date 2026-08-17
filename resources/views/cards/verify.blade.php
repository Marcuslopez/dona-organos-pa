@extends('layouts.app')

@section('title', 'Verificación de carné | DONA ÓRGANOS PANAMÁ')

@section('content')
    @php
        $issuedAt = $card?->issued_at
            ? \Carbon\Carbon::parse($card->issued_at)->timezone(config('app.timezone'))
            : null;
        $meridiem = $issuedAt?->format('A') === 'AM' ? 'a. m.' : 'p. m.';
    @endphp

    <main class="registration-page">
        <div class="registration-shell">
            <section class="registration-card verified-card" aria-labelledby="cardVerificationTitle">
                <span class="verified-mark {{ $valid ? '' : 'withdrawal-mark' }}" aria-hidden="true">
                    {{ $valid ? '✓' : '!' }}
                </span>

                @if ($valid && $card)
                    <p class="auth-eyebrow verification-status-title" id="cardVerificationTitle">
                        Registro activo verificado
                    </p>
                    <p>Hola, gracias por tomar la decisión de donar vida.</p>

                    <div class="verification-summary">
                        <p>
                            <strong>Voluntad registrada:</strong>
                            {{ $issuedAt->format('d/m/Y') }}, {{ $issuedAt->format('h:i') }} {{ $meridiem }}
                        </p>
                        <p><strong>Carné:</strong> {{ $card->folio }}</p>
                    </div>

                    <p class="verification-gratitude">
                        Tu decisión puede transformar vidas <span aria-hidden="true">♥</span>
                    </p>
                @elseif ($card)
                    <h1 id="cardVerificationTitle">Carné no vigente</h1>
                    <p>Gracias por consultar este carné.</p>
                    <p><strong>Carné:</strong> {{ $card->folio }}</p>
                    <p>Actualmente, este carné no se encuentra vigente.</p>
                @else
                    <h1 id="cardVerificationTitle">Carné no identificado</h1>
                    <p>No fue posible identificar este carné.</p>
                @endif

                <a class="btn btn-primary w-100" href="{{ route('home') }}">Ir al portal</a>
            </section>
        </div>
    </main>
@endsection
