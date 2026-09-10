# Módulo: Equipos (Equipment)

- **Ref. plan**: §5.3 de `project.md`
- **Estado**: ✅ implementado (CRUD + inventario + hoja de vida + catálogo maestro por categoría, ver [`categorias-equipos.md`](./categorias-equipos.md))
- **Depende de**: Clientes ✅ · Categorías de equipos ✅

> Inventario de equipos que pertenecen a un cliente. La hoja de vida/historial (§5.3) consolida las Órdenes de trabajo del equipo. Equipos eliminados = soft delete, visibles solo para admin.

## Hoja de vida en PDF
Botón «Hoja de vida (PDF)» en `equipment/show` (admin y panel cliente): genera con dompdf (`admin.equipment.pdf`, vista compartida) la ficha técnica completa + historial de OT. Rutas `admin.equipment.pdf` y `client.equipment.pdf` (cliente solo sus equipos). Logo del cliente embebido vía `Client::logoBase64()`.
**Visibilidad**: en la hoja de vida (web y PDF) del **cliente** solo aparecen OT aprobadas y enviadas por el admin (`WorkOrder::scopeSentToClient`); el admin ve el historial completo.

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
| category_id | FK equipment_categories | required, exists (nullOnDelete en BD) | Categoría/plantilla del equipo (ver `categorias-equipos.md`) |
| name | string | required, max:255 | Equipo; se prediligencia con el nombre de la categoría (editable) |
| brand_id | FK brands | nullable, exists | Marca (catálogo) |
| model_id | FK equipment_models | nullable, exists, debe pertenecer a la marca Y a la categoría | Modelo (catálogo) |
| serial_number | string | required, unique | Número de serie |
| entry_date | date | nullable | Fecha de ingreso |
| purchase_date | date | nullable | Fecha de compra |
| warranty_status | string | nullable, in:WARRANTY_STATUSES | Equipo en garantía |
| warranty_expiry | date | nullable | Vencimiento de garantía |
| location | string | nullable | Ubicación / sede (dirección de la instalación); distinto de `area_id` |
| risk_class | string | nullable, in:RISK_CLASSES | Clasificación por riesgo (INVIMA) |
| specialties | json (array de strings) | nullable | Especialidades (nombres del catálogo `specialties`, snapshot editable) |
| invima_registry | string | nullable | Registro INVIMA |
| manufacturer | string | nullable | Fabricante (snapshot autodiligenciado desde la marca) |
| origin_country | string | nullable | País de origen (snapshot autodiligenciado desde la marca) |
| acquisition_type | string | nullable, in:ACQUISITION_TYPES | Tipo de adquisición |
| voltage/amperage/current/power/temperature/pressure/weight/speed | string | nullable | Características técnicas |
| predominant_technology | string | nullable | Tecnología predominante |
| maintenance_tasks | json (array de strings) | nullable | Subtareas que aplican (nombres del catálogo, snapshot; se ejecutan en la OT) |
| accessories | json (array de strings) | nullable | Accesorios del equipo (nombres del catálogo, snapshot) |
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
- `ACQUISITION_TYPES`: purchase/comodato/leasing/donation.
- ⚠️ La **periodicidad de mantenimiento** y las **observaciones técnicas/generales** se eliminaron del equipo (las observaciones viven solo en las OT).
- ⚠️ `SPECIALTIES`, `MAINTENANCE_TASKS` y `ACCESSORIES` **dejan de ser constantes**: pasan a catálogos configurables por admin (tablas `specialties`, `maintenance_tasks`, `accessories`). Ver `categorias-equipos.md`.

En BD/código se guarda el valor en inglés; en vistas se muestra la etiqueta en español (helpers `statusLabel()`, `warrantyStatusLabel()`, `riskClassLabel()`, `acquisitionTypeLabel()`).

## Plantilla de mantenimiento y accesorios
`maintenance_tasks` y `accessories` definen QUÉ subtareas/accesorios aplican a **este** equipo (snapshot prediligenciado desde la categoría, editable por unidad). No registran la ejecución: al crear una OT de mantenimiento estos ítems se proponen/marcan por intervención (ver `docs/modules/ordenes-trabajo.md`). Las reglas de validación viven en el trait `InteractsWithEquipmentRules` (compartido por Store/Update).

## Catálogo maestro: categoría → marca → modelo
- `EquipmentCategory` es la **plantilla completa** del equipo (ver [`categorias-equipos.md`](./categorias-equipos.md)); al elegirla, el formulario prediligencia identificación, características técnicas, subtareas y accesorios (snapshot editable).
- `Brand` (tabla `brands`, con `manufacturer` y `origin_country` que se **autodiligencian** en el equipo al elegir la marca) y `EquipmentModel` (tabla `equipment_models`, `belongsTo Brand` + `belongsTo EquipmentCategory`). Un modelo pertenece a una marca (`unique(brand_id, name)`) y a una categoría.
- En el formulario, **cascada**: categoría → marca (filtrada a marcas con modelos de esa categoría) → modelo (filtrado por marca+categoría). Validado en Request.
- CRUD admin: `Admin/BrandController`, `Admin/EquipmentModelController`, `Admin/EquipmentCategoryController` (permisos `brands`, `equipment_models`, `equipment_categories`, solo admin). Semilla inicial en `EquipmentCatalogSeeder`.

## Relaciones
- `belongsTo(Client)` — dueño del equipo.
- `belongsTo(Area)` — área interna del cliente donde está el equipo (dependiente del cliente en el form).
- `belongsTo(EquipmentCategory)` · `belongsTo(Brand)` · `belongsTo(EquipmentModel, 'model_id')` — catálogo maestro.
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
