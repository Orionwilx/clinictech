@props(['label' => '', 'section' => ''])

{{-- Cabecera de sección del sidebar (acordeón).
     Expandido: botón desplegable con etiqueta + chevron. Colapsado: separador fino. --}}
<div class="pt-3 first:pt-1">
    <button type="button" x-show="!collapsed" x-cloak @click="setSection(@js($section))"
            class="w-full flex items-center justify-between gap-2 px-3 py-1.5 rounded-lg text-[0.65rem] font-semibold uppercase tracking-wider text-brand-300/70 hover:bg-white/5 hover:text-brand-100 transition-colors">
        <span class="whitespace-nowrap">{{ $label }}</span>
        <svg class="w-3.5 h-3.5 transition-transform duration-150" :class="openSection === @js($section) && 'rotate-90'"
             fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
    </button>
    <div x-show="collapsed" x-cloak class="mx-3 border-t border-white/10"></div>
</div>
