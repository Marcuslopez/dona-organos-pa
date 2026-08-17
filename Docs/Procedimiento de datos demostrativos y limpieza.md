# Procedimiento de datos demostrativos y limpieza

## 1. Propósito

Definir un proceso controlado, verificable y reversible para cargar datos
demostrativos que permitan probar las métricas de DONA ÓRGANOS PANAMÁ y para
eliminarlos posteriormente sin afectar registros creados manualmente ni datos
reales.

Este procedimiento cubre:

- generación de donantes ficticios distribuidos por fecha, edad, género,
  ubicación, estado y preferencia de donación;
- identificación inequívoca de los registros demostrativos;
- validaciones anteriores y posteriores a la carga;
- limpieza segura de los datos y de todas sus relaciones;
- evidencias mínimas de ejecución y recuperación ante fallos.

## 2. Objetivo

Proporcionar un conjunto representativo de datos para comprobar:

- tarjetas de resumen y gráficas administrativas;
- filtros por períodos y demás dimensiones disponibles;
- bajas y reactivaciones;
- distribución por edades, género, ubicación y alcance de donación;
- comportamiento de consultas y exportaciones;
- rendimiento inicial del módulo de métricas.

Los datos demostrativos no deben formar parte de indicadores institucionales ni
confundirse con voluntades reales de donación.

## 3. Alcance y principio de seguridad

El seeder y el comando de limpieza serán herramientas operativas separadas del
seeding normal de catálogos. No se ejecutarán desde `DatabaseSeeder` ni mediante
`php artisan migrate --seed`.

La tabla `donors` tendrá un indicador técnico booleano `is_demo` con valor
predeterminado `false`. Solo el seeder de métricas podrá crear registros con
`is_demo = true`. Las tablas dependientes conservarán su relación mediante
`donor_id`; no necesitan repetir este indicador.

El módulo de métricas incluirá todos los registros existentes en el ambiente,
tanto manuales como demostrativos, porque ambos deben participar en las pruebas
funcionales de las gráficas. El indicador `is_demo` se utilizará exclusivamente
para identificar y limpiar con seguridad los registros generados por el seeder.

> Un registro normal nunca se elimina físicamente por este procedimiento. La
> limpieza solo puede afectar donantes marcados de forma inequívoca como datos
> demostrativos.

## 4. Componentes que se implementarán

### 4.1 Seeder demostrativo

Clase propuesta:

```text
Database\Seeders\MetricsDemoSeeder
```

Ejecución explícita propuesta:

```bash
php artisan db:seed --class=MetricsDemoSeeder
```

El seeder deberá ser repetible sin duplicar registros. Antes de insertar deberá
detectar una carga demostrativa existente y detenerse con una explicación, salvo
que se implemente posteriormente una opción expresa de reposición.

### 4.2 Comando de inspección

Comando propuesto:

```bash
php artisan demo:status
```

Mostrará, sin modificar datos:

- ambiente actual;
- total de donantes demostrativos;
- cantidad de registros relacionados por tabla;
- rango mínimo y máximo de fechas;
- advertencias por relaciones huérfanas o inconsistencias;
- disponibilidad o bloqueo del correo saliente.

### 4.3 Comando de limpieza

Comando propuesto:

```bash
php artisan demo:purge
```

El comando no deberá borrar nada si no encuentra donantes con `is_demo = true`.
En ese caso finalizará correctamente con el mensaje `No existen datos
demostrativos para eliminar`.

La opción automatizada propuesta será:

```bash
php artisan demo:purge --force
```

`--force` omitirá la interacción, pero no omitirá ninguna validación de
seguridad. Su uso debe limitarse a automatización autorizada.

## 5. Validaciones previas a la carga

Antes de generar datos se deberá comprobar, en este orden:

1. La conexión apunta a la base de datos y ambiente esperados.
2. Todas las migraciones requeridas están aplicadas.
3. Existe el campo `donors.is_demo` y su valor predeterminado es `false`.
4. Los catálogos de género, parentesco, alcance y geografía tienen datos.
5. No existen registros demostrativos de una carga anterior.
6. Las claves foráneas requeridas están activas.
7. Las relaciones dependientes pueden eliminarse de forma controlada.
8. El correo está configurado con `log`, `array`, smtp4dev u otro capturador de
   pruebas; nunca con salida real para direcciones ficticias.
9. El número solicitado de registros está dentro del límite configurado.
10. Hay una copia de seguridad reciente cuando se ejecute en un ambiente
    compartido o productivo.

Si falla cualquiera de estas comprobaciones, la carga debe abortarse antes de
insertar el primer registro.

## 6. Distribución mínima de los datos

La carga debe ser determinista mediante una semilla aleatoria documentada. Se
recomienda comenzar con 300 donantes distribuidos durante los últimos 24 meses.

Debe incluir variación suficiente para validar:

- edades entre 18 y 100 años, agrupables por rangos;
- géneros disponibles en el catálogo;
- provincias, distritos y corregimientos;
- donantes activos y dados de baja;
- al menos un donante dado de baja con fecha de registro en cada mes del período;
- eventos de registro, baja y reactivación en meses diferentes;
- preferencias de córneas y de órganos y tejidos;
- uno y dos contactos;
- respuestas médicas afirmativas y negativas.

Las cédulas, códigos posteriores, correos y teléfonos serán inequívocamente
ficticios y respetarán las mismas restricciones de unicidad y formato que los
registros normales.

## 7. Validaciones posteriores a la carga

Terminada la transacción de carga, se comprobará:

1. La cantidad esperada de donantes con `is_demo = true`.
2. La inexistencia de cédulas, códigos posteriores, correos o carnés duplicados.
3. Que cada donante tenga sus relaciones obligatorias.
4. Que exista al menos un contacto por donante y nunca más del máximo permitido.
5. Que los estados concuerden con las fechas de baja y reactivación.
6. Que ninguna fecha de baja o reactivación sea posterior al momento de carga.
7. Que los carnés activos y revocados correspondan al estado del donante.
8. Que los rangos de fecha, edad y demás distribuciones sean útiles para las
   gráficas previstas.
9. Que las métricas incluyan tanto los registros manuales como los
   demostrativos del ambiente.
10. Que los resultados totales coincidan con la suma de ambos conjuntos.
11. Que no se haya intentado entregar correo a Internet.

La ejecución mostrará un resumen de resultados. Si una validación crítica falla,
la transacción completa deberá revertirse.

## 8. Validaciones previas a la limpieza

`demo:purge` deberá realizar obligatoriamente estas comprobaciones:

1. Confirmar que existe el campo `donors.is_demo`.
2. Obtener los IDs únicamente con `is_demo = true`.
3. Detenerse sin ejecutar `DELETE` cuando el resultado esté vacío.
4. Mostrar el total de donantes y registros relacionados que serían eliminados.
5. Verificar que ningún ID seleccionado corresponde a `is_demo = false`.
6. Revisar que no existan relaciones compartidas con donantes normales.
7. Comprobar la estrategia de eliminación de cada tabla dependiente.
8. Confirmar que el ambiente está en la lista permitida por configuración.
9. Solicitar una confirmación explícita que incluya el número de donantes por
   eliminar; una respuesta genérica no será suficiente.
10. Registrar el inicio de la operación sin escribir datos personales en logs.

Ejemplo conceptual de confirmación:

```text
Se eliminarán 300 donantes demostrativos y sus relaciones.
Escriba ELIMINAR 300 DATOS DEMO para continuar:
```

## 9. Ejecución segura de la limpieza

La limpieza se ejecutará dentro de una única transacción de base de datos:

1. Volver a consultar y bloquear los donantes `is_demo = true` seleccionados.
2. Eliminar relaciones en el orden documentado o mediante claves foráneas con
   `ON DELETE CASCADE` previamente verificadas.
3. Eliminar finalmente los donantes demostrativos.
4. Revertir toda la operación ante cualquier excepción.
5. Confirmar la transacción solo cuando las cantidades eliminadas coincidan con
   las previstas.

No se utilizarán patrones de correo, rangos de ID, nombres ni cédulas como
criterio principal de eliminación. La condición obligatoria será siempre
`is_demo = true` sobre IDs previamente inspeccionados.

## 10. Validaciones posteriores a la limpieza

Después de confirmar la transacción se deberá comprobar:

- `donors` no contiene filas con `is_demo = true`;
- no quedan contactos, consentimientos, respuestas médicas, preferencias,
  carnés ni eventos históricos pertenecientes a los IDs eliminados;
- no se produjeron registros huérfanos;
- la cantidad de donantes normales antes y después es idéntica;
- las métricas oficiales siguen disponibles y muestran datos coherentes;
- se puede ejecutar nuevamente `demo:status` sin detectar semillas;
- una segunda ejecución de `demo:purge` no modifica la base de datos.

El log final incluirá ambiente, fecha, usuario operativo, cantidades por tabla y
resultado. No incluirá cédulas, nombres, correos, códigos posteriores ni tokens.

## 11. Recuperación ante fallos

- Un fallo antes de confirmar la transacción debe dejar la base sin cambios.
- Si se detecta una inconsistencia después de la operación, no se ejecutarán
  correcciones manuales sin revisar primero el log y la copia de seguridad.
- La restauración de una copia de seguridad requiere autorización operativa y
  debe probarse primero en un ambiente aislado.
- Todo fallo debe conservar suficiente evidencia técnica para identificar la
  tabla y validación afectadas, sin exponer información personal.

## 12. Uso en ambientes

### Desarrollo y pruebas unitarias

La carga y limpieza pueden ejecutarse por personal técnico, conservando todas
las validaciones.

### Certificación

Debe registrarse quién ejecutó la carga, qué volumen se generó y cuándo se hizo
la limpieza. Las pruebas de aceptación deben indicar expresamente si utilizan
datos demostrativos.

### Producción

La recomendación principal es no cargar semillas demostrativas en producción.
Si la institución lo autoriza excepcionalmente para una validación inicial,
deben cumplirse además estas condiciones:

- autorización escrita y ventana de ejecución aprobada;
- copia de seguridad verificada;
- correo real bloqueado para los registros demostrativos;
- definición institucional previa sobre el uso de datos demo en cifras o
  exportaciones de producción;
- responsable técnico presente durante carga, validación y limpieza;
- limpieza y comprobaciones posteriores dentro de la misma ventana;
- evidencia de que ningún registro normal fue modificado.

Los datos demostrativos validan el funcionamiento estadístico y funcional. No
sustituyen monitoreo de infraestructura, pruebas de respaldo, seguridad,
conectividad, colas, correo, almacenamiento ni disponibilidad.

## 13. Lista de verificación operativa

### Antes de cargar

- [ ] Ambiente y base de datos confirmados.
- [ ] Migraciones y catálogos completos.
- [ ] No existen datos demo previos.
- [ ] Correo externo bloqueado.
- [ ] Copia de seguridad disponible cuando corresponda.
- [ ] Volumen y período de datos aprobados.

### Después de cargar

- [ ] Cantidades y relaciones validadas.
- [ ] Unicidad comprobada.
- [ ] Distribuciones útiles para todas las gráficas.
- [ ] Métricas incluyen registros manuales y demostrativos del ambiente.
- [ ] No hubo correos reales.

### Antes de limpiar

- [ ] `demo:status` identifica datos demostrativos.
- [ ] Cantidades por eliminar revisadas.
- [ ] Ningún donante normal está incluido.
- [ ] Confirmación explícita obtenida.

### Después de limpiar

- [ ] No quedan donantes demo.
- [ ] No quedan relaciones demo ni huérfanos.
- [ ] El total de donantes normales no cambió.
- [ ] Métricas y aplicación verificadas.
- [ ] Resultado registrado sin datos personales.

## 14. Estado de implementación

Se encuentran implementados y cubiertos por pruebas automatizadas:

- campo `donors.is_demo`, con valor predeterminado `false` e índice;
- servicio central `App\Services\AdminMetricsService`;
- servicio seguro `App\Services\DemoDataService`;
- seeder `Database\Seeders\MetricsDemoSeeder`;
- comandos `demo:status` y `demo:purge`;
- inclusión conjunta de registros manuales y demo en el servicio de métricas;
- validación de que la limpieza conserva los donantes normales y elimina las
  relaciones dependientes.

Antes de cargar semillas en un ambiente se deben configurar explícitamente las
variables `DEMO_DATA_*`, ejecutar las migraciones y seguir las validaciones de
este documento. La implementación no carga datos demostrativos automáticamente.
