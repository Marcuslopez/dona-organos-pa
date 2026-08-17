@extends('layouts.app')

@section('title', 'Dashboard administrativo | DONA ÓRGANOS PANAMÁ')

@section('content')
<div class="admin-app">
    <header class="admin-header">
        <div class="container-fluid admin-header-inner">
            <a class="admin-logo" href="{{ route('admin.dashboard') }}"><small>DONA ÓRGANOS PANAMÁ</small><span>Administración de donantes</span></a>
            <div class="admin-user">
                <div><strong>{{ auth()->user()->name }}</strong><span>{{ auth()->user()->email }}</span></div>
                <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="btn btn-outline-light btn-sm" type="submit">Cerrar sesión</button></form>
            </div>
        </div>
    </header>

    <main class="admin-main">
        <div class="container-fluid admin-container">
            <div class="admin-title-row">
                <div><h1>Registro de donantes</h1><p>Consulta y revisa las voluntades registradas en el sistema.</p></div>
                <div class="d-flex flex-wrap gap-2"><a class="btn btn-primary rounded-pill" href="{{ route('admin.metrics.index') }}">Ver métricas</a><a class="btn btn-primary rounded-pill" href="{{ route('admin.contents.index') }}">Gestionar CMS</a><a class="btn btn-outline-primary rounded-pill" href="{{ route('home') }}" target="_blank" rel="noopener">Volver al inicio</a></div>
            </div>

            <form class="mockup-filters" method="GET" action="{{ route('admin.dashboard') }}" aria-label="Filtros de donantes">
                <div class="filter-group"><label for="nombre">Nombre</label><input class="form-control" id="nombre" name="nombre" type="search" value="{{ $name }}" placeholder="Buscar por nombre"></div>
                <div class="filter-group"><label for="cedula">Cédula</label><input class="form-control" id="cedula" name="cedula" type="search" value="{{ $document }}" placeholder="Buscar por cédula"></div>
                <div class="filter-group"><label for="provincia">Provincia</label><select class="form-select" id="provincia" name="provincia"><option value="">Todas</option>@foreach($provinces as $option)<option value="{{ $option->id }}" @selected($province == $option->id)>{{ $option->name }}</option>@endforeach</select></div>
                <div class="filter-group"><label for="estado">Estado</label><select class="form-select" id="estado" name="estado"><option value="" {{ $status === '' ? 'selected' : '' }}>Todos</option><option value="active" {{ $status === 'active' ? 'selected' : '' }}>Activos</option><option value="withdrawn" {{ $status === 'withdrawn' ? 'selected' : '' }}>Bajas</option></select></div>
                <div class="filter-group"><label for="desde">Fecha desde</label><input class="form-control" id="desde" name="desde" type="date" value="{{ $dateFrom }}"></div>
                <div class="filter-group"><label for="hasta">Fecha hasta</label><input class="form-control" id="hasta" name="hasta" type="date" value="{{ $dateTo }}"></div>
                <button class="btn btn-primary mockup-search" type="submit">Buscar</button>
                <a class="btn mockup-clear" href="{{ route('admin.dashboard') }}">Limpiar</a>
            </form>

            <section class="admin-panel" aria-labelledby="donorsTitle">
                <h2 class="visually-hidden" id="donorsTitle">Listado de donantes</h2>

                <div class="table-responsive">
                    <table class="table admin-table align-middle">
                        <thead><tr><th>Nombre</th><th>Cédula</th><th>Correo</th><th>Provincia</th><th>Contacto</th><th>Fecha registro</th><th>Estado</th><th>Acción</th></tr></thead>
                        <tbody>
                            @forelse($donors as $donor)
                                <tr>
                                    <td><strong>{{ $donor->full_name }}</strong></td><td>{{ $donor->document_number }}</td><td>{{ $donor->email }}</td><td>{{ $donor->province_name }}</td><td>{{ $donor->contact_name ?: '—' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($donor->registered_at)->timezone('America/Panama')->format('d/m/Y') }}</td>
                                    <td><span class="status-badge {{ $donor->status === 'active' ? 'is-active' : 'is-withdrawn' }}">{{ $donor->status === 'active' ? 'Activo' : 'Baja' }}</span></td>
                                    <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.donors.show', $donor->id) }}">Ver</a></td>
                                </tr>
                            @empty
                                <tr><td class="admin-empty" colspan="8">No se encontraron donantes con los filtros seleccionados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="admin-table-footer"><div class="admin-table-footer-actions"><form method="GET" action="{{ route('admin.dashboard') }}"><input type="hidden" name="nombre" value="{{ $name }}"><input type="hidden" name="cedula" value="{{ $document }}"><input type="hidden" name="provincia" value="{{ $province }}"><input type="hidden" name="estado" value="{{ $status }}"><input type="hidden" name="desde" value="{{ $dateFrom }}"><input type="hidden" name="hasta" value="{{ $dateTo }}"><label for="porPagina">Registros por página</label><select class="form-select form-select-sm" id="porPagina" name="por_pagina" onchange="this.form.submit()"><option value="5" @selected($perPage === 5)>5</option><option value="10" @selected($perPage === 10)>10</option><option value="15" @selected($perPage === 15)>15</option><option value="20" @selected($perPage === 20)>20</option></select></form><button class="btn btn-primary admin-download-csv" type="button" data-csv-download="{{ route('admin.donors.export.csv', ['nombre' => $name, 'cedula' => $document, 'provincia' => $province, 'estado' => $status, 'desde' => $dateFrom, 'hasta' => $dateTo]) }}">Descargar CSV</button></div><span>{{ $donors->firstItem() ?? 0 }}–{{ $donors->lastItem() ?? 0 }} de {{ $donors->total() }}</span>@if($donors->hasPages())<div class="admin-pagination">{{ $donors->links() }}</div>@endif</div>
            </section>
        </div>
    </main>
</div>
@endsection
