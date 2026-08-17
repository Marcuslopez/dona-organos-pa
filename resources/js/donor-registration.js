const geography = document.querySelector('[data-geography]');

if (geography) {
    const province = geography.querySelector('[data-province]');
    const district = geography.querySelector('[data-district]');
    const corregimiento = geography.querySelector('[data-corregimiento]');
    const districts = JSON.parse(geography.dataset.districts || '[]');
    const corregimientos = JSON.parse(geography.dataset.corregimientos || '[]');

    const fill = (select, rows, placeholder, selected = '') => {
        select.innerHTML = `<option value="">${placeholder}</option>`;
        select.disabled = rows.length === 0;
        rows.forEach((row) => {
            const option = document.createElement('option');
            option.value = row.id;
            option.textContent = row.name;
            option.selected = String(row.id) === String(selected);
            select.append(option);
        });
    };

    const updateCorregimientos = (selected = '') => fill(
        corregimiento,
        corregimientos.filter((item) => String(item.district_id) === district.value),
        'Selecciona un corregimiento',
        selected,
    );

    const updateDistricts = (selectedDistrict = '', selectedCorregimiento = '') => {
        fill(district, districts.filter((item) => String(item.province_id) === province.value), 'Selecciona un distrito', selectedDistrict);
        updateCorregimientos(selectedCorregimiento);
    };

    province.addEventListener('change', () => updateDistricts());
    district.addEventListener('change', () => updateCorregimientos());
    updateDistricts(district.dataset.selected, corregimiento.dataset.selected);
}

const contacts = document.querySelector('[data-contacts]');
const addContact = document.querySelector('[data-add-contact]');

if (contacts && addContact) {
    const relationships = JSON.parse(contacts.dataset.relationships || '[]');
    const requiredAlert = document.querySelector('[data-contact-required-alert]');
    const form = contacts.closest('form');

    const refreshContacts = () => {
        const entries = contacts.querySelectorAll('[data-contact]');
        entries.forEach((entry, index) => {
            entry.querySelector('strong').textContent = `Contacto ${index + 1}`;
            entry.querySelectorAll('[name]').forEach((field) => {
                field.name = field.name.replace(/contacts\[\d+\]/, `contacts[${index}]`);
            });
        });
        addContact.disabled = entries.length >= 3;
        requiredAlert?.classList.toggle('d-none', entries.length > 0);
    };

    addContact.addEventListener('click', () => {
        const index = contacts.querySelectorAll('[data-contact]').length;
        if (index >= 3) return;
        const options = relationships.map((item) => `<option value="${item.id}">${item.name}</option>`).join('');
        const wrapper = document.createElement('div');
        wrapper.className = 'contact-entry';
        wrapper.dataset.contact = '';
        wrapper.innerHTML = `<div class="contact-heading"><strong>Contacto ${index + 1}</strong><button class="btn btn-sm btn-outline-danger" type="button" data-remove-contact>Eliminar</button></div><div class="row g-3"><div class="col-md-3"><label class="form-label">Primer nombre <span>*</span></label><input class="form-control" name="contacts[${index}][first_name]" data-person-name required></div><div class="col-md-3"><label class="form-label">Segundo nombre</label><input class="form-control" name="contacts[${index}][middle_name]" data-person-name></div><div class="col-md-3"><label class="form-label">Primer apellido <span>*</span></label><input class="form-control" name="contacts[${index}][first_last_name]" data-person-name required></div><div class="col-md-3"><label class="form-label">Segundo apellido</label><input class="form-control" name="contacts[${index}][second_last_name]" data-person-name></div><div class="col-md-4"><label class="form-label">Parentesco <span>*</span></label><select class="form-select" name="contacts[${index}][relationship_id]" required><option value="">Selecciona</option>${options}</select></div><div class="col-md-4"><label class="form-label">Correo electrónico</label><input class="form-control" name="contacts[${index}][email]" type="email"></div><div class="col-md-4"><label class="form-label">Teléfono <span>*</span></label><div class="input-group phone-control"><span class="input-group-text phone-prefix" aria-hidden="true"><span class="phone-flag">🇵🇦</span><span>+507</span><span class="phone-pipe">|</span></span><input class="form-control" name="contacts[${index}][phone]" inputmode="numeric" data-phone maxlength="9" placeholder="6123-4567" aria-label="Número telefónico de contacto en Panamá" required></div></div><div class="col-12"><div class="form-check"><input type="hidden" name="contacts[${index}][is_informed]" value="0"><input class="form-check-input" id="informed${index}" name="contacts[${index}][is_informed]" type="checkbox" value="1"><label class="form-check-label" for="informed${index}">Esta persona conoce mi decisión.</label></div></div></div>`;
        contacts.append(wrapper);
        refreshContacts();
    });

    contacts.addEventListener('click', (event) => {
        const remove = event.target.closest('[data-remove-contact]');
        if (remove) {
            remove.closest('[data-contact]').remove();
            refreshContacts();
        }
    });

    form?.addEventListener('submit', (event) => {
        if (contacts.querySelectorAll('[data-contact]').length === 0) {
            event.preventDefault();
            requiredAlert?.classList.remove('d-none');
            requiredAlert?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    refreshContacts();
}

const birthDateDisplay = document.querySelector('[data-birth-date-display]');
const birthDatePicker = document.querySelector('[data-birth-date-picker]');

if (birthDateDisplay && birthDatePicker) {
    const birthDateError = document.querySelector('[data-birth-date-error]');
    const birthDateForm = birthDateDisplay.closest('form');

    const validateBirthDate = (showError = true) => {
        const match = birthDateDisplay.value.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        let valid = false;
        let errorMessage = 'Fecha incorrecta.';

        if (match) {
            const [, day, month, year] = match;
            const isoDate = `${year}-${month}-${day}`;
            const date = new Date(`${isoDate}T00:00:00`);
            const isRealDate = date.getFullYear() === Number(year)
                && date.getMonth() + 1 === Number(month)
                && date.getDate() === Number(day);

            if (isRealDate && isoDate > birthDateDisplay.dataset.maxDate) {
                errorMessage = 'Debes tener al menos 18 años para completar el registro.';
            } else if (isRealDate && isoDate < birthDateDisplay.dataset.minDate) {
                errorMessage = 'La fecha de nacimiento no puede ser anterior a 100 años desde la fecha actual.';
            }

            valid = isRealDate
                && isoDate >= birthDateDisplay.dataset.minDate
                && isoDate <= birthDateDisplay.dataset.maxDate;
            birthDatePicker.value = valid ? isoDate : '';
        } else {
            birthDatePicker.value = '';
        }

        birthDateDisplay.classList.toggle('is-invalid', showError && !valid);
        if (birthDateError) birthDateError.textContent = errorMessage;
        birthDateError?.classList.toggle('d-none', !showError || valid);
        return valid;
    };

    birthDateDisplay.addEventListener('input', () => {
        const digits = birthDateDisplay.value.replace(/\D/g, '').slice(0, 8);
        birthDateDisplay.value = [digits.slice(0, 2), digits.slice(2, 4), digits.slice(4, 8)]
            .filter(Boolean).join('/');

        if (birthDateDisplay.value.length === 10) validateBirthDate();
        else {
            birthDateDisplay.classList.remove('is-invalid');
            birthDateError?.classList.add('d-none');
        }
    });

    birthDateDisplay.addEventListener('blur', () => validateBirthDate());

    birthDatePicker.addEventListener('change', () => {
        if (!birthDatePicker.value) return;
        const [year, month, day] = birthDatePicker.value.split('-');
        birthDateDisplay.value = `${day}/${month}/${year}`;
        validateBirthDate();
    });

    birthDateForm?.addEventListener('submit', (event) => {
        if (!validateBirthDate()) {
            event.preventDefault();
            birthDateDisplay.focus();
        }
    });
}

document.addEventListener('input', (event) => {
    if (event.target.matches('[data-phone]')) {
        const digits = event.target.value.replace(/\D/g, '').slice(0, 8);
        const split = digits.length === 8 ? 4 : 3;
        event.target.value = digits.length > split ? `${digits.slice(0, split)}-${digits.slice(split)}` : digits;
    }
    if (event.target.matches('[data-person-name]')) {
        event.target.value = event.target.value.replace(/[^\p{L}\s]/gu, '').replace(/\s{2,}/g, ' ');
    }
});

document.addEventListener('blur', (event) => {
    if (event.target.matches('[data-person-name]')) {
        event.target.value = event.target.value.trim().split(/\s+/).map((word) =>
            word.charAt(0).toLocaleUpperCase('es-PA') + word.slice(1).toLocaleLowerCase('es-PA')
        ).join(' ');
    }
}, true);
