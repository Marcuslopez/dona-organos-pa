const csvDownloadButton = document.querySelector('[data-csv-download]');

if (csvDownloadButton) {
    csvDownloadButton.addEventListener('click', async () => {
        const originalText = csvDownloadButton.textContent;
        csvDownloadButton.disabled = true;
        csvDownloadButton.textContent = 'Preparando CSV…';

        try {
            const response = await fetch(csvDownloadButton.dataset.csvDownload, {
                credentials: 'same-origin',
                headers: { Accept: 'text/csv' },
            });

            if (!response.ok) throw new Error('No se pudo generar el archivo CSV.');

            const blob = await response.blob();
            const disposition = response.headers.get('Content-Disposition') || '';
            const encodedName = disposition.match(/filename\*=UTF-8''([^;]+)/i)?.[1];
            const plainName = disposition.match(/filename="?([^";]+)"?/i)?.[1];
            const filename = encodedName ? decodeURIComponent(encodedName) : (plainName || 'donantes.csv');
            const objectUrl = URL.createObjectURL(blob);
            const link = document.createElement('a');

            link.href = objectUrl;
            link.download = filename;
            document.body.append(link);
            link.click();
            link.remove();
            window.setTimeout(() => URL.revokeObjectURL(objectUrl), 1000);
        } catch (error) {
            window.alert(error.message || 'No se pudo descargar el archivo CSV.');
        } finally {
            csvDownloadButton.disabled = false;
            csvDownloadButton.textContent = originalText;
        }
    });
}
