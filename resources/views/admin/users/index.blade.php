@extends('layouts.app')

@section('title', 'Usuarios administrativos | DONA ÓRGANOS PANAMÁ')

@section('content')
<div class="admin-app">
    <header class="admin-header"><div class="container-fluid admin-header-inner"><a class="admin-logo" href="{{ route('admin.users.index') }}"><small>DONA ÓRGANOS PANAMÁ</small><span>Administración de usuarios</span></a><div class="admin-user"><div><strong>{{ auth()->user()->name }}</strong><span>Usuario master</span></div><form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="btn btn-outline-light btn-sm" type="submit">Cerrar sesión</button></form></div></div></header>

    <main class="admin-main"><div class="container-fluid admin-container">
        <div class="admin-title-row"><div><h1>Usuarios administrativos</h1><p>Creación, roles, estado y restablecimiento de acceso del equipo administrativo.</p></div><button class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#createUserModal" type="button">Adicionar usuario</button></div>

        @if(session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger" role="alert"><strong>No fue posible guardar el usuario.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <form class="mockup-filters admin-user-filters" method="GET" action="{{ route('admin.users.index') }}">
            <div class="filter-group"><label for="buscar">Nombre o correo</label><input class="form-control" id="buscar" name="buscar" value="{{ $search }}" placeholder="Buscar usuario"></div>
            <div class="filter-group"><label for="rol">Rol</label><select class="form-select" id="rol" name="rol"><option value="">Todos</option><option value="master" @selected($role === 'master')>Master</option><option value="administrator" @selected($role === 'administrator')>Administrador</option></select></div>
            <div class="filter-group"><label for="estado">Estado</label><select class="form-select" id="estado" name="estado"><option value="">Todos</option><option value="active" @selected($status === 'active')>Activos</option><option value="inactive" @selected($status === 'inactive')>Inactivos</option></select></div>
            <button class="btn btn-primary mockup-search" type="submit">Buscar</button><a class="btn mockup-clear" href="{{ route('admin.users.index') }}">Limpiar</a>
        </form>

        <section class="admin-panel"><div class="table-responsive"><table class="table admin-table align-middle"><thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Último acceso</th><th>Contraseña</th><th>Acción</th></tr></thead><tbody>
            @forelse($users as $user)
                <tr><td><strong>{{ $user->name }}</strong>@if($user->is(auth()->user())) <small class="d-block text-muted">Tu cuenta</small>@endif</td><td>{{ $user->email }}</td><td>{{ $user->isMaster() ? 'Master' : 'Administrador' }}</td><td><span class="status-badge {{ $user->is_active ? 'is-active' : 'is-withdrawn' }}">{{ $user->is_active ? 'Activo' : 'Inactivo' }}</span></td><td>{{ $user->last_login_at?->timezone('America/Panama')->format('d/m/Y h:i a') ?? 'Sin acceso' }}</td><td>{{ $user->must_change_password ? 'Cambio pendiente' : 'Vigente' }}</td><td class="text-end"><button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUser{{ $user->id }}" type="button">Ver</button></td></tr>
            @empty<tr><td class="admin-empty" colspan="7">No se encontraron usuarios.</td></tr>@endforelse
        </tbody></table></div><div class="admin-table-footer"><span>{{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} de {{ $users->total() }}</span>@if($users->hasPages())<div class="admin-pagination">{{ $users->links() }}</div>@endif</div><div class="d-flex flex-wrap justify-content-end gap-2 border-top p-3"><a class="btn btn-primary" href="{{ route('admin.dashboard') }}">Dashboard administrativo</a><a class="btn btn-primary" href="{{ route('home') }}">Inicio</a></div></section>
    </div></main>
</div>

<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div><small>DONA ÓRGANOS PANAMÁ</small><h2 class="modal-title h4" id="createUserTitle">Adicionar usuario administrativo</h2></div>
                <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                @include('admin.users.partials.form', ['user' => null])
                <div class="modal-footer justify-content-end gap-2">
                    <button class="btn btn-primary" type="submit">Guardar</button>
                    <button class="btn btn-primary" data-bs-dismiss="modal" type="button">Volver</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($users as $user)
<div class="modal fade" id="editUser{{ $user->id }}" tabindex="-1" aria-labelledby="editUserTitle{{ $user->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div><small>DONA ÓRGANOS PANAMÁ</small><h2 class="modal-title h4" id="editUserTitle{{ $user->id }}">Modificar usuario administrativo</h2></div>
                <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('PUT')
                @include('admin.users.partials.form', ['user' => $user])
                <div class="modal-footer justify-content-end gap-2">
                    <button class="btn btn-primary" type="submit">Guardar</button>
                    <button class="btn btn-primary" data-bs-dismiss="modal" type="button">Volver</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
