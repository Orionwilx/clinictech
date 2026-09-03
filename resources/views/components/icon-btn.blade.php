@props(['href' => null, 'color' => 'gray', 'label' => '', 'target' => null])

@php
$colors = [
    'gray'  => 'text-gray-500 hover:text-gray-800 hover:bg-gray-100',
    'brand' => 'text-brand-600 hover:text-brand-800 hover:bg-brand-50',
    'red'   => 'text-red-500 hover:text-red-700 hover:bg-red-50',
    'green' => 'text-green-600 hover:text-green-800 hover:bg-green-50',
];
$cls = 'inline-flex items-center justify-center w-8 h-8 rounded transition-colors '.($colors[$color] ?? $colors['gray']);
@endphp

@if ($href)
    <a href="{{ $href }}" title="{{ $label }}" class="{{ $cls }}"
       @if($target) target="{{ $target }}" @endif>
        {{ $slot }}
    </a>
@else
    <button title="{{ $label }}" {{ $attributes }} class="{{ $cls }}">
        {{ $slot }}
    </button>
@endif
