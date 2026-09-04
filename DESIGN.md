# ClinicTech — Sistema de Diseño

Identidad visual orientada a salud: **Teal clínico** (calma, confianza, limpieza). Este documento es el estándar. Úsalo al construir cualquier vista nueva.

> Regla de oro: **nunca hardcodees un color de marca** (`teal-600`, `#0D9488`, `indigo-*`). Usa siempre los tokens `brand-*` definidos en `tailwind.config.js`. Cambiar la marca debe ser 1 línea.

## Paleta (tokens `brand-*`)
| Token | Hex | Uso |
|-------|-----|-----|
| `brand-50` | `#F0FDFA` | Fondos suaves, tintes, estados hover claros |
| `brand-100` | `#CCFBF1` | Fondos de badges, resaltados |
| `brand-500` | `#14B8A6` | Acentos, bordes activos, focus ring |
| `brand-600` | `#0D9488` | **Principal**: botones, enlaces activos, logo |
| `brand-700` | `#0F766E` | Hover de botones/enlaces |
| `brand-800` | `#115E59` | Active/pressed |
| `brand-900` | `#134E4A` | Texto de marca, headers |

Colores semánticos (NO son marca, no cambian con el tema):
- Éxito → `green-*` · Peligro/eliminar → `red-*` · Aviso → `amber-*` · Neutro/texto → `gray-*`

## Componentes (fuente de verdad en `resources/views/components/`)
Reutiliza SIEMPRE estos; no escribas clases sueltas para lo que ya existe:
- `<x-primary-button>` — acción principal (fondo `brand-600`).
- `<x-secondary-button>` — acción secundaria (blanco, borde gris).
- `<x-danger-button>` — acción destructiva (rojo, semántico).
- `<x-text-input>` / `<x-input-label>` / `<x-input-error>` — formularios.
- `<x-sidebar-link :href :active label>` con slot `icon` — ítem del menú lateral (activo en `brand-600`).
- `<x-breadcrumbs :items>` — migas de pan. `items` = array de `['label' => ..., 'href' => ...]`; el primer nivel "Inicio" (dashboard) se antepone solo; el último ítem se muestra como actual (sin enlace, en `brand-700`).
- `<x-page-header :title :breadcrumbs>` con slot `actions` — cabecera de página estándar (migas + título prominente `text-2xl font-bold` + botones a la derecha). Va SIEMPRE dentro del `<x-slot name="header">`.

## Shell administrativo (layout con sidebar)
El área autenticada usa un **sidebar colapsable** (estándar de software administrativo):
- Estructura en `layouts/app.blade.php` (shell + topbar + menú de usuario) y `layouts/sidebar.blade.php` (menú).
- Estado en Alpine: `collapsed` (recordado en `localStorage` como `sb_collapsed`) y `mobileOpen` (off-canvas en móvil).
- Sidebar oscuro `bg-brand-900`; ítem activo `bg-brand-600`. Colapsa a solo-iconos (`w-64` ↔ `w-20`).
- **Para añadir un módulo al menú**: agrega un `<x-sidebar-link>` en `layouts/sidebar.blade.php` protegido con `@can('view {modulo}')`, con su icono SVG (stroke, `w-5 h-5`).
- Topbar `sticky` con toggle móvil, el slot `header` de la página y el dropdown de usuario.

## Estándar de vistas
- **Layout**: envuelve todo en `<x-app-layout>` (autenticado, con sidebar) o `<x-guest-layout>` (público).
- **Header de página**: `<x-slot name="header">` con `<x-page-header title="..." :breadcrumbs="[...]">` (se muestra en la topbar; incluye migas de pan y título prominente). El botón de acción principal va en su slot `actions`. Patrón de migas: índice `[['label' => 'Equipos']]`; detalle/form `[['label' => 'Equipos', 'href' => route('admin.equipment.index')], ['label' => 'Nuevo']]`.
- **Contenedor**: `<div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">` (formularios: `max-w-2xl`).
- **Tarjeta**: `bg-white shadow-sm sm:rounded-lg p-6`.
- **Flash de éxito**: caja `bg-green-50 text-green-700` leyendo `session('status')`.
- **Tablas**: `thead` con `bg-gray-50`, filas `divide-y divide-gray-200`, cabeceras `text-xs uppercase text-gray-500`.
- **Badges de estado**: `rounded-full px-2 text-xs font-semibold` — activo `bg-green-100/green-800`, inactivo `bg-gray-100/gray-800`, eliminado `bg-red-100/red-800`.
- **Botón "crear"** en el header a la derecha (patrón: ver `admin/users/index.blade.php`).
- **Formularios**: extrae el cuerpo a un parcial `_form.blade.php` compartido entre `create` y `edit`.

## Selector con búsqueda — Tom Select (`<x-searchable-select>`)

Componente `resources/views/components/searchable-select.blade.php` para selects con búsqueda client-side.

```blade
<x-searchable-select
    name="technician_id"
    :options="$technicians"        {{-- array $value => $label --}}
    :selected="old('technician_id', $workOrder->technician_id ?? '')"
    placeholder="— Sin asignar —"  {{-- opcional, default "— Selecciona —" --}}
/>
```

- Tom Select se inicializa automáticamente en `DOMContentLoaded` sobre `select[data-searchable]`.
- El `placeholder` vive en el input de control (no como `<option>`); muestra texto gris cuando no hay selección.
- Plugin `clear_button` activo: aparece `×` dentro del control cuando hay valor seleccionado.
- Búsqueda sin distinción de acentos/mayúsculas (`window.normalize`).
- `dropdownParent: 'body'` — el dropdown escapa overflow y z-index del contenedor padre.

**Estados visuales** (alineados con `<x-text-input>`):

| Estado | Efecto |
|--------|--------|
| Normal | Borde `gray-300` |
| Hover  | Borde `brand-400` + fondo `brand-50` |
| Focus  | Borde `brand-500` + `box-shadow 0 0 0 1px brand-500` |

**Restricción importante:** Tom Select solo se aplica a selects:
1. Visibles desde el inicio (fuera de `x-show` / `x-cloak`).
2. Sin dependencia reactiva Alpine (`x-model` que dispare cambios en otros elementos).

Para selects reactivos (ej. cliente → área, marca → modelo) usar `<select>` nativo con Alpine.

**Archivos clave:**
- `resources/views/components/searchable-select.blade.php` — componente Blade
- `resources/js/app.js` — inicialización global (`initSearchableSelects`)
- `resources/css/app.css` — estilos Tom Select (overrides del tema bootstrap5)

## Referencia viva
Las vistas de **usuarios** (`resources/views/admin/users/`) son el patrón canónico. Copia su estructura al crear un módulo nuevo.
