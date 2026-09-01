# Módulo: Técnicos (Technician)

- **Ref. plan**: §5.5 de `project.md`
- **Estado**: ✅ implementado (ficha + cuenta vinculada; capacitaciones pendientes)
- **Depende de**: Usuarios/roles ✅

> Ficha de técnicos que atienden las órdenes de trabajo. Cada técnico tiene una cuenta de acceso vinculada (rol `tecnico`). Las capacitaciones (§5.5) son un módulo posterior (`Training`).

## Modelo
`Technician` → tabla `technicians`. Cuenta de acceso vinculada (rol `tecnico`) vía `user_id`.

## Campos (`technicians`)
| Campo | Tipo | Reglas | Notas |
|-------|------|--------|-------|
| name | string | required, max:255 | Nombre completo (= nombre de la cuenta) |
| document | string | required, unique | Documento / cédula |
| email | string | required, email | Correo (= login de la cuenta) |
| phone | string | nullable | Celular |
| specialty | string | nullable | Especialidad |
| is_active | boolean | default true | Activar/desactivar |
| user_id | FK users | nullable, constrained | Cuenta de login vinculada |
| (soft deletes) | — | — | Baja recuperable |

## Login del técnico (cuenta vinculada)
- Al crear un `Technician` se crea un `User` rol `tecnico`, enlazado por `user_id`.
- Mapeo: `name` → `User.name` · `email` → `User.email` (login) · `password` → `User.password`.
- Relación: `Technician belongsTo User` · `User hasOne Technician`.

## Relaciones
- `belongsTo(User)` — cuenta de acceso.
- (futuras) `hasMany(WorkOrder)` — órdenes asignadas · `hasMany(Training)` — capacitaciones.

## Reglas de negocio
- `document` y `email` únicos.
- Baja lógica recuperable; al eliminar se desactiva la cuenta (patrón de Clientes).

## Permisos (spatie)
- Módulo `technicians` ya en `RolePermissionSeeder` → `view/create/update/delete technicians`.
- admin: todo. (tecnico/cliente sin acceso de gestión.)

## Notas de UI
- Vistas en `resources/views/admin/technicians/` siguiendo `DESIGN.md`.
- Formulario con ficha + credenciales de acceso. Enlace en sidebar con `@can('view technicians')`.
