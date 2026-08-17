@extends('layouts.app')

@section('title', 'Gestión de contenidos | DONA ÓRGANOS PANAMÁ')

@section('content')
<div class="admin-app">
    <header class="admin-header">
        <div class="container-fluid admin-header-inner">
            <a class="admin-logo" href="{{ route('admin.contents.index') }}"><small>DONA ÓRGANOS PANAMÁ</small><span>Gestión de contenidos</span></a>
            <div class="admin-user">
                <div><strong>{{ auth()->user()->name }}</strong><span>{{ auth()->user()->email }}</span></div>
                <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="btn btn-outline-light btn-sm" type="submit">Cerrar sesión</button></form>
            </div>
        </div>
    </header>

    <main class="admin-main">
        <div class="container-fluid admin-container">
            <div class="admin-title-row">
                <div><h1>Contenidos del portal</h1><p>Consulta los contenidos administrables antes de habilitar su edición.</p></div>
                <div class="d-flex flex-wrap gap-2"><a class="btn btn-outline-primary rounded-pill" href="{{ route('admin.dashboard') }}">Volver al dashboard</a><a class="btn btn-outline-primary rounded-pill" href="{{ route('home') }}" target="_blank" rel="noopener">Ver portal público</a></div>
            </div>

            @if (session('status'))
                <div class="alert alert-success" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger" role="alert"><strong>No fue posible guardar el contenido.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <section class="cms-create-panel" aria-labelledby="cmsCreateTitle">
                <div><h2 id="cmsCreateTitle">Administración de contenidos</h2><p>Agrega textos o historias a las cuatro secciones administrables del portal.</p></div>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#createContentModal">Adicionar contenido</button>
            </section>

            <form class="mockup-filters cms-filters" method="GET" action="{{ route('admin.contents.index') }}" aria-label="Filtros de contenidos">
                <div class="filter-group"><label for="buscar">Título o texto</label><input class="form-control" id="buscar" name="buscar" type="search" value="{{ $search }}" placeholder="Buscar contenido"></div>
                <div class="filter-group"><label for="tipo">Sección</label><select class="form-select" id="tipo" name="tipo"><option value="">Todas</option>@foreach($types as $value => $label)<option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="filter-group"><label for="estado">Estado</label><select class="form-select" id="estado" name="estado"><option value="">Todos</option><option value="visible" @selected($status === 'visible')>Visible</option><option value="hidden" @selected($status === 'hidden')>Oculto</option></select></div>
                <button class="btn btn-primary mockup-search" type="submit">Buscar</button>
                <a class="btn mockup-clear" href="{{ route('admin.contents.index') }}">Limpiar</a>
            </form>

            <section class="admin-panel" aria-labelledby="contentsTitle">
                <h2 class="visually-hidden" id="contentsTitle">Listado de contenidos</h2>
                <div class="table-responsive">
                    <table class="table admin-table align-middle">
                        <thead><tr><th>Sección</th><th>Título</th><th>Contenido</th><th>Orden</th><th>Estado</th><th>Multimedia</th><th>Actualizado</th><th>Acción</th></tr></thead>
                        <tbody>
                            @forelse($contents as $content)
                                <tr>
                                    <td><strong>{{ $types[$content->type] }}</strong></td>
                                    <td>{{ $content->title }}@if($content->subtitle)<small class="d-block text-muted">{{ $content->subtitle }}</small>@endif</td>
                                    <td><span title="{{ strip_tags($content->body) }}">{{ \Illuminate\Support\Str::limit(strip_tags($content->body), 95) }}</span></td>
                                    <td>{{ $content->sort_order }}</td>
                                    <td><span class="status-badge {{ $content->is_visible && $content->published_at ? 'is-active' : 'is-withdrawn' }}">{{ $content->is_visible && $content->published_at ? 'Visible' : 'Oculto' }}</span></td>
                                    <td>{{ $content->media ? ($content->media->media_type === 'image' ? 'Imagen asociada' : 'Video asociado') : '—' }}</td>
                                    <td>{{ $content->updated_at->timezone('America/Panama')->format('d/m/Y h:i a') }}</td>
                                    <td><button class="btn btn-outline-primary btn-sm" type="button" data-cms-edit data-id="{{ $content->id }}" data-type="{{ $content->type }}" data-type-label="{{ $types[$content->type] }}" data-title="{{ $content->title }}" data-subtitle="{{ $content->subtitle }}" data-body="{{ $content->body }}" data-media-type="{{ $content->media?->media_type }}" data-media-url="{{ $content->media?->url }}" data-media-alt="{{ $content->media?->alt_text }}" data-sort-order="{{ $content->sort_order }}" data-visible="{{ $content->is_visible && $content->published_at ? '1' : '0' }}" data-update-url="{{ route('admin.contents.update', $content) }}" data-delete-url="{{ route('admin.contents.destroy', $content) }}" data-bs-toggle="modal" data-bs-target="#editContentModal">Ver</button></td>
                                </tr>
                            @empty
                                <tr><td class="admin-empty" colspan="8">No se encontraron contenidos con los filtros seleccionados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="admin-table-footer">
                    <form method="GET" action="{{ route('admin.contents.index') }}"><input type="hidden" name="buscar" value="{{ $search }}"><input type="hidden" name="tipo" value="{{ $type }}"><input type="hidden" name="estado" value="{{ $status }}"><label for="porPaginaContenido">Registros por página</label><select class="form-select form-select-sm" id="porPaginaContenido" name="por_pagina" onchange="this.form.submit()"><option value="10" @selected($perPage === 10)>10</option><option value="20" @selected($perPage === 20)>20</option><option value="30" @selected($perPage === 30)>30</option></select></form>
                    <span>{{ $contents->firstItem() ?? 0 }}–{{ $contents->lastItem() ?? 0 }} de {{ $contents->total() }}</span>
                    @if($contents->hasPages())<div class="admin-pagination">{{ $contents->links() }}</div>@endif
                </div>
            </section>
        </div>
    </main>
</div>

<div class="modal fade cms-modal" id="createContentModal" tabindex="-1" aria-labelledby="createContentTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><div><small>DONA ÓRGANOS PANAMÁ</small><h2 class="modal-title" id="createContentTitle">Agregar contenido</h2></div><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
        <form method="POST" action="{{ route('admin.contents.store') }}" enctype="multipart/form-data">@csrf
            <div class="modal-body">
                <div class="mb-3"><label class="form-label" for="createType">Sección <span class="text-danger">*</span></label><select class="form-select" id="createType" name="type" required><option value="">Selecciona una sección</option>@foreach($types as $value => $label)<option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>@endforeach</select></div>
                @include('admin.contents.partials.form', ['prefix' => 'create', 'content' => null])
            </div>
            <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" type="submit">Guardar contenido</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade cms-modal" id="editContentModal" tabindex="-1" aria-labelledby="editContentTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><div><small>DONA ÓRGANOS PANAMÁ</small><h2 class="modal-title" id="editContentTitle">Detalle del contenido</h2></div><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
        <form id="editContentForm" method="POST" enctype="multipart/form-data">@csrf @method('PUT')<input id="editType" type="hidden" name="type">
            <div class="modal-body">@include('admin.contents.partials.form', ['prefix' => 'edit', 'content' => null])</div>
            <div class="modal-footer cms-edit-actions"><button class="btn btn-primary" id="editSaveButton" type="submit" disabled>Guardar</button><button class="btn btn-danger" id="deleteContentButton" type="button">Eliminar</button><button class="btn btn-primary" type="button" data-bs-dismiss="modal">Volver</button></div>
        </form>
        <form class="d-none" id="deleteContentForm" method="POST">@csrf @method('DELETE')</form>
    </div></div>
</div>

<div class="modal fade cms-modal" id="deleteContentModal" tabindex="-1" aria-labelledby="deleteContentTitle" aria-describedby="deleteContentDescription" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><div><small>DONA ÓRGANOS PANAMÁ</small><h2 class="modal-title" id="deleteContentTitle">Eliminar contenido</h2></div></div>
        <div class="modal-body text-center"><span class="cms-delete-icon" aria-hidden="true">!</span><p id="deleteContentDescription" class="mb-0">¿Está seguro de que desea eliminar este contenido?</p></div>
        <div class="modal-footer justify-content-center"><button class="btn btn-outline-secondary" id="cancelDeleteContent" type="button">Cancelar</button><button class="btn btn-danger" id="confirmDeleteContent" type="button">Aceptar</button></div>
    </div></div>
</div>

<div class="modal fade cms-modal" id="cmsImageCropModal" tabindex="-1" aria-labelledby="cmsImageCropTitle" aria-describedby="cmsImageCropDescription" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><div><small>DONA ÓRGANOS PANAMÁ</small><h2 class="modal-title" id="cmsImageCropTitle">Recortar imagen</h2></div></div>
        <div class="modal-body">
            <p id="cmsImageCropDescription">Mueve o amplía la fotografía hasta encajarla dentro del recuadro 16:9.</p>
            <div class="cms-crop-workspace" id="cmsCropWorkspace"><img id="cmsCropImage" alt="Imagen seleccionada para recortar"></div>
            <div class="alert alert-danger d-none mt-3 mb-0" id="cmsCropError" role="alert"></div>
        </div>
        <div class="modal-footer cms-edit-actions"><button class="btn btn-outline-secondary" id="cmsResetCrop" type="button">Restablecer</button><button class="btn btn-primary" id="cmsConfirmCrop" type="button">Usar este recorte</button><button class="btn btn-primary" id="cmsCancelCrop" type="button">Cancelar</button></div>
    </div></div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const createModal = document.getElementById('createContentModal');
    const editModal = document.getElementById('editContentModal');
    const deleteModal = document.getElementById('deleteContentModal');
    const editForm = document.getElementById('editContentForm');
    const editSaveButton = document.getElementById('editSaveButton');
    let initialEditState = '';
    const formState = () => JSON.stringify([...new FormData(editForm).entries()].map(([key, value]) => value instanceof File ? [key, value.name, value.size] : [key, value]));
    const updateSaveState = () => editSaveButton.disabled = formState() === initialEditState;
    const toggleFields = (modal, type) => {
        modal.querySelectorAll('[data-story-field]').forEach((field) => field.classList.toggle('d-none', type !== 'story'));
        modal.querySelectorAll('[data-legal-field]').forEach((field) => field.classList.toggle('d-none', type !== 'legal'));
        modal.querySelectorAll('[data-myth-title-prefix]').forEach((field) => field.classList.toggle('d-none', type !== 'myth'));
    };
    const createTitle = createModal.querySelector('#createTitle');
    const titleTemplates = { faq: '¿?' };
    const applyCreateTitleTemplate = (type, focusTitle = true) => {
        if (type === 'myth' && /^Mito:\s*/iu.test(createTitle.value.trim())) {
            createTitle.value = createTitle.value.trim().replace(/^Mito:\s*/iu, '').replace(/^""$/, '');
        }

        const currentTitle = createTitle.value.trim();
        const isTemplateOrEmpty = currentTitle === '' || currentTitle === 'Mito: ""' || Object.values(titleTemplates).includes(currentTitle);

        if (!isTemplateOrEmpty) {
            return;
        }

        createTitle.value = type === 'myth'
            ? currentTitle.replace(/^Mito:\s*/iu, '').replace(/^""$/, '')
            : (titleTemplates[type] ?? '');
        createTitle.dispatchEvent(new Event('input', { bubbles: true }));

        if (focusTitle && ['myth', 'faq'].includes(type)) {
            requestAnimationFrame(() => {
                createTitle.focus();
                const cursorPosition = type === 'faq' ? 1 : createTitle.value.length;
                createTitle.setSelectionRange(cursorPosition, cursorPosition);
            });
        }
    };

    createModal.querySelector('#createType').addEventListener('change', (event) => {
        toggleFields(createModal, event.target.value);
        applyCreateTitleTemplate(event.target.value);
    });
    createModal.addEventListener('show.bs.modal', () => {
        const selectedType = createModal.querySelector('#createType').value;
        toggleFields(createModal, selectedType);
        applyCreateTitleTemplate(selectedType, false);
    });

    document.querySelectorAll('[data-cms-edit]').forEach((button) => button.addEventListener('click', () => {
        editForm.reset();
        editModal.querySelector('#editContentTitle').textContent = button.dataset.typeLabel;
        editModal.querySelector('#editType').value = button.dataset.type;
        editModal.querySelector('#editTitle').value = button.dataset.type === 'myth'
            ? button.dataset.title.replace(/^Mito:\s*/iu, '')
            : button.dataset.title;
        editModal.querySelector('#editSubtitle').value = button.dataset.subtitle;
        editModal.querySelector('#editBody').value = button.dataset.body;
        editModal.querySelector('#editBodyEditor').editor.loadHTML(button.dataset.body);
        editModal.querySelector('#editSortOrder').value = button.dataset.sortOrder;
        editModal.querySelector('#editVisible').checked = button.dataset.visible === '1';
        editModal.querySelector('#editImageAlt').value = button.dataset.mediaType === 'image' ? button.dataset.mediaAlt : '';
        editModal.querySelector('#editCurrentImage').textContent = button.dataset.type === 'legal' && button.dataset.mediaType === 'image' ? 'Este contenido tiene una imagen asociada.' : 'Este contenido no tiene imagen asociada.';
        editModal.querySelector('#editCurrentVideo').textContent = button.dataset.type === 'story' && button.dataset.mediaType === 'video' ? 'Esta historia tiene un video asociado.' : 'Esta historia no tiene video asociado.';
        editModal.querySelector('#editContentForm').action = button.dataset.updateUrl;
        editModal.querySelector('#deleteContentForm').action = button.dataset.deleteUrl;
        toggleFields(editModal, button.dataset.type);
        initialEditState = formState();
        updateSaveState();
    }));

    editForm.addEventListener('input', updateSaveState);
    editForm.addEventListener('change', updateSaveState);
    editForm.addEventListener('trix-change', updateSaveState);

    document.addEventListener('trix-file-accept', (event) => event.preventDefault());

    const reopenEditModal = () => window.bootstrap.Modal.getOrCreateInstance(editModal).show();
    document.getElementById('deleteContentButton').addEventListener('click', () => {
        window.bootstrap.Modal.getOrCreateInstance(editModal).hide();
        editModal.addEventListener('hidden.bs.modal', () => window.bootstrap.Modal.getOrCreateInstance(deleteModal).show(), { once: true });
    });
    document.getElementById('cancelDeleteContent').addEventListener('click', () => {
        window.bootstrap.Modal.getOrCreateInstance(deleteModal).hide();
        deleteModal.addEventListener('hidden.bs.modal', reopenEditModal, { once: true });
    });
    document.getElementById('confirmDeleteContent').addEventListener('click', () => document.getElementById('deleteContentForm').submit());

});
</script>
@endpush
