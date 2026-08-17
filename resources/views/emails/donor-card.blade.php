<!doctype html>
<html lang="es">
<body style="background:#f3f6fb;color:#243047;font-family:Arial,sans-serif;margin:0;padding:24px">
    <div style="background:#fff;border:1px solid #dbe4f0;border-radius:16px;margin:auto;max-width:620px;overflow:hidden">
        <div style="background:#15356d;color:#fff;padding:20px 28px"><strong>DONA ÓRGANOS PANAMÁ</strong></div>
        <div style="padding:28px">
            <h1 style="color:#1d1b84;font-size:24px;margin-top:0">Gracias por registrar tu voluntad</h1>
            <p>Hola {{ $card['record']->full_name }},</p>
            <p>Tu voluntad como donante fue registrada correctamente. Adjuntamos tu carné simbólico para que puedas conservarlo y compartir tu decisión con tu familia.</p>
            <p><strong>Folio:</strong> {{ $card['record']->folio }}</p>
            <p style="color:#64748b;font-size:13px">Este mensaje fue generado automáticamente. No respondas a este correo.</p>
        </div>
    </div>
</body>
</html>
