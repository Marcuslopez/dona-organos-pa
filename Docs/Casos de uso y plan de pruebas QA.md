# Casos de uso y plan de pruebas QA

## DONA ÓRGANOS PANAMÁ

**Versión del documento:** 1.1
**Fecha:** 21/08/2026
**Objetivo:** entregar al equipo de QA una guía funcional, verificable y organizada por módulos para validar el sistema.

## 1. Alcance

Este documento cubre los flujos implementados para:

- Portal público y navegación.
- Acceso administrativo.
- Validación de identidad.
- Registro, actualización, baja y reactivación de donantes.
- Carné, QR, PDF y correo electrónico.
- Consulta administrativa, filtros y exportación CSV.
- Métricas administrativas.
- CMS de contenidos y archivos multimedia.
- Contáctenos y mantenimiento administrativo de consultas.
- Datos demostrativos.
- Seguridad, permisos y validaciones transversales.

No forma parte de esta fase la integración real con el Tribunal Electoral ni el envío mediante el correo institucional definitivo. La identidad se valida mediante el proveedor simulado y los correos se capturan en smtp4dev.

## 2. Convenciones para QA

### Estados de ejecución

- **Aprobado:** el resultado coincide completamente con lo esperado.
- **Fallido:** existe una diferencia funcional, visual o de datos.
- **Bloqueado:** una dependencia del ambiente impide ejecutar el caso.
- **No aplica:** el caso no corresponde a la versión o ambiente probado.

### Prioridades

- **P0 crítica:** impide registrar, consultar, dar de baja o proteger los datos.
- **P1 alta:** afecta una función principal, pero existe una alternativa temporal.
- **P2 media:** afecta validaciones, presentación o usabilidad.
- **P3 baja:** mejora visual o de texto sin pérdida funcional.

### Evidencias mínimas

Por cada caso QA debe conservar:

- Resultado obtenido.
- Captura de pantalla o video cuando aplique.
- Datos de prueba utilizados, sin publicar información sensible real.
- Fecha, navegador, sistema operativo y usuario ejecutor.
- Identificador del defecto si el resultado es fallido.

> **Alcance de este plan:** las pruebas aquí descritas se ejecutan desde las interfaces web del portal, administración y la bandeja web de smtp4dev. No incluye comandos de terminal, consultas SQL, Artisan, inspección directa de archivos, migraciones, respaldos ni configuración de infraestructura. Esas tareas corresponden al procedimiento técnico de Desarrollo e Infraestructura.

## 3. Preparación del ambiente

### Servicios requeridos

- Aplicación Laravel disponible en el puerto configurado para el ambiente.
- El ambiente de QA está disponible, con sus datos de prueba, contenidos multimedia y cuentas previamente habilitados por Desarrollo e Infraestructura.
- smtp4dev está disponible mediante su interfaz web para inspeccionar los mensajes de prueba.

### Preparación de smtp4dev antes de casos de correo

Esta preparación **no es un caso de prueba**. La realiza Desarrollo o Infraestructura antes de que QA ejecute los casos que validan correos.

1. Desde el directorio del proyecto en el servidor, validar el estado del servicio:

   ```bash
   sudo docker compose -f compose.mail-dev.yaml ps
   ```

   Debe aparecer el servicio `smtp4dev` con estado `Up`.

2. Si no aparece o está detenido, iniciarlo:

   ```bash
   sudo docker compose -f compose.mail-dev.yaml up -d
   ```

3. Confirmar nuevamente el estado con el comando del paso 1.

4. Acceder a la bandeja de pruebas:

   - En una estación que tenga acceso local al servidor: `http://127.0.0.1:8080`.
   - Si el servidor solo expone smtp4dev en su propia interfaz local —configuración actual recomendada— Desarrollo o Infraestructura habilita un túnel SSH para el personal de QA:

     ```bash
     ssh -L 8080:127.0.0.1:8080 webdev@172.31.9.38
     ```

     Con el túnel abierto, QA navega en su equipo a `http://127.0.0.1:8080`.

5. Verificar que la bandeja muestra el indicador de servidor SMTP activo antes de ejecutar un caso de envío. Si no abre o el indicador no está activo, el caso se registra como **Bloqueado** y se escala a Desarrollo o Infraestructura.

### Configuración funcional relevante

- Máximo de intentos de identidad: 3.
- Bloqueo temporal predeterminado: 30 segundos.
- Vigencia predeterminada de identidad validada: 10 minutos, ajustable por ambiente.
- El formulario de un donante nuevo y el formulario de actualización ya iniciados no deben quedar bloqueados por el vencimiento de esa validación.
- Las sesiones de donantes y administradores vencen por inactividad según la configuración del ambiente; una actividad válida renueva únicamente el temporizador del perfil correspondiente.
- Para agilizar las pruebas, Desarrollo puede configurar previamente un tiempo reducido de inactividad. Las vistas protegidas de donantes y administración deben respetar ese vencimiento.
- Videos: MP4 o MOV, máximo 25 MB y 90 segundos.
- Las direcciones, credenciales y tiempos efectivos deben confirmarse contra el archivo `.env` del ambiente de QA.

### Datos sugeridos

- Administrador de desarrollo: `administrador1@admin.com`.
- Donante nuevo: cédula y código posterior que no existan.
- Donante activo: registro con cédula y código posterior conocidos.
- Donante de baja: registro previamente retirado.
- No utilizar cédulas ni información médica de personas reales en el ambiente de pruebas.

---

# Módulo 1. Portal público

## Flujo 1.1 — Visualización y navegación

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| PUB-001 | P1 | Abrir la página de inicio | Acceder a `/`. | El portal carga sin errores, conserva el diseño aprobado y muestra el enlace para registrarse y el acceso administrativo. |
| PUB-002 | P2 | Navegar por el menú | Activar cada opción del menú público. | Cada opción desplaza o dirige a la sección correcta sin recargas erróneas ni enlaces rotos. |
| PUB-003 | P1 | Iniciar registro | Activar “Haz clic para acceder” o la llamada de registro. | Se abre la validación de identidad en `/registro`. |
| PUB-004 | P1 | Abrir administración | Activar “Administración” con el candado. | Se abre el formulario de acceso administrativo. |
| PUB-005 | P2 | Diseño adaptable | Repetir en escritorio, tableta y móvil. | No existen desbordamientos horizontales, textos cortados ni controles inaccesibles. |
| PUB-006 | P2 | Accesibilidad básica | Navegar con teclado y revisar textos alternativos. | Los controles reciben foco visible; imágenes informativas tienen descripción y las decorativas no producen ruido al lector de pantalla. |

## Flujo 1.2 — Contenido publicado por el CMS

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| PUB-007 | P1 | Contenido visible | Publicar un contenido desde el CMS y recargar el inicio. | El contenido aparece en su sección y posición configurada. |
| PUB-008 | P1 | Contenido oculto | Desactivar “Mostrar en el portal público”. | El contenido no aparece en el portal, pero permanece administrable. |
| PUB-009 | P1 | Contenido eliminado | Eliminar lógicamente un contenido. | El contenido desaparece del portal y de la lista operativa sin afectar otros registros. |
| PUB-010 | P2 | Enlaces enriquecidos | Crear un enlace HTTPS dentro del contenido. | La frase seleccionada funciona como enlace; no se muestra como texto plano ni se permite un protocolo inseguro. |

---

# Módulo 2. Autenticación administrativa

## Flujo 2.1 — Inicio y cierre de sesión

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| AUT-001 | P0 | Acceso válido | Introducir credenciales de un administrador activo. | Se inicia sesión y se redirige directamente al dashboard. |
| AUT-002 | P1 | Credenciales inválidas | Introducir correo o contraseña incorrectos. | Se muestra un mensaje genérico que no revela qué dato falló. |
| AUT-003 | P0 | Administrador inactivo | Intentar acceder con una cuenta inactiva. | El acceso es rechazado y no se crea una sesión administrativa válida. |
| AUT-004 | P1 | Bloqueo por intentos | Fallar el acceso tres veces consecutivas. | El sistema bloquea temporalmente nuevos intentos y muestra la cuenta regresiva. |
| AUT-005 | P1 | Recuperación del bloqueo | Esperar el período configurado e intentar con datos válidos. | El acceso vuelve a estar disponible. |
| AUT-006 | P0 | Acceso directo sin sesión | Abrir una URL de administración en una ventana privada. | El sistema redirige al login. |
| AUT-007 | P0 | Cerrar sesión | Activar “Cerrar sesión”. | La sesión se invalida y las rutas protegidas dejan de ser accesibles. |
| AUT-008 | P0 | Expiración por inactividad administrativa | Mantener inactiva la sesión administrativa hasta superar el tiempo configurado. | Se informa que la sesión finalizó, se invalida el acceso y las rutas administrativas exigen autenticarse nuevamente. |
| AUT-009 | P1 | Renovación por actividad administrativa | Realizar una navegación o acción válida antes de que venza el temporizador. | El contador administrativo se renueva y la sesión continúa disponible. |

---

## Flujo 2.2 — Administración de usuarios (usuario master)

Este flujo se valida desde el navegador y está disponible únicamente para cuentas con rol **master**.

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| MAS-001 | P0 | Acceso inicial del master | Iniciar sesión con una cuenta master activa. | El usuario accede directamente a Administración de usuarios, sin pasar primero por el dashboard administrativo. |
| MAS-002 | P0 | Protección del mantenimiento | Intentar abrir Administración de usuarios sin sesión o usando una cuenta administradora regular. | El acceso se rechaza o redirige según corresponda; no se muestran datos de cuentas administrativas. |
| MAS-003 | P1 | Consulta y filtros | Buscar por nombre o correo; filtrar por rol y estado. | La grilla muestra únicamente las cuentas que cumplen los filtros seleccionados. |
| MAS-004 | P0 | Crear usuario administrativo | Usar “Adicionar usuario”, completar nombre, correo, contraseña, rol y estado válidos, y guardar. | Se crea la cuenta, aparece en la grilla y puede iniciar sesión de acuerdo con su rol y estado. |
| MAS-005 | P1 | Validaciones al crear | Intentar guardar con datos obligatorios vacíos, correo inválido o correo ya registrado. | No se crea la cuenta y se muestran mensajes claros junto a los datos que deben corregirse. |
| MAS-006 | P0 | Modificar usuario | Abrir “Ver”, cambiar los datos permitidos —por ejemplo nombre, rol, estado o contraseña— y guardar. | Solo los cambios realizados se conservan y se reflejan en la grilla y en el siguiente acceso del usuario. |
| MAS-007 | P1 | Estado de cuenta | Marcar una cuenta administrativa como inactiva e intentar iniciar sesión con ella; luego reactivarla. | La cuenta inactiva no puede acceder; al reactivarla recupera el acceso con sus credenciales vigentes. |
| MAS-008 | P1 | Navegación y cierre de sesión | Usar los botones inferiores “Dashboard administrativo” e “Inicio”; utilizar “Cerrar sesión” en la cabecera. | Cada botón lleva al destino correcto y el cierre invalida la sesión master. |

---

# Módulo 3. Validación de identidad

## Flujo 3.1 — Validación de entrada

| ID | Prioridad | Caso | Datos o pasos principales | Resultado esperado |
|---|---|---|---|---|
| IDV-001 | P1 | CAPTCHA correcto | Completar cédula, código posterior y CAPTCHA válido. | La solicitud continúa a la evaluación de identidad. |
| IDV-002 | P1 | CAPTCHA incorrecto | Introducir un CAPTCHA incorrecto. | Se rechaza la solicitud, se renueva el CAPTCHA y no se consume un intento de coincidencia de identidad. |
| IDV-003 | P2 | Renovar CAPTCHA | Activar “Otro código”. | Se genera una imagen nueva y la anterior deja de ser válida. |
| IDV-004 | P1 | Formatos nacionales | Probar `1-1234-12345` y provincias admitidas del 1 al 13. | Se aceptan únicamente estructuras válidas con guiones. |
| IDV-005 | P1 | Formatos especiales | Probar `PE-1234-12345`, `E-1234-123456` y `N-1234-1234`. | Los tres formatos se aceptan. |
| IDV-006 | P1 | Formato no admitido | Probar provincia 14, prefijo `8AV`, puntos, espacios o símbolos. | Se muestra una validación y no se procesa la identidad. |
| IDV-007 | P1 | Código posterior válido | Probar valores alfanuméricos de 9 y 12 posiciones. | Ambos límites se aceptan. |
| IDV-008 | P1 | Código posterior inválido | Probar 8 o 13 posiciones, espacios, guiones o símbolos. | Se rechaza el valor con un mensaje comprensible. |
| IDV-009 | P1 | Límite de intentos | Fallar tres coincidencias de identidad. | Se aplica el bloqueo temporal configurado. |
| IDV-010 | P0 | Entrada directa a pantalla validada | Abrir la pantalla validada sin completar el paso anterior. | El sistema impide continuar y redirige al inicio del flujo. |

## Flujo 3.2 — Decisión según existencia del donante

| ID | Prioridad | Caso | Precondición y pasos | Resultado esperado |
|---|---|---|---|---|
| IDV-011 | P0 | Identidad nueva | La cédula y el código no existen; completar validación. | Se abre el formulario de registro de un nuevo donante. |
| IDV-012 | P0 | Donante activo coincidente | La cédula existe y el código coincide. | Se muestra el saludo personalizado, el carné vigente y las opciones de imprimir, actualizar, dar de baja y volver. |
| IDV-013 | P0 | Donante activo sin coincidencia | La cédula existe, pero el código es distinto. | Se rechaza el acceso sin revelar información del donante. |
| IDV-014 | P0 | Código asociado a otra cédula | La cédula es nueva, pero el código ya pertenece a otra persona. | Se rechaza la combinación; no se permite duplicar el código posterior. |
| IDV-015 | P0 | Donante de baja coincidente | La cédula y el código corresponden a un donante retirado. | Se muestra el saludo, carné, fecha y hora de baja, y la opción de registrar nuevamente la voluntad. |

---

# Módulo 4. Registro de nuevo donante

## Flujo 4.1 — Datos personales y ubicación

| ID | Prioridad | Caso | Datos o pasos principales | Resultado esperado |
|---|---|---|---|---|
| REG-001 | P0 | Registro completo válido | Completar todos los datos obligatorios y guardar. | Se crea un único donante, consentimiento, contactos, respuestas médicas y carné activo. |
| REG-002 | P1 | Nombres válidos | Usar letras con tildes, primer nombre y primer apellido; completar opcionales. | Se aceptan los caracteres permitidos y se normalizan espacios al inicio y al final. |
| REG-003 | P1 | Nombres inválidos | Introducir números o símbolos no admitidos. | El formulario señala el campo y no guarda. |
| REG-004 | P1 | Correo electrónico | Probar correo válido y luego uno sin formato de correo. | Solo el formato válido permite continuar. |
| REG-005 | P1 | Teléfono de Panamá | Probar `123-4567` y `6123-4567`; luego letras o símbolos. | Los dos formatos se aceptan con `+507`; los demás se rechazan. |
| REG-006 | P1 | Fecha digitada válida | Escribir una fecha real en `DD/MM/AAAA`. | Se insertan automáticamente los separadores y la fecha se interpreta correctamente. |
| REG-007 | P1 | Fecha imposible | Probar 31/02, mes 13, fecha futura o año fuera del límite de 100 años. | Se muestra exclusivamente “Fecha incorrecta” y no se guarda. |
| REG-008 | P0 | Menor de 18 años | Introducir una fecha válida correspondiente a una persona menor. | Se muestra el mensaje de mayoría de edad y no se permite guardar. |
| REG-009 | P1 | Fecha mediante calendario | Elegir la fecha con el control de calendario. | Se refleja en formato `DD/MM/AAAA` y se aplican las mismas reglas. |
| REG-010 | P1 | Dirección encadenada | Elegir provincia, distrito y corregimiento. | Cada lista muestra únicamente opciones dependientes de la anterior y guarda la selección correcta. |

## Flujo 4.2 — Contactos, alcance y consentimiento

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| REG-011 | P0 | Un contacto obligatorio | Completar exactamente un contacto válido. | El registro se guarda; el primer contacto queda como principal. |
| REG-012 | P0 | Sin contactos | Eliminar o dejar vacíos todos los contactos e intentar guardar. | Se informa que debe existir por lo menos un contacto. |
| REG-013 | P1 | Dos contactos | Completar dos contactos. | Ambos quedan relacionados con el donante y aparecen en el detalle y carné. |
| REG-014 | P1 | Datos del contacto | Validar nombres, parentesco, correo opcional y teléfono. | Se aplican las mismas reglas de nombres, correo y teléfono definidas para el donante. |
| REG-015 | P1 | Alcance único | Seleccionar “Solo córneas” y repetir con “Órganos y tejidos”. | Solo una opción puede estar activa y se guarda exactamente la elegida. |
| REG-016 | P0 | Información médica | Responder las preguntas médicas y guardar. | Todas las respuestas se conservan y aparecen en el detalle administrativo. |
| REG-017 | P0 | Consentimiento requerido | Omitir una aceptación obligatoria. | No se crea el registro y se identifica el consentimiento pendiente. |
| REG-018 | P1 | Tiempo del formulario nuevo | Validar identidad, permanecer más allá del TTL y guardar el formulario ya iniciado. | El registro nuevo se guarda; el TTL no interrumpe el llenado en curso. |

## Flujo 4.3 — Finalización

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| REG-019 | P0 | Pantalla de éxito | Completar un registro válido. | Se muestra “Tu voluntad fue registrada”, el carné y los botones aprobados. |
| REG-020 | P0 | Registro visible | Buscar al donante en el dashboard después del alta. | Aparece una sola vez con los datos, estado y contacto registrados; no se duplica la cédula. |
| REG-021 | P1 | Folio del carné | Crear registros consecutivos. | Se generan identificadores únicos con formato `CD-0000001`. |
| REG-022 | P1 | Correo de alta | Revisar smtp4dev después del registro. | Llega un correo al destinatario capturado con el carné PDF adjunto. |

---

# Módulo 5. Donante activo: consulta, actualización y baja

## Flujo 5.1 — Autoservicio del donante activo

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| DON-001 | P0 | Pantalla del donante activo | Validar una identidad activa. | Se muestra un saludo con el primer nombre, documento, contador de inactividad, carné y botonera inferior. |
| DON-002 | P0 | Expiración por inactividad del donante | Mantener la pantalla sin actividad hasta superar el tiempo configurado. | Aparece un diálogo con icono de reloj y “Sesión finalizada”; la sesión del donante se invalida y debe repetirse la validación de identidad para continuar. |
| DON-003 | P1 | Imprimir o guardar carné | Activar el botón correspondiente. | Se abre la salida imprimible/PDF sin modificar el registro. |

## Flujo 5.2 — Actualización de datos

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| DON-004 | P0 | Actualizar datos personales | Cambiar teléfono, correo o dirección y guardar. | Se actualiza el mismo donante sin crear una fila adicional. |
| DON-005 | P0 | Actualizar contactos | Agregar, cambiar o retirar contactos, manteniendo al menos uno. | Se sincronizan los contactos; no quedan duplicados ni relaciones huérfanas. |
| DON-006 | P0 | Cambiar alcance | Cambiar la decisión de donación y aceptar el nuevo consentimiento. | Se conserva un solo donante, se registra el historial y se genera un consentimiento/carné nuevo cuando corresponde. |
| DON-007 | P1 | Cambio solo personal | Modificar únicamente un dato personal. | Se conserva el carné y consentimiento vigentes cuando el cambio no altera la voluntad. |
| DON-008 | P1 | Actualización prolongada | Iniciar la actualización y guardar después de vencer el TTL. | El formulario iniciado permite guardar las modificaciones válidas. |
| DON-009 | P1 | Correo de actualización | Completar un cambio que genera carné nuevo. | smtp4dev recibe el mensaje con el carné actualizado. |
| DON-010 | P0 | Unicidad después de actualizar | Buscar la cédula en el dashboard tras actualizar los datos. | La cédula aparece una sola vez; los carnés históricos no duplican al donante en la grilla. |

## Flujo 5.3 — Baja voluntaria

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| BAJ-001 | P0 | Abrir confirmación | Activar “Darme de baja”. | Se abre un diálogo propio del sistema con texto claro, botón rojo de confirmar y botón verde de cancelar. |
| BAJ-002 | P1 | Cancelar baja | Activar “Cancelar”. | El diálogo se cierra y el donante continúa activo. |
| BAJ-003 | P0 | Confirmar baja | Confirmar la acción. | El estado cambia a Baja, se registra fecha/hora e historial, y se revocan consentimiento y carné vigentes. |
| BAJ-004 | P0 | Visualización administrativa | Filtrar bajas en el dashboard. | El donante aparece una sola vez con etiqueta roja clara “Baja”. |
| BAJ-005 | P1 | Reingreso posterior | Volver a validar la misma cédula y código. | Se abre el flujo de reactivación, no el formulario de alta nueva. |

---

# Módulo 6. Reactivación del donante

## Flujo 6.1 — Registro nuevamente de la voluntad

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| REA-001 | P0 | Resumen de baja | Validar un donante retirado. | Se muestra saludo, carné, fecha y hora de baja en formato de 12 horas y los datos organizados visualmente. |
| REA-002 | P0 | Abrir reactivación | Activar “Registrar nuevamente mi voluntad”. | Se abre el formulario precargado para revisar datos y aceptar un nuevo consentimiento. |
| REA-003 | P0 | Reactivar | Completar las confirmaciones y guardar. | El mismo donante vuelve a Activo; se crean historial, consentimiento y carné nuevos. |
| REA-004 | P0 | No duplicar persona | Buscar la cédula en el dashboard tras reactivar. | Continúa apareciendo una sola fila para la cédula. |
| REA-005 | P1 | Folio nuevo | Comparar el carné revocado con el reactivado. | El carné nuevo tiene identificador distinto y el anterior permanece revocado. |
| REA-006 | P1 | Correo de reactivación | Revisar smtp4dev. | Se recibe el carné nuevo como adjunto. |

---

# Módulo 7. Carné, QR, PDF y correo

## Flujo 7.1 — Presentación del carné

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| CAR-001 | P1 | Diseño frontal y posterior | Revisar carné en alta, autoservicio y administración. | Las tres vistas utilizan el mismo componente visual y no divergen en contenido o formato. |
| CAR-002 | P1 | Medidas físicas | Imprimir o inspeccionar el PDF. | Cada cara respeta 85,60 × 53,98 mm según la configuración de salida. |
| CAR-003 | P1 | Nombre largo | Registrar dos nombres y dos apellidos. | El nombre del donante se representa completo sin salirse del área. |
| CAR-004 | P1 | Contactos largos | Usar nombres completos extensos. | En el carné se abrevian los segundos nombres/apellidos con inicial cuando sea necesario. |
| CAR-005 | P2 | Un solo contacto | Generar carné con un contacto. | La segunda fila conserva “Nombre:” y “Tel.:” vacíos para mantener el diseño. |
| CAR-006 | P1 | Nombre del archivo | Guardar el PDF. | El nombre identifica el carné, folio y donante de forma legible. |

## Flujo 7.2 — Verificación mediante QR

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| QR-001 | P0 | Escanear carné activo | Escanear desde otro dispositivo usando una URL accesible desde el móvil. | Se abre la verificación pública y se informa que el registro está activo. En QA, APP_URL no puede apuntar a localhost ni a 127.0.0.1. |
| QR-002 | P0 | Privacidad del QR | Revisar la página pública. | No se exponen nombre, cédula, contactos, información médica ni dirección. |
| QR-003 | P1 | Contenido público | Revisar fecha/hora e identificador. | Muestra saludo genérico, “Voluntad registrada”, carné `CD-...` y agradecimiento. |
| QR-004 | P0 | Carné revocado | Escanear el QR de un carné dado de baja. | No se presenta como vigente; se informa que el registro no está activo. |
| QR-005 | P1 | Token inválido | Alterar el token de la URL. | No se revela información y se responde con error controlado/no encontrado. |

## Flujo 7.3 — Correo y PDF adjunto

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| MAIL-001 | P0 | Entrega simulada | Registrar/reactivar/cambiar voluntad. | smtp4dev captura un mensaje con remitente, destinatario, asunto y cuerpo correctos. |
| MAIL-002 | P0 | Adjunto | Descargar el adjunto desde smtp4dev. | Es un PDF legible, no vacío y corresponde al donante y carné del mensaje. |
| MAIL-003 | P1 | Consistencia visual | Comparar adjunto con la vista administrativa. | Contenido y diseño coinciden. |
| MAIL-004 | P1 | Falla del correo | Simular indisponibilidad SMTP durante el registro. | El registro principal no queda corrupto; el error se registra y se informa según la política definida. |

---

# Módulo 8. Administración de donantes

## Flujo 8.1 — Grilla, filtros y paginación

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| ADM-001 | P1 | Estado predeterminado | Abrir el dashboard sin parámetros. | Se muestran únicamente donantes activos. |
| ADM-002 | P1 | Filtro Todos | Seleccionar “Todos”. | Se muestran activos y bajas sin duplicar donantes. |
| ADM-003 | P1 | Filtro Bajas | Seleccionar “Bajas”. | Solo aparecen registros con estado Baja. |
| ADM-004 | P1 | Filtros combinados | Combinar nombre, cédula, provincia, estado y fechas. | La lista cumple simultáneamente todas las condiciones. |
| ADM-005 | P2 | Limpiar | Aplicar filtros y activar “Limpiar”. | Se restauran los valores predeterminados y el estado Activos. |
| ADM-006 | P1 | Paginación | Probar 5, 10, 15 y 20 registros por página. | Se respeta el tamaño elegido y se conservan los filtros. |
| ADM-007 | P0 | Donante con varios carnés | Consultar una persona actualizada/reactivada. | Aparece una sola fila por donante. |
| ADM-008 | P2 | Etiquetas | Comparar Activo y Baja. | Activo usa verde claro y Baja rojo claro, ambas legibles. |

## Flujo 8.2 — Exportación CSV

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| CSV-001 | P1 | Exportar resultados | Aplicar filtros y descargar CSV. | El archivo incluye todos los registros coincidentes, no solo la página visible. |
| CSV-002 | P1 | Fidelidad | Comparar conteo y campos con el dashboard. | El contenido coincide con los filtros y no duplica donantes. |
| CSV-003 | P2 | Caracteres especiales | Exportar nombres con tildes y ñ. | El archivo conserva los caracteres y abre correctamente en una hoja de cálculo. |
| CSV-004 | P2 | Selección de carpeta | Descargar con el navegador configurado para preguntar ubicación. | El diálogo depende de la configuración del navegador; el sistema entrega nombre y tipo correctos. |

## Flujo 8.3 — Detalle administrativo

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| DET-001 | P0 | Ver expediente | Activar “Ver”. | Se muestra cada dato relevante una sola vez: persona, ubicación, contactos, voluntad, consentimiento e información médica. |
| DET-002 | P1 | Evidencia técnica | Activar el botón de evidencia. | La información técnica se despliega bajo demanda y está contraída inicialmente. |
| DET-003 | P1 | Imprimir expediente | Imprimir/guardar PDF con evidencia contraída. | El documento cabe en una página cuando el contenido lo permite y omite la evidencia cerrada. |
| DET-004 | P1 | Imprimir con evidencia | Expandir evidencia y volver a imprimir. | La sección técnica aparece en la salida. |
| DET-005 | P2 | Legibilidad | Revisar impresión en color y escala de grises. | Etiquetas, contactos y preguntas médicas mantienen contraste y tamaño legible. |
| DET-006 | P1 | Carné administrativo | Imprimir/guardar el carné desde el detalle. | Se utiliza el mismo formato oficial que en el registro y correo. |

---

# Módulo 9. Métricas administrativas

## Flujo 9.1 — Acceso y fidelidad de datos

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| MET-001 | P0 | Acceso protegido | Abrir métricas sin sesión y luego con administrador activo. | Sin sesión se redirige al login; con sesión se muestran las gráficas. |
| MET-002 | P0 | Fuente de datos | Comparar las gráficas con los listados y filtros visibles en el dashboard. | Los valores representan los donantes normales y demostrativos disponibles en el ambiente. |
| MET-003 | P0 | Crecimiento acumulado | Comparar los últimos 12 meses. | Solo acumula donantes cuyo estado actual es Activo, según la regla aprobada. |
| MET-004 | P0 | Altas y bajas mensuales | Comparar registros por mes y estado actual. | Las altas y bajas corresponden a fecha de registro y `donors.status`, sin valores inventados. |
| MET-005 | P1 | Meses sin actividad | Validar meses con valor cero. | Se muestran explícitamente con cero, sin omitir ni desplazar el mes. |
| MET-006 | P1 | Distribución por estado | Contar activos y bajas. | La gráfica coincide con el total actual de cada estado. |
| MET-007 | P1 | Distribución por edad | Recalcular edades con la fecha actual. | Los donantes quedan en el rango correcto y el total coincide con la población aplicable. |
| MET-008 | P1 | Donantes por provincia | Filtrar el dashboard por cada provincia y contrastar el conteo con la gráfica. | Cada barra/valor coincide con los registros visibles y la suma total es correcta. |
| MET-009 | P2 | Orden visual | Revisar la página completa. | El orden es: crecimiento acumulado; altas/bajas de 12 meses; altas/bajas; edad y provincia. |
| MET-010 | P1 | Actualización | Crear un donante de prueba y recargar métricas. | El registro se incluye inmediatamente en las gráficas correspondientes. |

---

# Módulo 10. CMS de contenidos

## Flujo 10.1 — Listado y permisos

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| CMS-001 | P0 | Acceso protegido | Abrir `/administracion/contenidos` sin sesión. | Se exige autenticación. |
| CMS-002 | P1 | Secciones permitidas | Abrir “Adicionar contenido”. | Solo existen: Aspectos Legales Importantes, Mitos y Tabúes Desmentidos, Preguntas Frecuentes e Historias Personales. |
| CMS-003 | P1 | Filtros | Filtrar por texto, sección y estado. | La grilla muestra únicamente contenidos coincidentes. |
| CMS-004 | P2 | Paginación | Probar 10, 20 y 30 registros. | La selección y los filtros se conservan. |

## Flujo 10.2 — Crear y modificar texto

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| CMS-005 | P0 | Campos obligatorios | Intentar guardar sin sección, título o contenido. | Se muestran advertencias y no se crea el registro. |
| CMS-006 | P1 | Crear contenido visible | Completar sección, título, texto, orden y visibilidad. | Se crea y aparece en el portal en la posición indicada. |
| CMS-007 | P1 | Crear contenido oculto | Guardar con visibilidad desactivada. | Se administra en CMS, pero no aparece públicamente. |
| CMS-008 | P1 | Editar | Abrir “Ver”, modificar un campo y guardar. | Guardar se habilita al cambiar datos y persiste la modificación. |
| CMS-009 | P1 | Volver sin guardar | Modificar y activar “Volver”. | Regresa al CMS sin aplicar cambios no guardados. |
| CMS-010 | P1 | Eliminar | Activar “Eliminar”, cancelar y luego confirmar. | Cancelar conserva el registro; confirmar realiza baja lógica y lo retira del portal. |
| CMS-011 | P1 | Orden ocupado | Insertar/mover un contenido a una posición ya usada. | El contenido ocupa esa posición y los siguientes se desplazan; no quedan órdenes duplicados. |
| CMS-012 | P1 | Cerrar espacio | Eliminar un contenido intermedio. | Los siguientes se reordenan para cerrar el espacio. |

## Flujo 10.3 — Formatos especiales y editor enriquecido

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| CMS-013 | P1 | Título de mito sin comillas | Escribir el texto del mito sin comillas. | Se guarda y presenta como `Mito: "texto"`. |
| CMS-014 | P1 | Título de mito con comillas | Escribir el texto ya encerrado entre comillas. | Se conserva una sola pareja de comillas, sin duplicarlas. |
| CMS-015 | P1 | Pregunta sin signos | Escribir una pregunta sin `¿` o `?`. | El sistema agrega ambos signos. |
| CMS-016 | P1 | Pregunta con signos | Escribir una pregunta completa. | Se conserva tal como fue escrita, sin signos duplicados. |
| CMS-017 | P1 | Trix con enlace seguro | Seleccionar una frase y asociar una URL HTTP/HTTPS. | El enlace se guarda, funciona públicamente y abre según la configuración definida. |
| CMS-018 | P0 | Enlace inseguro | Intentar `javascript:`, `data:` u otro protocolo no permitido. | Se elimina/rechaza el enlace sin ejecutar código. |
| CMS-019 | P0 | Saneamiento HTML | Pegar HTML con scripts, eventos o etiquetas peligrosas. | El contenido se sanea; no se ejecuta JavaScript. |

---

# Módulo 11. CMS multimedia

La metadata de los medios se administra junto con el contenido. Los archivos se publican a través del portal y no deben requerir acciones técnicas por parte de QA.

## Flujo 11.1 — Imágenes de aspectos legales

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| IMG-001 | P1 | Archivo de origen válido | Seleccionar JPG, PNG o WebP de hasta 15 MB en el selector. | Se abre el recortador sin enviar aún el formulario. |
| IMG-002 | P1 | Recorte 16:9 | Ajustar el marco y confirmar. | Se prepara un JPG de 1600×900, 16:9 y máximo 2 MB. |
| IMG-003 | P1 | Desplazamiento modal | Seleccionar una imagen grande y recorrer el formulario. | El modal tiene barra de desplazamiento y permite acceder a todos los controles. |
| IMG-004 | P1 | Descripción accesible | Intentar guardar una imagen sin descripción. | Se solicita una descripción accesible antes de guardar. |
| IMG-005 | P1 | Imagen publicada | Guardar un contenido con imagen y abrir el portal. | La imagen y su descripción accesible se muestran correctamente en el contenido publicado. |
| IMG-006 | P1 | Reemplazar imagen | Editar el contenido y cargar otra imagen. | La nueva imagen se muestra en el portal en lugar de la anterior. |
| IMG-007 | P1 | Retirar imagen | Retirar el medio y guardar. | El texto se mantiene publicado y la imagen deja de aparecer. |

## Flujo 11.2 — Videos de historias personales

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| VID-001 | P0 | MP4 horizontal válido | Subir H.264/AAC de hasta 25 MB y 90 s. | Se valida, normaliza si aplica, almacena y reproduce en el portal. |
| VID-002 | P0 | MOV válido | Subir MOV de iPhone/Mac dentro de límites. | Se acepta y convierte a MP4 H.264/AAC para publicación. |
| VID-003 | P1 | Video vertical | Subir 576×1024 o 720×1280 dentro de tamaño/duración. | Se normaliza a formato horizontal con bandas laterales, sin recortar contenido. |
| VID-004 | P1 | Duración excesiva | Subir un video de más de 90 segundos. | Se rechaza; se muestra solo el resumen de errores y no se abre automáticamente el formulario de edición. |
| VID-005 | P1 | Tamaño excesivo | Subir un video de más de 25 MB. | Se rechaza de forma controlada antes de procesarlo; no aparece una excepción técnica. |
| VID-006 | P1 | Formato no permitido | Subir AVI, MKV u otro formato no admitido. | Se muestra la lista de formatos permitidos y no se guarda. |
| VID-007 | P1 | Archivo corrupto | Subir un MP4/MOV ilegible. | Se muestra una advertencia clara y el video no se publica. |
| VID-008 | P0 | Reproducción pública | Publicar la historia y reproducirla. | El control muestra duración real y reproduce audio/video; no queda en 0:00. |
| VID-009 | P1 | Solicitudes parciales | Adelantar y retroceder el reproductor. | El servidor entrega rangos correctamente y el video continúa sin descargarlo completo nuevamente. |
| VID-010 | P1 | Sustituir video | Cargar un video nuevo sobre una historia existente. | Se publica el nuevo video en lugar del anterior. |
| VID-011 | P1 | Retirar video | Quitar el video y guardar la historia. | El texto permanece y el reproductor deja de aparecer. |
| VID-012 | P1 | Eliminar historia | Eliminar una historia con video. | La historia deja de mostrarse en el portal. |
| VID-013 | P1 | Historia sin video | Crear una historia solo con título y texto. | Se guarda y se presenta correctamente sin reproductor. |
| VID-014 | P0 | Texto obligatorio | Intentar crear una historia con video, pero sin título o texto. | Se rechaza porque el video no sustituye los campos obligatorios. |

---

# Módulo 12. Contáctenos y mantenimiento administrativo de consultas

## Flujo 12.1 — Formulario público y notificaciones

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| CON-001 | P1 | Navegación y atención telefónica | Abrir el menú público y activar “Contáctenos”. | La opción se presenta entre Testimonios y Registrarme; el formulario muestra el teléfono 503-6033 y el horario de 8:00 a. m. a 4:00 p. m. |
| CON-002 | P0 | Campos y aceptación obligatorios | Intentar enviar sin correo, consulta o aceptación de condiciones. | No se crea la consulta y se identifican claramente los campos o aceptación pendientes. |
| CON-003 | P1 | Nombre opcional y formato | Enviar una consulta sin nombre; repetir con nombre válido y con minúsculas, números o espacios duplicados. | Sin nombre se permite el envío; cuando se proporciona, solo acepta letras, un espacio entre palabras y cada palabra con inicial mayúscula. |
| CON-004 | P1 | Formato de correo | Ingresar un correo inválido y luego uno válido. | El formato inválido se rechaza; con correo válido se permite continuar si los demás requisitos se cumplen. |
| CON-005 | P1 | Condiciones de uso y privacidad | Abrir el enlace junto al check de aceptación. | Se muestra el texto de privacidad, la referencia a Ley 81 de 2019 y Decreto 285 de 2021, y los enlaces externos configurados son funcionales. |
| CON-006 | P0 | Envío identificado | Enviar consulta con nombre, correo, texto y aceptación válidos. | Se crea una consulta en estado Nueva con historial de creación; smtp4dev recibe el acuse para el remitente y la notificación interna para Administrador1. |
| CON-007 | P1 | Envío sin nombre | Enviar consulta válida omitiendo el nombre. | El acuse omite el saludo personal y la notificación interna usa “Sin nombre” en el asunto y datos del remitente. |
| CON-008 | P1 | Estructura de correos | Inspeccionar ambos mensajes en smtp4dev. | El remitente recibe el mensaje de recepción; soporte recibe asunto, nombre o “Sin nombre”, correo y texto íntegro de la consulta. |
| CON-009 | P1 | Límite antiabuso | Enviar más de cinco consultas desde el mismo cliente durante un minuto. | El límite bloquea el exceso sin crear consultas ni enviar correos adicionales. |

## Flujo 12.2 — Gestión administrativa de consultas

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| CON-010 | P0 | Acceso protegido | Abrir la gestión de consultas sin sesión o con sesión vencida. | Se redirige al login; no se expone el contenido de las consultas. |
| CON-011 | P1 | Listado y filtros | Abrir el mantenimiento y filtrar por texto y estados Nueva, En proceso, Respondida y Cerrada. | La grilla muestra solo las consultas coincidentes y conserva los filtros aplicados. |
| CON-012 | P0 | Tomar una consulta | Un administrador toma una consulta Nueva para atenderla personalmente. | La consulta pasa a En proceso, el administrador queda como responsable y el historial conserva la transición. |
| CON-013 | P0 | Evitar atención simultánea | Dos administradores intentan tomar la misma consulta. | Solo uno queda asignado; el otro recibe un mensaje controlado y no puede responderla. |
| CON-014 | P0 | Asignación y reasignación por usuario master | El master asigna una consulta a un administrador activo; repetir reasignándola a otro administrador. | Se actualiza el responsable, se registra cada cambio en el historial y el responsable asignado recibe una notificación por correo. |
| CON-015 | P0 | Protección del enlace de asignación | Abrir el enlace de una notificación con sesión vencida; repetir con sesión activa del administrador asignado. | Sin sesión se exige login; con sesión activa y autorización válida se abre la consulta asignada. El correo no muestra el detalle sensible. |
| CON-016 | P0 | Responder consulta | El responsable asignado redacta y envía una respuesta. | La respuesta queda registrada, se envía al correo del remitente y el estado pasa a Respondida. |
| CON-017 | P1 | Cerrar consulta | Intentar cerrar una consulta nueva/en proceso y luego una respondida. | Solo una consulta Respondida puede pasar a Cerrada; después no admite nuevas respuestas. |
| CON-018 | P1 | Trazabilidad | Revisar historial de una consulta creada, asignada, respondida y cerrada. | Se conservan acciones, usuario responsable, estado y fecha/hora de cada transición. |
| CON-019 | P0 | Respuesta limitada al responsable | Con una consulta asignada a Administrador2, abrirla como master y como otro administrador. | El master puede supervisar y reasignar, pero el área de respuesta y su envío permanecen deshabilitados; los demás administradores tampoco pueden responder. |
| CON-020 | P0 | Autoadjudicación del master | El master abre una consulta nueva o asignada a otro usuario, se la asigna a sí mismo y redacta una respuesta. | Tras quedar asignado al master, se habilita la respuesta; al enviarla se registra la acción y se notifica al remitente. |
| CON-021 | P1 | Consulta cerrada | Abrir una consulta Cerrada como master, responsable y otro administrador. | La información e historial pueden revisarse conforme a los permisos, pero no se permite reasignarla ni enviar respuestas adicionales. |

---

# Módulo 13. Seguridad y comportamiento transversal

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| SEG-002 | P0 | Autorización | Intentar rutas administrativas como invitado o usuario inactivo. | No se exponen datos ni se ejecutan acciones. |
| SEG-003 | P0 | Inyección SQL | Introducir caracteres SQL en búsquedas y formularios. | Se tratan como datos; no modifican consultas ni estructura. |
| SEG-004 | P0 | XSS | Introducir scripts en nombres y contenidos. | Se validan/sanean y nunca se ejecutan en el portal o administración. |
| SEG-007 | P1 | Concurrencia de folios | Crear dos donantes casi simultáneamente. | Ambos reciben folios únicos y válidos. |
| SEG-008 | P1 | Doble envío | Hacer doble clic o reenviar el formulario de alta. | No se crean donantes, consentimientos o carnés duplicados. |
| SEG-009 | P1 | Auditoría | Crear, modificar y dar de baja registros. | Se conservan fechas y usuario responsable donde el modelo lo contempla. |
| SEG-010 | P1 | Datos sensibles | Revisar URLs, QR, pantallas y mensajes públicos. | No exponen código posterior, respuestas médicas, cédula completa u otros datos fuera del contexto autorizado. |

---

# Módulo 14. Compatibilidad y desempeño básico

| ID | Prioridad | Caso | Pasos principales | Resultado esperado |
|---|---|---|---|---|
| CMP-001 | P2 | Navegadores | Probar versiones soportadas de Safari, Chrome, Edge y Firefox. | Los flujos principales funcionan sin diferencias bloqueantes. |
| CMP-002 | P2 | Sistemas | Probar macOS, Windows, iOS y Android. | Formularios, modales, calendarios, CAPTCHA y multimedia son utilizables. |
| CMP-003 | P1 | Equipo de 8 GB | Ejecutar navegación y reproducción en un equipo de recursos modestos. | La cuenta regresiva y la UI no producen consumo perceptible anormal. |
| CMP-004 | P1 | Carga del inicio | Medir con contenido e imágenes optimizadas. | No se descargan videos completos hasta que el navegador los necesita; la página permanece utilizable durante la carga. |
| CMP-005 | P1 | Carga de video | Subir un video cercano a 25 MB. | Se muestra una espera comprensible y la interfaz recupera el control con éxito o un mensaje claro. |
| CMP-006 | P2 | Impresión | Probar Safari y Chrome con papel Carta y escala 100 %. | Expediente y carné mantienen legibilidad y no producen páginas en blanco. |

---

# 15. Flujos integrales de aceptación

Estos recorridos deben ejecutarse completos al final de cada ciclo de QA.

## E2E-01 — Alta de donante

1. Abrir el portal.
2. Iniciar registro.
3. Completar CAPTCHA e identidad nueva.
4. Registrar datos personales, ubicación, un contacto, datos médicos y consentimiento.
5. Confirmar la pantalla de éxito.
6. Abrir/guardar el carné.
7. Verificar el correo y PDF en smtp4dev.
8. Escanear el QR.
9. Confirmar que el donante aparece una vez en el dashboard y en las métricas.

**Resultado:** alta consistente en portal, administración, correo, carné, QR y métricas.

## E2E-02 — Actualización

1. Validar la identidad de un donante activo.
2. Actualizar teléfono y contacto.
3. Cambiar el alcance de donación.
4. Aceptar el nuevo consentimiento.
5. Confirmar nuevo carné y correo.
6. Revisar detalle e historial administrativo.

**Resultado:** un solo donante, datos actuales correctos y trazabilidad de la voluntad anterior.

## E2E-03 — Baja y reactivación

1. Validar un donante activo.
2. Dar de baja y confirmar el diálogo.
3. Verificar estado, historial y revocación del carné.
4. Escanear el QR revocado.
5. Volver a validar la identidad.
6. Reactivar con nuevo consentimiento.
7. Confirmar nuevo carné, correo, estado y QR.

**Resultado:** la misma persona transita Activo → Baja → Activo sin duplicar `donors` y con historial completo.

## E2E-04 — Publicación CMS con multimedia

1. Crear un aspecto legal con imagen recortada y descripción.
2. Crear mito y pregunta frecuente verificando sus formatos automáticos.
3. Crear una historia de texto.
4. Crear una historia con MOV vertical válido.
5. Revisar reproducción en el portal.
6. Sustituir y luego retirar el video.
7. Confirmar que el portal solo muestre el medio vigente.

**Resultado:** contenido y multimedia coherentes entre CMS y portal público.

## E2E-05 — Administración y métricas

1. Iniciar sesión.
2. Filtrar activos, bajas y todos.
3. Combinar filtros y exportar CSV.
4. Abrir el detalle de un donante e imprimirlo.
5. Comparar los totales de métricas con los conteos obtenidos mediante los filtros visibles del dashboard.
6. Cerrar sesión y comprobar que el acceso queda protegido.

**Resultado:** consultas, exportación, impresión, métricas y permisos reflejan el mismo conjunto de datos.

## E2E-06 — Consulta pública y atención administrativa

1. Abrir el portal y acceder a “Contáctenos”.
2. Enviar una consulta con aceptación de condiciones.
3. Verificar el acuse al remitente y la notificación interna en smtp4dev.
4. Iniciar sesión como usuario master y asignar la consulta a un administrador activo.
5. Verificar que el master no pueda responder mientras la consulta pertenezca a otro usuario; iniciar sesión como el administrador asignado y responderla.
6. Verificar el correo de respuesta, el cambio de estado y el historial.
7. Cerrar la consulta.

**Resultado:** la consulta se atiende sin exponer datos a usuarios no autorizados y conserva trazabilidad desde la recepción hasta el cierre.

---

# 16. Plantilla de registro de ejecución

| Campo | Valor |
|---|---|
| ID del caso | |
| Ambiente/versión | |
| Fecha y hora | |
| QA responsable | |
| Navegador/SO/dispositivo | |
| Datos utilizados | |
| Resultado esperado | |
| Resultado obtenido | |
| Estado | Aprobado / Fallido / Bloqueado / No aplica |
| Evidencia | |
| Defecto relacionado | |
| Observaciones | |

## 17. Criterio recomendado de salida

La versión puede pasar a la siguiente fase cuando:

- Todos los casos P0 estén aprobados.
- No existan defectos abiertos que comprometan datos personales, consentimiento, estado o unicidad del donante.
- Los casos P1 principales estén aprobados o cuenten con una excepción formal aceptada.
- Los seis recorridos integrales estén aprobados.
- El ambiente de QA haya sido declarado disponible por Desarrollo e Infraestructura.
- Los valores de métricas, CSV y dashboard sean consistentes en las interfaces visibles.
- Las altas, bajas, reactivaciones y modificaciones posean trazabilidad verificable.
