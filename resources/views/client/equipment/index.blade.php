<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Mis equipos" :breadcrumbs="[['label' => 'Panel', 'href' => route('client.dashboard')], ['label' => 'Equipos']]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Filtros --}}
            <form method="GET" class="mb-4 bg-white shadow-sm sm:rounded-lg p-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2">
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                               placeholder="Nombre o serial…"
                               class="block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                    </div>
                    <select name="status" class="block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                        <option value="">Todos los estados</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-3 mt-3">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">
                        Filtrar
                    </button>
                    @if (array_filter($filters))
                        <a href="{{ route('client.equipment.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Limpiar</a>
                    @endif
                </div>
            </form>

            <x-data-table
                :cols="['w-[30%]', 'w-[26%]', 'w-40', 'w-36', 'w-24']"
                :heads="[['Equipo'], ['Marca / Modelo'], ['Área'], ['Estado'], ['Acciones', 'right']]">
                @forelse ($equipment as $eq)
                    <tr class="bg-white">
                        <x-td :title="$eq->name" :sub="$eq->serial_number ? 'S/N '.$eq->serial_number : null">{{ $eq->name }}</x-td>
                        <x-td :title="optional($eq->brand)->name" muted :sub="optional($eq->model)->name ?: null">{{ optional($eq->brand)->name ?: '—' }}</x-td>
                        <x-td :title="optional($eq->area)->name" muted>{{ optional($eq->area)->name ?: '—' }}</x-td>
                        <x-td plain>
                            <span @class([
                                'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                'bg-green-100 text-green-800' => $eq->status === 'active',
                                'bg-gray-100 text-gray-800' => $eq->status === 'inactive',
                                'bg-amber-100 text-amber-800' => $eq->status === 'maintenance',
                                'bg-red-100 text-red-800' => $eq->status === 'retired',
                            ])>{{ $eq->statusLabel() }}</span>
                        </x-td>
                        <x-td-actions>
                            <x-icon-btn :href="route('client.equipment.show', $eq)" color="gray" label="Ver hoja de vida">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            </x-icon-btn>
                        </x-td-actions>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No hay equipos registrados.</td></tr>
                @endforelse
            </x-data-table>

            <div class="mt-4">{{ $equipment->links() }}</div>
        </div>
    </div>
</x-app-layout>
