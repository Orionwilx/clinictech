# Módulo: Clientes (Client)

- **Ref. plan**: §5.4 de `project.md`
- **Estado**: ✅ implementado (solo empresa + login; contactos/áreas pendientes)
- **Depende de**: Usuarios/roles ✅

> Base del sistema: de Clientes cuelgan equipos, órdenes, mantenimientos y el panel cliente. "De menos a más": arrancamos con datos de empresa + login; contactos, áreas, adjuntos y recordatorios se añaden después.

## Modelo
`Client` → tabla `clients`. Cada cliente tiene una **cuenta de usuario vinculada** (rol `cliente`) para el panel cliente.

## Campos (`clients`)
| Campo | Tipo | Reglas | Notas |
|-------|------|--------|-------|
| name | string | required, max:255 | Nombre de la empresa |
| nit | string | required, unique | NIT / identificación |
| email | string | required, email | Correo (= login de la cuenta) |
| city | string | nullable | Ciudad |
| country | string | nullable | País |
| whatsapp | string | nullable | |
| phone | string | nullable | Celular |
| is_active | boolean | default true | Activar/desactivar |
| user_id | FK users | nullable, constrained | Cuenta de login vinculada |
| (soft deletes) | — | — | Baja recuperable |

## Login del cliente (cuenta vinculada)
- Al crear un `Client` se crea también un `User` con rol `cliente`, enlazado por `user_id`.
- Mapeo del formulario: `usuario` → `User.name` · `correo` → `User.email` (login) · `contraseña` → `User.password` (hasheada).
- Login por **email** (estándar Laravel). Login por username queda para la fase Panel Cliente (§5.7).
- Relación: `Client belongsTo User` · `User hasOne Client`.

## Relaciones (futuras)
- `hasMany(Equipment)` · `hasMany(WorkOrder)` · `hasMany(Contact)` · `hasMany(WorkArea)` — fases posteriores.

## Reglas de negocio
- `nit` único y requerido (identifica al cliente).
- Baja lógica recuperable por admin; al eliminar el Client, su cuenta `User` también se desactiva.
- Segregación: un usuario `cliente` solo ve su propio `Client` (Policy, §7 — fase Panel Cliente).

## Permisos (spatie)
- Añadir `clients` a `MODULES` en `RolePermissionSeeder` → `view/create/update/delete clients`.
- admin: todo · tecnico: `view clients` · cliente: solo su propio registro (Policy, después).

## Notas de UI
- Vistas en `resources/views/admin/clients/` siguiendo `DESIGN.md` (patrón de `admin/users`).
- Formulario con datos de empresa + credenciales de acceso (usuario, correo, contraseña).

## Semilla (git)
`git show HEAD:modules/clinics/CONTEXT.md`
