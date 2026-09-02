# Módulo: Equipos (Equipment)

- **Ref. plan**: §5.3 de `project.md`
- **Estado**: ✅ implementado (CRUD + inventario; hoja de vida pendiente de OT/Mantenimientos)
- **Depende de**: Clientes ✅

> Inventario de equipos que pertenecen a un cliente. La hoja de vida/historial (§5.3) se completa cuando existan Órdenes y Mantenimientos. Equipos eliminados = soft delete, visibles solo para admin.

## Modelo
`Equipment` → tabla `equipment` (incontable; el segmento de ruta y vistas también son `equipment`).

## Campos (`equipment`)
| Campo | Tipo | Reglas | Notas |
|-------|------|--------|-------|
| client_id | FK clients | required, constrained | Dueño del equipo |
| area_id | FK areas | nullable, exists, debe pertenecer al cliente | Área interna donde está el equipo (UCI…) |
| name | string | required, max:255 | Nombre del equipo |
| type | string | nullable | Tipo (monitor, ventilador…) |
| brand_id | FK brands | nullable, exists | Marca (catálogo) |
| model_id | FK equipment_models | nullable, exists, debe pertenecer a la marca | Modelo (catálogo) |
| serial_number | string | required, unique | Serial |
| purchase_date | date | nullable | Fecha de compra |
| warranty_expiry | date | nullable | Vencimiento de garantía |
| location | string | nullable | Ubicación / sede (dirección de la instalación del cliente); distinto de `area_id` |
| notes | text | nullable | Observaciones |
| status | string | required, in:STATUSES | Estado (ver abajo) |
| (soft deletes) | — | — | Baja recuperable, visible solo admin |

## Estados (código en inglés, UI en español)
Constante `Equipment::STATUSES` mapea valor → etiqueta:
- `active` → «Activo»
- `inactive` → «Inactivo»
- `maintenance` → «En mantenimiento»
- `retired` → «Dado de baja»

Default: `active`. En BD/código se guarda el valor en inglés; en vistas se muestra la etiqueta en español (`Equipment::STATUSES[$status]`).

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
