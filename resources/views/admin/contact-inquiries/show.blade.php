@extends('layouts.app')

@section('title', 'Consulta #'.$inquiry->id.' | DONA ÓRGANOS PANAMÁ')
@section('body_class', 'admin-page')

@section('content')
<div class="admin-app">
    <header class="admin-header">
        <div class="container-fluid admin-header-inner">
            <a class="admin-logo" href="{{ route('admin.contact-inquiries.index') }}">
                <small>DONA ÓRGANOS PANAMÁ</small>
                <span>Mantenimiento de consultas</span>
            </a>

            <div class="admin-user">
                <div>
                    <strong>{{ auth()->user()->name }}</strong>
                    <span>{{ auth()->user()->isMaster() ? 'Usuario master' : 'Administrador' }}</span>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="btn btn-outline-light btn-sm" type="submit">Cerrar sesión</button>
                </form>
            </div>
        </div>
    </header>

    <main class="admin-main">
        <div class="container contact-admin-main">
            <section class="contact-inquiry-heading">
                <div>
                    <p class="contact-admin-eyebrow">GESTIÓN ADMINISTRATIVA</p>
                    <h1>Consulta #{{ $inquiry->id }}</h1>
                    <p>Consulta recibida para atención y seguimiento administrativo.</p>
                </div>
                <span class="contact-status contact-status-{{ $inquiry->status }}">{{ $inquiry->statusLabel() }}</span>
            </section>

            <section class="contact-inquiry-summary" aria-label="Resumen de la consulta">
                <div><span>RECIBIDA</span><strong>{{ $inquiry->created_at->format('d/m/Y h:i a') }}</strong></div>
                <div><span>ESTADO</span><strong>{{ $inquiry->statusLabel() }}</strong></div>
                <div><span>ASIGNADA A</span><strong>{{ $inquiry->assignee?->name ?? 'Sin asignar' }}</strong></div>
            </section>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="contact-detail-grid">
                <section class="admin-detail-card">
                    <h2>Datos del remitente</h2>
                    <dl>
                        <div><dt>Nombre</dt><dd>{{ $inquiry->name ?: 'Sin nombre' }}</dd></div>
                        <div><dt>Correo electrónico</dt><dd><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></dd></div>
                        <div><dt>Estado</dt><dd>{{ $inquiry->statusLabel() }}</dd></div>
                        <div><dt>Asignada a</dt><dd>{{ $inquiry->assignee?->name ?? 'Sin asignar' }}</dd></div>
                    </dl>
                </section>

                <section class="admin-detail-card">
                    <h2>Consulta recibida</h2>
                    <p class="contact-message">{{ $inquiry->message }}</p>
                </section>
            </div>

            @if(auth()->user()->isMaster())
                <section class="admin-detail-card contact-assignment">
                    <h2>Asignación administrativa</h2>
                    <form method="POST" action="{{ route('admin.contact-inquiries.assign', $inquiry) }}" class="contact-inline-form">
                        @csrf
                        <label class="visually-hidden" for="assigned_to">Administrador responsable</label>
                        <select class="form-select" id="assigned_to" name="assigned_to" required>
                            <option value="">Selecciona un administrador</option>
                            @foreach($administrators as $administrator)
                                <option value="{{ $administrator->id }}" @selected($inquiry->assigned_to === $administrator->id)>{{ $administrator->name }} · {{ $administrator->email }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary" type="submit">Asignar responsable</button>
                    </form>
                </section>
            @elseif(!$inquiry->assigned_to && !in_array($inquiry->status, ['respondida', 'cerrada'], true))
                <section class="admin-detail-card contact-assignment">
                    <h2>Atención de consulta</h2>
                    <p>La consulta aún no está asignada. Puedes tomarla para responderla.</p>
                    <form method="POST" action="{{ route('admin.contact-inquiries.take', $inquiry) }}">
                        @csrf
                        <button class="btn btn-primary" type="submit">Tomar consulta</button>
                    </form>
                </section>
            @endif

            @if($inquiry->assigned_to === auth()->id())
                <section class="admin-detail-card contact-response">
                    <h2>Respuesta al remitente</h2>
                    @if(in_array($inquiry->status, ['respondida', 'cerrada'], true))
                        <p>Esta consulta ya fue respondida el {{ optional($inquiry->responded_at)->format('d/m/Y h:i a') }}.</p>
                    @else
                        <form method="POST" action="{{ route('admin.contact-inquiries.respond', $inquiry) }}">
                            @csrf
                            <label class="form-label" for="response">Escribe la respuesta <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('response') is-invalid @enderror" id="response" name="response" rows="7" maxlength="4000" required>{{ old('response') }}</textarea>
                            @error('response')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="admin-bottom-actions"><button class="btn btn-primary" type="submit">Enviar respuesta</button></div>
                        </form>
                    @endif
                </section>
            @elseif($inquiry->assigned_to && !in_array($inquiry->status, ['respondida', 'cerrada'], true))
                <section class="admin-detail-card contact-response">
                    <h2>Respuesta al remitente</h2>
                    <p class="mb-0">Esta consulta está asignada a <strong>{{ $inquiry->assignee?->name }}</strong>. Para responderla, asígnatela primero desde la sección de asignación administrativa.</p>
                </section>
            @endif

            @if($inquiry->status === 'respondida' && ($inquiry->assigned_to === auth()->id() || auth()->user()->isMaster()))
                <form class="admin-bottom-actions" method="POST" action="{{ route('admin.contact-inquiries.close', $inquiry) }}">
                    @csrf
                    <button class="btn btn-primary" type="submit">Cerrar consulta</button>
                </form>
            @endif

            @if($inquiry->replies->isNotEmpty())
                <section class="admin-detail-card">
                    <h2>Respuesta enviada</h2>
                    @foreach($inquiry->replies as $reply)
                        <article class="contact-reply">
                            <strong>{{ $reply->author?->name ?? 'Administrador' }}</strong>
                            <small>{{ optional($reply->sent_at)->format('d/m/Y h:i a') }}</small>
                            <p>{{ $reply->body }}</p>
                        </article>
                    @endforeach
                </section>
            @endif

            <details class="admin-detail-card contact-history">
                <summary>Historial de la consulta</summary>
                <ul>
                    @foreach($inquiry->history as $item)
                        <li><strong>{{ $item->created_at->format('d/m/Y h:i a') }}</strong> · {{ $item->actor?->name ?? 'Sistema' }} · {{ $item->action }}</li>
                    @endforeach
                </ul>
            </details>

            <div class="admin-bottom-actions">
                <a class="btn btn-primary" href="{{ route('admin.contact-inquiries.index') }}">Volver a consultas</a>
                <a class="btn btn-primary" href="{{ route('admin.dashboard') }}">Ir al dashboard</a>
            </div>
        </div>
    </main>
</div>
@endsection
