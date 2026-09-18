# Módulo: Clientes (Client)

- **Ref. plan**: §5.4 de `project.md`
- **Estado**: ✅ implementado (empresa + login + hub + áreas + adjuntos de documentos); ⏳ pendiente: recordatorios (sin alcance definido)
- **Depende de**: Usuarios/roles ✅

> Base del sistema: de Clientes cuelgan equipos, órdenes y el panel cliente. "De menos a más": empresa + login + hub con áreas ya implementados; contactos, adjuntos y recordatorios se añaden después.

## Hub del cliente
La ficha `clients/show` es un **tablero con pestañas** (Datos / Áreas / Equipos / Órdenes / OT pendientes / Documentos / PEC) que lista lo del cliente. La pestaña activa se puede fijar con `?tab=areas`. Desde Equipos/Órdenes se crea con `?client_id` precargado. Si el cliente tiene logo, se muestra en la cabecera del hub.

### Pestaña «OT pendientes»
Lista los **equipos del cliente que tienen OT activas** (estados `open`/`assigned`/`in_progress`, ver `WorkOrder::ACTIVE_STATUSES`). Columnas: equipo, área, marca/modelo, N. serie, estado, obs. técnicas, OT pendientes (nº + enlaces a cada OT) y acceso a la hoja de vida. El controlador arma `pendingEquipment` con `whereHas`/`withCount` sobre las OT activas.

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

> **Logo**: no hay columna `logo_path` en `clients`. El logo vive en la tabla `uploads` (collection `logo`, disk `private`). Se accede via `Client::logo()` (MorphOne), `Client::logoUrl()` → ruta `media.serve`, `Client::logoBase64()` → para PDFs.

## Login del cliente (cuenta vinculada)
- Al crear un `Client` se crea también un `User` con rol `cliente`, enlazado por `user_id`.
- Mapeo del formulario: `usuario` → `User.name` · `correo` → `User.email` (login) · `contraseña` → `User.password` (hasheada).
- **Credenciales visibles para el admin**: `clients.access_password` guarda una **copia cifrada reversible** (cast `encrypted`, Laravel Crypt) de la contraseña; se muestra en la pestaña «Datos» de la ficha con botón Mostrar/Ocultar (`@can('update clients')`). Se sincroniza al crear y al cambiar la contraseña desde el form (`ClientService`). El login sigue validando contra el hash de `users.password`.
- **Cuenta inactiva**: un usuario con `is_active = false` que intente entrar (o cuya sesión esté abierta) es expulsado por el middleware global `EnsureUserIsActive` a la pantalla `/cuenta-inactiva` («contacta con el administrador»).
- Login por **email** (estándar Laravel). Login por username queda para la fase Panel Cliente (§5.7).
- Relación: `Client belongsTo User` · `User hasOne Client`.

## Relaciones
- `hasMany(Equipment)` ✅ · `hasMany(WorkOrder)` ✅ · `hasMany(Area)` ✅
- `uploadMany('document')` ✅ via trait `HasUploads` — archivos del cliente (RUT, cámara de comercio, contratos, etc.)

## Adjuntos de documentos ✅
- Tabla central `uploads` (polimórfica, collection `document`, disk `private`). No hay tabla `client_attachments` ni modelo `ClientAttachment`.
- Controlador: `Admin/ClientAttachmentController` (store / download / destroy).
- Rutas: `POST clients/{client}/attachments`, `GET …/{attachment}/download`, `DELETE …/{attachment}` (donde `{attachment}` es un `Upload`).
- UI: pestaña «Documentos» en el hub del cliente; formulario de subida + tabla con descarga/eliminar.
- Permiso: `update clients` para subir/eliminar; `view clients` para descargar.

## Capacitaciones — PEC (Programa de Educación Continua) ✅
- **PDFs** que el admin sube para el cliente y este consulta desde su panel. Reutiliza `uploads` (collection `pec`, disk `private`). Relación `Client::pec()` (`uploadMany('pec')`).
- Admin: `Admin/ClientPecController` (store / download / destroy), pestaña «PEC» del hub. Solo acepta `mimes:pdf` (máx. 20 MB); `Upload.label` guarda el nombre/tema.
- Rutas admin: `POST clients/{client}/pec`, `GET …/{upload}/download`, `DELETE …/{upload}`.
- Cliente: `Client/PecController` (index + download), página «Capacitaciones (PEC)» en el panel + enlace en el sidebar; solo ve el PEC de su propia empresa.
- Permiso: reutiliza `clients` (admin gestiona; cliente accede solo al suyo por segregación de `client_id`).

## Pendientes

### Recordatorios
Sin alcance definido. No implementar hasta acordar con el cliente.

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
