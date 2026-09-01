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
| name | string | required, max:255 | Nombre del equipo |
| type | string | nullable | Tipo (monitor, ventilador…) |
| brand | string | nullable | Marca |
| model | string | nullable | Modelo |
| serial_number | string | required, unique | Serial |
| purchase_date | date | nullable | Fecha de compra |
| warranty_expiry | date | nullable | Vencimiento de garantía |
| location | string | nullable | Ubicación/área en el cliente |
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

## Relaciones
- `belongsTo(Client)` — dueño del equipo.
- (futuras) `hasMany(WorkOrder)`, `hasMany(Maintenance)` — alimentan la hoja de vida.

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
