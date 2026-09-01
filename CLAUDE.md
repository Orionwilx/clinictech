# ClinicTech Manager — Sistema de Gestión de Equipos (INGSOLN)

Plataforma web Laravel para gestión empresarial: clientes, equipos e inventario, órdenes de trabajo, técnicos, capacitaciones, mantenimientos y reportes. Con panel administrativo y panel de cliente (acceso segregado por permisos).

> Esta es la **única fuente de verdad operativa** para el agente. Mantenla corta y veraz. No describas inventario que cambia (nº de migraciones, listas de archivos) — describe reglas y dónde vive cada cosa.
>
> 📄 **Alcance, fases y flujos de negocio → [`project.md`](./project.md)** (documento de planificación; léelo bajo demanda al planificar un módulo, NO en cada sesión).
> 🎨 **Sistema de diseño e identidad visual → [`DESIGN.md`](./DESIGN.md)** (paleta `brand-*`, componentes y estándar de vistas; consúltalo al construir cualquier UI).
> 🧩 **Spec de cada módulo → [`docs/modules/`](./docs/modules/)** (campos, enums, relaciones y permisos por módulo; entrada de `/nuevo-recurso`. Se crea/finaliza justo antes de construir el módulo, reconciliado con `project.md`).

## ⚠️ Política de documentación (OBLIGATORIA)

**Todo cambio en el código debe actualizar la documentación afectada EN EL MISMO commit.** No se considera "terminada" una tarea si la doc quedó desincronizada. Antes de cerrar cualquier cambio, revisa esta tabla y actualiza lo que corresponda:

| Si cambiaste... | Actualiza |
|-----------------|-----------|
| Arquitectura, convenciones, comandos, stack | `CLAUDE.md` |
| Un módulo (campos, enums, relaciones, reglas, permisos) | su spec en `docs/modules/{modulo}.md` (y márcalo `✅ implementado` al acabar) |
| Colores, componentes UI, estándar de vistas | `DESIGN.md` |
| Alcance de negocio, fases o flujos | `project.md` |
| Estado de avance de un módulo del dominio | sección "Estado actual" + "Dominio" de `CLAUDE.md` |
| Roles o permisos | sección "Autorización" de `CLAUDE.md` + `RolePermissionSeeder` |

Regla práctica: si tras un cambio alguien leyendo la doc entendería algo **falso o desactualizado**, arréglalo antes de dar el trabajo por hecho.

## Stack
- Laravel 13 · PHP 8.3+ (local: 8.4)
- SQLite (`database/database.sqlite`) — configurable a MySQL/Postgres en `.env`
- Auth: Laravel Breeze (Blade + Alpine.js)
- Frontend: Blade + Alpine.js + Tailwind 3, build con Vite
- Tests: PHPUnit · Lint/format: Laravel Pint

## Arquitectura: Laravel estándar
Nada de módulos custom. Todo vive donde Laravel lo espera:
- Modelos → `app/Models`
- Controladores → `app/Http/Controllers` (área admin en `app/Http/Controllers/Admin`)
- Form Requests para validación → `app/Http/Requests`
- Lógica de negocio no trivial → `app/Services` (crear cuando haga falta, no antes)
- Rutas web → `routes/web.php` · auth → `routes/auth.php`
- Migraciones → `database/migrations`
- Vistas Blade → `resources/views`
- Namespace único: `App\` (PSR-4). No inventar namespaces nuevos.

## Mapa de navegación
Para el recurso `Clinic` (singular PascalCase), los archivos SIEMPRE están aquí. Deduce la ruta del nombre — no explores:

| Tipo | Ruta | Ejemplo |
|------|------|---------|
| Modelo | `app/Models/{Recurso}.php` | `app/Models/Clinic.php` |
| Controlador (admin) | `app/Http/Controllers/Admin/{Recurso}Controller.php` | `app/Http/Controllers/Admin/ClinicController.php` |
| Form Request | `app/Http/Requests/{Recurso}/{Store\|Update}{Recurso}Request.php` | `app/Http/Requests/Clinic/StoreClinicRequest.php` |
| Servicio | `app/Services/{Recurso}Service.php` | `app/Services/ClinicService.php` |
| Migración | `database/migrations/*_create_{plural_snake}_table.php` | `..._create_clinics_table.php` |
| Factory | `database/factories/{Recurso}Factory.php` | `database/factories/ClinicFactory.php` |
| Seeder | `database/seeders/{Recurso}Seeder.php` | `database/seeders/ClinicSeeder.php` |
| Vistas | `resources/views/admin/{plural_snake}/` | `resources/views/admin/clinics/index.blade.php` |
| Rutas | `routes/web.php` (grupo `admin`) | `Route::resource('clinics', ...)` |
| Test | `tests/Feature/Admin/{Recurso}Test.php` | `tests/Feature/Admin/ClinicTest.php` |

## Convención de nombres (para que Glob/Grep acierten a la 1ª)
- **Modelo**: singular, PascalCase → `Clinic`, `ServiceOrder`.
- **Tabla / carpeta de vistas / segmento de ruta**: plural, snake_case → `clinics`, `service_orders`.
- **Controlador**: `{Recurso}Controller` en `Admin/`.
- **Request**: `Store{Recurso}Request` / `Update{Recurso}Request`.
- **Servicio**: `{Recurso}Service`.
- Sin abreviaturas ni sinónimos (`ClinicController`, nunca `ClinicsController`, `clinic_ctrl`, `ClinicMgr`).

## Convenciones de código
- Controladores de recurso: `php artisan make:controller Admin/XController --resource --model=X`
- Modelo + migración juntos: `php artisan make:model X -m`
- Slim controllers, fat models. Lógica de negocio no trivial → `app/Services`.
- Validación siempre en Form Requests, nunca inline en el controlador.
- Archivos cortos (guía: ~150 líneas). Una responsabilidad por archivo.
- `User` usa `SoftDeletes` — usa `withTrashed()`/`restore()` cuando aplique; borra con `delete()` (soft), no `forceDelete()` salvo intención explícita.
- Para crear un CRUD nuevo usa el comando `/nuevo-recurso {Recurso}` (ver `.claude/commands/nuevo-recurso.md`).
- Antes de cerrar una tarea: `./vendor/bin/pint` y `php artisan test`.

## Estrategia de migraciones (desarrollo temprano — "de menos a más")
El esquema evoluciona constantemente; se añaden campos de forma incremental. Para NO acumular migraciones basura:
- **Mientras NO haya datos de producción**: EDITA la migración original de la tabla y corre `php artisan migrate:fresh --seed`. **NO** crees una migración `add_x_to_y` por cada campo nuevo.
- **Una migración por tabla** como norma (la de `create_{tabla}`). Las migraciones `alter` separadas se reservan para cuando haya datos que preservar (producción).
- **Purga pendiente (posterior)**: antes de producción, consolidar el historial. Candidatas actuales a fusionar dentro de `create_users_table`: `add_soft_deletes_to_users_table`, `add_is_active_to_users_table`.
- Excepción: las tablas de framework/paquetes (cache, jobs, `create_permission_tables` de spatie) no se tocan.

## Comandos
```bash
php artisan serve            # servidor dev (o: composer dev)
php artisan migrate:fresh --seed  # recrear esquema desde cero (dev; borra datos)
php artisan migrate          # aplicar migraciones
php artisan make:model X -m  # modelo + migración
php artisan test             # tests
./vendor/bin/pint            # formatear
npm run dev                  # assets en watch
```

## Autorización (spatie/laravel-permission)
- Roles base: `admin`, `tecnico`, `cliente` (sembrados en `RolePermissionSeeder`).
- Permisos con formato `"{verbo} {modulo}"` (ej. `view users`, `create clients`). Verbos: view/create/update/delete.
- `User` usa el trait `HasRoles`. Protege acciones con `$this->authorize('view users')` en controllers y `@can('view users')` en Blade.
- Al añadir un módulo nuevo: agrégalo a `MODULES` en `RolePermissionSeeder` y re-siembra.
- Admin de arranque: `admin@ingsoln.com` (seeder).

## Estado actual
- ✅ Base Laravel + Breeze (auth completo).
- ✅ **Usuarios/roles**: spatie instalado, roles+permisos sembrados, CRUD admin completo (`Admin/UserController`) con activar/desactivar (`is_active`) y baja/recuperación (soft delete + restore). Tests en `tests/Feature/Admin/UserManagementTest.php`.
- ✅ **Clientes** (`Admin/ClientController` + `ClientService`): CRUD de empresa (name, nit, email, city, country, whatsapp, phone) con **cuenta de acceso vinculada** (`Client belongsTo User` rol `cliente`; usuario→`User.name`, correo→`User.email`, login por email). Soft delete + restore; al eliminar se desactiva la cuenta. Spec en `docs/modules/clientes.md`, tests en `ClientManagementTest`.
- ℹ️ Autoeliminación de cuenta (`ProfileController::destroy`) usa `forceDelete` (borrado real); el soft delete es solo para bajas gestionadas por admin.
- ✅ **Equipos** (`Admin/EquipmentController`): CRUD + inventario (`equipment` pertenece a `Client`); campos type/brand/model/serial(único)/compra/garantía/ubicación/notas; `status` con **valores en inglés en código y etiquetas en español en UI** (`Equipment::STATUSES`). Soft delete (equipos eliminados visibles solo admin). Spec en `docs/modules/equipos.md`.
- ⏳ Pendiente en Clientes: contactos, áreas de trabajo, adjuntos, recordatorios (§5.4). Login por username (fase Panel Cliente).
- ✅ **Técnicos** (`Admin/TechnicianController` + `TechnicianService`): ficha (name, document único, email, phone, specialty) con **cuenta vinculada** (`Technician belongsTo User` rol `tecnico`, login por email). Soft delete + restore. Spec en `docs/modules/tecnicos.md`.
- ⏳ Pendiente en Equipos: hoja de vida/historial (requiere OT y Mantenimientos). Pendiente en Técnicos: capacitaciones (`Training`).
- ⏳ Siguiente: Órdenes de trabajo → Mantenimientos → Reportes → Panel cliente + despliegue AWS.

## Dominio (orden de implementación por fases — detalle en `project.md`)
1. ✅ **Usuarios/roles y permisos** — admin, técnico, cliente. Base de seguridad y segregación por cliente.
2. ✅ **Clientes** (`Client`) — empresa + cuenta de acceso vinculada. Pendiente: contactos, áreas, adjuntos, recordatorios.
3. ✅ **Equipos e inventario** (`Equipment`) — pertenece a un cliente; estados (código EN/UI ES); equipos eliminados visibles solo a admin. Pendiente: hoja de vida/historial.
4. **Órdenes de trabajo** (`WorkOrder`) — ciclo completo: creación → asignación a técnico → estados → diagnóstico/evidencias → cierre. Relaciona cliente y equipo.
5. ✅ **Técnicos** (`Technician`) — ficha + cuenta vinculada. Pendiente: capacitaciones (`Training`).
6. **Mantenimientos** (`Maintenance`) — preventivo/correctivo; programación; recordatorios.
7. **Reportes** — por cliente, equipo, OT, técnico, mantenimiento; filtros y exportaciones.
8. **Panel cliente** — acceso segregado a su propia información según permisos.

> Nombres de modelo tentativos en inglés (convención del código). Confirma el mapeo negocio↔modelo en `project.md` antes de crear cada recurso.
