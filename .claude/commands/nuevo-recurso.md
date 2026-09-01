---
description: Genera un CRUD completo (modelo, migración, request, controller, rutas, test) siguiendo las convenciones del proyecto
argument-hint: <Recurso en singular PascalCase, ej. Clinic>
---

Crea un recurso CRUD completo para **$1** siguiendo EXACTAMENTE las convenciones de `CLAUDE.md` (mapa de navegación y convención de nombres). No inventes rutas ni nombres: dedúcelos de las reglas.

**Paso 0 — Lee el spec del módulo.** Busca `docs/modules/{recurso}.md` (nombre del recurso en español/kebab, ej. `docs/modules/clientes.md`). Si existe, úsalo como ESPECIFICACIÓN: toma de ahí campos, tipos, enums, relaciones, reglas de negocio y permisos. Si el spec está en estado "borrador" con campos por confirmar, PREGÚNTAME antes de generar la migración. Si no existe spec, infiere de `project.md` y créalo con la plantilla `docs/modules/_TEMPLATE.md` como parte del trabajo. Sigue también el estándar visual de `DESIGN.md` para las vistas.

Nomenclatura a usar (deriva todo de `$1`):
- Modelo: `$1` (singular PascalCase)
- Tabla / vistas / segmento de ruta: plural snake_case de `$1`
- Controlador: `Admin/$1Controller`
- Requests: `$1/Store$1Request`, `$1/Update$1Request`

Pasos (ejecútalos en orden, parando si algo falla):

1. **Andamiaje con artisan** (una sola pasada):
   ```bash
   php artisan make:model $1 -mf
   php artisan make:controller Admin/$1Controller --resource --model=$1
   php artisan make:request $1/Store$1Request
   php artisan make:request $1/Update$1Request
   ```
   (`-mf` = migración + factory.)

2. **Migración**: define columnas mínimas razonables según el dominio de `$1` (mira el modelo relacionado en `CLAUDE.md` → sección Dominio para inferir relaciones, p. ej. `foreignId` a la tabla padre). Añade `$table->softDeletes()` si el recurso debe ser recuperable. NO la ejecutes todavía; muéstrame el esquema propuesto.

3. **Modelo**: añade `#[Fillable([...])]`, casts necesarios, `SoftDeletes` si aplica, y los métodos de relación Eloquent (`belongsTo`/`hasMany`) según el dominio.

4. **Form Requests**: `authorize()` devuelve `true` por ahora; `rules()` con validación real derivada de las columnas. Store y Update pueden divergir (p. ej. `unique` ignorando el propio id en Update).

5. **Controlador**: implementa los 7 métodos resource. Slim controller — si hay lógica no trivial, extráela a `app/Services/$1Service.php`. Devuelve vistas `admin.{plural_snake}.*`.

6. **Rutas**: en `routes/web.php`, dentro de un grupo `->middleware(['auth'])->prefix('admin')->name('admin.')` (créalo si no existe), añade `Route::resource('{plural_snake}', Admin\$1Controller::class)`.

7. **Vistas**: crea `resources/views/admin/{plural_snake}/` con `index`, `create`, `edit`, `show` en Blade + Tailwind, reutilizando el layout de Breeze (`x-app-layout`).

8. **Test**: `tests/Feature/Admin/$1Test.php` cubriendo index/store/update/destroy con usuario autenticado y la factory.

9. **Cerrar**: ejecuta `php artisan migrate`, `./vendor/bin/pint` y `php artisan test`. Si todo pasa, muéstrame un resumen de archivos creados. Actualiza la sección "Estado actual" de `CLAUDE.md` marcando `$1` como implementado, y marca el spec `docs/modules/{recurso}.md` como `✅ implementado`.

Al terminar, reporta qué archivos creaste (con sus rutas) y el resultado de los tests.
