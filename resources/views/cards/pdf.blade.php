<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; size: letter landscape; }
        * { box-sizing: border-box; }
        body { color: #172554; font-family: DejaVu Sans, sans-serif; margin: 0; }
        .sheet { height: 215.9mm; position: relative; width: 279.4mm; }
        .card { background: #fff; border: .25mm solid #94a3b8; border-radius: 3mm; height: 53.98mm; overflow: hidden; position: absolute; top: 20mm; width: 85.60mm; }
        .front { left: 45mm; }
        .back { left: 138.60mm; }
        .layout { border-collapse: collapse; height: 53.48mm; table-layout: fixed; width: 100%; }
        .front-layout { height: 46.48mm; }
        .layout > tbody > tr > td { padding: 0; }
        .header-cell { border-bottom: 1mm solid #15356d; height: 12mm; padding: 0 4mm !important; vertical-align: middle; }
        .brand { color: #15356d; font-size: 7.5pt; font-weight: bold; }
        .life { color: #b4233c; float: right; font-size: 6.4pt; font-weight: bold; }
        .front-body { height: 34.48mm; padding: 2.3mm 4mm !important; vertical-align: top; }
        .front-title { color: #15356d; font-size: 9pt; font-weight: bold; line-height: 1.05; margin-bottom: 1.7mm; }
        .front-title small { display: block; font-size: 5pt; }
        .content-table { border-collapse: collapse; table-layout: fixed; width: 100%; }
        .content-table td { padding: 0; vertical-align: top; }
        .fields-cell { width: 55mm; }
        .qr-cell { padding-left: 2mm !important; text-align: center; width: 22mm; }
        .front-qr-wrap { position: relative; top: -5.5mm; }
        .field-table { border-collapse: separate; border-spacing: 0 1mm; table-layout: fixed; width: 100%; }
        .field-table td { background: #f1f5f9; border-left: 1mm solid #fff; height: 7.4mm; padding: .9mm 1.5mm; }
        .field-table td:first-child { border-left: 0; }
        .label { color: #334155; font-size: 4.5pt; font-weight: bold; text-transform: uppercase; }
        .value { color: #172554; font-size: 6.4pt; font-weight: bold; line-height: 1.15; margin-top: .4mm; }
        .qr-cell img { display: block; height: 19mm; margin: 0 auto; width: 19mm; }
        .qr-cell small { color: #475569; display: block; font-size: 5.50pt; margin-top: .2mm; }
        .card-footer { background: #15356d; bottom: 0; color: #fff; font-size: 6.00pt; height: 7mm; left: 0; padding: 2.1mm 4mm 0; position: absolute; width: 100%; }
        .card-footer span:last-child { float: none; position: absolute; right: 12mm; }
        .back .header-cell { text-align: center; }
        .back-body { height: 41.48mm; padding: .7mm 4mm 2mm !important; vertical-align: top; }
        .back-message { background: #f1f5f9; color: #15356d; font-size: 7.00pt; line-height: 1.35; margin-bottom: .8mm; padding: 1mm 1.6mm; text-align: left; }
        .back-heading { color: #b4233c; font-size: 5pt; font-weight: bold; margin-bottom: .2mm; }
        .contacts-table { border-collapse: collapse; table-layout: fixed; width: 100%; }
        .contacts-table td { border-bottom: .4mm dotted #15356d; color: #15356d; font-size: 6.50pt; height: 5.2mm; overflow: hidden; padding: .3mm .3mm .3mm 0; white-space: nowrap; }
        .contacts-table .contact-name { width: 47mm; }
        .contacts-table .contact-phone {padding-left: 10.00mm;}
        .contacts-table b { color: #15356d; }
        .back-qr { bottom: 2mm; height: 9mm; left: 4mm; position: absolute; width: 9mm; }
        .back-qr img { display: block; height: 9mm; width: 9mm; }
        .back-copy { bottom: 2.8mm; color: #15356d; font-size: 6.50pt; left: 14.2mm; line-height: 1.35; position: absolute; white-space: nowrap; }
    </style>
</head>
<body>
    <main class="sheet">
        <article class="card front">
            <table class="layout front-layout">
                <tr><td class="header-cell"><span class="brand">DONA ÓRGANOS PANAMÁ</span><span class="life">♥ &nbsp; DONA VIDA</span></td></tr>
                <tr><td class="front-body">
                    <div class="front-title">CARNÉ DE DONANTE<small>VOLUNTAD DE DONACIÓN DE ÓRGANOS</small></div>
                    <table class="content-table"><tr>
                        <td class="fields-cell"><table class="field-table">
                            <tr><td colspan="2"><div class="label">Nombre completo</div><div class="value">{{ $card['record']->card_name }}</div></td></tr>
                            <tr><td><div class="label">Cédula</div><div class="value">{{ $card['record']->document_number }}</div></td><td><div class="label">Registro</div><div class="value">{{ \Carbon\Carbon::parse($card['record']->issued_at)->timezone('America/Panama')->format('d/m/Y') }}</div></td></tr>
                        </table></td>
                        <td class="qr-cell"><div class="front-qr-wrap"><img src="{{ $card['qr'] }}"><b><small>ID: {{ $card['record']->folio }}</small></b></div></td>
                    </tr></table>
                </td></tr>
            </table>
            <footer class="card-footer"><span>“He decidido compartir vida.”</span><span>Confirma y comparte tu decisión</span></footer>
        </article>

        <article class="card back">
            <table class="layout">
                <tr><td class="header-cell"><span class="brand">♥ &nbsp; COMPARTE TU DECISIÓN</span></td></tr>
                <tr><td class="back-body">
                    <div class="back-message">El paso más importante es <b>informar a tu familia</b> sobre tu decisión de ser donante.</div>
                    <div class="back-heading">CONTACTOS INFORMADOS</div>
                    @php($contacts = $card['contacts']->take(2)->values())
                    <table class="contacts-table">
                        @for($index = 0; $index < 2; $index++)
                            @php($contact = $contacts->get($index))
                            <tr>
                                <td class="contact-name"><b>Nombre:&nbsp;{{ $contact?->card_name ?? '' }}</b></td>
                                <td class="contact-phone"><b>Tel.:&nbsp;{{ $contact ? '+507 '.$contact->phone : '' }}</b></td>
                            </tr>
                        @endfor
                    </table>
                </td></tr>
            </table>
            <div class="back-qr"><img src="{{ $card['qr'] }}"></div>
            <div class="back-copy">Escanea este código para verificar este carné simbólico<br>y conocer más sobre el programa de donación.</div>
        </article>
    </main>
</body>
</html>
