import Cropper from 'cropperjs';

const OUTPUT_WIDTH = 1600;
const OUTPUT_HEIGHT = 900;
const MAX_OUTPUT_BYTES = 2 * 1024 * 1024;
const MAX_SOURCE_BYTES = 15 * 1024 * 1024;
const ACCEPTED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

document.addEventListener('DOMContentLoaded', () => {
    const cropModalElement = document.getElementById('cmsImageCropModal');

    if (!cropModalElement) {
        return;
    }

    const cropImage = document.getElementById('cmsCropImage');
    const confirmButton = document.getElementById('cmsConfirmCrop');
    const cancelButton = document.getElementById('cmsCancelCrop');
    const resetButton = document.getElementById('cmsResetCrop');
    const errorBox = document.getElementById('cmsCropError');
    const cropModal = window.bootstrap.Modal.getOrCreateInstance(cropModalElement, {
        backdrop: 'static',
        keyboard: false,
    });
    let cropper = null;
    let sourceModal = null;
    let activeInput = null;
    let sourceUrl = null;
    let accepted = false;

    const showError = (message = '') => {
        errorBox.textContent = message;
        errorBox.classList.toggle('d-none', message === '');
    };

    const reopenSourceModal = () => {
        if (sourceModal) {
            window.bootstrap.Modal.getOrCreateInstance(sourceModal).show();
        }
    };

    const closeCropper = () => {
        cropper?.destroy();
        cropper = null;
        cropImage.removeAttribute('src');

        if (sourceUrl) {
            URL.revokeObjectURL(sourceUrl);
            sourceUrl = null;
        }
    };

    const resetInput = () => {
        if (activeInput) {
            activeInput.value = '';
            activeInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    };

    const openCropper = (input, file) => {
        activeInput = input;
        sourceModal = input.closest('.modal');
        accepted = false;
        showError();
        sourceUrl = URL.createObjectURL(file);
        cropImage.src = sourceUrl;

        const showCropModal = () => cropModal.show();

        if (sourceModal?.classList.contains('show')) {
            sourceModal.addEventListener('hidden.bs.modal', showCropModal, { once: true });
            window.bootstrap.Modal.getOrCreateInstance(sourceModal).hide();
        } else {
            showCropModal();
        }
    };

    document.querySelectorAll('[data-cms-crop-input]').forEach((input) => {
        input.addEventListener('change', () => {
            if (input.dataset.cropped === '1') {
                delete input.dataset.cropped;

                return;
            }

            const file = input.files?.[0];

            if (!file) {
                return;
            }

            if (!ACCEPTED_TYPES.includes(file.type)) {
                input.value = '';
                window.alert('Selecciona una imagen JPG, PNG o WebP.');

                return;
            }

            if (file.size > MAX_SOURCE_BYTES) {
                input.value = '';
                window.alert('La imagen original no puede superar 15 MB.');

                return;
            }

            openCropper(input, file);
        });
    });

    cropModalElement.addEventListener('shown.bs.modal', () => {
        cropper = new Cropper(cropImage, {
            container: document.getElementById('cmsCropWorkspace'),
            template: '<cropper-canvas background><cropper-image rotatable scalable translatable></cropper-image><cropper-shade hidden></cropper-shade><cropper-handle action="move" plain></cropper-handle><cropper-selection initial-aspect-ratio="1.7777777778" aspect-ratio="1.7777777778" initial-coverage="0.85" movable resizable zoomable><cropper-grid role="grid" bordered covered></cropper-grid><cropper-crosshair centered></cropper-crosshair><cropper-handle action="move" theme-color="rgba(255,255,255,.35)"></cropper-handle><cropper-handle action="n-resize"></cropper-handle><cropper-handle action="e-resize"></cropper-handle><cropper-handle action="s-resize"></cropper-handle><cropper-handle action="w-resize"></cropper-handle><cropper-handle action="ne-resize"></cropper-handle><cropper-handle action="nw-resize"></cropper-handle><cropper-handle action="se-resize"></cropper-handle><cropper-handle action="sw-resize"></cropper-handle></cropper-selection></cropper-canvas>',
        });
    });

    resetButton.addEventListener('click', () => {
        cropper?.getCropperImage()?.$resetTransform();
        cropper?.getCropperSelection()?.$reset();
        showError();
    });

    cancelButton.addEventListener('click', () => {
        accepted = false;
        cropModal.hide();
    });

    confirmButton.addEventListener('click', async () => {
        const selection = cropper?.getCropperSelection();

        if (!selection) {
            showError('No fue posible obtener el área seleccionada.');

            return;
        }

        confirmButton.disabled = true;
        showError();

        try {
            const canvas = await selection.$toCanvas({
                width: OUTPUT_WIDTH,
                height: OUTPUT_HEIGHT,
                beforeDraw(context) {
                    context.fillStyle = '#ffffff';
                    context.fillRect(0, 0, OUTPUT_WIDTH, OUTPUT_HEIGHT);
                },
            });
            let blob = null;

            for (const quality of [0.9, 0.82, 0.74, 0.66, 0.58]) {
                blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', quality));

                if (blob && blob.size <= MAX_OUTPUT_BYTES) {
                    break;
                }
            }

            if (!blob || blob.size > MAX_OUTPUT_BYTES) {
                showError('No fue posible reducir la imagen a un máximo de 2 MB. Prueba con otra fotografía.');

                return;
            }

            const originalName = activeInput.files?.[0]?.name.replace(/\.[^.]+$/, '') || 'imagen';
            const croppedFile = new File([blob], `${originalName}-recortada.jpg`, {
                type: 'image/jpeg',
                lastModified: Date.now(),
            });
            const transfer = new DataTransfer();
            transfer.items.add(croppedFile);
            activeInput.files = transfer.files;
            activeInput.dataset.cropped = '1';
            accepted = true;

            const legalField = activeInput.closest('[data-legal-field]');
            const preview = legalField?.querySelector('[data-cms-crop-preview]');
            const result = legalField?.querySelector('[data-cms-crop-result]');

            if (preview) {
                preview.src = URL.createObjectURL(croppedFile);
                preview.classList.remove('d-none');
            }

            if (result) {
                result.textContent = `Imagen preparada: 1600×900 px · ${(blob.size / 1024).toFixed(0)} KB`;
                result.classList.remove('d-none');
            }

            activeInput.dispatchEvent(new Event('change', { bubbles: true }));
            cropModal.hide();
        } catch {
            showError('No fue posible procesar esta imagen. Prueba con otro archivo.');
        } finally {
            confirmButton.disabled = false;
        }
    });

    cropModalElement.addEventListener('hidden.bs.modal', () => {
        if (!accepted) {
            resetInput();
        }

        closeCropper();
        reopenSourceModal();
    });
});
