@extends('layouts.app')

@section('title', 'Mantenimiento de consultas | DONA ÓRGANOS PANAMÁ')
@section('body_class', 'admin-page')

@section('content')
<div class="admin-app">
    <header class="admin-header">
        <div class="container-fluid admin-header-inner">
            <a class="admin-logo" href="{{ route('admin.contact-inquiries.index') }}">
                <small>DONA ÓRGANOS PANAMÁ</small>
                <span>GESTIÓN ADMINISTRATIVA</span>
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
            <section class="contact-admin-intro-panel">
                <h1>Mantenimiento de consultas</h1>
            </section>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form class="admin-filters contact-admin-filters" method="GET">
                <div>
                    <label for="q">Nombre o correo</label>
                    <input class="form-control" id="q" name="q" value="{{ $search }}" placeholder="Buscar consulta">
                </div>
                <div>
                    <label for="status">Estado</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Todos</option>
                        @foreach(\App\Models\ContactInquiry::STATUSES as $item)
                            <option value="{{ $item }}" @selected($status === $item)>{{ \App\Models\ContactInquiry::labelFor($item) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-filter-actions">
                    <button class="btn btn-primary" type="submit">Buscar</button>
                    <a class="btn btn-outline-primary" href="{{ route('admin.contact-inquiries.index') }}">Limpiar</a>
                </div>
            </form>

            <section class="admin-table-wrap">
                <div class="table-responsive">
                    <table class="table admin-table align-middle">
                        <thead>
                            <tr>
                                <th>REMITENTE</th>
                                <th>CONSULTA</th>
                                <th>ESTADO</th>
                                <th>ASIGNADA A</th>
                                <th>RECIBIDA</th>
                                <th>ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inquiries as $inquiry)
                                <tr>
                                    <td><strong>{{ $inquiry->name ?: 'Sin nombre' }}</strong><br><small>{{ $inquiry->email }}</small></td>
                                    <td>{{ \Illuminate\Support\Str::limit($inquiry->message, 95) }}</td>
                                    <td><span class="contact-status contact-status-{{ $inquiry->status }}">{{ $inquiry->statusLabel() }}</span></td>
                                    <td>{{ $inquiry->assignee?->name ?? 'Sin asignar' }}</td>
                                    <td>{{ $inquiry->created_at->format('d/m/Y h:i a') }}</td>
                                    <td><a class="btn btn-outline-primary btn-sm" href="{{ route('admin.contact-inquiries.show', $inquiry) }}">Ver</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-5">No se encontraron consultas con los filtros seleccionados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="admin-table-footer">
                    <span>{{ $inquiries->firstItem() ?? 0 }}–{{ $inquiries->lastItem() ?? 0 }} de {{ $inquiries->total() }}</span>
                    @if($inquiries->hasPages())
                        <div class="admin-pagination">{{ $inquiries->links() }}</div>
                    @endif
                </div>
            </section>

            <div class="admin-bottom-actions">
                <a class="btn btn-primary" href="{{ route('admin.dashboard') }}">Ir al dashboard</a>
                <a class="btn btn-primary" href="{{ route('home') }}">Inicio</a>
            </div>
        </div>
    </main>
</div>
@endsection
