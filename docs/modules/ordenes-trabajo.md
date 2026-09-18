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
| code | string | unique, autogenerado | Nº de OT con sigla de tipo `OT-000001_MP` (lo asigna `WorkOrderService`; se re-deriva la sigla al cambiar el tipo) |
| client_id | FK clients | required, exists | Cliente dueño de la OT |
| equipment_id | FK equipment | nullable, exists, debe pertenecer al cliente | Equipo intervenido |
| technician_id | FK technicians | nullable, exists | Técnico asignado |
| title | string | required, max:255 | Asunto/motivo |
| description | text | nullable | Descripción de la solicitud/falla |
| type | string | required, in:TYPES | Preventivo/Correctivo/Revisión (código EN/UI ES) |
| priority | string | required, in:PRIORITIES | Baja/Media/Alta (código EN/UI ES) |
| status | string | required, in:STATUSES | Estado (ver abajo) |
| diagnosis | text | nullable | Diagnóstico técnico |
| work_performed | text | nullable | Actividades realizadas / solución |
| maintenance_tasks | json (array) | nullable, in:Equipment::MAINTENANCE_TASKS | Subtareas ejecutadas en esta OT |
| accessories_checked | json (array) | nullable, in:Equipment::ACCESSORIES | Accesorios revisados en esta OT |
| scheduled_at | datetime | nullable | Fecha programada |
| started_at | datetime | nullable | Se sella al pasar a «En proceso» |
| completed_at | datetime | nullable | Se sella al «Completar» / «En revisión» |
| closed_at | datetime | nullable | Se sella al «Cerrar» |
| requested_by_client | bool | default false | La OT nació de una solicitud del cliente (draft) |
| visible_to_client | bool | default false | El admin ya la envió al panel del cliente |
| rejection_reason | text | nullable | Motivo de rechazo/devolución (cliente o técnico) |
| (soft deletes) | — | — | Baja recuperable, visible solo admin |

## Estados (código EN / UI ES) — `WorkOrder::STATUSES`
- `draft` → «Borrador» (solicitud del cliente pendiente de aprobación)
- `open` → «Abierta»
- `assigned` → «Asignada»
- `in_progress` → «En proceso»
- `pending_review` → «En revisión» (técnico completó, espera al admin)
- `completed` → «Completada»
- `closed` → «Cerrada»
- `cancelled` → «Cancelada»

Máquina de estados del flujo colaborativo: `draft → open/assigned → in_progress → pending_review → closed → (visible_to_client)`.

**Los estados son automáticos — no hay selector manual en el formulario admin:**
- Al **crear** una OT desde el admin: si incluye `technician_id` → `assigned`; si no → `open`.
- Al **editar** una OT en estado `open`/`assigned`: asignar técnico → `assigned`; quitar técnico → `open`. Editar contenido sin tocar `technician_id` no cambia el estado.
- `draft`: solo lo crea `WorkOrderService::createClientRequest` (solicitud del cliente). No lo toca el admin en el form.
- `in_progress`: el técnico empieza a diligenciar (transición automática en `Technician\WorkOrderController::update`).
- `pending_review`: el técnico envía a revisión (`submitForReview`). **El admin también puede enviarlo a revisión** desde la ficha (ruta `POST work_orders/{work_order}/submit-review`, nombre `work_orders.submit-review`, acción `WorkOrderController::submitReview`) cuando la OT está `assigned` o `in_progress` — el admin suele llenar la OT del técnico.
- `closed`: el admin aprueba el trabajo (`approveWork`).
- `visible_to_client = true`: el **único paso manual** del flujo — el admin elige cuándo enviarlo al cliente (`sendToClient`). No es un estado independiente sino una bandera sobre `closed`.

## Tipos (`WorkOrder::TYPES`)
- `preventive` → «Preventivo» (default) · `corrective` → «Correctivo» · `review` → «Revisión»
- Sigla por tipo (`WorkOrder::TYPE_ABBREVIATIONS`): `MP` (preventivo) · `MC` (correctivo) · `MR` (revisión). Se usa en el `code` y en el nombre del PDF.
- Representan la naturaleza del trabajo. **Diseñado para ampliarse**; pendiente hacerlos configurables por el admin.

## Prioridades (`WorkOrder::PRIORITIES`)
- `low` → «Baja» · `medium` → «Media» (default) · `high` → «Alta»

## Relaciones
- `belongsTo(Client)` — cliente de la OT.
- `belongsTo(Equipment)` — equipo intervenido (nullable).
- `belongsTo(Technician)` — técnico asignado (nullable).
- Inversas: `Client hasMany WorkOrder` · `Equipment hasMany WorkOrder` · `Technician hasMany WorkOrder`.

## Reglas de negocio
- `code` autogenerado y único (`OT-` + consecutivo de 6 dígitos + `_` + sigla del tipo, p. ej. `OT-000001_MP`) en `WorkOrderService::create`. Al cambiar el tipo en la edición, `update` re-deriva la sigla conservando el número.
- **Nombre del PDF** descriptivo y saneado (sin acentos): `MP_OT000001_equipo_marca_serie.pdf` (`WorkOrder::pdfFileName()` + `sanitizeFileName()`). La hoja de vida del equipo usa `HV_equipo_marca_serie.pdf` (`Equipment::pdfFileName()`).
- Al crear/reasignar una OT con técnico desde el admin, `WorkOrderService::create`/`update` **notifican al técnico** (además de `assignTechnician`/`approveClientRequest`).
- `equipment_id`, si se indica, **debe pertenecer** al `client_id` seleccionado (validado en el Request).
- Sellos de tiempo automáticos según el estado (en `WorkOrderService`):
  - `in_progress` → `started_at` · `completed` → `completed_at` · `closed` → `closed_at` (solo si estaban vacíos).
- Baja lógica recuperable; OT eliminadas listadas aparte para admin (patrón de Equipos).

## Permisos (spatie)
- Módulo `work_orders` ya en `RolePermissionSeeder` → `view/create/update/delete work_orders`.
- admin: todo · tecnico: `view`, `update` (opera sus OT) · cliente: `view` (su propia OT, Policy futura).

## Notas de UI
- **Layout horizontal consistente**: la cabecera de identidad de una OT (Nº de orden, Asunto, Cliente, Equipo, Técnico, Tipo, Prioridad, Estado, Fecha programada) se presenta en **cuadrícula horizontal** (`grid grid-cols-2 sm:grid-cols-3 gap-3`) en los tres roles: admin (formulario editable, `lg:grid-cols-3`), técnico (tarjetas solo lectura en la cabecera, formulario de diligenciamiento debajo) y cliente (tarjetas solo lectura). Los textos largos (Descripción, Diagnóstico, Actividades, Observaciones) van a ancho completo debajo de la cuadrícula. Admin y técnico ven inputs; el cliente ve solo tarjetas `rounded-lg bg-gray-50 border border-gray-100 p-3` con `dt`/`dd`.
- Vistas en `resources/views/admin/work_orders/` siguiendo `DESIGN.md`.
- Selectores de cliente, equipo y técnico; selectores de tipo/prioridad/estado con etiquetas en español.
- **Lista dependiente cliente→equipo** (Alpine): al elegir cliente se filtran solo sus equipos; al cambiar de cliente se limpia el equipo. Además validado en servidor (el equipo debe pertenecer al cliente).
- **Un solo botón «Nueva OT»** (no hay atajos separados preventiva/correctiva): el tipo se elige dentro del formulario. Desde la pestaña «Equipos» del hub del cliente cada equipo tiene un botón visible «+ Nueva OT» que precarga `client_id`+`equipment_id`; el hub y la ficha del equipo enlazan al mismo `create` con esos parámetros. El técnico/admin solo rellena las novedades (el panel «Datos del equipo» ya viene precargado).
- **Panel «Datos del equipo» editable en la OT** (admin form + diligenciamiento del técnico): al seleccionar un equipo se muestra un **resumen de identificación** (marca, modelo, categoría, serial, área, ubicación, registro INVIMA — solo lectura) y un panel con sus **características técnicas** (voltaje, potencia, presión, clase de riesgo…), **subtareas** y **accesorios**, todo editable ahí mismo. El controlador embebe estos datos (relaciones marca/modelo/categoría/área incluidas) para el prellenado; al abrir el form con `?equipment_id=` (p. ej. desde «+ Nueva OT» del hub) se **prediligencia automáticamente** vía `$nextTick` (se conserva `preEquipmentId` para que el `<select>` con `x-for` no pierda el valor inicial). Los checkboxes salen del catálogo completo + los propios del equipo; se puede **agregar** ítems de texto libre. Al guardar la OT, **lo editado persiste en la ficha del equipo** (`WorkOrderService::syncEquipmentData`): características → columnas del equipo; lo marcado → `equipment.maintenance_tasks`/`accessories` y también queda registrado en la OT (`maintenance_tasks`/`accessories_checked`) para el PDF/historial. Los campos del equipo viajan con prefijo `eq_*` (validados por el trait `InteractsWithOrderEquipmentRules`, separados del payload de la OT con `splitEquipmentData`). Prioriza no salir de la OT al hacer muchas. El técnico ya tiene permiso `update equipment`; el «+ Agregar» añade a la ficha del equipo (no toca el catálogo global, que requiere permiso admin).
- **Filtros en el índice**: búsqueda por Nº/asunto (`search`, LIKE sobre `code`/`title`) + selectores de cliente, técnico, tipo, estado y prioridad. Se combinan (AND), persisten en la paginación (`withQueryString`) y «Limpiar» resetea. Implementados con `->when()` en `WorkOrderController::index`.
- Enlace en el sidebar con `@can('view work_orders')`.

## Gestión desde la lista (admin) — pestañas + acciones rápidas y masivas
El índice de OT es un **centro de operación** orientado a reducir clics del admin (no hay que abrir cada OT para decidir):

- **Pestañas/bandejas** (parámetro `tab`, default `all`): «Requieren tu acción» (con **badge de conteo** que centraliza la atención), «En curso», «Todas», «Papelera». La bandeja de acción usa el scope `WorkOrder::scopeAwaitingAdminAction` (draft de cliente · `pending_review` · `closed` sin enviar). Los filtros conviven con la pestaña (se preserva `tab`).
- **Acción primaria contextual por fila** (1 clic): la decide `WorkOrder::primaryAdminAction()` (fat model → la vista solo pinta). Botón «Aprobar»/«Enviar» que hace POST a `work_orders/{wo}/advance`; el `▾` abre un popover para «Aprobar y asignar técnico» (solicitudes) o el motivo de rechazo/devolución (POST a `.../regress`). Selects del popover son **nativos** (no Tom Select, por la restricción de popovers/`x-show`).
- **Acciones masivas** (checkbox por fila + «seleccionar todo» + barra flotante Alpine): «Aprobar», «Rechazar/Devolver» (modal con motivo) y «Asignar» (modal con técnico). Envían un único POST a `work_orders/batch` con `action` (`approve|reject|assign`), `ids[]` y opcionales `technician_id`/`rejection_reason`.
- El **badge de estado** se centraliza en `<x-work-order-status-badge :order>` (reusado en índice/hub); muestra «Solicitud» para draft de cliente y «Enviada al cliente» para closed visible.

**Servicio** (`WorkOrderService`): `advanceForAdmin` / `regressForAdmin` (despachan la transición correcta según estado), `assignTechnician` (ajusta `open⇆assigned` y notifica) y `batchForAdmin` (itera y devuelve cuántas afectó; omite las que no aplican). **Rutas**: `advance`, `regress`, `assign` (por OT) y `batch` (colección, declarada **antes** del `Route::resource` para no colisionar con `{work_order}`).

## Evidencias fotográficas (técnico y admin)
- `Upload` polimórfico (collection `photo`) · `WorkOrder::photos()` → `uploadMany('photo')`.
- El técnico dueño de la OT (estado `assigned`/`in_progress`) sube fotos desde el diligenciamiento; **subida AJAX inmediata** (rutas `technician.work_orders.photos.{store,update,destroy}`) — no dependen del envío del formulario, así no se pierden con mala conexión.
- **El admin también anexa/quita fotos** desde la ficha de la OT (`admin/work_orders/show`, galería interactiva; rutas `admin.work_orders.photos.{store,update,destroy}`, permiso `update work_orders`, **cualquier estado**). Es habitual que el admin llene las OT que ejecutó el técnico y suba las fotos que este tomó. Por eso `store`/`update` de OT ahora **redirigen a la ficha** (show) para poder anexar fotos de inmediato.
- **Descripción por foto**: cada foto tiene un campo de texto (`Upload.label`) editable inline que se guarda por AJAX (`PATCH .../photos/{photo}`, ruta `*.work_orders.photos.update`). Se muestra como pie de foto en show admin/técnico/cliente y en el PDF.
- **Compresión en servidor** (`ImageService`, GD): lado mayor ≤ 1600 px, re-codificado JPEG calidad 72, orientación EXIF corregida, transparencias aplanadas en blanco. Límite de subida 15 MB.
- Galería visible en el show del admin, del cliente (cuando la OT le es visible) y en el **PDF de la OT**.

## Firmas
Las firmas ya NO se suben por-OT. Modelo nuevo (T3):
- **Firma del técnico**: ligada al `User` del técnico. El técnico la sube una vez en su perfil (`POST /profile/signature`, `DELETE /profile/signature`; `ProfileController::updateSignature/destroySignature`). Se guarda como `Upload` con collection `signature` sobre el `User`. Se lee con `User::signatureBase64()` (data-URI inline). Si el técnico asignado no tiene firma y la OT está en `assigned`/`in_progress`, se muestra un banner amber no bloqueante con enlace al perfil.
- **Firma de empresa** (administrador): única global. El admin la configura en `GET/POST/DELETE admin/company-signature` (`Admin/CompanySignatureController`), guardada como `Upload` collection `company_signature` sobre el `User` admin que la sube. Se recupera globalmente con `App\Support\CompanySignature::current()/base64()` sin depender de qué admin la subió.
- Ambas firmas se muestran **solo lectura** en el show de admin/técnico/cliente y en el **PDF**, siempre como imagen base64 inline (data-URI); si no existe, se muestra una línea vacía. NO hay subida desde la OT.

## Borrador con autoguardado (técnico)
El formulario de diligenciamiento se **autoguarda** cada 20 s cuando hay cambios (fetch `PUT` con `Accept: application/json`; `Technician\WorkOrderController::update` responde JSON `saved_at`). Indicador «✓ Borrador guardado» / aviso de sin conexión. El botón «Guardar borrador» sigue disponible.

## Fechas sin horas (regla de producto)
Todas las vistas de producto (admin/cliente/técnico) muestran **solo fechas, nunca horas** (`Y-m-d` / `d/m/Y`); `scheduled_at` se captura con input `date`. Los timestamps completos se conservan en BD para auditoría de los devs.

## Visibilidad para el cliente (regla dura)
El cliente SOLO ve OT **aprobadas y enviadas** por el admin (`visible_to_client = true`, scope `sentToClient`): aplica a su listado, detalle, PDF de OT, hoja de vida del equipo (web/PDF) y métricas del dashboard. OT en proceso, sin aprobar o eliminadas jamás se muestran. Excepción: sus **propias solicitudes** (`requested_by_client` en draft/cancelled) sí aparecen en su listado para seguimiento (`scopeListableForClient`). Tests en `ClientVisibilityTest`.

## Pendiente (iteraciones futuras)
- Adjuntos de archivo no fotográficos (§5.2).
- Integración con hoja de vida de equipos y con Mantenimientos.
