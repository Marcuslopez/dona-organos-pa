@extends('layouts.app')

@section('title', 'Contáctenos | DONA ÓRGANOS PANAMÁ')
@section('body_class', 'home-page contact-page')

@section('content')
<div class="topline"><div class="home-wrap"><span class="topline-brand"><span class="topline-heart">♥</span>DONA ÓRGANOS PANAMÁ</span><span>Panamá · Atención: 503-6033</span></div></div>
<header class="site-header"><nav class="navbar navbar-expand-lg" aria-label="Navegación principal"><div class="container-xl home-navigation">
<a class="navbar-brand nav-donor-mark" href="{{ route('home') }}#inicio"><img src="{{ asset('images/icono-donacion-manos-corazon.png') }}" alt="" width="48" height="48"></a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavigation"><span class="navbar-toggler-icon"></span></button>
<div class="collapse navbar-collapse" id="mainNavigation"><ul class="navbar-nav ms-auto align-items-lg-center">
<li class="nav-item"><a class="nav-link" href="{{ route('home') }}#inicio">Inicio</a></li><li class="nav-item"><a class="nav-link" href="{{ route('home') }}#aspectos-legales">Aspectos legales</a></li><li class="nav-item"><a class="nav-link" href="{{ route('home') }}#mitos">Mitos</a></li><li class="nav-item"><a class="nav-link" href="{{ route('home') }}#preguntas">Preguntas frecuentes</a></li><li class="nav-item"><a class="nav-link" href="{{ route('home') }}#historias">Testimonios</a></li><li class="nav-item"><a class="nav-link active" aria-current="page" href="{{ route('contact.create') }}">Contáctenos</a></li>
</ul><div class="nav-actions ms-lg-3"><a class="button button-primary" href="{{ route('registration.identity') }}">Registrarme</a><a class="admin-link" href="{{ auth()->check() ? route('admin.dashboard') : route('login') }}"><span aria-hidden="true">🔒</span><span>Administración</span></a></div></div>
</div></nav></header>
<main id="contenido-principal" class="contact-main"><section class="container contact-container"><div class="contact-intro"><p class="section-eyebrow">Estamos para orientarte</p><h1>Contáctanos</h1><p>Si deseas realizar una consulta, completa el siguiente formulario. Te responderemos tan pronto sea posible.</p></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="contact-card"><h2>Contacta con nosotros</h2><form method="POST" action="{{ route('contact.store') }}" novalidate>@csrf
<div class="mb-3"><label class="form-label" for="name">Nombre <small>(opcional)</small></label><input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" autocomplete="name" placeholder="Ej. María González">@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label class="form-label" for="email">Correo electrónico <span class="text-danger">*</span></label><input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label class="form-label" for="message">Escribe tu consulta <span class="text-danger">*</span></label><textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="7" maxlength="2000" required>{{ old('message') }}</textarea>@error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<input class="visually-hidden" tabindex="-1" autocomplete="off" name="website" value="">
<div class="form-check mb-4"><input class="form-check-input @error('privacy_accepted') is-invalid @enderror" type="checkbox" id="privacy_accepted" name="privacy_accepted" value="1" @checked(old('privacy_accepted')) required><label class="form-check-label" for="privacy_accepted">He leído y acepto las <button class="contact-policy-link" type="button" data-bs-toggle="modal" data-bs-target="#privacyPolicyModal">condiciones de uso y política de privacidad</button>. <span class="text-danger">*</span></label>@error('privacy_accepted')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
<div class="contact-actions">
    <button class="btn btn-primary" type="submit">Enviar consulta</button>
</div>
 <div> <aside class="contact-phone"><p>También puedes contactarnos por teléfono, de lunes a viernes de<br><strong>8:00 a. m. a 4:00 p. m.</strong></p><a href="tel:5036033">☎ <strong>503-6033</strong></a></aside></div>
</form></div>

</section></main>
<div class="modal fade" id="privacyPolicyModal" tabindex="-1" aria-labelledby="privacyPolicyTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><h2 class="modal-title fs-5" id="privacyPolicyTitle">Condiciones de uso y política de privacidad</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div><div class="modal-body"><p>Esta plantilla de Política de Privacidad está diseñada para cumplir con la <a href="https://istmodigital.net/que-exige-la-ley-81-de-proteccion-de-datos-en-panama" target="_blank" rel="noopener">Ley 81 de 2019 y el Decreto Ejecutivo 285 de 2021 de Panamá</a>, cubriendo la recopilación de datos, finalidades (gestión de consultas), y derechos ARCO. El texto establece el consentimiento expreso y medidas de seguridad técnicas para el uso de formularios de contacto. Puede obtener más información sobre la normativa en el sitio web de la Autoridad Nacional de Transparencia y Acceso a la Información (ANTAI).</p><p><a href="https://www.arispeabogado.com/p/politicas-y-privacidad.html" target="_blank" rel="noopener">Referencia de plantilla de política de privacidad</a>.</p></div><div class="modal-footer"><button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendido</button></div></div></div></div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const nameInput = document.getElementById('name');

    if (!nameInput) {
        return;
    }

    // Mientras escribe
    nameInput.addEventListener('input', function () {
        let value = this.value;

        // Solo permite letras, espacios, apóstrofe y guion
        value = value.replace(/[^\p{L}\s'-]/gu, '');

        // Permite máximo UN guion en todo el nombre
        const firstHyphen = value.indexOf('-');

        if (firstHyphen !== -1) {
            value =
                value.slice(0, firstHyphen + 1) +
                value.slice(firstHyphen + 1).replace(/-/g, '');
        }

        this.value = value;
    });

    // Cuando sale del campo
    nameInput.addEventListener('blur', function () {
        let value = this.value.trim();

        if (!value) {
            this.value = '';
            return;
        }

        // Reduce varios espacios a uno
        value = value.replace(/\s+/g, ' ');

        // Elimina guion o apóstrofe al inicio
        value = value.replace(/^['-]+/g, '');

        // Elimina guion o apóstrofe al final
        value = value.replace(/['-]+$/g, '');

        // Elimina guion/apóstrofe que tenga espacios alrededor
        value = value.replace(/\s+['-]+/g, '');
        value = value.replace(/['-]+\s+/g, ' ');

        // Normaliza todo primero a minúsculas
        value = value.toLocaleLowerCase('es');

        // Mayúscula al inicio de cada nombre,
        // después de guion y después de apóstrofe
        value = value.replace(
            /(^|[\s'-])([\p{L}])/gu,
            (match, separator, letter) =>
                separator + letter.toLocaleUpperCase('es')
        );

        this.value = value;
    });
});
</script>
@endsection
