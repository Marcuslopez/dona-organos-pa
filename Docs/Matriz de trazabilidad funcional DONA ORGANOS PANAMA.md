# Matriz de trazabilidad funcional de DONA ÓRGANOS PANAMÁ

## 1. Objetivo

Esta matriz convierte las pantallas y comportamientos de `Proyecto_corneas/Mockups` en requisitos trazables para el desarrollo de **DONA ÓRGANOS PANAMÁ**.

Debe leerse junto con:

- `Proyecto_corneas/Docs/Guia tecnica para iniciar DONA ORGANOS PANAMA.md`;
- `Proyecto_corneas/Docs/Formulario de Registro de Donante de Córnea.md`;
- los archivos HTML de `Proyecto_corneas/Mockups`.

La matriz documenta lo que actualmente muestra el mockup, pero no convierte automáticamente en requisito legal, médico o institucional todo su contenido. Cuando existe una duda se identifica expresamente.

---

## 2. Clasificación

| Código | Significado |
|---|---|
| `IMP` | Implementar como parte del alcance conocido |
| `VAL` | Validar con responsables funcionales, jurídicos, médicos o de seguridad antes de implementar |
| `SUS` | Sustituir la simulación del mockup por una implementación productiva |
| `REF` | Conservar como referencia visual o de experiencia, permitiendo refinamiento |
| `EXC` | Excluir del producto; pertenece únicamente a la demostración |
| `FUT` | Posible fase futura; no comprometer en el primer incremento |

### Prioridad

| Código | Significado |
|---|---|
| `P0` | Necesario para el núcleo inicial o para proteger datos y acceso |
| `P1` | Necesario para completar la experiencia funcional principal |
| `P2` | Complementario o refinable después del núcleo |

---

## 3. Inventario de pantallas

| ID | Pantalla | Mockup | Propósito productivo | Clasificación | Prioridad |
|---|---|---|---|---|---|
| UI-001 | Portal público | `index.html` | Informar, educar y dirigir al registro | `REF/VAL` | P1 |
| UI-002 | Acceso administrativo | Modal en `index.html` | Autenticar usuarios autorizados | `SUS` | P0 |
| UI-003 | Validación de identidad | Paso 1 de `formulario.html` | Identificar al ciudadano antes de registrar o solicitar baja | `VAL/SUS` | P0 |
| UI-004 | Registro del donante | Paso 2 de `formulario.html` | Capturar voluntad, datos y autorizaciones | `IMP/VAL` | P0 |
| UI-005 | Solicitud de baja | Formulario alterno en `formulario.html` | Cambiar el estado del donante a `Baja` | `VAL` | P0 |
| UI-006 | Confirmación y carnet | Diálogo en `formulario.html` | Confirmar el registro y mostrar el carnet | `IMP` | P1 |
| UI-007 | Bandeja de correo | `correos-simulados.html` | Solo demuestra el correo esperado | `SUS/EXC` | P2 |
| UI-008 | Administración de donantes | `administracion.html` | Consultar, buscar, filtrar y exportar | `IMP` | P1 |
| UI-009 | Detalle del donante | Diálogo en `administracion.html` | Consultar todos los datos autorizados | `IMP/VAL` | P1 |
| UI-010 | Carnet dentro del detalle | Sección inferior de detalle | Mostrar, ocultar e imprimir el carnet sin modal anidada | `IMP` | P1 |
| UI-011 | Métricas | `metricas.html` | Presentar indicadores y gráficas administrativas | `IMP` | P1 |
| UI-012 | Verificación pública | `verificar-donante.html` | Comprobar la vigencia de un carnet mediante QR | `SUS/VAL` | P1 |
| UI-013 | Gestión de contenidos | `contenidos.html` | Administrar el contenido editorial publicado en el portal | `IMP/SUS` | P1 |

---

## 4. Navegación y flujos

| ID | Origen | Acción | Destino o resultado | Regla productiva | Clasificación |
|---|---|---|---|---|---|
| NAV-001 | Portal | Seleccionar llamada a la acción | Inicio de registro | Ruta pública estable | `IMP` |
| NAV-002 | Portal | Seleccionar icono administrativo | Mostrar acceso administrativo | Usar autenticación Laravel real | `SUS` |
| NAV-003 | Acceso administrativo | Credenciales válidas | Dashboard administrativo | Redirección según rol y sesión segura | `SUS` |
| NAV-004 | Acceso administrativo | Credenciales inválidas | Permanecer y mostrar error genérico | No revelar si usuario o contraseña falló | `IMP` |
| NAV-005 | Validación | Identidad sin registro activo | Mostrar formulario de alta | La decisión debe provenir del backend | `SUS/VAL` |
| NAV-006 | Validación | Identidad con estado `Activo` | Mostrar opción de baja | No duplicar registro activo | `IMP/VAL` |
| NAV-007 | Validación | Identidad con estado `Baja` | Definir reactivación o nuevo consentimiento | Comportamiento aún no definido | `VAL` |
| NAV-008 | Cualquier paso de registro | Cancelar | Confirmar salida | No persistir formulario incompleto sin autorización | `IMP` |
| NAV-009 | Confirmación de salida | Regresar | Cerrar diálogo y conservar formulario | Mantener estado de la pantalla | `IMP` |
| NAV-010 | Confirmación de salida | Salir | Volver al portal | Limpiar datos temporales sensibles | `IMP` |
| NAV-011 | Registro exitoso | Imprimir/Guardar PDF | Diálogo de impresión del sistema | Vista de impresión dedicada | `IMP` |
| NAV-012 | Registro exitoso | Aceptar | Volver al portal o pantalla final | Confirmar destino funcional | `REF/VAL` |
| NAV-013 | Administración | Métricas | Vista de métricas | Mantener sesión y autorización | `IMP` |
| NAV-014 | Administración | Ver donante | Detalle del donante | Cargar por identificador autorizado | `IMP` |
| NAV-015 | Detalle | Ver carnet | Expandir sección inferior | Botón cambia a `Ocultar carnet` | `IMP` |
| NAV-016 | Detalle | Ocultar carnet | Contraer sección | Botón vuelve a `Ver carnet` | `IMP` |
| NAV-017 | Detalle | Cerrar | Cerrar todo el detalle | Debe funcionar aun si el carnet está abierto | `IMP` |
| NAV-018 | Carnet | Escanear QR | Verificación pública | Token opaco y revocable | `SUS` |
| NAV-019 | Administración | Cerrar sesión | Confirmar y terminar sesión | Invalidar sesión y token CSRF | `IMP` |
| NAV-020 | Administración | Gestión de contenidos | Abrir CMS interno manteniendo sesión y autorización | `IMP/SUS` |
| NAV-021 | CMS | Vista pública | Abrir el portal para comprobar los contenidos publicados | Mantener separación entre edición autenticada y lectura pública | `IMP` |

---

## 5. Portal público

Los textos del portal son contenido editorial modificable. La estructura visual puede usarlos como referencia, pero ninguna regla del registro, autenticación, administración, API, carnet o métricas debe depender de frases concretas del inicio.

| ID | Elemento | Comportamiento o contenido del mockup | Tratamiento productivo | Clasificación | Prioridad |
|---|---|---|---|---|---|
| PUB-001 | Marca | `DONA ÓRGANOS PANAMÁ` en mayúsculas | Mantener nombre oficial | `IMP` | P0 |
| PUB-002 | Navegación | Inicio, Córneas, Aspectos legales, Mitos, Preguntas frecuentes y Testimonios | Adaptar la arquitectura de contenidos al alcance general | `REF/VAL` | P1 |
| PUB-003 | Hero | Mensaje centrado actualmente en visión y córneas | Reescribir para órganos y tejidos en general | `VAL` | P1 |
| PUB-004 | CTA | Acceso al formulario | Mantener acción clara y accesible | `IMP` | P0 |
| PUB-005 | Sección educativa | Información sobre donación de córneas | Mantener como contenido específico dentro de un alcance general | `REF/VAL` | P2 |
| PUB-006 | Tarjetas legales | Seis tarjetas expandibles por clic, teclado o foco | Conservar interacción accesible; validar contenido | `REF/VAL` | P2 |
| PUB-007 | Enlaces legales | Documentos de Ley 3 y otras referencias | Revisar vigencia, URL oficial y texto relacionado | `VAL` | P0 |
| PUB-008 | Mitos | Acordeones y elementos adicionales ocultos | Conservar patrón; validar afirmaciones médicas | `REF/VAL` | P2 |
| PUB-009 | Mostrar más mitos | Alterna elementos, texto y símbolo; actualiza `aria-expanded` | Mantener accesibilidad | `IMP` | P2 |
| PUB-010 | Preguntas frecuentes | Acordeones y preguntas adicionales | Gestionar contenido validado, idealmente centralizado | `REF/VAL` | P2 |
| PUB-011 | Testimonios | Video simulado y dos historias | Sustituir solo con testimonios autorizados; no inventar identidades | `SUS/VAL` | P2 |
| PUB-012 | Contactos oficiales | Teléfono CSS y correo MINSA mostrados | Verificar vigencia y autorización antes de publicar | `VAL` | P0 |
| PUB-013 | Volver arriba | Aparece después del desplazamiento y realiza scroll suave | Mantener si ayuda a páginas extensas | `REF` | P2 |
| PUB-014 | Responsive | Navegación y contenido adaptables | Implementar con Bootstrap y pruebas móviles | `IMP` | P1 |
| PUB-015 | Contenido desacoplado | Los textos informativos pueden variar | Centralizar contenido y evitar condiciones funcionales basadas en textos visibles | `IMP` | P0 |
| PUB-016 | Sustitución editorial | Títulos, párrafos, mitos, FAQ, testimonios y contactos pueden cambiar | El cambio no debe requerir modificar servicios, endpoints, validaciones ni base funcional | `IMP` | P1 |

---

## 6. Acceso administrativo

| ID | Campo/acción | Mockup | Regla productiva | Clasificación | Prioridad |
|---|---|---|---|---|---|
| AUTH-001 | Usuario | Texto obligatorio | Se recomienda correo o identificador institucional | `VAL` | P0 |
| AUTH-002 | Contraseña | Contraseña obligatoria | Hash seguro, nunca texto plano | `IMP` | P0 |
| AUTH-003 | Ingresar | Compara credenciales escritas en JavaScript | Autenticación Laravel en servidor | `SUS` | P0 |
| AUTH-004 | Sesión | Usa `sessionStorage` | Sesión Laravel con cookie segura | `SUS` | P0 |
| AUTH-005 | Error | “Usuario o contraseña incorrectos” | Mantener mensaje genérico | `IMP` | P0 |
| AUTH-006 | Cancelar/cerrar | Cierra modal y limpia formulario | Mantener | `IMP` | P1 |
| AUTH-007 | Intentos | Sin limitación | Agregar rate limiting, registro de eventos y bloqueo definido | `IMP` | P0 |
| AUTH-008 | Roles | Un administrador simulado | Definir roles reales; separar función y monitoreo técnico | `VAL` | P0 |

Credenciales demostrativas como `Administrador1` y `Administrator` quedan expresamente excluidas.

---

## 7. Paso 1: validación de identidad

| ID | Campo/elemento | Tipo y reglas del mockup | Regla o decisión productiva | Clasificación | Prioridad |
|---|---|---|---|---|---|
| IDV-001 | Indicador | “Paso 1 de 2” | Mantener orientación del flujo | `REF` | P1 |
| IDV-002 | Cédula | Obligatoria, máximo 18, sin espacios, mayúsculas | Normalizar en backend y definir formatos oficiales | `IMP/VAL` | P0 |
| IDV-003 | Patrones de cédula | Regular, `PE`, `E`, `N` y `AV` | Validar formatos con fuente institucional | `VAL` | P0 |
| IDV-004 | Ayuda de cédula | Tooltip con ejemplos | Mantener ayuda accesible después de validar formatos | `REF/VAL` | P1 |
| IDV-005 | Código posterior | Obligatorio, 8–20, letras y números, sin espacios | Confirmar si existe servicio oficial capaz de verificarlo | `VAL` | P0 |
| IDV-006 | Ayuda de código | Ubicación y distinción I/1 y O/0 | Mantener si el campo es aprobado | `REF` | P1 |
| IDV-007 | CAPTCHA | Seis letras minúsculas/números, regenerable | Sustituir por control seguro accesible o rate limiting según riesgo | `SUS/VAL` | P0 |
| IDV-008 | Errores | Mensajes junto al campo y `aria-live` | Mantener patrón accesible | `IMP` | P0 |
| IDV-009 | Validar y continuar | Decide alta o baja mediante `localStorage` | Consultar backend sin revelar datos del donante | `SUS` | P0 |
| IDV-010 | Cancelar | Abre confirmación de salida | Mantener | `IMP` | P1 |
| IDV-011 | Restaurar demo | Botón flotante repone donantes semilla | No incluir en producción | `EXC` | P0 |
| IDV-012 | Tres intentos fallidos | Para la demostración, la cédula `8-123-1234` consume un intento cuando el código posterior no coincide | Pausar la entrada durante 30 segundos y mostrar cuenta regresiva | `REF/SUS` | P1 |
| IDV-013 | Reinicio de intentos | Una verificación correcta o el final de la pausa reinicia el contador | En producción debe controlarse en servidor | `REF/SUS` | P1 |
| IDV-014 | Errores que no cuentan | Formato inválido y CAPTCHA incorrecto no consumen intentos de identidad | Mantener separación entre validación formal y fallo de credenciales | `IMP` | P1 |

La cédula y el código posterior no deben considerarse por sí solos un mecanismo fuerte de autenticación hasta que seguridad y negocio definan el proceso de identidad y baja.

---

## 8. Paso 2: campos del registro

### 8.1 Identificación y contacto del donante

| ID | Campo | Tipo | Obligatorio en mockup | Normalización/validación observada | Persistencia propuesta | Clasificación |
|---|---|---|---|---|---|---|
| REG-001 | Cédula | Texto deshabilitado | Sí | Proviene del paso 1 | `donors.document_number` normalizado | `IMP/VAL` |
| REG-002 | Código posterior | Texto deshabilitado | Sí | Proviene del paso 1 | Evitar persistir si no es necesario; revisar sensibilidad | `VAL` |
| REG-003 | Nombre completo | Texto | Sí | Capitalización por palabra; espacios normalizados | `donors.full_name` | `IMP` |
| REG-004 | Fecha de nacimiento | Fecha | Sí | Selector de fecha | `donors.birth_date` | `IMP/VAL` |
| REG-005 | Género | Selección | Sí | Femenino, Masculino, Otro, Prefiero no indicar | Catálogo o valor controlado | `VAL` |
| REG-006 | Correo | Email | Sí | Minúsculas y espacios recortados | `donors.email` | `IMP` |
| REG-007 | Teléfono | Teléfono | Sí | Solo números; 7–15; inicia visualmente con `507` | `donors.phone` en formato acordado | `IMP/VAL` |

La mayoría de edad y las fechas futuras deben validarse en Laravel; el mockup no debe considerarse la única fuente de esas reglas.

### 8.2 Residencia

| ID | Campo | Mockup | Regla productiva | Clasificación |
|---|---|---|---|---|
| LOC-001 | Provincia | Obligatoria; diez provincias listadas | Usar catálogo oficial y actualizado | `IMP/VAL` |
| LOC-002 | Distrito | Obligatorio; depende de provincia | Carga en cascada desde catálogo | `IMP/VAL` |
| LOC-003 | Corregimiento | Obligatorio; depende de distrito | Carga en cascada desde catálogo | `IMP/VAL` |
| LOC-004 | Catálogo local | Objeto JavaScript parcial | Sustituir por datos versionados y completos | `SUS` |
| LOC-005 | Comarcas y divisiones faltantes | No aparecen de forma completa | Resolver cobertura geográfica oficial | `VAL` |

### 8.3 Contacto informado

| ID | Campo | Tipo | Obligatorio en mockup | Valores/regla | Persistencia | Clasificación |
|---|---|---|---|---|---|---|
| CON-001 | Nombre completo | Texto | Sí | Capitalización y espacios normalizados | `donor_contacts.full_name` | `IMP` |
| CON-002 | Parentesco | Selección | Sí | Hermano(a), Padre/Madre, Cónyuge, Amistad, Otro | Catálogo controlado | `IMP/VAL` |
| CON-003 | Correo | Email | Sí | Minúsculas | `donor_contacts.email` | `IMP/VAL` |
| CON-004 | Teléfono | Teléfono | Sí | Solo números, 7–15 | `donor_contacts.phone` | `IMP` |
| CON-005 | Conversó su decisión | Checkbox | No | Booleano | `donor_contacts.informed` | `IMP/VAL` |
| CON-006 | Cantidad de contactos | Uno en formulario; dos espacios en reverso | Definir si se capturará uno o más | `VAL` |

### 8.4 Consentimiento informado

| ID | Campo | Obligatorio | Mockup | Tratamiento productivo | Clasificación |
|---|---|---|---|---|---|
| CNS-001 | Voluntad libre | Sí | Checkbox con referencia a Ley 3 de 2010 | Validar texto y guardar versión aceptada | `VAL` |
| CNS-002 | Comprensión sobre desfiguración | No | Texto exclusivo de córneas | Mantener solo si corresponde al alcance y es aprobado | `VAL` |
| CNS-003 | Firma electrónica | Sí | Nombre escrito | Definir valor jurídico, evidencia y equivalencia con nombre | `VAL` |
| CNS-004 | Aceptación electrónica | Sí | Checkbox de confirmación | Guardar versión, instante y evidencia permitida | `VAL` |
| CNS-005 | Fecha de aceptación | Sistema | ISO generada en navegador | Generar también en servidor; hora del servidor prevalece | `SUS` |
| CNS-006 | Enlace a ley | Visible | Abre PDF externo | Validar documento vigente y disponibilidad | `VAL` |

### 8.5 Datos de salud

| ID | Pregunta | Obligatoria | Opciones | Tratamiento productivo | Clasificación |
|---|---|---|---|---|---|
| SAL-001 | VIH, hepatitis B/C o sífilis | Sí | Sí, No, No sé | Confirmar necesidad, lenguaje, acceso y retención | `VAL` |
| SAL-002 | Cáncer sistémico o leucemia | Sí | Sí, No, No sé | Confirmar necesidad, lenguaje, acceso y retención | `VAL` |
| SAL-003 | Glaucoma, infecciones corneales o queratocono | No | Sin responder, Sí, No | Específico de córneas; revisar alcance general | `VAL` |
| SAL-004 | Cirugías oculares | No | Sin responder, Sí, No | Específico de córneas; revisar alcance general | `VAL` |
| SAL-005 | Nota de triaje | Indica que idoneidad se evalúa al fallecimiento | Validar médicamente | `VAL` |

Estas respuestas deben considerarse sensibles. No implementar su persistencia sin decisión explícita.

### 8.6 Preferencias y alcance

| ID | Campo | Obligatorio | Valores del mockup | Tratamiento productivo | Clasificación |
|---|---|---|---|---|---|
| PRF-001 | Alcance | Sí | Solo córneas / Órganos y tejidos | Mantener provisionalmente; confirmar catálogo definitivo | `IMP/VAL` |
| PRF-002 | Selección individual | No existe | No crear catálogo individual sin autorización | Pendiente | `VAL/FUT` |
| PRF-003 | Investigación/docencia | Sí | Autoriza uso de córneas no aptas | Validar texto, alcance y obligatoriedad | `VAL` |

### 8.7 Protección de datos

| ID | Campo | Obligatorio | Mockup | Tratamiento productivo | Clasificación |
|---|---|---|---|---|---|
| PRI-001 | Tratamiento de datos sensibles | Sí | Checkbox con referencia a Ley 81 | Validar texto; guardar versión e instante | `VAL` |
| PRI-002 | Consulta institucional | Sí | Autoriza OPT, MINSA y CSS | Confirmar instituciones, finalidad y base legal | `VAL` |
| PRI-003 | Enlace Ley 81 | Visible | PDF externo | Verificar URL oficial y vigencia | `VAL` |

### 8.8 Acciones

| ID | Acción | Resultado esperado | Regla backend | Clasificación |
|---|---|---|---|---|
| ACT-001 | Enviar registro | Validar, guardar atómicamente y confirmar | Transacción; idempotencia; duplicidad controlada | `IMP` |
| ACT-002 | Procesando | Estado visual durante solicitud | Evitar doble envío; respuesta clara y rápida | `IMP` |
| ACT-003 | Error | Mostrar fallo recuperable | No perder datos; no mostrar detalles técnicos | `IMP` |
| ACT-004 | Cancelar | Confirmar abandono | No persistir silenciosamente | `IMP` |

---

## 9. Baja del donante

| ID | Elemento | Mockup | Regla requerida | Clasificación | Prioridad |
|---|---|---|---|---|---|
| BAJ-001 | Registro activo detectado | Muestra aviso y formulario alterno | Backend debe determinar estado | `IMP` | P0 |
| BAJ-002 | Documento y código | Solo lectura | No devolver otros datos sensibles | `IMP/VAL` | P0 |
| BAJ-003 | Solicitar baja | Botón explícito | Definir nivel de verificación de identidad | `VAL` | P0 |
| BAJ-004 | Confirmación | Diálogo “¿Estás seguro?” | Mantener confirmación inequívoca | `IMP` | P0 |
| BAJ-005 | Cancelar baja | Cierra diálogo | No cambiar datos | `IMP` | P0 |
| BAJ-006 | Baja completada | Mensaje y retorno al portal | Registrar instante y mecanismo; invalidar carnet | `IMP/VAL` | P0 |
| BAJ-007 | Persistencia | Cambia `activo=false`, `estado=Baja`, fecha de revocación | Evento transaccional; no eliminar donante | `SUS` | P0 |
| BAJ-008 | Reactivación | No está definida con claridad | Requiere nuevo consentimiento o procedimiento aprobado | `VAL` | P1 |

---

## 10. Confirmación, correo y carnet

### 10.1 Confirmación

| ID | Elemento | Mockup | Implementación | Clasificación |
|---|---|---|---|---|
| CNF-001 | Registro completado | Diálogo modal | Mostrar solo después del commit exitoso | `IMP` |
| CNF-002 | Destino de correo | Muestra correo del donante | Enmascarar parcialmente si es apropiado | `REF/VAL` |
| CNF-003 | Vista previa | Frente y reverso | Componente reutilizable en registro y administración | `IMP` |
| CNF-004 | Imprimir/Guardar PDF | Un solo botón | Abrir diálogo nativo con vista de impresión | `IMP` |
| CNF-005 | Ver correo simulado | Navega a bandeja local | Excluir en producción; sustituir por confirmación de envío | `EXC/SUS` |

### 10.2 Correo

| ID | Elemento | Contenido de referencia | Implementación productiva | Clasificación |
|---|---|---|---|---|
| EML-001 | Remitente | DONA ÓRGANOS PANAMÁ | Dirección institucional verificada | `SUS/VAL` |
| EML-002 | Asunto | Carnet simbólico de donante de órganos | Plantilla versionada | `REF` |
| EML-003 | Cuerpo | Saludo, agradecimiento, folio y mensaje familiar | Revisar comunicación institucional | `REF/VAL` |
| EML-004 | Adjuntos | Frente y reverso simulados | Definir PDF/imagen/enlace seguro | `VAL` |
| EML-005 | Envío | Guarda mensaje en `localStorage` | Cola Laravel, reintentos y proveedor autorizado | `SUS` |
| EML-006 | Estado | “Enviado (simulación)” | Registrar estados técnicos sin exponerlos al usuario | `SUS` |
| EML-007 | Bandeja demostrativa | Lista local de correos | No forma parte del producto | `EXC` |

### 10.3 Carnet frontal

| ID | Elemento | Requisito visual/funcional | Clasificación |
|---|---|---|---|
| CRD-001 | Marca | `DONA ÓRGANOS PANAMÁ` | `IMP` |
| CRD-002 | Icono | Rojo, concepto de dos manos sosteniendo un corazón | `REF/IMP` |
| CRD-003 | Título | Carnet de donante / voluntad de donación de órganos | `REF/VAL` |
| CRD-004 | Etiquetas | Nombre completo, Cédula o documento, Registro | `IMP` |
| CRD-005 | Tamaño de etiquetas | Aproximadamente el doble del tamaño original previo | `REF` |
| CRD-006 | Valores | Nombre y documento con alta legibilidad | `REF` |
| CRD-007 | QR | Enlace de verificación | `SUS` |
| CRD-008 | Folio | Identificador visible tipo `DC-año-consecutivo` en mockup | Formato definitivo por confirmar | `VAL` |
| CRD-009 | Pie | Mensaje de compartir/confirmar la decisión | `REF/VAL` |

### 10.4 Carnet posterior

| ID | Elemento | Requisito visual/funcional | Clasificación |
|---|---|---|---|
| CRB-001 | Encabezado | “COMPARTE TU DECISIÓN” | `REF` |
| CRB-002 | Mensaje | Informar a la familia sobre la decisión | `REF/VAL` |
| CRB-003 | Sección | Familiar / contacto informado | Renombrar si negocio lo solicita | `REF/VAL` |
| CRB-004 | Dos renglones | Cada uno con Nombre de contacto y Teléfono | `IMP` |
| CRB-005 | Etiquetas | Nombre de contacto y Teléfono en azul y negrita | `REF/IMP` |
| CRB-006 | Líneas | Líneas punteadas azules para escribir | `REF/IMP` |
| CRB-007 | QR inferior | Información o verificación del programa | Definir si es el mismo token o QR informativo | `VAL` |
| CRB-008 | Texto inferior | Instrucción de escaneo | Validar destino | `REF/VAL` |

### 10.5 Impresión

| ID | Regla | Implementación esperada | Clasificación |
|---|---|---|---|
| PRT-001 | Un solo botón | `Imprimir / Guardar PDF` en registro y administración | `IMP` |
| PRT-002 | Destino | El navegador permite impresora o guardar PDF | `IMP` |
| PRT-003 | Color | No forzar blanco y negro mediante CSS | `IMP` |
| PRT-004 | Fondo | Usar `print-color-adjust` donde corresponda, sujeto al navegador | `IMP` |
| PRT-005 | QR canvas | Convertir a imagen antes de clonar/imprimir si es necesario | `IMP` |
| PRT-006 | Ventana emergente | Informar si el navegador la bloquea | `IMP` |
| PRT-007 | Diseño | Frente y reverso en una hoja según el mockup | `REF/VAL` |

---

## 11. Administración

### 11.1 Acceso y cabecera

| ID | Elemento | Requisito | Clasificación |
|---|---|---|---|
| ADM-001 | Protección | Redirigir si no existe sesión autorizada | `IMP` |
| ADM-002 | Marca | DONA ÓRGANOS PANAMÁ / Administración | `REF` |
| ADM-003 | Cerrar sesión | Confirmación, salir o cancelar | `IMP` |
| ADM-004 | Métricas | Enlace destacado | `IMP` |

### 11.2 Filtros

| ID | Filtro | Tipo | Comportamiento | API/query | Clasificación |
|---|---|---|---|---|---|
| FIL-001 | Nombre contiene | Búsqueda | Coincidencia parcial sin distinguir mayúsculas | `full_name` | `IMP` |
| FIL-002 | Cédula | Búsqueda | Coincidencia definida y segura | Documento normalizado | `IMP/VAL` |
| FIL-003 | Provincia | Selección | Todas o una provincia | `province_id` | `IMP` |
| FIL-004 | Estado | Selección | Todos, Activo, Baja | `status` | `IMP` |
| FIL-005 | Fecha desde | Fecha | Inclusiva | `registered_from` | `IMP` |
| FIL-006 | Fecha hasta | Fecha | Inclusiva hasta fin del día | `registered_to` | `IMP` |
| FIL-007 | Buscar | Botón | Aplica filtros y vuelve a página 1 | Query string | `IMP` |
| FIL-008 | Limpiar | Botón | Restablece filtros | Query limpia | `IMP` |
| FIL-009 | Estado inicial | Mockup inicia filtrando activos en variable, aunque la UI puede mostrar otra cosa | Definir explícitamente si se muestran todos o activos | `VAL` |

### 11.3 Tabla

| ID | Columna/elemento | Tratamiento | Clasificación |
|---|---|---|---|
| TBL-001 | Nombre | Texto | `IMP` |
| TBL-002 | Cédula | Considerar enmascarado según rol | `IMP/VAL` |
| TBL-003 | Correo | Considerar enmascarado o acceso restringido | `IMP/VAL` |
| TBL-004 | Provincia | Texto de catálogo | `IMP` |
| TBL-005 | Contacto | Nombre del contacto | `IMP/VAL` |
| TBL-006 | Correo contacto | Dato de tercero; revisar necesidad en listado | `VAL` |
| TBL-007 | Fecha de registro | Formato `es-PA` | `IMP` |
| TBL-008 | Estado | Badge Activo/Baja | `IMP` |
| TBL-009 | Acción | Botón Ver | `IMP` |
| TBL-010 | Sin resultados | Mensaje claro | `IMP` |
| TBL-011 | Responsive | Scroll horizontal de tabla | `REF/IMP` |

### 11.4 Paginación y exportación

| ID | Elemento | Mockup | Implementación | Clasificación |
|---|---|---|---|---|
| PAG-001 | Tamaño | 5 o 10 | Paginación servidor; ampliar opciones solo si se aprueba | `IMP` |
| PAG-002 | Información | Página y cantidad visible | Incluir total si autorización y rendimiento permiten | `IMP` |
| PAG-003 | Controles | Anterior, números, siguiente | Mantener estado y query | `IMP` |
| EXP-001 | Descargar CSV | Exporta solo la página visible del mockup | Definir si exporta resultados filtrados completos; recomendado completo | `VAL` |
| EXP-002 | Columnas CSV | Nombre, cédula, correo, provincia, contacto, correo contacto, fecha y estado | Revisar datos personales y autorización | `VAL` |
| EXP-003 | Escala | Cliente genera archivo | Generar/streaming en backend; cola si es grande | `SUS` |

### 11.5 Detalle del donante

| ID | Sección | Campos del mockup | Regla | Clasificación |
|---|---|---|---|---|
| DET-001 | Datos del donante | Nombre, estado, cédula, código posterior, nacimiento, género, provincia, distrito, corregimiento, correo, teléfono | Código posterior y datos sensibles requieren restricción | `IMP/VAL` |
| DET-002 | Trazabilidad | Folio, fecha/hora registro, estado, fecha de baja | Mantener historial coherente | `IMP` |
| DET-003 | Consentimiento | Voluntad, no desfigura, firma, aceptación | Acceso restringido; mostrar versión e instante si se aprueba | `VAL` |
| DET-004 | Salud | Cuatro respuestas | No mostrar sin autorización médica y rol específico | `VAL` |
| DET-005 | Preferencias | Alcance, investigación/docencia | Mantener si quedan aprobadas | `IMP/VAL` |
| DET-006 | Protección de datos | Dos autorizaciones | Mantener con versión del consentimiento | `VAL` |
| DET-007 | Contacto | Nombre, parentesco, correo, teléfono, conversación | Proteger datos de tercero | `IMP/VAL` |
| DET-008 | Modal única | Detalle y carnet dentro de la misma modal | Mantener para evitar superposición y bloqueo | `IMP` |
| DET-009 | Estado Baja | No muestra carnet vigente | Mantener; permitir historial solo si corresponde | `IMP/VAL` |
| DET-010 | Escape HTML | Mockup escapa contenido dinámico | Blade escapa por defecto; evitar HTML no confiable | `IMP` |

La administración actualmente no incluye editar, aceptar ni rechazar donantes. Esas acciones no deben agregarse por inferencia.

---

### 11.6 CMS interno de contenidos

El CMS es un requisito funcional confirmado y constituye un módulo separado de la consulta de donantes. La comparación con WordPress se limita a administrar contenidos; no autoriza un constructor visual, temas, plugins, comentarios ni modificación libre de la estructura del portal.

#### 11.6.1 Alcance editorial

| ID | Elemento | Comportamiento del mockup | Implementación productiva | Clasificación | Prioridad |
|---|---|---|---|---|---|
| CMS-001 | Categorías | Aspectos legales, mitos y realidades, preguntas frecuentes | Valores controlados; ampliar solo mediante decisión documentada | `IMP` | P1 |
| CMS-002 | Listado | Tabla con orden, categoría, título, resumen, estado y acciones | Consulta paginada o acotada desde MySQL | `IMP/SUS` | P1 |
| CMS-003 | Crear | Formulario modal de nuevo contenido | Validar y persistir mediante Laravel | `IMP/SUS` | P1 |
| CMS-004 | Editar | Reutiliza el formulario con los valores existentes | Autorizar, validar y actualizar mediante Laravel | `IMP/SUS` | P1 |
| CMS-005 | Eliminar | Confirmación y eliminación del registro local | Confirmación; definir eliminación física o lógica antes de producción | `IMP/VAL` | P1 |
| CMS-006 | Visibilidad | Alterna entre Visible y Oculto | Persistir estado; el portal solo consulta visibles | `IMP/SUS` | P1 |
| CMS-007 | Orden | Número entero por categoría | Validar entero positivo y ordenar de forma estable | `IMP` | P1 |
| CMS-008 | Título | Título, mito o pregunta; máximo demostrativo de 180 caracteres | Longitud definitiva validada en servidor | `IMP/VAL` | P1 |
| CMS-009 | Contenido | Descripción o respuesta; máximo demostrativo de 1500 caracteres | Texto validado y limpiado; formato enriquecido limitado solo si se aprueba | `IMP/VAL` | P1 |
| CMS-010 | Enlace | URL relacionada opcional | Validar protocolo y URL; enlaces legales sujetos a vigencia institucional | `IMP/VAL` | P1 |
| CMS-011 | Buscar y filtrar | Filtro por categoría y coincidencia de texto | Mantener para operación administrativa | `IMP` | P2 |
| CMS-012 | Resumen | Totales globales y por categoría | Derivar de la misma fuente persistente | `REF` | P2 |
| CMS-013 | Vista pública | Abre el portal en otra pestaña | Facilitar comprobación sin exponer funciones administrativas | `REF/IMP` | P2 |
| CMS-014 | Restaurar demostración | Repone contenidos semilla en `localStorage` | No incluir en producción | `EXC` | P0 |

#### 11.6.2 Reglas de publicación y seguridad

| ID | Regla | Clasificación | Prioridad |
|---|---|---|---|
| CMP-001 | Solo usuarios administrativos autorizados pueden crear, editar, ocultar, mostrar o eliminar | `IMP` | P0 |
| CMP-002 | La lectura pública devuelve únicamente contenidos visibles y ordenados | `IMP` | P0 |
| CMP-003 | Los cambios editoriales no deben alterar registro, autenticación, carnet, API de donantes ni métricas | `IMP` | P0 |
| CMP-004 | Validar y limpiar contenido y enlaces en el servidor para impedir XSS o inyección | `IMP` | P0 |
| CMP-005 | Blade debe escapar texto por defecto; cualquier HTML permitido utilizará una lista segura explícita | `IMP` | P0 |
| CMP-006 | El CMS no convierte contenido legal o médico en aprobado; la validación institucional sigue siendo obligatoria | `VAL` | P0 |
| CMP-007 | No se requiere inicialmente constructor de páginas, temas, plugins, comentarios, publicación programada, multilenguaje ni historial completo de versiones | `EXC/FUT` | P2 |

#### 11.6.3 Diferencia entre mockup y producción

| ID | Mockup | Producción | Clasificación |
|---|---|---|---|
| CMS-SIM-001 | `localStorage` conserva contenidos en un navegador | MySQL será la fuente central para todos los usuarios | `SUS` |
| CMS-SIM-002 | Credenciales y sesión administrativas simuladas | Autenticación, sesión y autorización Laravel | `SUS` |
| CMS-SIM-003 | Operaciones ejecutadas totalmente en JavaScript | Rutas, controladores/Form Requests, servicios y Policies Laravel | `SUS` |
| CMS-SIM-004 | Datos editoriales semilla incluidos en JavaScript | Seeders solo para desarrollo/pruebas; contenido institucional administrado | `SUS/VAL` |

---

## 12. Métricas

### 12.1 Orden y presentación

| ID | Orden | Gráfica | Visual del mockup | Fuente productiva | Clasificación |
|---|---:|---|---|---|---|
| MET-001 | 1 | Crecimiento acumulado | Línea/área, total sobre el punto, incremento `+N` debajo y mes en eje | Altas por mes y acumulado | `IMP` |
| MET-002 | 2 | Altas y bajas últimos 12 meses | Barras dobles por mes, valores y leyenda | Fecha alta y fecha baja | `IMP` |
| MET-003 | 3 | Estado de los donantes | Dona Activos/Bajas con total y leyenda | Estado actual | `IMP/VAL` |
| MET-004 | 4A | Donantes por edad | Barras verticales | Fecha de nacimiento | `IMP/VAL` |
| MET-005 | 4B | Donantes por provincia | Barras horizontales, orden descendente | Provincia | `IMP` |

La solicitud denominó la tercera gráfica “Altas y bajas de donantes”, mientras el mockup muestra “Estado de los donantes”. Debe confirmarse el título definitivo; funcionalmente es una distribución del estado actual.

### 12.2 Reglas de cálculo

| ID | Cálculo | Definición observada | Definición productiva requerida | Clasificación |
|---|---|---|---|---|
| CAL-001 | Ventana mensual | Mes actual más once anteriores | Usar zona `America/Panama` y meses calendario | `IMP` |
| CAL-002 | Alta | Registro creado en el mes | Confirmar que no es reactivación | `VAL` |
| CAL-003 | Baja | Fecha de baja en el mes | Contar evento efectivo de cambio | `IMP` |
| CAL-004 | Acumulado | Suma progresiva de altas semilla | Definir si resta bajas; nombre sugiere donantes registrados acumulados | `VAL` |
| CAL-005 | Incremento | `+altas del mes` debajo del punto | Mantener incluso en cero si se desea consistencia | `IMP/REF` |
| CAL-006 | Estado | Conteo actual Activo/Baja | Total debe coincidir con leyenda | `IMP` |
| CAL-007 | Edad | Diferencia desde nacimiento al día actual | Usar fecha de corte; no persistir edad | `IMP` |
| CAL-008 | Rangos edad | `+20`, `+30`, `+40`, `+50`, `+60` equivalen a 20–29, 30–39, 40–49, 50–59, 60+ | Confirmar tratamiento de menores de 20 y etiquetas más claras | `VAL` |
| CAL-009 | Provincia | Conteo y orden descendente; desempate alfabético | Incluir cero o solo provincias con datos, por decidir | `VAL` |
| CAL-010 | Datos | Arreglos semilla independientes de registros reales | Consultas MySQL filtradas y probadas | `SUS` |

### 12.3 Requisitos visuales y accesibles

| ID | Requisito | Clasificación |
|---|---|---|
| VIS-001 | Usar Chart.js con diseño adaptable | `IMP` |
| VIS-002 | No depender solo del color; incluir leyendas y valores | `IMP` |
| VIS-003 | Proporcionar nombre accesible o resumen equivalente | `IMP` |
| VIS-004 | Mostrar estados vacíos, carga y error | `IMP` |
| VIS-005 | Mantener el orden confirmado en escritorio y móvil | `IMP` |
| VIS-006 | Permitir refinamiento visual sin alterar fórmulas | `REF` |
| VIS-007 | Los datos del mockup son demostrativos; no constituyen cifras oficiales | `EXC` |

---

## 13. Verificación pública del carnet

| ID | Elemento | Mockup | Implementación productiva | Clasificación |
|---|---|---|---|---|
| VRF-001 | Parámetros URL | Nombre, folio y fecha en texto | Sustituir por token opaco | `SUS` |
| VRF-002 | Nombre completo | Se muestra públicamente | No mostrar sin base y autorización; preferir dato mínimo | `VAL` |
| VRF-003 | Folio | Se muestra completo | Definir exposición o enmascarado | `VAL` |
| VRF-004 | Fecha inscripción | Fecha y hora completa | Definir si se muestra solo fecha | `VAL` |
| VRF-005 | Estado | Mensaje fijo de inscrito | Consultar vigencia real; distinguir Activo/Baja/no encontrado | `SUS` |
| VRF-006 | Nota | “No constituye certificación oficial” | Definir naturaleza oficial del producto | `VAL` |
| VRF-007 | Enumeración | URL predecible | Token largo, aleatorio, revocable y con rate limiting | `IMP` |
| VRF-008 | Cache | No definida | Evitar cachear respuestas sensibles o revocadas | `IMP` |

---

## 14. Esquema de datos trazado desde el mockup

| Campo del mockup | Entidad propuesta | Campo técnico sugerido | Sensibilidad | Observación |
|---|---|---|---|---|
| Cédula | Donante | `document_number` | Alta | Único normalizado; considerar cifrado/tokenización |
| Código posterior | Verificación | Por definir | Alta | No persistir sin necesidad demostrada |
| Nombre | Donante | `full_name` | Personal | Normalizado sin perder valor original si se requiere |
| Fecha nacimiento | Donante | `birth_date` | Personal | Base para edad estadística |
| Género | Donante | `gender_code` | Personal | Catálogo por aprobar |
| Correo | Donante | `email` | Personal | Minúsculas |
| Teléfono | Donante | `phone` | Personal | Formato internacional por definir |
| Provincia | Donante/dirección | `province_id` | Personal | FK de catálogo |
| Distrito | Donante/dirección | `district_id` | Personal | FK dependiente |
| Corregimiento | Donante/dirección | `corregimiento_id` | Personal | FK dependiente |
| Estado | Donante/estado | `status` | Funcional | Solo Activo/Baja en alcance actual |
| Fecha registro | Donante/estado | `registered_at` | Funcional | Hora del servidor |
| Fecha baja | Donante/estado | `deactivated_at` | Funcional | Nula mientras Activo |
| Folio | Carnet | `folio` | Público controlado | Único; formato por confirmar |
| Contacto nombre | Contacto | `full_name` | Tercero | Consentimiento/aviso de privacidad por revisar |
| Contacto parentesco | Contacto | `relationship_code` | Tercero | Catálogo |
| Contacto correo | Contacto | `email` | Tercero | Necesidad por validar |
| Contacto teléfono | Contacto | `phone` | Tercero | Protegido |
| Contacto conversado | Contacto | `is_informed` | Funcional | Booleano |
| Consentimiento voluntario | Consentimiento | `voluntary_accepted` | Sensible | Guardar versión de texto |
| No desfigura | Consentimiento | `cornea_information_acknowledged` | Sensible | Específico de córneas |
| Firma electrónica | Consentimiento | `signed_name` o evidencia | Sensible | Definición jurídica pendiente |
| Aceptación electrónica | Consentimiento | `electronically_accepted` | Sensible | Instante del servidor |
| Fecha aceptación | Consentimiento | `accepted_at` | Sensible | ISO/UTC almacenado, mostrado en Panamá |
| Salud infecciosa | Respuesta de salud | `answer_code` | Muy alta | Persistencia pendiente |
| Salud cáncer | Respuesta de salud | `answer_code` | Muy alta | Persistencia pendiente |
| Salud ocular | Respuesta de salud | `answer_code` | Muy alta | Específico de córneas |
| Cirugías oculares | Respuesta de salud | `answer_code` | Muy alta | Específico de córneas |
| Alcance | Preferencia | `scope_code` | Sensible | Solo córneas / general provisionalmente |
| Investigación | Preferencia/consentimiento | `research_authorized` | Sensible | Texto versionado |
| Datos sensibles | Consentimiento | `sensitive_data_authorized` | Sensible | Texto versionado |
| Consulta institucional | Consentimiento | `institutional_query_authorized` | Sensible | Instituciones/versiones |
| Token QR | Carnet | `public_token_hash` | Seguridad | No guardar token plano si el diseño permite hash |
| Tipo de contenido | Contenido editorial | `type` | Público/controlado | `legal`, `myth` o `faq` inicialmente |
| Título/pregunta | Contenido editorial | `title` | Público | Validar longitud y escapar salida |
| Descripción/respuesta | Contenido editorial | `body` | Público | Texto plano o HTML limitado y limpiado |
| Enlace relacionado | Contenido editorial | `related_url` | Público | Opcional; URL y vigencia verificadas |
| Visibilidad | Contenido editorial | `is_visible` | Funcional | Solo visibles en el portal |
| Orden editorial | Contenido editorial | `sort_order` | Funcional | Entero positivo dentro del tipo |

---

## 15. Correspondencia con servicios y endpoints

| ID | Capacidad | Servicio sugerido | Endpoint/ruta de referencia | Prueba mínima |
|---|---|---|---|---|
| API-001 | Validar identidad | `IdentityValidationService` | `POST /api/v1/identity-checks` | No filtra existencia ni datos innecesarios |
| API-002 | Registrar donante | `DonorRegistrationService` | `POST /api/v1/donors` | Transacción, validación, duplicidad |
| API-003 | Solicitar baja | `DonorStatusService` | Ruta por definir después de aprobar identidad | Baja, fecha e invalidación de carnet |
| API-004 | Listar donantes | `DonorFilterQuery` | `GET /api/v1/admin/donors` | Roles, filtros y paginación |
| API-005 | Ver detalle | `DonorDetailQuery` | `GET /api/v1/admin/donors/{id}` | Autorización y campos permitidos |
| API-006 | Obtener carnet | `DonorCardService` | `GET /api/v1/admin/donors/{id}/card` | Activo/Baja y QR |
| API-007 | Verificar carnet | `DonorVerificationService` | `GET /api/v1/donor-cards/{token}/verify` | Vigente, revocado, inexistente |
| API-008 | Exportar | `DonorExportService` | `GET /api/v1/admin/donors/export` | Respeta filtros y permisos |
| API-009 | Métricas resumen | `DonorMetricsService` | `GET /api/v1/admin/metrics/summary` | Totales consistentes |
| API-010 | Crecimiento | `DonorMetricsService` | `GET /api/v1/admin/metrics/cumulative-growth` | Meses vacíos, cambio de año |
| API-011 | Altas/bajas | `DonorMetricsService` | `GET /api/v1/admin/metrics/registrations-and-deactivations` | Ventana de 12 meses |
| API-012 | Edad | `DonorMetricsService` | `GET /api/v1/admin/metrics/by-age` | Fecha de corte y límites |
| API-013 | Provincia | `DonorMetricsService` | `GET /api/v1/admin/metrics/by-province` | Orden y catálogos |
| API-014 | Enviar carnet | Job/Notification | Evento posterior al registro | Reintentos sin duplicar registro |
| API-015 | Listar contenidos | `PublishedContentQuery` / consulta administrativa | `GET /api/v1/admin/contents` o ruta Blade | Autorización, filtros y orden |
| API-016 | Crear contenido | `ContentManagementService` | `POST /api/v1/admin/contents` o ruta Blade | Permiso, validación y limpieza |
| API-017 | Editar contenido | `ContentManagementService` | `PUT /api/v1/admin/contents/{id}` o ruta Blade | Permiso y actualización consistente |
| API-018 | Eliminar contenido | `ContentManagementService` | `DELETE /api/v1/admin/contents/{id}` o ruta Blade | Confirmación y política de eliminación |
| API-019 | Cambiar visibilidad | `ContentManagementService` | `PATCH /api/v1/admin/contents/{id}/visibility` o ruta Blade | Solo autorizados; lectura pública actualizada |
| API-020 | Leer contenido público | `PublishedContentQuery` | Ruta/controlador del portal | Solo visibles, orden estable y salida segura |

Estos endpoints son una propuesta de organización. Blade puede consumir controladores web directamente; lo importante es preservar servicios reutilizables y contratos consistentes.

---

## 16. Estados de interfaz que deben diseñarse

| ID | Estado | Pantallas aplicables | Resultado esperado |
|---|---|---|---|
| STA-001 | Inicial | Todas | Instrucciones y foco claros |
| STA-002 | Cargando | Validación, registro, filtros, métricas | Indicador y controles protegidos contra doble acción |
| STA-003 | Validación fallida | Formularios | Error próximo al campo y resumen si procede |
| STA-004 | Error de red/servidor | Todas las operaciones | Mensaje recuperable y correlación de soporte, sin traza |
| STA-005 | Sin resultados | Administración y métricas | Mensaje, no gráfica engañosa |
| STA-006 | Registro exitoso | Formulario | Carnet y siguientes acciones |
| STA-007 | Baja exitosa | Formulario | Confirmación e invalidación visible |
| STA-008 | Carnet revocado | Verificación y administración | No presentarlo como vigente |
| STA-009 | Sesión expirada | Administración | Redirigir al login sin bucle |
| STA-010 | Impresión bloqueada | Carnet | Indicar habilitar ventana emergente |
| STA-011 | Correo pendiente/fallido | Confirmación | Registro sigue válido; informar sin duplicarlo |
| STA-012 | CMS sin contenidos | Gestión de contenidos/portal | Mensaje claro sin romper la estructura pública |
| STA-013 | Guardado editorial exitoso | Gestión de contenidos | Confirmar acción y reflejar estado persistido |
| STA-014 | Error de guardado editorial | Gestión de contenidos | Conservar formulario y permitir reintento seguro |

---

## 17. Requisitos visuales transversales

| ID | Requisito | Clasificación |
|---|---|---|
| UX-001 | Diseño adaptable a móvil, tableta y escritorio | `IMP` |
| UX-002 | Jerarquía clara mediante tarjetas, secciones y encabezados | `REF` |
| UX-003 | Colores institucionales azul, verde y rojo según función | `REF/VAL` |
| UX-004 | Contraste WCAG y foco visible | `IMP` |
| UX-005 | Operación por teclado de modales, tarjetas y acordeones | `IMP` |
| UX-006 | `aria-expanded`, nombres accesibles y mensajes `aria-live` | `IMP` |
| UX-007 | No depender exclusivamente de iconos o colores | `IMP` |
| UX-008 | Botones con verbos inequívocos: Ver, Ocultar, Cerrar, Cancelar | `IMP` |
| UX-009 | Evitar modales anidadas | `IMP` |
| UX-010 | Conservar datos válidos ante errores recuperables | `IMP` |
| UX-011 | Confirmar acciones de abandono o baja | `IMP` |
| UX-012 | Escapar contenido dinámico y evitar inyección | `IMP` |

---

## 18. Simulaciones que deben reemplazarse o excluirse

| ID | Simulación actual | Sustitución productiva |
|---|---|---|
| SIM-001 | `localStorage` para donantes | MySQL mediante Laravel |
| SIM-002 | `sessionStorage` para autenticación | Sesiones Laravel seguras |
| SIM-003 | Credenciales JavaScript fijas | Usuarios, hashes y autorización backend |
| SIM-004 | Google Apps Script comentado | API/controladores Laravel |
| SIM-005 | CAPTCHA construido en cliente | Control antiabuso evaluado y verificable en servidor |
| SIM-006 | Folio basado en milisegundos | Generador concurrente y único |
| SIM-007 | QR con nombre, folio y fecha | Token público opaco |
| SIM-008 | Página de verificación con query string | Consulta de vigencia en backend |
| SIM-009 | Bandeja de correos local | Proveedor de correo y cola |
| SIM-010 | Datos semilla embebidos | Seeders solo en desarrollo/testing |
| SIM-011 | Métricas independientes de registros | Consultas agregadas de MySQL |
| SIM-012 | CSV en navegador y página visible | Exportación backend según alcance aprobado |
| SIM-013 | Videos/testimonios de muestra | Contenido autorizado o retirar |
| SIM-014 | Contenidos del CMS en `localStorage` | Tabla `contents` en MySQL mediante Laravel |
| SIM-015 | Restaurar contenidos de demostración | Excluir de producción; usar seeders solo en desarrollo/pruebas |

---

## 19. Requisitos no funcionales derivados

| ID | Área | Requisito | Prioridad |
|---|---|---|---|
| NFR-001 | Rendimiento | Registro normal sin esperas artificiales ni servicios externos innecesarios | P0 |
| NFR-002 | Integridad | Registro compuesto dentro de una transacción | P0 |
| NFR-003 | Idempotencia | Evitar doble alta por doble clic o reintento | P0 |
| NFR-004 | Privacidad | Minimización, enmascarado y acceso por rol | P0 |
| NFR-005 | Seguridad | HTTPS, CSRF, rate limiting, cookies seguras y validación servidor | P0 |
| NFR-006 | Observabilidad | Logs estructurados, correlación, duración y errores | P0 |
| NFR-007 | Disponibilidad | Endpoint de salud y monitoreo de servicios | P1 |
| NFR-008 | Recuperación | Backups cifrados y restauración probada | P0 |
| NFR-009 | Accesibilidad | Flujo operable por teclado y tecnologías de asistencia | P1 |
| NFR-010 | Compatibilidad | Navegadores institucionales definidos; impresión comprobada | P1 |
| NFR-011 | Trazabilidad | Versionar textos de consentimiento y cambios de estado | P0 |
| NFR-012 | Calidad | Pruebas de flujo, permisos, métricas y seguridad | P0 |
| NFR-013 | Seguridad editorial | Validar, limpiar y escapar contenidos y enlaces administrables | P0 |

---

## 20. Preguntas bloqueantes y no bloqueantes

### Bloqueantes antes de poner en producción

1. Método válido para comprobar identidad.
2. Procedimiento válido para dar de baja o reactivar.
3. Textos de consentimiento y privacidad.
4. Datos médicos que se almacenarán y perfiles con acceso.
5. Información pública permitida en la verificación QR.
6. Catálogos geográficos oficiales y cobertura completa.
7. Instituciones autorizadas para consultar datos.
8. Política de conservación, eliminación, respaldo y recuperación.

### No bloqueantes para crear la base técnica

1. Refinamiento final de colores y espacios.
2. Testimonios definitivos.
3. Plataforma centralizada de observabilidad.
4. Selección individual de órganos, mientras no se modele prematuramente.
5. Opciones adicionales de paginación.
6. Título definitivo de la tercera gráfica, siempre que su cálculo quede separado.

---

## 21. Criterios globales de aceptación

El primer producto funcional no se considerará equivalente al mockup hasta comprobar que:

1. El portal dirige correctamente al registro y al acceso administrativo.
2. La identidad se valida mediante el procedimiento aprobado.
3. Todos los campos confirmados se validan en cliente y servidor.
4. El registro se guarda atómicamente en MySQL y responde sin demoras artificiales.
5. El estado solo utiliza `Activo` y `Baja`, salvo una decisión posterior documentada.
6. La baja invalida la verificación del carnet sin eliminar la trazabilidad.
7. El carnet puede verse, imprimirse o guardarse como PDF desde registro y administración.
8. El QR no expone datos personales en la URL.
9. La administración respeta autenticación, permisos, filtros y paginación.
10. El detalle protege datos personales, sensibles y de terceros.
11. Las cinco gráficas respetan orden, fórmula y datos provenientes de MySQL.
12. El crecimiento muestra el total acumulado y el incremento mensual `+N`.
13. La exportación respeta los filtros y permisos aprobados.
14. Los correos se procesan de forma desacoplada sin invalidar un registro exitoso.
15. Los estados de carga, vacío, error y sesión expirada están implementados.
16. Las pruebas automatizadas cubren reglas, permisos, duplicidades, baja y métricas.
17. Los logs no contienen datos personales o sensibles prohibidos.
18. La aplicación se prueba en Ubuntu ARM64 con Nginx, PHP-FPM y la versión fijada de MySQL.
19. El CMS permite crear, editar, eliminar, ordenar y mostrar u ocultar aspectos legales, mitos y preguntas frecuentes.
20. El portal obtiene los contenidos visibles desde MySQL y no depende de `localStorage` ni de textos fijados en el código.
21. Las operaciones editoriales exigen autorización y el contenido publicado se valida, limpia y escapa de forma segura.

---

## 22. Uso de esta matriz durante el desarrollo

- Referenciar los identificadores de esta matriz en issues, historias o merge requests.
- Marcar una fila como implementada únicamente cuando tenga prueba o evidencia verificable.
- Registrar toda decisión que cambie un requisito `VAL`.
- No convertir una referencia visual `REF` en regla de negocio sin aprobación.
- Actualizar la matriz cuando el diseño funcional cambie.
- Mantener separados los datos demostrativos y los datos productivos.

Ejemplo de historia:

```text
Historia: Registro inicial del donante
Trazabilidad: REG-001 a REG-007, LOC-001 a LOC-005, ACT-001 a ACT-004
Backend: API-002
Aceptación: criterios globales 3 y 4
```

Esta matriz representa la lectura funcional completa de los mockups actuales y será la referencia de trazabilidad al trasladar el proyecto a su nuevo repositorio.
