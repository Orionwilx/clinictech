@props(['active' => false, 'href' => '#', 'label' => ''])

@php
    $classes = $active
        ? 'bg-brand-600 text-white'
        : 'text-brand-100 hover:bg-white/10 hover:text-white';
@endphp

<a href="{{ $href }}"
   :title="collapsed ? @js($label) : null"
   {{ $attributes->merge(['class' => "group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors $classes"]) }}>
    <span class="shrink-0 w-6 h-6 flex items-center justify-center">{{ $icon }}</span>
    <span x-show="!collapsed" x-cloak class="truncate">{{ $label }}</span>
</a>
