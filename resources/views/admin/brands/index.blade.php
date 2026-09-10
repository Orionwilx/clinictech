<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Marcas" :breadcrumbs="[['label' => 'Marcas']]">
            <x-slot:actions>
                @can('create brands')
                    <x-primary-link :href="route('admin.brands.create')">{{ __('Nueva marca') }}</x-primary-link>
                @endcan
            </x-slot:actions>
        </x-page-header>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <form method="GET" class="mb-4 bg-white shadow-sm sm:rounded-lg p-4">
                <div class="flex flex-wrap gap-3">
                    <div class="relative flex-1 min-w-40">
                        <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0Z"/></svg>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Buscar marca…"
                               class="pl-9 pr-3 py-2 w-full text-sm border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-md hover:bg-brand-700">Filtrar</button>
                    @if (array_filter($filters))
                        <a href="{{ route('admin.brands.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Limpiar</a>
                    @endif
                </div>
            </form>

            <x-data-table
                :cols="['w-auto', 'w-32', 'w-32', 'w-28']"
                :heads="[['Marca'], ['Modelos'], ['Equipos'], ['Acciones', 'right']]">
                @forelse ($brands as $brand)
                    <tr class="bg-white">
                        <x-td :title="$brand->name">{{ $brand->name }}</x-td>
                        <x-td muted>{{ $brand->models_count }}</x-td>
                        <x-td muted>{{ $brand->equipment_count }}</x-td>
                        <x-td-actions>
                            @can('update brands')
                                <x-icon-btn :href="route('admin.brands.edit', $brand)" color="brand" label="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                                </x-icon-btn>
                            @endcan
                            @can('delete brands')
                                <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" class="inline"
                                      onsubmit="return confirm('¿Eliminar esta marca? Se eliminarán también sus modelos.');">
                                    @csrf @method('DELETE')
                                    <x-icon-btn color="red" label="Eliminar" type="submit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                    </x-icon-btn>
                                </form>
                            @endcan
                        </x-td-actions>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No hay marcas.</td></tr>
                @endforelse
            </x-data-table>

            <div class="mt-4">{{ $brands->links() }}</div>
        </div>
    </div>
</x-app-layout>
