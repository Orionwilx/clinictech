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
- Antes de cerrar una tarea, ejecutar en orden:
  1. `./vendor/bin/pint` — formateo
  2. `php artisan test` — suite completa
  3. `php artisan route:list` — verifica que rutas, middlewares y controllers resuelvan sin error
  4. Verificar en navegador si se tocó: middleware, `bootstrap/app.php`, layouts o bindings
- **Middlewares de Spatie NO se auto-registran en Laravel 11.** Siempre declararlos en `bootstrap/app.php` → `$middleware->alias([...])`: `role`, `permission`, `role_or_permission`.
- **Fechas sin horas en el producto**: toda vista de admin/cliente/técnico muestra solo fechas (`Y-m-d`/`d/m/Y`), nunca `H:i`. Los timestamps completos quedan en BD para auditoría de los devs. (`diffForHumans` relativo en notificaciones sí se permite.)
- **Usuarios inactivos**: `EnsureUserIsActive` (global en el grupo web, `bootstrap/app.php`) expulsa a cualquier usuario con `is_active=false` a `/cuenta-inactiva` cerrando su sesión.

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
- ✅ **Clientes** (`Admin/ClientController` + `ClientService`): CRUD de empresa (name, **logo**, nit, email, city, country, whatsapp, phone) con **cuenta de acceso vinculada** (`Client belongsTo User` rol `cliente`; usuario→`User.name`, correo→`User.email`, login por email). **Credenciales visibles para admin**: copia cifrada reversible de la contraseña (`clients.access_password`, cast `encrypted`) mostrada en la ficha con Mostrar/Ocultar. Logo en disco `private` vía `Upload` (collection `logo`). Soft delete + restore; al eliminar se desactiva la cuenta. Spec en `docs/modules/clientes.md`, tests en `ClientManagementTest`.
- ℹ️ Autoeliminación de cuenta (`ProfileController::destroy`) usa `forceDelete` (borrado real); el soft delete es solo para bajas gestionadas por admin.
- ✅ **Equipos** (`Admin/EquipmentController`): CRUD + inventario (`equipment` pertenece a `Client`); ficha extendida por secciones; enums código-EN/UI-ES; `status` (`Equipment::STATUSES`): solo `active`/`inactive` — activo visible a todos, inactivo solo admin (`scopeVisibleToClients()`). **Catálogo maestro por categoría**: `EquipmentCategory` es la **plantilla completa** del equipo (riesgo, especialidades, características técnicas, subtareas, accesorios); en el form hay **cascada categoría→marca→modelo** (Alpine) y al elegir categoría se **prediligencia todo** como snapshot editable (cambios de plantilla NO tocan equipos existentes). La **marca** lleva `manufacturer`/`origin_country` y los autodiligencia al elegirla. `EquipmentModel` pertenece a `Brand` y a `EquipmentCategory`. Sin periodicidad ni observaciones técnicas/generales (las observaciones viven solo en las OT). **Hoja de vida en PDF** (ficha técnica + historial de OT) desde el show, admin y cliente. **Catálogos de opciones configurables** (`MaintenanceTask`/`Accessory`/`Specialty`, guardados como JSON de nombres): CRUD con pestañas (`Admin/EquipmentCatalogController`) + **alta rápida inline** desde forms (`<x-catalog-checkboxes>`). CRUDs admin: `Admin/EquipmentCategoryController`, `Admin/BrandController`, `Admin/EquipmentModelController` (permisos `equipment_categories`/`equipment_catalogs`/`brands`/`equipment_models`); semilla `EquipmentCatalogSeeder`. Soft delete (equipos eliminados visibles solo admin). **Hoja de vida** (`equipment/show`): identidad + resumen + historial cronológico de OT. Specs en `docs/modules/equipos.md` y `docs/modules/categorias-equipos.md`.
- ✅ **Áreas de trabajo** (`Area` + `Admin/AreaController`): subdivisiones internas del cliente (UCI, Urgencias…), `Area belongsTo Client`, únicas por cliente. Se gestionan **en línea** en la pestaña «Áreas» del hub del cliente (rutas anidadas `clients/{client}/areas`). El equipo enlaza a un área (`equipment.area_id`, selector dependiente del cliente); `location` se conserva como **sede/dirección de la instalación** (concepto distinto del área). Permiso `areas`. Tests en `AreaManagementTest`.
- ✅ **Adjuntos de documentos de cliente** (`ClientAttachment`): pestaña «Documentos» en el hub; subida a disco `private`; descarga autenticada; `Admin/ClientAttachmentController`. Permiso reutilizado `clients`.
- ⏳ Pendiente en Clientes: recordatorios (sin alcance definido). Login por username (fase Panel Cliente).
- ✅ **Técnicos** (`Admin/TechnicianController` + `TechnicianService`): ficha (name, document único, email, phone, specialty) con **cuenta vinculada** (`Technician belongsTo User` rol `tecnico`, login por email). Soft delete + restore. Spec en `docs/modules/tecnicos.md`.
- ✅ **Órdenes de trabajo** (`Admin/WorkOrderController` + `WorkOrderService`): OT que relaciona `Client` (req.), `Equipment` (opcional, debe pertenecer al cliente) y `Technician` (opcional). `code` autogenerado con **sigla del tipo** (`OT-000001_MP`/`_MC`/`_MR`, `WorkOrder::TYPE_ABBREVIATIONS`; al cambiar el tipo en edición se re-deriva la sigla); `type` (preventive/corrective/**review**)/`priority`/`status` con **código EN y etiquetas ES** (`WorkOrder::TYPES/PRIORITIES/STATUSES`); sellos automáticos `started_at/completed_at/closed_at` según estado. **Estados automáticos — sin selector manual en el form**: al crear, si hay `technician_id` → `assigned`, sino → `open`; al editar, si la OT está `open`/`assigned`, asignar/quitar técnico hace la transición correspondiente. **El admin puede enviar a revisión** desde la ficha (`POST work_orders/{wo}/submit-review`, nombre `work_orders.submit-review`, `WorkOrderController::submitReview`) cuando la OT está `assigned` o `in_progress` — fluye a `pending_review`, luego el admin aprueba → `closed`. **Enviar al cliente** (`visible_to_client=true`) es el único paso completamente manual. **Notifica al técnico** al crear/reasignar la OT desde admin (`WorkOrderService::create`/`update`, no solo `assign`). Soft delete + restore. **Centro de operación en el índice**: pestañas/bandejas (`?tab=action|active|all|trashed`, «Requieren tu acción» con badge de conteo vía `scopeAwaitingAdminAction`), **acción primaria contextual por fila** en 1 clic (`WorkOrder::primaryAdminAction()` → rutas `advance`/`regress`/`assign`) y **acciones masivas** (checkbox + barra flotante → ruta `batch`, `WorkOrderService::batchForAdmin`; el submit del board Alpine usa `$nextTick` para que el `action` viaje). **Nombre de PDF descriptivo** saneado sin acentos: `MP_OT000001_equipo_marca_serie.pdf` (`WorkOrder::pdfFileName()`/`sanitizeFileName()`); hoja de vida del equipo → `HV_equipo_marca_serie.pdf` (`Equipment::pdfFileName()`). Badge centralizado en `<x-work-order-status-badge>`. Spec en `docs/modules/ordenes-trabajo.md`, tests en `WorkOrderManagementTest`.
- ℹ️ **Mantenimiento = tipo de OT** (NO hay entidad separada): un mantenimiento es una `WorkOrder` con `type` **preventivo (MP) / correctivo (MC) / revisión (MR)**. Se crean/consultan desde el módulo de Órdenes y desde el hub del cliente. Tipos abiertos a ampliar (pendiente: hacerlos configurables por admin).
- ✅ **Panel «Datos del equipo» editable en la OT** (admin + técnico): al elegir un equipo se muestran/editan sus características técnicas, subtareas y accesorios ahí mismo (el diligenciamiento del técnico **prellena** `eq_*` y subtareas/accesorios desde la ficha del equipo cuando la OT aún no tiene valores propios); al guardar la OT **persiste en la ficha del equipo** (`WorkOrderService::syncEquipmentData`; campos `eq_*` validados por `InteractsWithOrderEquipmentRules`). Prioriza eficiencia al hacer muchas OT sin salir a editar el equipo. **Bloqueo de equipo/cliente**: si la OT ya está `in_progress` o más avanzada, el selector de equipo y cliente se vuelven read-only en el form de edición (hidden inputs preservan los valores); solo es editable en `draft`/`open`/`assigned`.
- ✅ **Flujo de captura de OT estandarizado**: dashboard con **filas de cliente clicables** (icono/flecha, toda la fila abre el hub); **un solo botón «Nueva OT»** en todo el producto (sin atajos preventiva/correctiva — el tipo se elige en el form), con botón por equipo en el hub que precarga `client_id`+`equipment_id`; **toggle rápido activo/inactivo** por equipo (`<x-equipment-status-toggle>`, `PATCH equipment.toggle-active`) en el índice y el hub.
- ✅ **Hub del cliente**: la ficha `clients/show` es un tablero con pestañas (Datos / Áreas / Equipos / Órdenes / OT pendientes / Documentos / PEC) que lista lo del cliente y ofrece «+ Nuevo» con `?client_id` precargado (editable). En Órdenes hay accesos «+ OT preventiva» / «+ OT correctiva» que precargan `type`. «OT pendientes» lista equipos con OT activas (`WorkOrder::ACTIVE_STATUSES`). El cliente tiene **logo** (disco `private`, vía `Upload`) mostrado en la cabecera.
- ✅ **Sistema de archivos privado + modelo Upload polimórfico**: **todos** los archivos del negocio viven en disco `private` (`storage/app/private`). Ninguno es accesible por URL directa. Registro central: `Upload` (tabla `uploads`, `morphs('uploadable')`) con `collection` (`logo`/`photo`/`document`/`pec`/`signature`/`company_signature`), `disk`, `path`, `original_name`, `mime_type`, `size`, `label`, `uploaded_by`. Trait `HasUploads` en `Client`, `User` y `WorkOrder` (y cualquier modelo futuro). Punto de acceso único: `MediaController::serve(Upload $upload)` bajo middleware `auth`, con control de acceso por rol y relación. Al migrar a S3 solo cambia el driver en `.env`; el campo `disk` por registro permite coexistencia. `Upload::purge()` borra físico + registro en un paso. `Upload::url()` → ruta `media.serve`; `Upload::absolutePath()` → para PDFs (dompdf). Logos de cliente: `Client::logo()` (MorphOne, collection `logo`), `Client::logoUrl()`, `Client::logoBase64()`. Documentos de cliente: `Client::documents()` (MorphMany, collection `document`), gestionados por `Admin/ClientAttachmentController`. Fotos de OT: `WorkOrder::photos()` → `uploadMany('photo')`, `ImageService` compresión GD ≤1600px/JPEG q72/EXIF, con **descripción por foto** editable (`Upload.label`, endpoints `work_orders.photos.update`). **Firma del técnico**: collection `signature` sobre `User`; `User::signature()` (MorphOne) + `User::signatureBase64()` — el técnico la sube UNA vez en su perfil (`profile.signature.update/destroy`) y se reutiliza en todas sus OT. **Firma de empresa** (administrador): collection `company_signature` sobre el `User` admin que la sube; recuperada globalmente por `App\Support\CompanySignature::current/base64/absolutePath()`; administrada en `admin/company-signature` (`Admin/CompanySignatureController`). Ambas firmas se sirven SIEMPRE como **base64 inline** (data-URI): NO pasan por MediaController — funcionan en pantalla y en dompdf sin cambios. Si el técnico no tiene firma y su OT está en `assigned`/`in_progress`, se muestra un aviso no bloqueante con enlace al perfil. **Capacitaciones PEC** del cliente: `Client::pec()` (collection `pec`, solo PDF), `Admin/ClientPecController` (tab «PEC» del hub) + `Client/PecController` (panel del cliente). Permiso de acceso: admin=todo; técnico=uploads de OT propias; cliente=uploads de su empresa (solo OT `visible_to_client`).
- ✅ **Evidencias fotográficas en OT** (`ImageService` + `Upload`, collection `photo`): **el técnico** las sube por AJAX inmediato al diligenciar (`Technician/WorkOrderPhotoController`); **el admin también** anexa/quita fotos desde `admin/work_orders/show` (`update work_orders`, cualquier estado) — el admin suele llenar las OT del técnico y subir sus fotos, por eso `store`/`update` de OT redirigen a la ficha. Galería en show admin/cliente y en el PDF. El diligenciamiento del técnico tiene **autoguardado de borrador** (cada 20 s, JSON).
- ⏳ Pendiente en Técnicos: capacitaciones (`Training`). Pendiente en OT: recordatorios (para preventivas), tipos configurables por admin.
- ✅ **Panel cliente** (`Client/DashboardController`, `Client/EquipmentController`, `Client/WorkOrderController`, `Client/TechnicianController`, `Client/PecController`): acceso segregado por `client_id`; rutas bajo `/client` con middleware `role:cliente` + `EnsureClientProfile`; redirección post-login al `client.dashboard`; sidebar condicional por rol. Vistas: dashboard (métricas), mis equipos (hoja de vida + PDF), mis OT (detalle + PDF), mis técnicos, solicitar mantenimiento, **capacitaciones (PEC)** (listado + descarga de PDFs cargados por admin). **Regla de visibilidad**: el cliente SOLO ve OT aprobadas y enviadas por el admin (`scopeSentToClient`) — en hoja de vida, PDFs y métricas jamás aparecen OT en proceso, sin aprobar o eliminadas; sus propias solicitudes (draft/cancelled) solo en su listado (`scopeListableForClient`). Tests en `ClientVisibilityTest`. ⏳ Pendiente: hoja de vida de técnicos, capacitaciones.
- ✅ **Panel técnico** (`Technician/DashboardController`, `Technician/WorkOrderController`): rutas bajo `/technician` con middleware `role:tecnico` + `EnsureTechnicianProfile`; redirección post-login a `technician.dashboard`; sidebar propio. Vistas: dashboard (OTs pendientes + métricas), mis OT (índice + diligenciamiento de formulario + envío a revisión). ⏳ Pendiente: hoja de vida propia.
- ✅ **Flujo colaborativo de OT**: estado `draft` (solicitud cliente) + `pending_review` (técnico completó, esperando admin) + `visible_to_client` (bool) + `requested_by_client` (bool) + `rejection_reason`. Máquina de estados: draft→open/assigned→in_progress→pending_review→closed→visible_to_client. Admin puede aprobar/rechazar solicitud, aprobar/devolver trabajo del técnico y enviar al cliente. Notificaciones in-app (tabla `notifications`, canal database) en cada transición. ⏳ Deuda técnica: notificaciones por email (Laravel Mail).
- ⏳ Siguiente: despliegue AWS.

## Dominio (orden de implementación por fases — detalle en `project.md`)
1. ✅ **Usuarios/roles y permisos** — admin, técnico, cliente. Base de seguridad y segregación por cliente.
2. ✅ **Clientes** (`Client`) — empresa + cuenta de acceso vinculada + adjuntos de documentos. Pendiente: recordatorios.
3. ✅ **Equipos e inventario** (`Equipment`) — pertenece a un cliente; estados (código EN/UI ES); equipos eliminados visibles solo a admin. Hoja de vida (historial de OT) implementada. **Catálogo maestro**: categoría (plantilla completa prediligenciada como snapshot) → marca → modelo; catálogos de subtareas/accesorios/especialidades configurables por admin.
4. ✅ **Órdenes de trabajo** (`WorkOrder`) — ciclo: creación → asignación a técnico → estados → diagnóstico/actividades → cierre. Relaciona cliente y equipo. Evidencias fotográficas implementadas vía `Upload`. Pendiente: recordatorios.
5. ✅ **Técnicos** (`Technician`) — ficha + cuenta vinculada. Pendiente: capacitaciones (`Training`).
6. ✅ **Mantenimientos** — NO es entidad propia: es una OT de `type` preventivo/correctivo (ver Fase 4). Pendiente: recordatorios/notificaciones para preventivas.
7. **Reportes** — por cliente, equipo, OT, técnico, tipo de OT (mantenimiento); filtros y exportaciones.
8. ✅ **Panel cliente** — acceso segregado por `client_id`. Dashboard, equipos, OT (solo `visible_to_client=true` + solicitudes propias), técnicos, solicitar mantenimiento. ⏳ Pendiente: hoja de vida técnicos, capacitaciones.
   ✅ **Panel técnico** — dashboard + mis OT + diligenciamiento de formulario + envío a revisión. ⏳ Pendiente: hoja de vida propia.
   ✅ **Flujo colaborativo** — solicitud cliente → aprobación admin → asignación técnico → diligenciamiento → revisión admin → envío al cliente. Notificaciones in-app en cada paso.

> Nombres de modelo tentativos en inglés (convención del código). Confirma el mapeo negocio↔modelo en `project.md` antes de crear cada recurso.
