# Módulo: Categorías de equipos y catálogos de opciones

- **Ref. plan**: extiende §5.3 de `project.md` (alcance solicitado por el cliente: catálogo maestro de equipos)
- **Estado**: ✅ implementado
- **Depende de**: Equipos ✅ (marca/modelo ya existen)

> La **categoría** (Compresor, Pantalla, Autoclave…) es la **plantilla completa del equipo**: define por defecto casi todos los campos del formulario (características técnicas, riesgo, subtareas, accesorios, observaciones…). Al crear un equipo se elige categoría → marca → modelo y todo se **prediligencia**; el usuario solo captura los datos únicos de la unidad (serial, registro INVIMA, fechas, cliente…) y puede ajustar cualquier valor prediligenciado. Los valores quedan como **snapshot** en el equipo: cambios posteriores en la categoría NO afectan equipos existentes.

## Entidades

### `EquipmentCategory` → tabla `equipment_categories`

| Campo | Tipo | Reglas | Notas |
|-------|------|--------|-------|
| name | string | required, unique, max:255 | «Compresor», «Pantalla»… |
| description | text | nullable | Descripción interna |
| **Plantilla — Identificación** | | | |
| risk_class | string | nullable, in:RISK_CLASSES | Riesgo INVIMA por defecto |
| specialties | json (array de strings) | nullable | Nombres de especialidades que aplican |
| **Plantilla — Características técnicas** | | | |
| voltage/amperage/current/power/temperature/pressure/weight/speed | string | nullable | Valores por defecto (campos fijos) |
| predominant_technology | string | nullable | |
| **Plantilla — Mantenimiento / accesorios** | | | |
| maintenance_tasks | json (array de strings) | nullable | Nombres de subtareas que aplican |
| accessories | json (array de strings) | nullable | Nombres de accesorios que aplican |
| components | text | nullable | Componentes (detalle) |
| default_ot_observations | text | nullable | Observaciones por defecto para OT |

- Sin soft delete. **No se puede eliminar** una categoría con modelos asociados (`restrictOnDelete`); en equipos la FK es `nullOnDelete` (el equipo conserva su snapshot).
- NO incluye datos únicos de la unidad: serial, registro INVIMA, fechas, garantía, adquisición, cliente/área/sede, estado, notas.
- NO incluye **fabricante ni país de origen**: viven en la **marca** (`brands.manufacturer` / `brands.origin_country`) y se autodiligencian al elegirla. Tampoco periodicidad ni observaciones técnicas/generales (eliminadas del dominio de equipos).

### Catálogos de opciones (configurables por admin)

Reemplazan las constantes fijas `Equipment::MAINTENANCE_TASKS`, `Equipment::ACCESSORIES` y `Equipment::SPECIALTIES`:

| Modelo | Tabla | Campos |
|--------|-------|--------|
| `MaintenanceTask` | `maintenance_tasks` | name (unique), is_active (default true) |
| `Accessory` | `accessories` | name (unique), is_active (default true) |
| `Specialty` | `specialties` | name (unique), is_active (default true) |

- Son la **fuente de opciones** de los checkboxes en formularios de categoría y de equipo.
- La selección se guarda como **JSON de nombres (strings)** en categoría y equipo — no pivotes. Esto hace el snapshot trivial: renombrar/desactivar un ítem del catálogo no altera lo ya guardado. Si un valor guardado ya no existe en el catálogo, se sigue mostrando marcado (como ítem extra).
- `is_active = false` oculta el ítem de los checkboxes para selecciones nuevas, sin borrar historial.
- Los nombres son datos del usuario (ES) — la convención código-EN/UI-ES aplica solo a enums fijos del código.
- Semilla inicial: los 15 + 15 + 4 ítems actuales (etiquetas ES de las constantes) vía `EquipmentCatalogSeeder`.

### Cambios en entidades existentes

- **`EquipmentModel`**: gana `category_id` (FK required, `restrictOnDelete`) y **pierde** todos los campos de defaults (type, manufacturer, origin_country, risk_class, specialties, invima_registry, maintenance_frequency, maintenance_tasks, accessories) — los defaults viven SOLO en la categoría. Queda: `brand_id`, `category_id`, `name` (`unique(brand_id, name)`).
- **`Equipment`**: gana `category_id` (FK required en el form, `nullOnDelete`), **pierde** `type` (lo reemplaza la categoría). `specialties`/`maintenance_tasks`/`accessories` pasan de claves de enum a **nombres del catálogo** (JSON de strings, snapshot editable). El resto de columnas de plantilla se conservan como snapshot. `name` se prediligencia con el nombre de la categoría (editable).
- Constantes eliminadas de `Equipment`: `MAINTENANCE_TASKS`, `ACCESSORIES`, `SPECIALTIES` (y sus helpers de etiquetas). `RISK_CLASSES`, `FREQUENCIES`, `STATUSES`, `WARRANTY_STATUSES`, `ACQUISITION_TYPES` siguen como enums fijos.

## Flujo del formulario de equipo

1. Selects en cascada: **categoría → marca (filtrada a marcas con modelos de esa categoría) → modelo (filtrado por marca+categoría)**.
2. Al elegir categoría, Alpine **prediligencia** toda la plantilla (datos embebidos como JSON en la vista; sin endpoint extra). El usuario puede editar cualquier campo.
3. Checkboxes (especialidades, subtareas, accesorios) se renderizan desde el catálogo activo, premarcados según la plantilla; ítems guardados que ya no estén en el catálogo se muestran igualmente.
4. **Alta rápida**: desde el formulario se puede escribir un ítem nuevo → POST JSON al store del catálogo → se agrega el checkbox marcado y queda disponible globalmente.
5. Al guardar, los valores van al equipo como snapshot; cambios futuros de la categoría no lo tocan.

## Prediligenciado al editar
En `edit` NO se re-aplica la plantilla: se muestran los valores del equipo. Cambiar la categoría de un equipo existente ofrece re-aplicar la plantilla (confirmación en UI; por defecto conserva los valores actuales).

## Relaciones
- `EquipmentCategory` hasMany `EquipmentModel`, hasMany `Equipment`.
- `EquipmentModel` belongsTo `Brand`, belongsTo `EquipmentCategory`.
- `Equipment` belongsTo `EquipmentCategory`.

## Permisos (spatie)
- Módulo `equipment_categories` → CRUD de categorías (solo admin).
- Módulo `equipment_catalogs` → CRUD de los tres catálogos de opciones (solo admin). La alta rápida usa `create equipment_catalogs`.
- Agregar ambos a `MODULES` en `RolePermissionSeeder`.

## Controladores y rutas (admin)
- `Admin/EquipmentCategoryController` — resource (sin show); su form es la versión «plantilla» del form de equipo.
- `Admin/EquipmentCatalogController` — controlador único parametrizado por segmento `{catalog}` (`maintenance_tasks|accessories|specialties`, coincide con la tabla): rutas `equipment_catalogs.{index,store,update,destroy}`. `store` responde JSON 201 cuando la petición es AJAX (alta rápida). Requests en `app/Http/Requests/EquipmentCatalog/`.
- Vistas en `resources/views/admin/equipment_categories/` y una vista agrupada con pestañas para los tres catálogos en `resources/views/admin/equipment_catalogs/index.blade.php`.
- Componente reutilizable `<x-catalog-checkboxes>` (`resources/views/components/catalog-checkboxes.blade.php`): checkboxes desde catálogo + alta rápida inline; usado en forms de categoría y de equipo.

## Reglas de negocio
- `name` de categoría único; ítems de catálogo únicos por tabla.
- El modelo elegido debe pertenecer a la marca Y a la categoría seleccionadas (validado en Request de equipos).
- No eliminar ítem de catálogo en uso: preferir `is_active = false` (el destroy avisa si está referenciado en categorías).

## Impacto en OT
El diligenciamiento del técnico propone las subtareas/accesorios del **snapshot del equipo** (igual que hoy); solo cambia el origen de los valores (strings del catálogo en vez de claves de enum). Verificar `docs/modules/ordenes-trabajo.md` al implementar.
