<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Equipos" :breadcrumbs="[['label' => 'Equipos']]">
            <x-slot:actions>
                <a href="{{ route('admin.equipment.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">
                    {{ __('Nuevo equipo') }}
                </a>
            </x-slot:actions>
        </x-page-header>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Equipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Serial</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($equipment as $item)
                            <tr class="{{ $item->trashed() ? 'bg-red-50' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->name }}
                                    <span class="block text-xs text-gray-400">{{ optional($item->brand)->name }} {{ optional($item->model)->name }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->serial_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($item->client)->name ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($item->trashed())
                                        <span class="inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold text-red-800">Eliminado</span>
                                    @else
                                        <span @class([
                                            'inline-flex rounded-full px-2 text-xs font-semibold',
                                            'bg-green-100 text-green-800' => $item->status === 'active',
                                            'bg-gray-100 text-gray-800' => $item->status === 'inactive',
                                            'bg-amber-100 text-amber-800' => $item->status === 'maintenance',
                                            'bg-red-100 text-red-800' => $item->status === 'retired',
                                        ])>{{ $item->statusLabel() }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    @if ($item->trashed())
                                        <form action="{{ route('admin.equipment.restore', $item->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="text-green-600 hover:text-green-900">Restaurar</button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.equipment.show', $item) }}" class="text-gray-600 hover:text-gray-900">Ver</a>
                                        <a href="{{ route('admin.equipment.edit', $item) }}" class="text-brand-600 hover:text-brand-800">Editar</a>
                                        <form action="{{ route('admin.equipment.destroy', $item) }}" method="POST" class="inline"
                                              onsubmit="return confirm('¿Eliminar este equipo?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No hay equipos.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $equipment->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
