# Decisiones y confirmaciones pendientes

**Proyecto:** DONA ÓRGANOS PANAMÁ  
**Fecha de actualización:** 13 de agosto de 2026  
**Propósito:** centralizar la información que todavía debe ser confirmada por la institución, infraestructura, seguridad o los responsables funcionales antes de certificación y producción.

Este documento no representa defectos del sistema ni impide continuar las pruebas actuales. Separa las decisiones provisionales de aquellas que requieren una definición oficial.

## 1. Identidad y seguridad del donante

| Tema | Situación actual | Confirmación requerida | Responsable sugerido | Momento límite |
|---|---|---|---|---|
| Servicio oficial de identidad | En desarrollo se simula la validación de la combinación cédula y código posterior. | Proveedor, mecanismo de integración, credenciales, ambientes, disponibilidad y datos que devolverá el servicio oficial. | Institución / Tribunal Electoral / Seguridad | Antes de certificación |
| Nivel de autenticación | La cédula y su código posterior permiten acceder a consulta, actualización, baja y reactivación. | Confirmar si esta combinación es suficiente o si se requerirá OTP, correo, teléfono u otro segundo factor para operaciones sensibles. | Seguridad / Legal / Negocio | Antes de certificación |
| Tiempo de sesión | Todos los flujos del donante usan vencimiento renovable por actividad. Para pruebas, la inactividad está configurada temporalmente en 2 minutos y se muestra una advertencia 30 segundos antes. | Definir el tiempo definitivo de inactividad para registro, consulta, actualización, baja y reactivación. | Seguridad / Negocio | Antes de certificación |
| Intentos fallidos | Se permiten 3 intentos y una pausa temporal. | Duración definitiva del bloqueo, alcance por usuario/IP y procedimiento ante bloqueos reiterados. | Seguridad | Antes de certificación |
| CAPTCHA | Se usa un CAPTCHA propio para pruebas. | Confirmar la solución aprobada para producción y sus requisitos de accesibilidad y privacidad. | Seguridad / Infraestructura | Antes de certificación |

## 2. Reglas del registro de donantes

| Tema | Situación actual | Confirmación requerida | Responsable sugerido | Momento límite |
|---|---|---|---|---|
| Edad permitida | Se exige una edad mínima de 18 años y, provisionalmente, una fecha no anterior a 100 años desde la fecha actual. | Confirmar oficialmente la edad mínima y si debe existir un límite máximo de edad en el formulario. | Negocio / Legal / Médico | Antes de certificación |
| Alcance de donación | Las opciones actuales son “Solo córneas” y “Órganos y tejidos”. | Confirmar nombres definitivos y si se permitirán selecciones adicionales o individuales en el futuro. | Área médica / Negocio | Antes de certificación |
| Información médica | Se almacenan las respuestas médicas acordadas para apoyar la evaluación del donante. | Confirmar redacción definitiva, obligatoriedad, quién puede consultarlas y durante cuánto tiempo deben conservarse. | Área médica / Legal | Antes de certificación |
| Contactos | Se requiere al menos uno y se permiten hasta tres; el carné representa como máximo dos. | Confirmar máximo permitido en el registro, cuáles deben imprimirse y si correo y conocimiento de la decisión continúan siendo opcionales. | Negocio | Antes de certificación |
| Catálogos | Género, parentesco, alcance y respuestas médicas se gestionan mediante catálogos. | Entregar o aprobar los valores institucionales definitivos y el procedimiento para modificarlos. | Negocio / Área médica | Antes de certificación |
| Geografía | La semilla actual contiene provincias/comarcas, distritos y corregimientos procedentes del insumo aceptado durante el desarrollo. | Confirmar por escrito la versión, fecha y autoridad del conjunto geográfico que se utilizará en producción, además del proceso de actualización. | Institución / Negocio | Antes de producción |

## 3. Consentimiento, privacidad y auditoría

| Tema | Situación actual | Confirmación requerida | Responsable sugerido | Momento límite |
|---|---|---|---|---|
| Texto de consentimiento | Se registra aceptación electrónica, nombre firmado, fecha, IP, agente de usuario y versión. | Texto legal definitivo, instituciones autorizadas, finalidad del tratamiento y versión inicial aprobada. | Legal / Institución | Antes de certificación |
| Política de privacidad | La aplicación procesa datos personales, contactos y respuestas de salud. | Política oficial, derechos del titular, canal de atención y texto que debe presentarse antes de aceptar. | Legal / Protección de datos | Antes de certificación |
| Retención y eliminación | Los estados y contenidos conservan historial; aún no existe una política institucional completa de retención. | Plazos para donantes activos, bajas, consentimientos revocados, auditoría, correos y copias de seguridad; definir anonimización o eliminación. | Legal / Seguridad | Antes de producción |
| Acceso a datos sensibles | El detalle administrativo presenta información médica y evidencia técnica contraída. | Definir qué perfiles pueden ver, imprimir o exportar cada categoría de información. | Seguridad / Negocio | Antes de certificación |
| Auditoría | Se guardan eventos relevantes, fecha, origen, IP y agente de usuario. | Eventos obligatorios, plazo de conservación, responsables de revisión y mecanismo de entrega ante investigación. | Seguridad / Auditoría | Antes de producción |

## 4. Carné y verificación por QR

| Tema | Situación actual | Confirmación requerida | Responsable sugerido | Momento límite |
|---|---|---|---|---|
| Diseño del carné | Se adaptó el formato oficial proporcionado y se usa tamaño 85,60 × 53,98 mm. | Aprobación formal del diseño final, tipografías, colores, textos, logos y uso de imagen institucional. | Comunicaciones / Institución | Antes de certificación |
| Folio | Se usa el formato `CD-0000001`. | Aprobación definitiva del prefijo, longitud, consecutivo y comportamiento al reactivar o reemitir. | Negocio / Institución | Antes de certificación |
| Contenido del QR | El QR permite verificar la vigencia del carné mediante un token. | Información exacta que podrá mostrar la consulta pública y qué datos nunca deben exponerse. | Seguridad / Legal / Negocio | Antes de certificación |
| Vigencia del carné | El carné se revoca al dar de baja al donante y se genera uno nuevo al reactivar. | Confirmar si además tendrá vencimiento periódico, renovación o causas adicionales de revocación. | Negocio / Legal | Antes de producción |

## 5. Correo electrónico

| Tema | Situación actual | Confirmación requerida | Responsable sugerido | Momento límite |
|---|---|---|---|---|
| Desarrollo y pruebas | smtp4dev captura los mensajes y el PDF sin enviarlos a Internet. Puede instalarse con Docker en la máquina Ubuntu de pruebas. | Confirmar autorización para Docker y el método de acceso restringido a la bandeja de smtp4dev. | Infraestructura / Seguridad | Antes de instalar la VM |
| SMTP institucional | Todavía no se cuenta con el servicio definitivo. | Host, puerto, cifrado, autenticación, remitente autorizado, límites, ambientes y procedimiento de rotación de credenciales. | Infraestructura / Correo | Antes de certificación con envío real |
| Remitente | En pruebas se usa `dona-organos-dev@edupan.com` como identidad visible. | Dirección y nombre oficiales del remitente, dirección de respuesta y tratamiento de rebotes. | Institución / Comunicaciones | Antes de certificación |
| Contenido del mensaje | Existe una plantilla con agradecimiento, folio y carné adjunto. | Aprobación institucional del asunto, cuerpo, pie legal y datos de contacto. | Comunicaciones / Legal | Antes de certificación |

## 6. Infraestructura y ambientes

| Tema | Situación actual | Confirmación requerida | Responsable sugerido | Momento límite |
|---|---|---|---|---|
| Ubicación del servidor | Se espera una máquina virtual Ubuntu, pero no se ha confirmado si estará en nube u on-premise. | Ubicación, proveedor, red, restricciones y responsables operativos. | Infraestructura | Antes del aprovisionamiento |
| Servidor de pruebas | Se propusieron Ubuntu Server 24.04 LTS, 4 vCPU, 8 GB RAM y 80 GB SSD. | Aprobación o ajuste de recursos, arquitectura de CPU y capacidad de ampliación. | Infraestructura | Antes del aprovisionamiento |
| Acceso administrativo | El equipo del proyecto instalaría el stack. | Usuarios SSH, llaves, permisos `sudo`, VPN o IP autorizada y política de instalación de software. | Infraestructura / Seguridad | Antes de la entrega |
| Docker | Se requiere inicialmente para smtp4dev y puede utilizarse para servicios auxiliares. | Confirmar si Docker Engine y Compose están permitidos y quién los mantiene. | Infraestructura / Seguridad | Antes de la instalación |
| DNS y HTTPS | Todavía no se conocen los nombres de los ambientes. | DNS de pruebas, certificación y producción; responsable de certificados y renovación. | Infraestructura | Antes de publicar cada ambiente |
| Red y puertos | Se prevé publicar solo HTTP/HTTPS y restringir SSH, MySQL y smtp4dev. | Reglas definitivas de firewall, proxy, balanceador, VPN y salida a Internet. | Infraestructura / Seguridad | Antes de publicar pruebas |
| Respaldos | No se ha definido una política institucional. | Frecuencia, retención, cifrado, ubicación y pruebas de restauración de base de datos y archivos. | Infraestructura / Seguridad | Antes de producción |
| Recuperación | No se han definido RPO, RTO ni alta disponibilidad. | Objetivos de recuperación, tolerancia a caída y procedimiento de contingencia. | Institución / Infraestructura | Antes de producción |
| Monitoreo | Falta seleccionar herramientas y responsables. | Monitoreo de disponibilidad, errores, espacio, base de datos, colas y alertas. | Infraestructura / Soporte | Antes de producción |

## 7. Administración, métricas y exportación

| Tema | Situación actual | Confirmación requerida | Responsable sugerido | Momento límite |
|---|---|---|---|---|
| Perfiles administrativos | Actualmente existe un administrador de pruebas. | Roles reales, permisos por módulo, responsables de alta/baja de cuentas y necesidad de MFA. | Institución / Seguridad | Antes de certificación |
| Recuperación de acceso | No se ha definido el proceso institucional. | Restablecimiento de contraseña, bloqueo, caducidad y canal de soporte. | Seguridad / Soporte | Antes de certificación |
| Métricas | El módulo está previsto, pero faltan definiciones oficiales. | Indicadores, fórmulas, periodos, filtros, estados incluidos y responsables que podrán consultarlos. | Negocio / Dirección | Antes de desarrollar métricas definitivas |
| CSV | La exportación respeta los filtros del listado. | Columnas autorizadas, tratamiento de datos sensibles, perfiles con permiso y registro de cada exportación. | Seguridad / Negocio | Antes de certificación |

## 8. Portal público y CMS

| Tema | Situación actual | Confirmación requerida | Responsable sugerido | Momento límite |
|---|---|---|---|---|
| Textos legales y educativos | Se conservaron los contenidos iniciales de aspectos legales, mitos y preguntas frecuentes. | Revisión y aprobación de redacción, vigencia normativa, enlaces y responsable editorial. | Legal / Comunicaciones | Antes de certificación |
| Contactos oficiales | El portal presenta información de contacto tomada del material inicial. | Teléfonos, correos, horarios y autorización para su publicación. | Institución / Comunicaciones | Antes de certificación |
| Testimonios e historias | Los testimonios actuales son ilustrativos; se prevé administrar uno o dos videos sin guardarlos como BLOB en la base de datos. | Videos definitivos, autorización de imagen y voz, subtítulos, formato, tamaño, alojamiento y responsable de aprobación. | Comunicaciones / Legal | Antes de publicar testimonios reales |
| CMS | Está previsto un CMS con activación/desactivación, fecha y usuario de modificación. | Flujo editorial, perfiles, aprobación previa, versionado y alcance exacto de secciones editables. | Comunicaciones / Negocio | Antes de desarrollar el CMS definitivo |
| “Contáctenos” | Se ha contemplado, pero no se ha definido su operación final. | Destinatarios, campos, consentimiento, CAPTCHA, plazo de respuesta y conservación de mensajes. | Institución / Comunicaciones | Antes de implementar el módulo |

## 9. Compatibilidad y aceptación

| Tema | Situación actual | Confirmación requerida | Responsable sugerido | Momento límite |
|---|---|---|---|---|
| Navegadores y dispositivos | El desarrollo es responsivo y se prueba principalmente en navegadores modernos. | Matriz oficial de navegadores, versiones, teléfonos, tabletas y resoluciones soportadas. | Institución / QA | Antes de certificación |
| Accesibilidad | Se aplican controles básicos de etiquetas, foco y reducción de movimiento. | Nivel requerido, por ejemplo WCAG 2.1 AA, y mecanismo de evaluación. | Institución / QA | Antes de certificación |
| Rendimiento y volumen | No se dispone de cifras oficiales. | Donantes esperados, registros diarios, usuarios concurrentes, correos por hora y crecimiento anual. | Institución / Infraestructura | Antes de dimensionar producción |
| Criterios de aceptación | Las pruebas actuales se basan en requisitos conversados y mockups. | Casos oficiales de aceptación, responsables de aprobar y evidencias requeridas para pasar entre ambientes. | Negocio / QA | Antes de certificación |

## 10. Prioridad recomendada

### Antes de aprovisionar el servidor de pruebas

1. Ubicación y características de la máquina virtual.
2. Acceso SSH, `sudo`, VPN y políticas de Docker.
3. DNS, HTTPS, firewall y responsables de infraestructura.
4. Autorización y acceso seguro a smtp4dev.

### Antes de certificación

1. Servicio y nivel de validación de identidad.
2. Textos legales, consentimiento y privacidad.
3. Roles administrativos y acceso a datos sensibles.
4. Aprobación del carné, QR, correo y contenidos públicos.
5. Tiempo definitivo de sesión y controles de seguridad.
6. Casos y responsables de aceptación.

### Antes de producción

1. SMTP institucional y credenciales administradas.
2. Política de retención, respaldos y restauración.
3. Monitoreo, alertas, RPO y RTO.
4. Volumen esperado y dimensionamiento definitivo.
5. Aprobaciones institucionales y legales documentadas.

## 11. Registro de respuestas

Cada respuesta debe incorporarse a este documento indicando:

- decisión aprobada;
- nombre y área de quien la confirma;
- fecha de confirmación;
- documento o correo que sirve como respaldo;
- ambientes donde aplica;
- cambio técnico requerido, si corresponde.
