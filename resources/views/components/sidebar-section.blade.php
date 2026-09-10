@props(['label' => ''])

{{-- Encabezado de sección del sidebar.
     Expandido: etiqueta en mayúsculas tenue. Colapsado: un separador fino. --}}
<div class="pt-4 pb-1 first:pt-1">
    <p x-show="!collapsed" x-cloak
       class="px-3 text-[0.65rem] font-semibold uppercase tracking-wider text-brand-300/70 whitespace-nowrap">
        {{ $label }}
    </p>
    <div x-show="collapsed" x-cloak class="mx-3 border-t border-white/10"></div>
</div>
