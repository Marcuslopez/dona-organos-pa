<!doctype html><html lang="es"><body style="font-family:Arial,sans-serif;color:#20324d;line-height:1.55">
<h2 style="color:#171d81">DONA ÓRGANOS PANAMÁ</h2>
<p>Hola{{ $inquiry->name ? ', '.$inquiry->name : '' }}:</p>
<p>Respondemos a tu consulta:</p>
<blockquote style="white-space:pre-wrap;border-left:4px solid #4d72d9;padding-left:12px">{{ $reply->body }}</blockquote>
<p>Saludos.</p>
</body></html>
