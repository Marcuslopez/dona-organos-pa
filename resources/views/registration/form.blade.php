@extends('layouts.app')

@section('title', 'Registro de donante | DONA ÓRGANOS PANAMÁ')

@section('content')
@php
    $value = fn (string $key, mixed $fallback = '') => old($key, data_get($defaults, $key, $fallback));
    $formContacts = old('contacts', data_get($defaults, 'contacts', [['first_name' => '', 'middle_name' => '', 'first_last_name' => '', 'second_last_name' => '', 'relationship_id' => '', 'phone' => '', 'email' => '', 'is_informed' => false]]));
@endphp
<main class="registration-page registration-form-page">
    <div class="registration-shell registration-shell-wide">
        <form class="registration-card donor-form" method="POST" action="{{ $isUpdate ? route('registration.update.store') : ($isReactivation ? route('registration.reactivation.store') : route('registration.store')) }}" novalidate>
            @csrf
            <a class="auth-brand identity-card-brand" href="{{ route('home') }}">DONA ÓRGANOS PANAMÁ</a>
            <p class="step-indicator"><span>Paso 2 de 2</span> Registro de voluntad</p>
            <h1>{{ $isUpdate ? 'Actualizar mis datos' : ($isReactivation ? 'Reactivar mi voluntad' : 'Registro de donante') }}</h1>
            <p class="registration-intro">{{ $isUpdate ? 'Revisa tus datos y contactos. Si cambias tus contactos de confianza, emitiremos un carné actualizado.' : ($isReactivation ? 'Revisa y actualiza tus datos. Debes confirmar nuevamente tu consentimiento para reactivar el registro.' : 'Completa tus datos, registra al menos un contacto y confirma tu voluntad.') }}</p>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert"><strong>Revisa los campos indicados.</strong> La información todavía no fue registrada.</div>
            @endif

            <fieldset class="form-section">
                <legend>Datos personales</legend>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label" for="firstName">Primer nombre <span>*</span></label><input class="form-control @error('first_name') is-invalid @enderror" id="firstName" name="first_name" value="{{ $value('first_name') }}" data-person-name autocomplete="given-name" required>@error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4"><label class="form-label" for="middleName">Segundo nombre</label><input class="form-control @error('middle_name') is-invalid @enderror" id="middleName" name="middle_name" value="{{ $value('middle_name') }}" data-person-name>@error('middle_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4"><label class="form-label">Cédula validada</label><input class="form-control" value="{{ $verification['document_number'] }}" disabled></div>
                    <div class="col-md-4"><label class="form-label" for="firstLastName">Primer apellido <span>*</span></label><input class="form-control @error('first_last_name') is-invalid @enderror" id="firstLastName" name="first_last_name" value="{{ $value('first_last_name') }}" data-person-name autocomplete="family-name" required>@error('first_last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4"><label class="form-label" for="secondLastName">Segundo apellido</label><input class="form-control @error('second_last_name') is-invalid @enderror" id="secondLastName" name="second_last_name" value="{{ $value('second_last_name') }}" data-person-name>@error('second_last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    @php
                        $birthDateValue = $value('birth_date');
                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthDateValue)) {
                            $birthDateValue = \Carbon\Carbon::parse($birthDateValue)->format('d/m/Y');
                        }
                    @endphp
                    <div class="col-md-4"><label class="form-label" for="birthDate">Fecha de nacimiento <span>*</span></label><div class="input-group birth-date-control"><input class="form-control @error('birth_date') is-invalid @enderror" id="birthDate" name="birth_date" type="text" inputmode="numeric" maxlength="10" placeholder="DD/MM/AAAA" value="{{ $birthDateValue }}" data-birth-date-display data-min-date="{{ now()->subYears(100)->toDateString() }}" data-max-date="{{ now()->subYears(18)->toDateString() }}" autocomplete="bday" required><label class="input-group-text birth-date-calendar" for="birthDatePicker" title="Abrir calendario" aria-label="Abrir calendario"><span aria-hidden="true">📅</span></label><input class="birth-date-picker" id="birthDatePicker" type="date" min="{{ now()->subYears(100)->toDateString() }}" max="{{ now()->subYears(18)->toDateString() }}" data-birth-date-picker tabindex="-1" aria-label="Seleccionar fecha de nacimiento en el calendario">@error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="birth-date-error d-none" data-birth-date-error role="alert">Fecha incorrecta.</div><div class="form-text">Puedes escribirla como DD/MM/AAAA o elegirla en el calendario.</div></div>
                    <div class="col-md-4"><label class="form-label" for="gender">Género <span>*</span></label><select class="form-select @error('gender_id') is-invalid @enderror" id="gender" name="gender_id" required><option value="">Selecciona</option>@foreach($catalogs['genders'] as $option)<option value="{{ $option->id }}" @selected($value('gender_id') == $option->id)>{{ $option->name }}</option>@endforeach</select>@error('gender_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4"><label class="form-label" for="email">Correo electrónico <span>*</span></label><input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ $value('email') }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4"><label class="form-label" for="phone">Teléfono <span>*</span></label><div class="input-group phone-control"><span class="input-group-text phone-prefix" aria-hidden="true"><span class="phone-flag">🇵🇦</span><span>+507</span><span class="phone-pipe">|</span></span><input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" inputmode="numeric" data-phone maxlength="9" placeholder="6123-4567" value="{{ $value('phone') }}" aria-label="Número telefónico de Panamá" required>@error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
                </div>
            </fieldset>

            <fieldset class="form-section" data-geography data-districts='@json($catalogs["districts"])' data-corregimientos='@json($catalogs["corregimientos"])'>
                <legend>Dirección</legend>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label" for="province">Provincia o comarca <span>*</span></label><select class="form-select @error('province_id') is-invalid @enderror" id="province" name="province_id" data-province required><option value="">Selecciona una provincia</option>@foreach($catalogs['provinces'] as $option)<option value="{{ $option->id }}" @selected($value('province_id') == $option->id)>{{ $option->name }}</option>@endforeach</select>@error('province_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4"><label class="form-label" for="district">Distrito <span>*</span></label><select class="form-select @error('district_id') is-invalid @enderror" id="district" name="district_id" data-district data-selected="{{ $value('district_id') }}" disabled required><option value="">Selecciona un distrito</option></select>@error('district_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4"><label class="form-label" for="corregimiento">Corregimiento <span>*</span></label><select class="form-select @error('corregimiento_id') is-invalid @enderror" id="corregimiento" name="corregimiento_id" data-corregimiento data-selected="{{ $value('corregimiento_id') }}" disabled required><option value="">Selecciona un corregimiento</option></select>@error('corregimiento_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                </div>
            </fieldset>

            <fieldset class="form-section">
                <legend>Contacto de confianza</legend>
                <p class="section-help">Debes registrar al menos uno. El primero será el contacto principal.</p>
                <div data-contacts data-relationships='@json($catalogs["relationships"])'>
                    @foreach($formContacts as $index => $contact)
                        <div class="contact-entry" data-contact>
                            <div class="contact-heading"><strong>Contacto {{ $index + 1 }}</strong><button class="btn btn-sm btn-outline-danger" type="button" data-remove-contact>Eliminar</button></div>
                            <div class="row g-3">
                                <div class="col-md-3"><label class="form-label">Primer nombre <span>*</span></label><input class="form-control" name="contacts[{{ $index }}][first_name]" value="{{ $contact['first_name'] ?? '' }}" data-person-name required></div>
                                <div class="col-md-3"><label class="form-label">Segundo nombre</label><input class="form-control" name="contacts[{{ $index }}][middle_name]" value="{{ $contact['middle_name'] ?? '' }}" data-person-name></div>
                                <div class="col-md-3"><label class="form-label">Primer apellido <span>*</span></label><input class="form-control" name="contacts[{{ $index }}][first_last_name]" value="{{ $contact['first_last_name'] ?? '' }}" data-person-name required></div>
                                <div class="col-md-3"><label class="form-label">Segundo apellido</label><input class="form-control" name="contacts[{{ $index }}][second_last_name]" value="{{ $contact['second_last_name'] ?? '' }}" data-person-name></div>
                                <div class="col-md-4"><label class="form-label">Parentesco <span>*</span></label><select class="form-select" name="contacts[{{ $index }}][relationship_id]" required><option value="">Selecciona</option>@foreach($catalogs['relationships'] as $option)<option value="{{ $option->id }}" @selected(($contact['relationship_id'] ?? '') == $option->id)>{{ $option->name }}</option>@endforeach</select></div>
                                <div class="col-md-4"><label class="form-label">Correo electrónico</label><input class="form-control" name="contacts[{{ $index }}][email]" type="email" value="{{ $contact['email'] ?? '' }}"></div>
                                <div class="col-md-4"><label class="form-label">Teléfono <span>*</span></label><div class="input-group phone-control"><span class="input-group-text phone-prefix" aria-hidden="true"><span class="phone-flag">🇵🇦</span><span>+507</span><span class="phone-pipe">|</span></span><input class="form-control" name="contacts[{{ $index }}][phone]" inputmode="numeric" data-phone maxlength="9" placeholder="6123-4567" value="{{ $contact['phone'] ?? '' }}" aria-label="Número telefónico de contacto en Panamá" required></div></div>
                                <div class="col-12"><div class="form-check"><input type="hidden" name="contacts[{{ $index }}][is_informed]" value="0"><input class="form-check-input" id="informed{{ $index }}" name="contacts[{{ $index }}][is_informed]" type="checkbox" value="1" @checked($contact['is_informed'] ?? false)><label class="form-check-label" for="informed{{ $index }}">Esta persona conoce mi decisión.</label></div></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="contact-required-alert{{ count($formContacts) > 0 ? ' d-none' : '' }}" data-contact-required-alert role="alert" aria-live="polite">
                    Debes agregar al menos un contacto de confianza antes de registrar tu voluntad.
                </div>
                <button class="btn btn-outline-primary" type="button" data-add-contact>Agregar otro contacto</button>
                @error('contacts')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            </fieldset>
            <fieldset class="form-section consent-section">
                <legend>Consentimiento</legend>
                <div class="form-check consent-check">
                    <input class="form-check-input @error('consent_accepted') is-invalid @enderror" id="consentAccepted" name="consent_accepted" type="checkbox" value="1" @checked(old('consent_accepted')) required>
                    <label class="form-check-label" for="consentAccepted">Confirmo que mi decisión de donar órganos es libre, voluntaria e informada, y autorizo el tratamiento de mis datos personales para este registro. He leído y acepto las <button class="contact-policy-link" type="button" data-bs-toggle="modal" data-bs-target="#privacyPolicyModal">condiciones de uso y política de privacidad</button>. <span class="required-mark">*</span></label>
                    @error('consent_accepted')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mt-4"><label class="form-label" for="signedName">Firma electrónica</label><input class="form-control" id="signedName" placeholder="Por definir…" disabled><div class="form-text">La integración de validación biométrica será definida por la institución antes de su puesta en producción.</div></div>
            </fieldset>

            <div class="registration-form-actions">
                <button class="btn btn-primary registration-submit" type="submit">{{ $isUpdate ? 'Confirmar actualización' : ($isReactivation ? 'Confirmar reactivación' : 'Registrar mi voluntad') }}</button>
                <a class="btn btn-primary registration-back" href="{{ route('home') }}">Volver al inicio</a>
            </div>
            <p class="registration-security">La fecha, versión y estado del consentimiento se conservarán como evidencia de tu decisión.</p>
        </form>
    </div>
</main>

<div class="modal fade" id="privacyPolicyModal" tabindex="-1" aria-labelledby="privacyPolicyTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="privacyPolicyTitle">Condiciones de uso y política de privacidad</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p>Esta plantilla de Política de Privacidad está diseñada para cumplir con la <a href="https://istmodigital.net/que-exige-la-ley-81-de-proteccion-de-datos-en-panama" target="_blank" rel="noopener">Ley 81 de 2019 y el Decreto Ejecutivo 285 de 2021 de Panamá</a>, cubriendo la recopilación de datos, finalidades y derechos ARCO. El texto establece el consentimiento expreso y medidas de seguridad técnicas para el uso de formularios del portal. Puede obtener más información sobre la normativa en el sitio web de la Autoridad Nacional de Transparencia y Acceso a la Información (ANTAI).</p>
                <p><a href="https://www.arispeabogado.com/p/politicas-y-privacidad.html" target="_blank" rel="noopener">Referencia de plantilla de política de privacidad</a>.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>
@endsection
