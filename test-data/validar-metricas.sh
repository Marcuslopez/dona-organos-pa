#!/usr/bin/env bash

# Validador técnico de las métricas. No modifica datos ni muestra información personal.
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

if [[ ! -f "$PROJECT_ROOT/artisan" ]]; then
    echo "Error: no se encontró artisan. Ejecuta este script desde una copia válida del repositorio." >&2
    exit 1
fi

echo "Generando validación de métricas..."
echo "La salida es temporal: se muestra únicamente en esta terminal."

cd "$PROJECT_ROOT"
php artisan metrics:validate --no-ansi

echo
echo "Validación finalizada."
