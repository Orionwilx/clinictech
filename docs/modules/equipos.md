# Módulo: Equipos (Equipment)

- **Ref. plan**: §5.3 de `project.md`
- **Estado**: ✅ implementado (CRUD + inventario + hoja de vida)
- **Depende de**: Clientes ✅

> Inventario de equipos que pertenecen a un cliente. La hoja de vida/historial (§5.3) consolida las Órdenes de trabajo del equipo. Equipos eliminados = soft delete, visibles solo para admin.

## Hoja de vida (`equipment/show`)
La ficha del equipo es su **hoja de vida**: identidad (cliente, área, marca/modelo, serial, compra/garantía, ubicación, observaciones) + **resumen** (nº de intervenciones, preventivas, correctivas, última intervención) + **historial cronológico** de sus `WorkOrder` (código enlazado, tipo, estado, título, diagnóstico, trabajo realizado, técnico, fecha), ordenado del más reciente al más antiguo. El controlador (`EquipmentController::show`) carga `workOrders.technician` con `latest()`. Botón «+ Nueva orden» precarga `client_id` + `equipment_id` en el form de OT.

## Modelo
`Equipment` → tabla `equipment` (incontable; el segmento de ruta y vistas también son `equipment`).

## Campos (`equipment`)
El formulario está organizado en secciones: **Inventario**, **Datos específicos del cliente**, **Identificación**, **Características técnicas**, **Subtareas de mantenimiento** y **Estado de accesorios**.

| Campo | Tipo | Reglas | Notas |
|-------|------|--------|-------|
| client_id | FK clients | required, constrained | Dueño del equipo |
| area_id | FK areas | nullable, exists, debe pertenecer al cliente | Área interna donde está el equipo (UCI…) |
| name | string | required, max:255 | Equipo (p. ej. «Autoclave») |
| type | string | nullable | Tipo |
| brand_id | FK brands | nullable, exists | Marca (catálogo) |
| model_id | FK equipment_models | nullable, exists, debe pertenecer a la marca | Modelo (catálogo) |
| serial_number | string | required, unique | Número de serie |
| entry_date | date | nullable | Fecha de ingreso |
| purchase_date | date | nullable | Fecha de compra |
| warranty_status | string | nullable, in:WARRANTY_STATUSES | Equipo en garantía |
| warranty_expiry | date | nullable | Vencimiento de garantía |
| location | string | nullable | Ubicación / sede (dirección de la instalación); distinto de `area_id` |
| risk_class | string | nullable, in:RISK_CLASSES | Clasificación por riesgo (INVIMA) |
| specialties | json (array) | nullable, in:SPECIALTIES | Clasificación por especialidad (multi) |
| invima_registry | string | nullable | Registro INVIMA |
| manufacturer | string | nullable | Fabricante |
| origin_country | string | nullable | País de origen |
| maintenance_frequency | string | nullable, in:FREQUENCIES | Periodicidad |
| acquisition_type | string | nullable, in:ACQUISITION_TYPES | Tipo de adquisición |
| voltage/amperage/current/power/temperature/pressure/weight/speed | string | nullable | Características técnicas |
| predominant_technology | string | nullable | Tecnología predominante |
| technical_observations / general_observations | text | nullable | Observaciones técnicas / generales |
| maintenance_tasks | json (array) | nullable, in:MAINTENANCE_TASKS | Plantilla: subtareas que aplican (se ejecutan en la OT) |
| accessories | json (array) | nullable, in:ACCESSORIES | Plantilla: accesorios del equipo |
| components | text | nullable | Componentes/accesorios (detalle) |
| default_ot_observations | text | nullable | Observaciones por defecto para OT |
| notes | text | nullable | Notas/observaciones del equipo |
| status | string | required, in:STATUSES | Estado (ver abajo) |
| (soft deletes) | — | — | Baja recuperable, visible solo admin |

## Enumeraciones (código EN / UI ES)
Constantes en `Equipment`:
- `STATUSES`: active/inactive/maintenance/retired (default `active`).
- `WARRANTY_STATUSES`: en_garantia/sin_garantia/leasing.
- `RISK_CLASSES`: I/IIA/IIB/III (clasificación INVIMA de dispositivos médicos, Colombia).
- `SPECIALTIES` (multi): prevention/rehabilitation/treatment/lab_analysis.
- `FREQUENCIES`: monthly/bimonthly/quarterly/biannual/annual.
- `ACQUISITION_TYPES`: purchase/comodato/leasing/donation.
- `MAINTENANCE_TASKS` (15 ítems) y `ACCESSORIES` (15 ítems): listas fijas de checkboxes. Ver el modelo para el detalle.

En BD/código se guarda el valor en inglés; en vistas se muestra la etiqueta en español (helpers `statusLabel()`, `warrantyStatusLabel()`, `riskClassLabel()`, `frequencyLabel()`, `acquisitionTypeLabel()`, `specialtyLabels()`).

## Plantilla de mantenimiento y accesorios
`maintenance_tasks` y `accessories` definen QUÉ subtareas/accesorios aplican a **este** equipo (plantilla). No registran la ejecución: al crear una OT de mantenimiento estos ítems se proponen/marcan por intervención (ver `docs/modules/ordenes-trabajo.md`). Las reglas de validación viven en el trait `InteractsWithEquipmentRules` (compartido por Store/Update).

## Catálogo de marcas y modelos
- `Brand` (tabla `brands`) y `EquipmentModel` (tabla `equipment_models`, `belongsTo Brand`). Un modelo pertenece a una marca (`unique(brand_id, name)`).
- En el formulario de equipo, **listas dependientes**: al elegir marca se filtran sus modelos (Alpine). El `model_id` debe pertenecer al `brand_id` (validado en Request).
- CRUD admin: `Admin/BrandController` y `Admin/EquipmentModelController` (permisos `brands` y `equipment_models`, solo admin). Semilla inicial en `EquipmentCatalogSeeder`.

## Relaciones
- `belongsTo(Client)` — dueño del equipo.
- `belongsTo(Area)` — área interna del cliente donde está el equipo (dependiente del cliente en el form).
- `belongsTo(Brand)` · `belongsTo(EquipmentModel, 'model_id')` — catálogo.
- `hasMany(WorkOrder)`. (Mantenimiento = OT tipo preventivo/correctivo.)

## Reglas de negocio
- `serial_number` requerido y único (identifica el equipo).
- `client_id` requerido (todo equipo pertenece a un cliente).
- Baja lógica recuperable; equipos eliminados listados aparte para admin.

## Permisos (spatie)
- Módulo `equipment` ya en `RolePermissionSeeder` → `view/create/update/delete equipment`.
- admin: todo · tecnico: `view`, `update` · cliente: `view` (su propio equipo, Policy futura).

## Notas de UI
- Vistas en `resources/views/admin/equipment/` siguiendo `DESIGN.md`.
- Selector de cliente (dropdown) y selector de estado con etiquetas en español.
- Enlace en el sidebar con `@can('view equipment')`.

## Semilla (git)
`git show HEAD:modules/equipment/CONTEXT.md`
