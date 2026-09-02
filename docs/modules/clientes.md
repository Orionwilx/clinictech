# Módulo: Clientes (Client)

- **Ref. plan**: §5.4 de `project.md`
- **Estado**: ✅ implementado (empresa + login + hub + áreas; contactos/adjuntos/recordatorios pendientes)
- **Depende de**: Usuarios/roles ✅

> Base del sistema: de Clientes cuelgan equipos, órdenes y el panel cliente. "De menos a más": empresa + login + hub con áreas ya implementados; contactos, adjuntos y recordatorios se añaden después.

## Hub del cliente
La ficha `clients/show` es un **tablero con pestañas** (Datos / Áreas / Equipos / Órdenes) que lista lo del cliente. La pestaña activa se puede fijar con `?tab=areas`. Desde Equipos/Órdenes se crea con `?client_id` precargado.

## Áreas de trabajo (`Area`)
Subdivisiones internas del cliente (UCI, Urgencias, Laboratorio…). `Area belongsTo Client`, `unique(client_id, name)`. Campos: `name`, `description?`, `is_active`. Se gestionan **en línea** en la pestaña «Áreas» del hub (rutas `clients/{client}/areas` store, `areas/{area}` update/destroy). Permiso `areas` (admin total; técnico/cliente `view`). El equipo se asigna a un área (`equipment.area_id`); distinto de `location` (sede/dirección de la instalación).

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
