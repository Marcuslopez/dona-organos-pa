# Validación de datos para métricas

Este directorio contiene una verificación técnica de solo lectura para contrastar los datos que alimentan las gráficas del panel administrativo.

## Ejecución

Desde la raíz del proyecto:

```bash
bash test-data/validar-metricas.sh
```

El script usa la configuración activa de Laravel (`.env`), no solicita ni guarda contraseñas y no modifica registros. La salida es temporal: se muestra en la terminal y no crea archivos de reporte.

## Información mostrada

- Totales de donantes activos y en baja.
- Altas, bajas y acumulado activo por cada uno de los últimos 12 meses.
- Distribución por rango de edad y provincia.
- Alertas de integridad: estado inválido, fechas o provincia ausentes y contactos sin donante.

No incluye cédulas, nombres, correos ni otros datos personales.

> La salida reproduce la regla actual de las métricas: las barras de altas y bajas agrupan por fecha de registro y por el **estado actual** del donante.
