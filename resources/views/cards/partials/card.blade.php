@php($cardContacts = $card['contacts']->take(2)->values())

<div class="donor-card-pair">
    <article class="identity-card identity-card-front">
        <header>
            <strong>DONA ÓRGANOS PANAMÁ</strong>
            <span class="life-mark">♥ &nbsp; DONA VIDA</span>
        </header>

        <div class="identity-card-body">
            <h2>CARNÉ DE DONANTE<small>VOLUNTAD DE DONACIÓN DE ÓRGANOS</small></h2>

            <div class="identity-card-content">
                <dl>
                    <div class="wide">
                        <dt>Nombre completo</dt>
                        <dd>{{ $card['record']->card_name }}</dd>
                    </div>
                    <div>
                        <dt>Cédula</dt>
                        <dd>{{ $card['record']->document_number }}</dd>
                    </div>
                    <div>
                        <dt>Registro</dt>
                        <dd>{{ \Carbon\Carbon::parse($card['record']->issued_at)->timezone('America/Panama')->format('d/m/Y') }}</dd>
                    </div>
                </dl>

                <div class="identity-card-qr">
                    <img src="{{ $card['qr'] }}" alt="Código QR para verificar el carné">
                    <small>ID: {{ $card['record']->folio }}</small>
                </div>
            </div>
        </div>

        <footer>
            <span>“He decidido compartir vida.”</span>
            <span>Confirma y comparte tu decisión</span>
        </footer>
    </article>

    <article class="identity-card identity-card-back">
        <header><strong>♥ &nbsp; COMPARTE TU DECISIÓN</strong></header>

        <div class="identity-card-body">
            <p>El paso más importante es <strong>informar a tu familia</strong> sobre tu decisión de ser donante.</p>
            <h2>CONTACTOS INFORMADOS</h2>

            <div class="card-contact-list">
                @for($index = 0; $index < 2; $index++)
                    @php($contact = $cardContacts->get($index))
                    <div>
                        <span><b>Nombre:</b>&nbsp;{{ $contact?->card_name ?? '' }}</span>
                        <span><b>Tel.:</b>&nbsp;{{ $contact ? '+507 '.$contact->phone : '' }}</span>
                    </div>
                @endfor
            </div>

            <div class="card-back-bottom">
                <img src="{{ $card['qr'] }}" alt="Código QR de verificación">
                <span>
                    Escanea este código para verificar este carné simbólico<br>
                    y conocer más sobre el programa de donación.
                </span>
            </div>
        </div>
    </article>
</div>
