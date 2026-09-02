@props(['items' => []])

{{-- items: array de ['label' => string, 'href' => string|null]. El último se muestra como actual (sin enlace). --}}
<nav {{ $attributes->merge(['class' => 'flex']) }} aria-label="Migas de pan">
    <ol class="flex items-center flex-wrap gap-1.5 text-sm">
        <li>
            <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-brand-700 transition-colors">Inicio</a>
        </li>
        @foreach ($items as $item)
            <li class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
                @if (! empty($item['href']) && ! $loop->last)
                    <a href="{{ $item['href'] }}" class="text-gray-400 hover:text-brand-700 transition-colors">{{ $item['label'] }}</a>
                @else
                    <span class="font-medium text-brand-700" aria-current="page">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
