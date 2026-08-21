<!doctype html><html lang="es"><body style="font-family:Arial,sans-serif;color:#20324d;line-height:1.55">
<h2 style="color:#171d81">📥 Notificación de Nueva Consulta <small>(Uso Interno)</small></h2>
<p>Estimado equipo de Administración / Soporte:</p>
<p>Se ha recibido una nueva consulta a través del formulario de contacto del sitio web. A continuación, se detallan los datos del usuario para su debida atención:</p>
<h3>👤 Datos del Remitente</h3>
<ul><li><strong>Nombre:</strong> {{ $inquiry->name ?: 'Sin nombre' }}</li><li><strong>Correo electrónico:</strong> {{ $inquiry->email }}</li></ul>
<h3>💬 Detalle de la Consulta</h3><blockquote style="white-space:pre-wrap;border-left:4px solid #4d72d9;padding-left:12px">{{ $inquiry->message }}</blockquote><hr>
</body></html>
