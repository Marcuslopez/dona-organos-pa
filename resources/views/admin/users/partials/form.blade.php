<div class="modal-body admin-user-form">
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="name{{ $user?->id }}">Nombre completo <span class="text-danger">*</span></label><input class="form-control" id="name{{ $user?->id }}" name="name" value="{{ $user?->name }}" maxlength="120" required></div>
        <div class="col-md-6"><label class="form-label" for="email{{ $user?->id }}">Correo electrónico <span class="text-danger">*</span></label><input class="form-control" id="email{{ $user?->id }}" name="email" type="email" value="{{ $user?->email }}" maxlength="255" required></div>
        <div class="col-md-6"><label class="form-label" for="role{{ $user?->id }}">Rol <span class="text-danger">*</span></label><select class="form-select" id="role{{ $user?->id }}" name="role" required><option value="administrator" @selected(($user?->role ?? 'administrator') === 'administrator')>Administrador</option><option value="master" @selected($user?->role === 'master')>Master</option></select></div>
        <div class="col-md-6 d-flex align-items-end"><div class="form-check form-switch mb-2"><input name="is_active" type="hidden" value="0"><input class="form-check-input" id="active{{ $user?->id }}" name="is_active" type="checkbox" value="1" @checked($user?->is_active ?? true)><label class="form-check-label" for="active{{ $user?->id }}">Usuario activo</label></div></div>
        @if($user)
            <div class="col-12">
                <section class="border rounded-3 p-3 bg-light">
                    <h3 class="h6 mb-3">Acceso y contraseña</h3>
                    @if($user->login_locked_until?->isFuture())
                        <div class="alert alert-warning mb-3">
                            <strong>Cuenta bloqueada temporalmente.</strong><br>
                            Motivo: {{ $user->login_lock_reason ?? 'Intentos fallidos de acceso' }}.<br>
                            Bloqueada desde: {{ $user->login_locked_at?->timezone('America/Panama')->format('d/m/Y h:i:s a') ?? 'No disponible' }}.<br>
                            Disponible nuevamente: {{ $user->login_locked_until->timezone('America/Panama')->format('d/m/Y h:i:s a') }}.
                        </div>
                        <div class="form-check mb-3"><input name="unlock_access" type="hidden" value="0"><input class="form-check-input" id="unlockAccess{{ $user->id }}" name="unlock_access" type="checkbox" value="1"><label class="form-check-label" for="unlockAccess{{ $user->id }}">Desbloquear acceso ahora</label><div class="form-text">Al guardar, se eliminará el bloqueo y quedará registrada la acción del usuario master.</div></div>
                    @else
                        <input name="unlock_access" type="hidden" value="0">
                        <p class="text-muted mb-3">La cuenta no tiene un bloqueo activo.</p>
                    @endif
                    <div class="form-check"><input name="reset_password" type="hidden" value="0"><input class="form-check-input" id="resetPassword{{ $user->id }}" name="reset_password" type="checkbox" value="1" data-password-reset><label class="form-check-label" for="resetPassword{{ $user->id }}">Restablecer contraseña del administrador</label><div class="form-text">Al guardar, la contraseña vigente dejará de funcionar y la nueva se enviará al correo {{ $user->email }}.</div></div>
                </section>
            </div>
            <div class="col-md-6 d-none" data-temporary-password-fields><label class="form-label" for="password{{ $user->id }}">Nueva contraseña temporal <span class="text-danger">*</span></label><input class="form-control" id="password{{ $user->id }}" name="password" type="text" autocomplete="off" readonly disabled><div class="form-text">Contraseña aleatoria generada por el sistema.</div></div>
            <div class="col-md-6 d-none" data-temporary-password-fields><label class="form-label" for="passwordConfirmation{{ $user->id }}">Confirmar contraseña <span class="text-danger">*</span></label><input class="form-control" id="passwordConfirmation{{ $user->id }}" name="password_confirmation" type="text" autocomplete="off" readonly disabled></div>
        @else
            <div class="col-md-6"><label class="form-label" for="password">Contraseña temporal <span class="text-danger">*</span></label><input class="form-control" id="password" name="password" type="password" autocomplete="new-password" required><div class="form-text">Mínimo 12 caracteres, mayúsculas, minúsculas y números. Se enviará al correo del usuario.</div></div>
            <div class="col-md-6"><label class="form-label" for="passwordConfirmation">Confirmar contraseña <span class="text-danger">*</span></label><input class="form-control" id="passwordConfirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
        @endif
    </div>
</div>
