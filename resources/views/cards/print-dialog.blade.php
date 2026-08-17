<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>carnet-{{ $card['record']->folio }}-{{ \Illuminate\Support\Str::slug($card['record']->full_name) }}</title>
    <style>
        * { box-sizing: border-box; }
        body { background: #eef3f9; color: #172554; font-family: Arial, sans-serif; margin: 0; }
        header { align-items: center; background: #fff; border-bottom: 1px solid #dbe4f0; display: flex; gap: 16px; justify-content: space-between; padding: 14px 24px; }
        header strong { display: block; } header span { color: #64748b; display: block; font-size: 13px; margin-top: 3px; }
        button { background: #15356d; border: 0; border-radius: 999px; color: #fff; cursor: pointer; font-weight: 700; padding: 11px 18px; }
        iframe { background: #fff; border: 0; display: block; height: calc(100vh - 75px); width: 100%; }
    </style>
</head>
<body>
    <header><div><strong>Carné {{ $card['record']->folio }}</strong><span>Selecciona una impresora o “Guardar como PDF” en el diálogo del sistema.</span></div><button type="button" id="printCard">Imprimir / Guardar PDF</button></header>
    <iframe id="cardPdf" src="{{ $pdfUrl }}" title="Vista previa del carné"></iframe>
    <script>
        const frame = document.getElementById('cardPdf');
        const printButton = document.getElementById('printCard');
        let automaticPrintStarted = false;

        const openPrintDialog = () => {
            try {
                frame.contentWindow.focus();
                frame.contentWindow.print();
            } catch (error) {
                window.print();
            }
        };

        printButton.addEventListener('click', openPrintDialog);
        frame.addEventListener('load', () => {
            if (automaticPrintStarted) return;
            automaticPrintStarted = true;
            window.setTimeout(openPrintDialog, 500);
        });
    </script>
</body>
</html>
