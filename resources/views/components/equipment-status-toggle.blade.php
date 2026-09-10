@props(['equipment'])

{{-- Toggle rápido activo⇄inactivo del equipo (solo con permiso update equipment).
     Los estados 'en mantenimiento' / 'dado de baja' se muestran pero el switch
     los reactiva a 'activo'; para fijarlos se usa el formulario completo. --}}
@php($isActive = $equipment->status === 'active')
@php($labelColor = match ($equipment->status) {
    'active' => 'text-green-700',
    'maintenance' => 'text-amber-700',
    'retired' => 'text-red-700',
    default => 'text-gray-500',
})

@can('update equipment')
    <form method="POST" action="{{ route('admin.equipment.toggle-active', $equipment) }}" class="inline-flex items-center gap-2">
        @csrf
        @method('PATCH')
        <button type="submit" role="switch" aria-checked="{{ $isActive ? 'true' : 'false' }}"
                title="{{ $isActive ? 'Marcar como inactivo' : 'Marcar como activo' }}"
                @class([
                    'relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-1',
                    'bg-green-500' => $isActive,
                    'bg-gray-300' => ! $isActive,
                ])>
            <span @class([
                'inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform',
                'translate-x-4' => $isActive,
                'translate-x-0.5' => ! $isActive,
            ])></span>
        </button>
        <span class="text-xs font-medium {{ $labelColor }}">{{ $equipment->statusLabel() }}</span>
    </form>
@else
    <span class="text-xs font-medium {{ $labelColor }}">{{ $equipment->statusLabel() }}</span>
@endcan
