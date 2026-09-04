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
- `<x-primary-button>` — acción principal (fondo `brand-600`). Es un `<button>` (submit de formulario).
- `<x-primary-link :href>` — **mismo estilo que primary-button pero como `<a>`**; úsalo para el CTA «Nuevo X» de la cabecera (nunca vuelvas a escribir el `<a class="inline-flex ... bg-brand-600 ...">` a mano).
- `<x-secondary-button>` — acción secundaria (blanco, borde gris).
- `<x-danger-button>` — acción destructiva (rojo, semántico).
- `<x-text-input>` / `<x-input-label>` / `<x-input-error>` — formularios.
- `<x-sidebar-link :href :active label>` con slot `icon` — ítem del menú lateral (activo en `brand-600`).
- `<x-breadcrumbs :items>` — migas de pan. `items` = array de `['label' => ..., 'href' => ...]`; el primer nivel "Inicio" (dashboard) se antepone solo; el último ítem se muestra como actual (sin enlace, en `brand-700`).
- `<x-page-header :title :breadcrumbs>` con slot `actions` — cabecera de página estándar (migas + título prominente `text-2xl font-bold` + botones a la derecha). Va SIEMPRE dentro del `<x-slot name="header">`.
- `<x-data-table :heads :cols>` + `<x-td>` + `<x-td-actions>` — **data table estándar** de cualquier listado admin (truncado+tooltip, columna de acciones fija). Ver sección «Componentes de data table» más abajo. `<x-icon-btn>` para los botones de acción.

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

## Tabla con selección y acciones masivas (bulk)
Para listas administrativas donde el usuario despacha muchos registros (ej. Órdenes de trabajo), el estándar es **tabla + pestañas de bandeja + acción contextual por fila + barra flotante masiva**. Referencia canónica: `resources/views/admin/work_orders/index.blade.php`.

- **Pestañas/bandejas**: `<nav>` con `border-b`; pestaña activa `border-brand-500 text-brand-700`, inactiva `border-transparent text-gray-500`. Badge de conteo `rounded-full px-2 text-xs` (ámbar para «requieren acción», gris para el resto). Cada pestaña es un enlace que preserva filtros y cambia `?tab=`.
- **Acción primaria por fila**: un botón que resuelve el caso feliz en 1 clic + un `▾` que abre un **popover** (`x-data="{ open:false }"`, `@click.outside`, `x-cloak`) para el caso con motivo/asignación. La lógica de qué acción aplica vive en el **modelo** (fat model), no en la vista.
- **Selección**: checkbox por fila con `x-model.number="selected"`; «seleccionar todo» con un getter `allSelected`. Fila seleccionada se resalta `:class="{ 'bg-brand-50': selected.includes(id) }"`.
- **Barra flotante**: `fixed inset-x-0 bottom-6`, píldora `bg-gray-900 text-white rounded-full`, visible con `x-show="selected.length"`. Dispara un **form oculto** (`x-ref`) que envía `ids[]` vía `<template x-for>`; modales (`fixed inset-0 bg-black/40`) recogen motivo/técnico antes de enviar.
- **Selects dentro de popovers/modales**: SIEMPRE nativos, nunca Tom Select (restricción de `x-show`/`x-cloak`).
- **Acciones de fila = iconos** (`<x-icon-btn>` con `label` como tooltip), sin emojis ni texto largo; la barra flotante usa botones-icono redondos sobre fondo oscuro. La acción de flujo (aprobar/enviar) también es icono; el menú «Más acciones» (`▾`) abre el popover con motivo/asignación.
- **Ojo con el clipping**: si las filas tienen popover, el contenedor de la tabla **no** debe llevar `overflow-hidden`/`overflow-x-auto` (recortan el desplegable, igual que a Tom Select). Usa `sm:rounded-lg` sin overflow.

### Componentes de data table (estándar obligatorio)
**Toda tabla de listado del admin se construye con estos componentes** (no escribas `<table>` a mano):

```blade
<x-data-table
    :cols="['w-[28%]','w-[28%]','w-32','w-32']"   {{-- anchos <colgroup>; la última col fija = acciones --}}
    :heads="[['Nombre'], ['Correo'], ['Estado'], ['Acciones','right']]">
    @forelse ($users as $user)
        <tr class="{{ $user->trashed() ? 'bg-red-50' : 'bg-white' }}">
            <x-td :title="$user->name">{{ $user->name }}</x-td>       {{-- truncate + tooltip --}}
            <x-td :title="$user->email" muted>{{ $user->email }}</x-td> {{-- muted = gris --}}
            <x-td plain>…badge…</x-td>                                 {{-- plain = sin truncado --}}
            <x-td-actions>…<x-icon-btn/>…</x-td-actions>              {{-- columna fija sticky --}}
        </tr>
    @empty
        <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Sin registros.</td></tr>
    @endforelse
</x-data-table>
```

- **`<x-data-table :heads :cols>`** — tarjeta + `table-fixed` + `<colgroup>` + `<thead>`. `heads`: `'Etiqueta'` o `['Etiqueta','right'|'center']` (la última `right` queda sticky).
- **`<x-td :title :sub :subTitle align muted plain colspan>`** — celda con truncado + tooltip (`title`); `sub` = segunda línea gris; `muted` = principal en gris; `plain` = contenido libre (badges/botones).
- **`<x-td-actions>`** — celda de acciones con ancho reservado, `sticky right-0 md:static bg-inherit`. Acepta atributos extra (p. ej. `x-data`/`:class` para elevar z-index del menú ⋮). El `<tr>` debe llevar un bg (`bg-white`/`bg-red-50`) para que `bg-inherit` tape al hacer scroll.

Los archivos viven en `resources/views/components/{data-table,td,td-actions}.blade.php`. `work_orders` usa además checkbox de selección, pestañas y barra masiva propios, pero **sus celdas usan los mismos `<x-td>`/`<x-td-actions>`**.

### Tabla robusta ante texto variable (`table-fixed`) — cómo funciona por dentro
El componente `<x-data-table>` aplica esto; documentado por si necesitas replicarlo o entenderlo:
- **Anchos por `<col>`**: checkbox `w-12`; columnas de texto flexibles en `%` (ej. `w-[26%]`); columnas cortas fijas (`w-28`/`w-36`); **acciones con ancho fijo reservado** (`w-32`).
- **Texto variable**: cada celda de texto envuelve su contenido en `<div class="truncate" title="…">` — recorte con puntos suspensivos + **tooltip nativo** con el valor completo. Para dos líneas usar `line-clamp-2`. Celdas con `align-top`.
- **Columna de acciones estable**: `sticky right-0 md:static bg-inherit` (la fila define su color con `:class`, la celda lo hereda para tapar el contenido al hacer scroll). El contenedor es `overflow-x-auto md:overflow-x-visible`: en móvil hay scroll horizontal con la columna fija a la derecha; en ≥md el overflow es visible para no recortar el menú `⋮`.
- **⚠️ Gotcha de stacking con `sticky`**: `position: sticky` crea un **stacking context por celda**, así que el dropdown de una fila queda atrapado y las filas de abajo (posteriores en el DOM) lo tapan. Solución: la celda de acciones usa **`md:static`** (en escritorio no crea contexto → el dropdown escapa al contexto raíz y cubre todo) y, para el modo sticky en móvil, se **eleva el z-index de la celda activa** con `x-data="{ open }"` en el `<td>` y `:class="open ? 'z-30' : ''"`. El dropdown lleva `bg-white` sólido + `z-30`; su wrapper es `relative` **sin** z-index (para no volver a crear un contexto que lo atrape).
- **Acciones agrupadas**: primarias visibles (acción de flujo + Ver) + menú `⋮` con las secundarias. Máximo ~3 iconos por fila.
- **Regla de oro del menú `⋮`**: contiene **solo acciones simples** (enlaces/POST de un clic: PDF, Editar, Eliminar). Los **flujos que requieren input** (asignar técnico, motivo de rechazo) **NO** se embeben en el popover estrecho —se recortan y solapan—: abren un **modal compartido** (`openRowModal(cfg)` en el componente Alpine → `rowModal` state → form oculto `x-ref="rowForm"` con `action` dinámica hacia `advance`/`regress`). Mismo criterio que los modales de acciones masivas.

**JS de vista**: el layout expone `@stack('scripts')` antes de `</body>`; una vista inyecta su componente Alpine con `@push('scripts')` (define una función global `xData()` que `x-data` invoca). No pongas lógica de página en `app.js`.

## Referencia viva
Las vistas de **usuarios** (`resources/views/admin/users/`) son el patrón canónico de CRUD simple (usan `<x-data-table>`). Para listas operativas con pestañas y acciones masivas, ver **órdenes de trabajo** (`resources/views/admin/work_orders/`). Las 8 tablas del admin comparten los mismos componentes `<x-data-table>`/`<x-td>`/`<x-td-actions>`.
