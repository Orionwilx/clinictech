# Módulo: Órdenes de trabajo (WorkOrder)

- **Ref. plan**: §5.2 y §6 de `project.md` (Fase 4)
- **Estado**: ✅ implementado (ciclo CRUD + estados + asignación; adjuntos/evidencias de archivo pendientes)
- **Depende de**: Clientes ✅ · Equipos ✅ · Técnicos ✅

> Piedra angular operativa. Una orden de trabajo (OT) relaciona un **cliente**, un **equipo** (opcional) y un **técnico** asignado (opcional). Cubre el ciclo: creación → asignación → en proceso → diagnóstico/actividades → finalización/cierre. Su historial alimentará la hoja de vida de equipos y los reportes. Los **adjuntos/evidencias de archivo** (§5.2) se difieren a una iteración posterior (mismo criterio que «adjuntos» de Clientes); el diagnóstico y las actividades se registran como texto.
>
> **Mantenimiento = tipo de OT.** No existe una entidad `Maintenance` separada: un mantenimiento **preventivo** o **correctivo** es una OT con el `type` correspondiente. Desde el hub del cliente hay accesos directos «+ OT preventiva» / «+ OT correctiva» que precargan el tipo.

## Modelo
`WorkOrder` → tabla `work_orders`. Segmento de ruta y vistas: `work_orders`.

## Campos (`work_orders`)
| Campo | Tipo | Reglas | Notas |
|-------|------|--------|-------|
| code | string | unique, autogenerado | Nº de OT `OT-000001` (lo asigna `WorkOrderService`) |
| client_id | FK clients | required, exists | Cliente dueño de la OT |
| equipment_id | FK equipment | nullable, exists, debe pertenecer al cliente | Equipo intervenido |
| technician_id | FK technicians | nullable, exists | Técnico asignado |
| title | string | required, max:255 | Asunto/motivo |
| description | text | nullable | Descripción de la solicitud/falla |
| type | string | required, in:TYPES | Correctivo/Preventivo (código EN/UI ES) |
| priority | string | required, in:PRIORITIES | Baja/Media/Alta (código EN/UI ES) |
| status | string | required, in:STATUSES | Estado (ver abajo) |
| diagnosis | text | nullable | Diagnóstico técnico |
| work_performed | text | nullable | Actividades realizadas / solución |
| scheduled_at | datetime | nullable | Fecha programada |
| started_at | datetime | nullable | Se sella al pasar a «En proceso» |
| completed_at | datetime | nullable | Se sella al «Completar» |
| closed_at | datetime | nullable | Se sella al «Cerrar» |
| (soft deletes) | — | — | Baja recuperable, visible solo admin |

## Estados (código EN / UI ES) — `WorkOrder::STATUSES`
- `open` → «Abierta» (default)
- `assigned` → «Asignada»
- `in_progress` → «En proceso»
- `completed` → «Completada»
- `closed` → «Cerrada»
- `cancelled` → «Cancelada»

## Tipos (`WorkOrder::TYPES`)
- `corrective` → «Correctivo» (default) · `preventive` → «Preventivo»
- Representan la naturaleza del trabajo (mantenimiento correctivo/preventivo). **Diseñado para ampliarse**; pendiente hacerlos configurables por el admin.

## Prioridades (`WorkOrder::PRIORITIES`)
- `low` → «Baja» · `medium` → «Media» (default) · `high` → «Alta»

## Relaciones
- `belongsTo(Client)` — cliente de la OT.
- `belongsTo(Equipment)` — equipo intervenido (nullable).
- `belongsTo(Technician)` — técnico asignado (nullable).
- Inversas: `Client hasMany WorkOrder` · `Equipment hasMany WorkOrder` · `Technician hasMany WorkOrder`.

## Reglas de negocio
- `code` autogenerado y único (`OT-` + consecutivo de 6 dígitos) en `WorkOrderService::create`.
- `equipment_id`, si se indica, **debe pertenecer** al `client_id` seleccionado (validado en el Request).
- Sellos de tiempo automáticos según el estado (en `WorkOrderService`):
  - `in_progress` → `started_at` · `completed` → `completed_at` · `closed` → `closed_at` (solo si estaban vacíos).
- Baja lógica recuperable; OT eliminadas listadas aparte para admin (patrón de Equipos).

## Permisos (spatie)
- Módulo `work_orders` ya en `RolePermissionSeeder` → `view/create/update/delete work_orders`.
- admin: todo · tecnico: `view`, `update` (opera sus OT) · cliente: `view` (su propia OT, Policy futura).

## Notas de UI
- Vistas en `resources/views/admin/work_orders/` siguiendo `DESIGN.md`.
- Selectores de cliente, equipo y técnico; selectores de tipo/prioridad/estado con etiquetas en español.
- **Lista dependiente cliente→equipo** (Alpine): al elegir cliente se filtran solo sus equipos; al cambiar de cliente se limpia el equipo. Además validado en servidor (el equipo debe pertenecer al cliente).
- Enlace en el sidebar con `@can('view work_orders')`.

## Pendiente (iteraciones futuras)
- Adjuntos/evidencias de archivo (§5.2).
- Integración con hoja de vida de equipos y con Mantenimientos.
