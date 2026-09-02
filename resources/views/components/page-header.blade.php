@props(['title', 'breadcrumbs' => []])

{{-- Cabecera de página estándar: migas de pan + título prominente + acciones opcionales (slot). --}}
<div class="w-full">
    @if (count($breadcrumbs))
        <x-breadcrumbs :items="$breadcrumbs" class="mb-1.5" />
    @endif
    <div class="flex items-center justify-between gap-4">
        <h1 class="font-bold text-2xl text-gray-900 leading-tight truncate">{{ $title }}</h1>
        @isset($actions)
            <div class="flex items-center gap-2 shrink-0">{{ $actions }}</div>
        @endisset
    </div>
</div>
