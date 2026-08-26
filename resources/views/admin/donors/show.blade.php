@extends('layouts.app')
@section('title', 'donante-'.($card?->folio ?: 'sin-folio').'-'.\Illuminate\Support\Str::slug($record->full_name))
@section('content')
<div class="admin-app donor-record-page">
    <header class="admin-header no-print"><div class="container-fluid admin-header-inner"><a class="admin-logo" href="{{ route('admin.dashboard') }}"><small>DONA ÓRGANOS PANAMÁ</small><span>Administración de donantes</span></a><div class="admin-user"><div><strong>{{ auth()->user()->name }}</strong><span>Administrador</span></div><form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="btn btn-outline-light btn-sm" type="submit">Cerrar sesión</button></form></div></div></header>
    <main class="admin-main"><div class="container-fluid admin-container">
        <div class="print-only print-heading"><strong>DONA ÓRGANOS PANAMÁ</strong><span>Expediente administrativo del donante</span></div>
        <div class="detail-actions no-print"><a class="admin-back" href="{{ route('admin.dashboard') }}">← Volver al dashboard donantes</a><button class="btn btn-primary rounded-pill" type="button" onclick="window.print()">Imprimir / Guardar PDF</button></div>
        <div class="donor-detail-heading"><div><p class="auth-eyebrow">Expediente administrativo</p><h1>Detalle del donante</h1><p>Información registrada para consulta institucional.</p></div></div>

        <section class="record-summary" aria-label="Resumen del registro">
            <div><span>Folio</span><strong>{{ $card?->folio ?: '—' }}</strong></div>
            <div><span>Fecha de registro</span><strong>{{ \Carbon\Carbon::parse($record->registered_at)->timezone('America/Panama')->format('d/m/Y H:i') }}</strong></div>
            <div><span>Estado</span><strong>{{ $record->status === 'active' ? 'Activo' : 'Baja' }}</strong></div>
        </section>

        <div class="detail-stack">
            <section class="admin-panel detail-card"><h2>Datos personales y ubicación</h2><dl class="detail-list detail-list-horizontal"><div><dt>Nombre completo</dt><dd>{{ $record->full_name }}</dd></div><div><dt>Cédula</dt><dd>{{ $record->document_number }}</dd></div><div><dt>Nacimiento</dt><dd>{{ \Carbon\Carbon::parse($record->birth_date)->format('d/m/Y') }}</dd></div><div><dt>Género</dt><dd>{{ $record->gender_name }}</dd></div><div><dt>Correo</dt><dd>{{ $record->email }}</dd></div><div><dt>Teléfono</dt><dd>+507 {{ $record->phone }}</dd></div><div><dt>Provincia</dt><dd>{{ $record->province_name }}</dd></div><div><dt>Distrito</dt><dd>{{ $record->district_name }}</dd></div><div><dt>Corregimiento</dt><dd>{{ $record->corregimiento_name }}</dd></div></dl></section>

            <section class="admin-panel detail-card"><h2>Contactos de confianza</h2><div class="contacts-grid">@forelse($contacts as $contact)<div class="contact-summary"><strong>{{ $contact->full_name }}</strong><span>{{ $contact->relationship_name }}{{ $contact->is_primary ? ' · Contacto principal' : '' }}</span><span>+507 {{ $contact->phone }} · {{ $contact->email ?: 'Sin correo' }}</span><span>Conoce la decisión: <b>{{ $contact->is_informed ? 'Sí' : 'No' }}</b></span></div>@empty<p class="detail-empty">No hay contactos registrados.</p>@endforelse</div></section>

            <section class="admin-panel detail-card"><h2>Consentimiento</h2><dl class="detail-list detail-list-horizontal consent-detail"><div><dt>Estado</dt><dd>{{ $consent?->accepted ? ($consent?->revoked_at ? 'Revocado' : 'Aceptado') : '—' }}</dd></div><div><dt>Secuencia</dt><dd>{{ $consent?->consent_sequence ?: '—' }}</dd></div><div><dt>Versión</dt><dd>{{ $consent?->version ?: '—' }}</dd></div><div><dt>Fecha de aceptación</dt><dd>{{ $consent?->accepted_at ? \Carbon\Carbon::parse($consent->accepted_at)->timezone('America/Panama')->format('d/m/Y H:i') : '—' }}</dd></div></dl></section>

            @if($record->status === 'withdrawn' || $consent?->revoked_at || $card?->revoked_at)
                <section class="admin-panel detail-card"><h2>Baja y revocación</h2><dl class="detail-list detail-list-horizontal"><div><dt>Fecha de baja</dt><dd>{{ $record->withdrawn_at ? \Carbon\Carbon::parse($record->withdrawn_at)->timezone('America/Panama')->format('d/m/Y H:i') : '—' }}</dd></div><div><dt>Consentimiento revocado</dt><dd>{{ $consent?->revoked_at ? \Carbon\Carbon::parse($consent->revoked_at)->timezone('America/Panama')->format('d/m/Y H:i') : '—' }}</dd></div><div><dt>Motivo</dt><dd>{{ $consent?->revocation_reason ?: '—' }}</dd></div><div><dt>Carné revocado</dt><dd>{{ $card?->revoked_at ? \Carbon\Carbon::parse($card->revoked_at)->timezone('America/Panama')->format('d/m/Y H:i') : '—' }}</dd></div></dl></section>
            @endif
        </div>

        <section class="admin-panel admin-card-section no-print">
            <div class="admin-card-section-heading">
                <div>
                    <p class="auth-eyebrow">Carné del donante</p>
                    <h2>{{ $card?->folio }}</h2>
                </div>
            </div>

            @if($cardView && $cardView['is_active'])
                @include('cards.partials.card', ['card' => $cardView])
                <div class="card-action-row">
                    <a class="btn btn-primary" href="{{ route('admin.donors.card.print', $record->id) }}" target="_blank" rel="noopener">Imprimir / Guardar PDF</a>
                </div>
            @else
                <div class="card-unavailable"><strong>Carné no disponible</strong><span>El registro está dado de baja o el carné fue revocado.</span></div>
            @endif
        </section>

        <nav class="detail-footer-actions no-print" aria-label="Acciones del detalle del donante">
            <a class="btn btn-primary" href="{{ route('admin.dashboard') }}">Volver al dashboard donantes</a>
        </nav>
        <footer class="print-only print-confidential">Documento confidencial para uso institucional. Impreso el {{ now()->timezone('America/Panama')->format('d/m/Y H:i') }} por {{ auth()->user()->name }}.</footer>
    </div></main>
</div>
@endsection
