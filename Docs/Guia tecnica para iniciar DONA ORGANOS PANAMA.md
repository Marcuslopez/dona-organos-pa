# Guía técnica para iniciar el desarrollo de DONA ÓRGANOS PANAMÁ

## 1. Propósito del documento

Este documento reúne el contexto técnico y funcional necesario para iniciar, en un proyecto y una conversación separados, el desarrollo de **DONA ÓRGANOS PANAMÁ** sin depender de la conversación donde se construyeron los mockups.

Debe utilizarse como:

- documento de traspaso al equipo de desarrollo;
- contexto inicial para un nuevo chat de asistencia técnica;
- referencia para crear la arquitectura, el modelo de datos y la API;
- inventario de decisiones confirmadas y asuntos todavía pendientes;
- vínculo entre los mockups existentes y la aplicación productiva.

La trazabilidad detallada de pantallas, campos, acciones, datos, métricas y sustituciones productivas se encuentra en `Proyecto_corneas/Docs/Matriz de trazabilidad funcional DONA ORGANOS PANAMA.md`.

Este documento no sustituye la validación jurídica, médica, institucional ni de protección de datos requerida antes de poner la aplicación en producción.

---

## 2. Identidad y orientación del proyecto

### Nombre oficial

**DONA ÓRGANOS PANAMÁ**

### Orientación

La aplicación estará orientada al registro de la voluntad de donar **órganos y tejidos en general**. Aunque el trabajo inicial nació alrededor de la donación de córneas, el producto nuevo no debe quedar limitado ni nombrado como un sistema exclusivo de córneas.

Las córneas pueden mantenerse como una preferencia o alcance particular de la donación, pero no como la identidad central del sistema.

### Separación respecto de los mockups

El desarrollo será un proyecto nuevo, con estructura, base de datos, seguridad y código propios. Los archivos de `Proyecto_corneas/Mockups` quedarán congelados como referencia visual y funcional; no deben convertirse directamente en la aplicación productiva ni copiarse completos dentro de Laravel.

### Principios de identidad técnica

- Usar nombres de clases, tablas, variables, rutas y documentación relacionados con donantes, donación de órganos, consentimiento, contactos y carnets.
- Evitar nombres heredados como `corneas`, salvo cuando identifiquen una opción específica y vigente del alcance de donación.
- Mantener la interfaz en español y preparar la estructura para textos centralizados o traducciones futuras.
- Conservar una identidad visual institucional propia, tomando el mockup como referencia, no como restricción permanente.
- Separar el contenido editorial del inicio de los componentes y servicios funcionales, permitiendo sustituir textos sin alterar los flujos del sistema.

---

## 3. Alcance funcional inicial

### Sitio público

- Página informativa y educativa.
- Acceso al formulario de registro de donante.
- Registro de la voluntad del donante.
- Confirmación del registro.
- Generación de un carnet simbólico con código QR.
- Impresión o guardado como PDF mediante el diálogo de impresión del navegador.
- Página de verificación asociada al código del carnet.
- Comunicación de la importancia de informar la decisión a la familia.

El texto informativo propuesto para la pantalla de inicio puede variar durante el desarrollo y después de su validación institucional. Este contenido editorial no debe estar acoplado a las reglas de registro, autenticación, administración, API, carnet ni métricas. Los cambios de títulos, párrafos, mitos, preguntas frecuentes, testimonios y datos de contacto no deben requerir modificaciones en la lógica funcional del sistema.

### Formulario del donante

El formulario de referencia contiene estas agrupaciones:

1. Datos de identificación.
2. Ubicación de residencia.
3. Consentimiento informado.
4. Información de salud relevante.
5. Preferencias y alcance de la donación.
6. Protección y autorización de tratamiento de datos.
7. Contacto informado.
8. Confirmación, folio, estado y carnet.

Comportamientos ya definidos en el mockup:

- Los campos de teléfono solo permiten caracteres numéricos.
- Los nombres normalizan la primera letra de cada palabra en mayúscula.
- Los correos electrónicos se normalizan a minúsculas.
- El correo debe validarse tanto en el cliente como en el servidor.
- El guardado productivo debe ocurrir en MySQL; `localStorage` solo pertenece a la simulación del mockup.
- Google Apps Script está desactivado en el mockup y no forma parte de la arquitectura nueva.

### Administración

- Autenticación de usuarios administrativos.
- Listado paginado de donantes.
- Búsqueda y filtros.
- Consulta del detalle del donante.
- Visualización y ocultamiento del carnet dentro del mismo detalle, evitando modales anidados.
- Impresión o guardado del carnet como PDF.
- Exportación de reportes.
- Dashboard de indicadores y gráficas.
- Gestión de contenidos informativos del portal mediante un CMS interno limitado.

En la definición actual, la administración de **donantes** es principalmente de consulta. No debe suponerse que puede editar datos, aprobar o rechazar registros si esa capacidad no ha sido autorizada posteriormente. Esta restricción no impide la edición de contenido editorial mediante el CMS, que constituye un módulo separado y confirmado.

### CMS interno de contenidos

Se requiere un módulo administrativo limitado, similar a WordPress únicamente en su capacidad de mantener contenido editorial. No se requiere un constructor visual de páginas, sistema de temas, comentarios, plugins ni edición libre de la estructura del portal.

Alcance inicial confirmado:

- Categorías administrables: aspectos legales, mitos y realidades, y preguntas frecuentes.
- Crear, consultar, editar y eliminar contenidos.
- Mostrar u ocultar un contenido sin eliminarlo.
- Definir el orden de presentación dentro de cada categoría.
- Mantener título o pregunta, descripción o respuesta y enlace relacionado opcional.
- Buscar y filtrar contenidos en Administración.
- Reflejar en el portal público únicamente los contenidos visibles y en el orden establecido.
- Restringir todas las operaciones de escritura a usuarios administrativos autorizados.

El mockup lo demuestra mediante `contenidos.html` y `assets/contenidos-cms.js`, con persistencia en `localStorage`. En producción, los contenidos deben persistirse en MySQL mediante Laravel; `localStorage`, el botón para restaurar la demostración y los datos editoriales semilla no forman parte del comportamiento productivo.

El contenido legal y médico continuará sujeto a validación institucional aunque el CMS permita editarlo. El sistema debe validar y limpiar cualquier contenido enriquecido antes de mostrarlo para impedir inyección de HTML o scripts. Para la primera versión puede utilizarse texto plano o formato enriquecido limitado; no se requiere un editor visual avanzado.

### Estado del donante

La terminología confirmada es:

- Campo: **estado**.
- Valores iniciales: **Activo** y **Baja**.

La antigua categoría administrativa con valores `Aceptado`, `Rechazado` y `Por validar` fue eliminada. El antiguo campo llamado `consentimiento`, cuyos valores eran `Activo` y `Baja`, pasó a llamarse `estado`.

No deben reintroducirse los estados administrativos eliminados ni funciones basadas en ellos.

---

## 4. Referencias disponibles

### Mockups funcionales

Ubicación actual:

```text
Proyecto_corneas/Mockups/
```

| Archivo | Uso como referencia |
|---|---|
| `index.html` | Portal público, información, acceso al formulario y acceso administrativo simulado |
| `formulario.html` | Registro del donante, validaciones, confirmación y carnet |
| `administracion.html` | Listado, filtros, detalle y carnet del donante |
| `contenidos.html` | CMS simulado: listado, creación, edición, visibilidad, orden y eliminación de contenido editorial |
| `assets/contenidos-cms.js` | Datos semilla, persistencia local y proyección del contenido visible en el portal; sustituir en producción |
| `metricas.html` | Orden y presentación esperada de las gráficas |
| `verificar-donante.html` | Concepto de verificación mediante QR |
| `correos-simulados.html` | Ejemplo demostrativo de comunicación al donante |

Los datos y credenciales presentes en los mockups son demostrativos y no deben pasar a producción.

### Matriz funcional exhaustiva

```text
Proyecto_corneas/Docs/Matriz de trazabilidad funcional DONA ORGANOS PANAMA.md
```

La matriz identifica cada pantalla, campo, interacción, dato, gráfica y estado de interfaz, y los clasifica como implementación confirmada, referencia visual, validación pendiente, simulación que debe sustituirse o elemento que debe excluirse.

### Documento funcional anterior

```text
Proyecto_corneas/Docs/Formulario de Registro de Donante de Córnea.md
```

Este documento contiene una propuesta detallada de campos y justificaciones, pero conserva terminología centrada en córneas y afirmaciones legales o médicas que requieren revisión. Debe consultarse como antecedente, no como especificación jurídica definitiva.

### Aplicación de referencia arquitectónica

```text
/Users/marcosrodolforamoslopez/Developer/lab/visitor-management-system
```

Esta aplicación Laravel puede aportar buenas prácticas de organización:

- controladores pequeños;
- Form Requests para validación;
- servicios de dominio;
- consultas y filtros separados;
- transacciones de base de datos;
- middleware de roles;
- migraciones, seeders y factories;
- métricas calculadas desde MySQL;
- paginación y exportación;
- Docker Compose para MySQL en desarrollo.

No deben copiarse su dominio, tablas, identidad, reglas de visitas, pruebas de ejemplo, combinación Tailwind/Bootstrap ni configuraciones provisionales.

---

## 5. Stack técnico propuesto

### Producción

| Componente | Decisión propuesta |
|---|---|
| Sistema operativo | Ubuntu Server 24.04 LTS ARM64 |
| Servidor web | Nginx |
| Runtime backend | PHP 8.3 o una versión posterior compatible y validada |
| Framework | Laravel 13 |
| Dependencias PHP | Composer 2 |
| Base de datos | MySQL 8, fijando una versión exacta antes de iniciar |
| Renderizado | Blade |
| Interfaz | Bootstrap 5.3.8 |
| Lógica del navegador | JavaScript modular |
| Gráficas | Chart.js |
| Compilación de recursos | Vite 8 |
| Runtime de recursos | Node.js 24 LTS y npm |
| Control de versiones | Git y GitLab |
| Procesos | PHP-FPM; systemd o Supervisor para colas cuando sean necesarias |
| TLS | HTTPS obligatorio en producción |

Ubuntu 24.04 LTS se prefiere por madurez. Antes del despliegue debe confirmarse la compatibilidad exacta entre las versiones fijadas de Ubuntu, PHP, Laravel, Node, Vite y MySQL.

### Entorno disponible en macOS

El equipo de desarrollo reportó:

| Componente | Versión o estado |
|---|---|
| Homebrew | 6.0.15 |
| UTM | Instalado |
| Docker Desktop | Docker 29.6.2 |
| PHP local | 8.5.9 |
| Composer | 2.10.2 |
| Laravel Installer | 5.31.0 |
| Node.js con NVM | 24.18.1 LTS |
| npm | 11.16.0 |
| Git | 2.55.0 |
| MySQL local | 9.7.1 |

### Estrategia local recomendada

- Ejecutar Laravel, PHP, Composer, Node y Vite directamente en macOS.
- Ejecutar MySQL en Docker con la misma versión mayor usada en producción.
- Evitar usar MySQL local 9.7.1 para este proyecto si producción utilizará MySQL 8.
- Usar UTM con Ubuntu ARM64 para comprobar instalación y despliegue, no como requisito para cada ciclo diario de programación.
- No instalar Nginx en macOS para el desarrollo ordinario; utilizarlo en la máquina virtual y en producción.

Docker no es obligatorio en producción. MySQL Server puede instalarse directamente en Ubuntu si esa es la decisión de infraestructura.

---

## 6. Arquitectura recomendada

La primera versión puede ser un **monolito modular Laravel**. Es más simple de operar que una arquitectura de microservicios y cubre adecuadamente el alcance conocido.

```text
Navegador
   |
   +-- Sitio público Blade + Bootstrap
   +-- Administración Blade + Bootstrap + Chart.js
   |
Nginx
   |
PHP-FPM / Laravel 13
   |
   +-- Controllers
   +-- Form Requests
   +-- Policies / Middleware
   +-- Application / Domain Services
   +-- Query Services
   +-- Jobs / Notifications
   +-- API Resources
   |
MySQL 8
```

### Capas y responsabilidades

#### Controladores

- Recibir solicitudes HTTP.
- Delegar validaciones a Form Requests.
- Invocar servicios o consultas.
- Devolver vistas, redirecciones o JSON.
- No concentrar reglas de negocio ni consultas complejas.

#### Form Requests

- Validación del registro.
- Mensajes en español.
- Normalización controlada de nombres, correos y teléfonos.
- Autorización básica de la operación.

La validación JavaScript mejora la experiencia, pero nunca reemplaza la validación en Laravel.

#### Servicios de aplicación o dominio

Servicios iniciales sugeridos:

- `DonorRegistrationService`
- `DonorStatusService`
- `DonorCardService`
- `DonorVerificationService`
- `DonorMetricsService`
- `DonorExportService`
- `ConsentService`
- `ContentManagementService`

El registro de un donante, sus contactos, preferencias y consentimiento debe ejecutarse dentro de una transacción de MySQL.

#### Consultas

Clases sugeridas:

- `DonorFilterQuery`
- `DonorMetricsQuery`
- `DonorVerificationQuery`
- `PublishedContentQuery`

Las métricas deben agregarse en MySQL. El navegador recibirá series ya calculadas y Chart.js se limitará a representarlas.

#### Autorización

- Middleware para acceso autenticado.
- Policies o permisos para acciones específicas.
- Separar al menos el rol administrativo del posible rol técnico de monitoreo.
- No exponer logs, excepciones ni información técnica al administrador funcional.

---

## 7. Orientación de la API

Aunque la primera interfaz se renderice con Blade, conviene definir una frontera clara de servicios y respuestas. Si se crea una API JSON, debe versionarse desde el inicio:

```text
/api/v1
```

### Endpoints iniciales de referencia

La lista siguiente es una propuesta técnica, no un contrato definitivo:

```text
POST   /api/v1/donors
GET    /api/v1/donors/{donor}
GET    /api/v1/donors/{donor}/card
GET    /api/v1/donor-cards/{publicToken}/verify

GET    /api/v1/admin/donors
GET    /api/v1/admin/donors/{donor}
GET    /api/v1/admin/metrics/summary
GET    /api/v1/admin/metrics/cumulative-growth
GET    /api/v1/admin/metrics/registrations-and-deactivations
GET    /api/v1/admin/metrics/by-age
GET    /api/v1/admin/metrics/by-province
GET    /api/v1/admin/donors/export

GET    /api/v1/admin/contents
POST   /api/v1/admin/contents
GET    /api/v1/admin/contents/{content}
PUT    /api/v1/admin/contents/{content}
DELETE /api/v1/admin/contents/{content}
PATCH  /api/v1/admin/contents/{content}/visibility
```

El cambio de estado, si queda autorizado como operación administrativa o de autoservicio, debe definirse expresamente antes de agregar un endpoint de escritura.

Los endpoints del CMS son una referencia si se decide exponer JSON. Con Blade también pueden implementarse como rutas web protegidas, manteniendo las mismas reglas de autorización, validación y persistencia.

### Convenciones de respuesta

- JSON en UTF-8.
- Fechas y horas en ISO 8601.
- Zona horaria de negocio: `America/Panama`.
- Códigos HTTP apropiados: `201`, `200`, `204`, `401`, `403`, `404`, `409`, `422`, `429` y `500`.
- Errores de validación con estructura consistente.
- Paginación en listados administrativos.
- Identificador de correlación en respuestas y logs.
- No devolver modelos Eloquent completos; utilizar API Resources o DTO.

### API pública y administración

- El registro público debe protegerse con limitación de frecuencia, validación estricta y medidas contra automatización abusiva.
- Las rutas administrativas deben exigir autenticación y autorización.
- El endpoint de verificación del carnet debe devolver solamente información pública mínima.
- Una cédula, teléfono, correo, dirección, respuestas médicas o consentimiento no deben quedar expuestos mediante el QR.

---

## 8. Modelo de datos inicial

El modelo debe validarse con los responsables funcionales antes de crear las migraciones definitivas. Una separación inicial razonable sería:

### `users`

- Usuario administrativo.
- Nombre, correo, contraseña cifrada y rol.
- Estado de la cuenta y último acceso, si se requieren.

### `donors`

- Identificador interno no significativo.
- Tipo y número de documento con restricción única normalizada.
- Nombre completo.
- Fecha de nacimiento.
- Género, si se confirma como requerido.
- Correo y teléfono.
- Provincia, distrito y corregimiento mediante claves de catálogo.
- Estado `Activo` o `Baja`.
- Fecha de registro y fecha de baja.
- Folio público independiente del identificador interno.

### `donor_contacts`

- Donante.
- Nombre del contacto.
- Relación o parentesco.
- Correo, si se mantiene como requisito.
- Teléfono.
- Indicador de que conoce la decisión.
- Permitir uno o más contactos si el alcance final lo requiere.

### `donation_preferences`

- Donante.
- Alcance autorizado.
- Preferencias específicas que sean confirmadas.
- Autorización para investigación o docencia, si permanece vigente.

No debe crearse un “catálogo de órganos y tejidos” hasta que el negocio confirme que el ciudadano seleccionará componentes individuales. El mockup actual representa principalmente opciones de alcance general.

### `consents`

- Donante.
- Versión del texto aceptado.
- Fecha y hora de aceptación.
- Evidencias técnicamente permitidas.
- Autorizaciones de tratamiento y consulta de datos.
- Revocación o baja cuando corresponda.

No basta con guardar valores booleanos: conservar la versión exacta del texto aceptado permite demostrar qué condiciones fueron presentadas.

### `donor_health_answers`

- Donante.
- Pregunta o versión del cuestionario.
- Respuesta.

Estas respuestas son datos sensibles. Antes de incluirlas debe confirmarse si realmente deben persistirse, quién puede consultarlas y durante cuánto tiempo.

### `donor_cards`

- Donante.
- Folio o número de carnet.
- Token público aleatorio y no predecible.
- Fecha de emisión.
- Vigencia o revocación.
- No almacenar necesariamente el PDF si puede generarse de manera reproducible.

### `contents`

- Identificador interno.
- Tipo controlado: `legal`, `myth` o `faq`, con nombres visibles en español.
- Título o pregunta.
- Contenido o respuesta.
- Enlace relacionado opcional y validado.
- Indicador de visibilidad.
- Orden de presentación dentro de la categoría.
- Usuario creador y último usuario que modificó, si el modelo de auditoría inicial lo contempla.
- Fechas de creación y modificación.
- Eliminación lógica recomendada si se requiere recuperación; la recuperación no forma parte del alcance confirmado del primer incremento.

La lectura pública debe devolver únicamente contenidos visibles y ordenados. Los tipos deben validarse en servidor y no aceptarse como texto arbitrario.

### Catálogos geográficos

- `provinces`
- `districts`
- `corregimientos`

Debe decidirse si estos catálogos serán administrables o datos de referencia versionados.

### Índices iniciales

- Documento único normalizado.
- Estado y fecha de registro.
- Provincia y fecha de registro.
- Fecha de nacimiento para consultas estadísticas.
- Folio y token público únicos.
- Claves foráneas e índices para contactos, preferencias y consentimientos.

---

## 9. Métricas y gráficas

### Orden funcional confirmado

1. Crecimiento acumulado de donantes.
2. Altas y bajas de los últimos 12 meses.
3. Altas y bajas de donantes o distribución por estado.
4. Donantes por edad y donantes por provincia.

El nombre exacto de la tercera gráfica debe confirmarse porque el mockup conserva el título “Estado de los donantes”, mientras la solicitud utilizó “Altas y bajas de donantes”.

### Reglas técnicas

- Calcular agregados en MySQL mediante servicios o consultas dedicadas.
- Entregar a Chart.js etiquetas, series y metadatos preparados.
- Aplicar los mismos filtros temporales y de alcance a todas las series relacionadas.
- Definir claramente si “alta” significa fecha inicial de registro y “baja” significa fecha efectiva de cambio a `Baja`.
- No contar registros eliminados físicamente; se recomienda conservar la trazabilidad del estado.
- Añadir pruebas automáticas con fechas límite, meses sin registros y cambios de año.

### Crecimiento acumulado

- Mostrar el total acumulado por mes.
- Debajo o junto a cada punto se debe mostrar el incremento del mes, por ejemplo `+1` o `+2`.
- El acumulado del mes debe ser igual al acumulado anterior más las altas aplicables, conforme a la definición funcional acordada.

### Edad

- Calcular la edad desde la fecha de nacimiento en una fecha de corte conocida.
- Definir rangos estables y documentados.
- No guardar una edad fija en la base de datos porque cambia con el tiempo.

### Rendimiento

- Crear índices adecuados antes de optimizar con caché.
- Medir tiempos reales de las consultas.
- Considerar caché únicamente cuando el volumen o frecuencia lo justifique.
- Para exportaciones grandes, utilizar cursores o trabajos en cola.

---

## 10. Carnet y verificación

### Características conocidas

- Frente y reverso.
- Nombre, documento y fecha de registro visibles en el frente.
- Código QR y folio.
- Icono rojo basado en el concepto de dos manos sosteniendo un corazón.
- En el reverso: “Nombre de contacto” y “Teléfono” en azul y negrita, con líneas azules para completar.
- Opciones unificadas mediante un botón `Imprimir / Guardar PDF`.

### Implementación recomendada

- Renderizar una vista específica de impresión.
- Usar CSS de impresión y medidas físicas controladas.
- Convertir el QR a una representación imprimible antes de abrir el diálogo si se genera en canvas.
- Permitir que el diálogo del sistema seleccione impresora o “Guardar como PDF”.
- No intentar controlar desde JavaScript el destino predeterminado de impresión; el navegador y el sistema operativo lo deciden.

### Seguridad del QR

- Utilizar un token aleatorio, suficientemente largo y revocable.
- No colocar cédula, correo, teléfono ni respuestas médicas en la URL.
- La verificación pública debe revelar solo el mínimo acordado, por ejemplo vigencia del carnet, folio parcialmente mostrado y fecha de emisión.
- Evitar identificadores correlativos que permitan enumerar donantes.

---

## 11. Seguridad y protección de datos

La aplicación tratará datos personales y potencialmente sensibles. La seguridad debe diseñarse desde el inicio.

### Controles mínimos

- HTTPS obligatorio.
- Contraseñas con hashing seguro proporcionado por Laravel.
- Regeneración de sesión al autenticar.
- Protección CSRF.
- Cookies `Secure`, `HttpOnly` y política `SameSite` apropiada.
- Limitación de intentos de autenticación y registro público.
- Autorización por roles y acciones.
- Validación y normalización en servidor.
- Encabezados de seguridad en Nginx y aplicación.
- Dependencias actualizadas y revisión de vulnerabilidades.
- Usuario de MySQL con permisos mínimos.
- Secretos únicamente en variables de entorno o gestor autorizado.
- Copias de seguridad cifradas y pruebas periódicas de restauración.
- Política definida de conservación, baja y eliminación.

### Datos que no deben registrarse en logs

- Contraseñas.
- Tokens de sesión o verificación completos.
- Cédulas o pasaportes completos.
- Correos y teléfonos completos.
- Direcciones completas.
- Respuestas médicas.
- Texto o evidencia íntegra del consentimiento.
- Credenciales de base de datos, correo u otros servicios.

Los identificadores necesarios para investigar errores deben estar anonimizados, enmascarados o representados por identificadores internos no sensibles.

### Validación externa pendiente

Antes de producción deben revisarse, por personal competente:

- textos de consentimiento;
- fundamento jurídico y vigencia de las leyes citadas;
- tratamiento de menores y capacidad legal;
- datos médicos realmente necesarios;
- valor legal de la aceptación electrónica;
- proceso de revocación;
- instituciones autorizadas para consultar información;
- plazos de conservación y eliminación;
- contenido público del QR y carnet.

---

## 12. Logging, monitoreo y rendimiento

Laravel, Nginx, PHP-FPM, MySQL y Ubuntu proporcionan logs básicos, pero un flujo útil de observabilidad debe configurarse expresamente.

### Primera fase

- Logs estructurados, preferiblemente JSON en producción.
- Identificador de solicitud o correlación.
- Registro de excepciones y código HTTP.
- Duración total de cada solicitud.
- Detección de consultas lentas.
- Logs de Nginx, PHP-FPM y MySQL.
- Rotación y conservación definida mediante `logrotate` o equivalente.
- Endpoint técnico de salud, sin información sensible.
- Alertas por espacio en disco, errores críticos y caída de servicios.
- Laravel Pail únicamente como herramienta de desarrollo o diagnóstico controlado.

### Evolución posible

- OpenTelemetry para logs, métricas y trazas.
- Collector centralizado.
- Plataforma de visualización y alertas por decidir.
- Sentry u otra plataforma solo después de evaluar privacidad, ubicación de datos, costos y políticas institucionales.

### Vista administrativa

No se requiere inicialmente una vista de logs dentro de la administración funcional. Si posteriormente se crea una pantalla “Estado del sistema”, debe estar restringida a un rol técnico y mostrar resúmenes, no logs crudos ni trazas completas.

---

## 13. Configuración por entornos

### Ambientes mínimos

- `local`: desarrollo en macOS.
- `testing`: pruebas automatizadas con configuración aislada.
- `staging`: validación previa similar a producción.
- `production`: servidor Ubuntu.

### Variables que deben definirse

Ejemplo conceptual, sin credenciales reales:

```dotenv
APP_NAME="DONA ÓRGANOS PANAMÁ"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=America/Panama

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=dona_organos_panama
DB_USERNAME=dona_organos_user
DB_PASSWORD=

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=debug

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=false
QUEUE_CONNECTION=database
```

En producción:

- `APP_ENV=production`.
- `APP_DEBUG=false`.
- `APP_URL` con HTTPS.
- cookies seguras habilitadas.
- credenciales fuertes fuera del repositorio.
- nivel de log adecuado, sin información sensible.

El archivo `.env` nunca debe confirmarse en Git.

### Docker para MySQL local

El proyecto debe incluir un `compose.yaml` versionado que:

- fije la versión exacta de MySQL;
- use un volumen persistente con nombre específico del proyecto;
- lea credenciales desde variables de entorno;
- incluya un healthcheck;
- exponga un puerto local que no choque con MySQL instalado;
- no publique credenciales reales.

---

## 14. Pruebas y calidad

### Pruebas mínimas

- Registro correcto de un donante.
- Rechazo de datos inválidos.
- Normalización de correo, nombre y teléfono.
- Duplicidad de documento.
- Transacción y reversión ante fallos.
- Acceso permitido y denegado por rol.
- Estado `Activo` y `Baja`.
- Verificación pública sin fuga de información.
- Generación de folio y token no predecibles.
- Filtros y paginación.
- Fórmulas de cada métrica.
- Exportación consistente con los filtros.
- Respuesta ante meses sin datos.

### Herramientas y comandos esperados

```bash
composer test
php artisan test
./vendor/bin/pint --test
npm run build
php artisan route:list
php artisan migrate:fresh --seed
```

Se recomienda agregar integración continua en GitLab para ejecutar pruebas, formato y compilación en cada merge request.

### Datos de prueba

- Utilizar factories y seeders.
- No copiar datos reales de ciudadanos.
- Mantener escenarios suficientes para probar edades, provincias, altas, bajas y meses vacíos.

---

## 15. Despliegue previsto

### Flujo general

1. Integración y revisión mediante GitLab.
2. Ejecución de pruebas y compilación en CI.
3. Despliegue primero en `staging`.
4. Respaldo antes de migraciones productivas.
5. Instalación de dependencias con opciones de producción.
6. Migraciones controladas.
7. Construcción o publicación de assets.
8. Optimización de Laravel.
9. Reinicio controlado de PHP-FPM y trabajadores.
10. Verificación de salud y pruebas rápidas posteriores.

### Servicios de Ubuntu

- Nginx.
- PHP-FPM.
- MySQL Server si se instala en la misma máquina.
- Trabajador de colas cuando se incorporen correos, exportaciones o tareas diferidas.
- Programador de Laravel mediante cron o timer de systemd.
- Rotación de logs.
- Firewall y acceso SSH restringido.

No deben ejecutarse Node/Vite en modo de desarrollo ni `php artisan serve` en producción.

---

## 16. Decisiones confirmadas

- El producto nuevo se llama **DONA ÓRGANOS PANAMÁ**.
- Su alcance general es la donación de órganos y tejidos, no exclusivamente córneas.
- Será un desarrollo nuevo; los mockups solo son referencia.
- El stack se basa en Laravel 13, Blade, Bootstrap, JavaScript, Vite, Chart.js y MySQL.
- Se utilizará Ubuntu ARM64 con Nginx y PHP-FPM para producción.
- MySQL en Docker se recomienda para desarrollo local, no es obligatorio en producción.
- El campo funcional es `estado` con valores `Activo` y `Baja`.
- Se eliminan los estados `Aceptado`, `Rechazado` y `Por validar`.
- La administración actual es principalmente de consulta.
- El carnet se muestra dentro del detalle del donante y no mediante una segunda modal anidada.
- Un solo botón permite imprimir o guardar como PDF mediante el navegador.
- Google Apps Script y `localStorage` no forman parte del backend productivo.
- Se requiere logging y monitoreo técnico, pero no una vista de logs para el administrador funcional en la primera fase.
- El contenido informativo de la pantalla de inicio puede cambiar sin afectar la funcionalidad del sistema.
- Se requiere un CMS interno limitado para administrar aspectos legales, mitos y preguntas frecuentes: crear, editar, eliminar, ordenar y mostrar u ocultar.
- El CMS no es un constructor visual de páginas ni una instalación de WordPress; se implementará dentro de Laravel y persistirá en MySQL.
- El mockup muestra una pausa de seguridad de 30 segundos, con cuenta regresiva, después de tres códigos posteriores incorrectos para la cédula demostrativa `8-123-1234`.

---

## 17. Decisiones pendientes

Estas preguntas deben resolverse con los responsables correspondientes; no deben ser contestadas por suposición:

1. Lista definitiva de campos obligatorios y opcionales.
2. Textos legales definitivos y sus versiones.
3. Necesidad real de almacenar respuestas médicas.
4. Procedimiento autorizado para solicitar y aprobar una baja.
5. Si la administración podrá modificar datos en fases posteriores.
6. Roles administrativos definitivos.
7. Información mínima mostrada en la verificación pública.
8. Si se permitirán varios contactos informados.
9. Si habrá selección individual de órganos y tejidos.
10. Versión exacta de MySQL para desarrollo y producción.
11. Estrategia de envío de correo y proveedor institucional.
12. Política de copias de seguridad, retención y recuperación.
13. Necesidades de integración con OPT, MINSA, CSS u otros sistemas.
14. Definición exacta y nombre de la tercera gráfica de métricas.
15. Plataforma final de observabilidad y alertas.
16. Dominio, certificados, infraestructura y responsables de producción.
17. Tiempo definitivo de la pausa de seguridad por intentos fallidos y si el tiempo será progresivo o escalonado.
18. Si el CMS utilizará texto plano o un editor de formato enriquecido limitado.
19. Si la eliminación editorial será lógica y recuperable o definitiva.

---

## 18. Secuencia sugerida para iniciar el desarrollo

### Fase 0: definición

- Crear el repositorio nuevo.
- Aprobar alcance inicial y terminología.
- Fijar versiones del stack.
- Definir modelo de datos preliminar.
- Identificar las decisiones legales o funcionales bloqueantes.

### Fase 1: base técnica

- Crear Laravel 13.
- Configurar Bootstrap, Vite y Chart.js.
- Configurar MySQL en Docker.
- Preparar `.env.example` y ambientes.
- Añadir CI inicial.
- Establecer logging, identificador de solicitud y endpoint de salud.

### Fase 2: núcleo backend

- Migraciones y modelos.
- Registro del donante mediante Form Request y servicio transaccional.
- Folio y token público.
- Pruebas del registro.

### Fase 3: administración

- Autenticación y autorización.
- Listado, detalle, filtros y paginación.
- Exportación.
- Carnet y verificación.
- CMS interno: contenidos, visibilidad, orden, autorización e integración con el portal.

### Fase 4: métricas

- Consultas agregadas.
- Endpoints o controladores de métricas.
- Chart.js.
- Pruebas de fórmulas y rendimiento.

### Fase 5: endurecimiento y despliegue

- Revisión jurídica, funcional y de privacidad.
- Seguridad, respaldos y restauración.
- Staging en Ubuntu ARM64.
- Pruebas de aceptación.
- Documentación operativa y despliegue productivo.

---

## 19. Texto sugerido para iniciar el chat nuevo

Copiar el siguiente contexto al comenzar:

> Vamos a desarrollar desde cero la aplicación **DONA ÓRGANOS PANAMÁ**. El proyecto nuevo no debe modificar ni reutilizar directamente el código de los mockups. Usa como documentos rectores `Proyecto_corneas/Docs/Guia tecnica para iniciar DONA ORGANOS PANAMA.md` y `Proyecto_corneas/Docs/Matriz de trazabilidad funcional DONA ORGANOS PANAMA.md`; consulta `Proyecto_corneas/Mockups` únicamente como referencia funcional y visual. La aplicación de `/Users/marcosrodolforamoslopez/Developer/lab/visitor-management-system` es solo una referencia de buenas prácticas arquitectónicas. Antes de implementar, revisa ambos documentos completos, comprueba las versiones instaladas, propone la estructura inicial y señala cualquier decisión pendiente que realmente bloquee el primer incremento. No inventes requisitos jurídicos, médicos ni administrativos.

### Información que debe aportarse al chat nuevo

- Ruta elegida para el nuevo proyecto.
- URL del repositorio GitLab, cuando exista.
- Rama y estrategia de ramas acordadas.
- Versión exacta de MySQL seleccionada.
- Prioridad del primer incremento funcional.
- Cualquier decisión pendiente que ya haya sido resuelta después de publicar este documento.

---

## 20. Criterio final

La aplicación de referencia aporta una forma útil de organizar Laravel, mientras que los mockups describen la experiencia y las capacidades esperadas. **DONA ÓRGANOS PANAMÁ debe combinar esas referencias sin heredar su código, su dominio ni sus limitaciones.**

El objetivo es obtener una aplicación institucional mantenible, comprobable y segura, donde las reglas del negocio estén separadas de la interfaz, las métricas sean calculadas de manera consistente y los datos personales se manejen con el nivel de protección correspondiente.
