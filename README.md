# DONA ÓRGANOS PANAMÁ

Aplicación institucional para registrar y consultar la voluntad de donar órganos y tejidos en Panamá. El sistema contempla registro y baja voluntaria de donantes, carnet simbólico verificable por QR, administración, métricas y un CMS editorial limitado.

> Estado actual: base técnica Laravel y esquema inicial de base de datos. Los módulos funcionales se desarrollan de forma incremental y únicamente después de su aprobación.

## Stack

- PHP 8.3 o posterior compatible
- Laravel 13
- MySQL 8.4 LTS
- Blade y Bootstrap 5.3.8
- JavaScript modular, Vite 8 y Chart.js
- Composer 2, Node.js 24 LTS y npm
- Producción prevista: Ubuntu Server 24.04 LTS ARM64, Nginx y PHP-FPM

## Requisitos locales

- PHP con la extensión `pdo_mysql`
- Composer
- Node.js y npm
- Docker Desktop para ejecutar MySQL 8.4 LTS

## Instalación local

```bash
composer install
cp .env.example .env
php artisan key:generate
docker compose up -d --wait
php artisan migrate --seed
npm install
npm run build
```

La configuración local predeterminada utiliza:

```text
Host: 127.0.0.1
Puerto: 3308
Base de datos: dona_organos_pa
Usuario: dona_organos
```

El puerto `3308` evita conflictos con otras instalaciones locales de MySQL. Dentro del contenedor se conserva el puerto estándar `3306`. Las credenciales de `.env.example` son exclusivamente para desarrollo y deben sustituirse en cualquier ambiente compartido o productivo.

## Operación local

Iniciar y comprobar MySQL:

```bash
docker compose up -d --wait
docker compose ps
```

Detener el contenedor sin eliminar los datos:

```bash
docker compose stop
```

Ejecutar migraciones pendientes y cargar catálogos:

```bash
php artisan migrate
php artisan db:seed
```

Los datos geográficos semilla incluyen 14 provincias/comarcas, 83 distritos y 702 corregimientos desde una fuente comunitaria versionada. Deben validarse contra un insumo institucional antes de producción; consulta la documentación de arquitectura para conocer su procedencia.

No se debe ejecutar `docker compose down --volumes` salvo que se pretenda eliminar toda la base de datos local.

## Desarrollo y calidad

```bash
composer run dev
php artisan test
vendor/bin/pint --test
npm run build
```

`composer run dev` inicia PHP con los límites requeridos por los videos cortos
del CMS: 25 MB por archivo y 32 MB por solicitud. Para iniciar únicamente el
servidor Laravel con esos mismos límites se puede ejecutar:

```bash
composer run serve
```

No se debe usar `php artisan serve` directamente para probar videos, porque la
configuración predeterminada de PHP puede limitar las solicitudes a 8 MB.

### Correo local con smtp4dev

Durante el desarrollo, los correos y sus archivos adjuntos se capturan localmente y
no se envían a destinatarios reales.

```bash
docker compose -f compose.mail-dev.yaml up -d
```

La bandeja de entrada de pruebas queda disponible en:

```text
http://127.0.0.1:8080
```

Para detenerla sin eliminar los mensajes almacenados:

```bash
docker compose -f compose.mail-dev.yaml stop
```

Para retirar smtp4dev del entorno basta con detener el contenedor, eliminar
`compose.mail-dev.yaml` y sustituir las variables `MAIL_*` por las credenciales
SMTP institucionales. La lógica de envío y la plantilla del correo no necesitan
cambiar.

Las pruebas usan SQLite en memoria para mantenerse aisladas de la base local. Las migraciones también deben validarse contra MySQL 8.4 LTS antes de integrar cambios.

### Datos demostrativos para métricas

Los datos para validar gráficas están deshabilitados por defecto y nunca se
cargan con `migrate --seed`. Consulta el procedimiento operativo antes de
habilitarlos. Los comandos de inspección y limpieza son:

```bash
php artisan demo:status
php artisan demo:purge
```

La carga requiere `DEMO_DATA_ENABLED=true` y la ejecución explícita de
`MetricsDemoSeeder`. No se debe habilitar correo saliente real para estos datos.

### Usuario master de desarrollo

El rol `master` administra las cuentas del panel, mientras que el rol
`administrator` utiliza donantes, métricas y CMS. Para crear o actualizar la
cuenta master únicamente en ambientes `local` o `testing`:

```bash
php artisan db:seed --class=DevelopmentMasterSeeder
```

Sus valores se configuran con `DEVELOPMENT_MASTER_NAME`,
`DEVELOPMENT_MASTER_EMAIL` y `DEVELOPMENT_MASTER_PASSWORD`. La primera sesión
obliga a sustituir la contraseña temporal. En certificación y producción no se
debe ejecutar este seeder; la cuenta inicial debe aprovisionarse mediante el
procedimiento seguro acordado con infraestructura.

## Módulos previstos

1. Login y autorización de usuarios administrativos.
2. Registro y baja voluntaria de donantes.
3. Dashboard de administración.
4. Métricas administrativas.
5. CMS del sitio público.

Los únicos estados funcionales del donante son `Activo` y `Baja`. No existe un flujo administrativo de aceptación o rechazo.

## Documentación

- [Arquitectura de Base de Datos](Docs/Arquitectura%20de%20Base%20de%20Datos.md)
- [Guía técnica](Docs/Guia%20tecnica%20para%20iniciar%20DONA%20ORGANOS%20PANAMA.md)
- [Matriz de trazabilidad funcional](Docs/Matriz%20de%20trazabilidad%20funcional%20DONA%20ORGANOS%20PANAMA.md)
- [Requerimientos del formulario](Docs/Formulario%20de%20Registro%20de%20Donante%20de%20Córnea.md)
- [Decisiones y confirmaciones pendientes](Docs/Decisiones%20y%20confirmaciones%20pendientes.md)
- [Procedimiento de datos demostrativos y limpieza](Docs/Procedimiento%20de%20datos%20demostrativos%20y%20limpieza.md)

Los mockups y documentos originales son referencias funcionales e históricas. No son código productivo y sus simulaciones con `localStorage`, credenciales JavaScript o Google Apps Script no deben incorporarse a la aplicación.

## Seguridad

El proyecto procesa datos personales y de salud. No deben registrarse en logs cédulas, respuestas médicas, consentimientos, tokens de QR ni datos de contactos. Las credenciales reales nunca deben almacenarse en Git. Los textos legales, las políticas de retención y los permisos institucionales requieren aprobación antes de producción.
