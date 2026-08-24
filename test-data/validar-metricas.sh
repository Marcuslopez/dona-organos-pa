#!/usr/bin/env bash

# Validador técnico de las métricas. No modifica datos ni muestra información personal.
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
RESULTS_DIR="$PROJECT_ROOT/test-data/results"
TIMESTAMP="$(date '+%Y%m%d-%H%M%S')"
REPORT="$RESULTS_DIR/validacion-metricas-$TIMESTAMP.txt"
TEMP_REPORT="$RESULTS_DIR/.validacion-metricas-$TIMESTAMP.tmp"

if [[ ! -f "$PROJECT_ROOT/artisan" ]]; then
    echo "Error: no se encontró artisan. Ejecuta este script desde una copia válida del repositorio." >&2
    exit 1
fi

mkdir -p "$RESULTS_DIR"

echo "Generando validación de métricas..."
echo "El reporte se guardará en: $REPORT"

cd "$PROJECT_ROOT"
trap 'rm -f "$TEMP_REPORT"' EXIT
php artisan metrics:validate --no-ansi | tee "$TEMP_REPORT"
mv "$TEMP_REPORT" "$REPORT"
trap - EXIT

echo
echo "Validación finalizada. Reporte: $REPORT"
