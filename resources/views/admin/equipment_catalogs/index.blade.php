<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Catálogos de equipos" :breadcrumbs="[['label' => 'Catálogos de equipos']]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            {{-- Pestañas por catálogo --}}
            <div class="mb-4 border-b border-gray-200">
                <nav class="-mb-px flex gap-6">
                    @foreach ($catalogs as $key => $meta)
                        <a href="{{ route('admin.equipment_catalogs.index', $key) }}"
                           @class([
                               'py-3 text-sm font-medium border-b-2',
                               'border-brand-600 text-brand-700' => $catalog === $key,
                               'border-transparent text-gray-500 hover:text-gray-700' => $catalog !== $key,
                           ])>{{ $meta['label'] }}</a>
                    @endforeach
                </nav>
            </div>

            <p class="mb-4 text-xs text-gray-400">Estas opciones alimentan los checkboxes de categorías y equipos. Desactivar una opción la oculta para selecciones nuevas sin alterar lo ya guardado; eliminarla tampoco borra los valores guardados.</p>

            {{-- Alta --}}
            @can('create equipment_catalogs')
                <form action="{{ route('admin.equipment_catalogs.store', $catalog) }}" method="POST"
                      class="mb-4 bg-white shadow-sm sm:rounded-lg p-4 flex items-end gap-3">
                    @csrf
                    <div class="flex-1">
                        <x-input-label for="name" :value="$catalogs[$catalog]['item'] . ' nueva'" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <x-primary-button>{{ __('Agregar') }}</x-primary-button>
                </form>
            @endcan

            <x-data-table
                :cols="['w-auto', 'w-32', 'w-40']"
                :heads="[[$catalogs[$catalog]['item']], ['Estado'], ['Acciones', 'right']]">
                @forelse ($items as $item)
                    <tr class="bg-white" x-data="{ editing: false }">
                        <td class="px-6 py-3 text-sm text-gray-700">
                            <span x-show="!editing">{{ $item->name }}</span>
                            @can('update equipment_catalogs')
                                <form x-show="editing" x-cloak action="{{ route('admin.equipment_catalogs.update', [$catalog, $item->id]) }}" method="POST" class="flex items-center gap-2">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="is_active" value="{{ $item->is_active ? 1 : 0 }}">
                                    <x-text-input name="name" type="text" class="block w-full text-sm" :value="$item->name" required />
                                    <x-primary-button>{{ __('Guardar') }}</x-primary-button>
                                    <button type="button" @click="editing = false" class="text-sm text-gray-500 hover:text-gray-700">Cancelar</button>
                                </form>
                            @endcan
                        </td>
                        <td class="px-6 py-3">
                            <span @class([
                                'inline-flex px-2 py-0.5 rounded-full text-xs font-medium',
                                'bg-green-100 text-green-800' => $item->is_active,
                                'bg-gray-100 text-gray-500' => ! $item->is_active,
                            ])>{{ $item->is_active ? 'Activa' : 'Inactiva' }}</span>
                        </td>
                        <x-td-actions>
                            @can('update equipment_catalogs')
                                <x-icon-btn color="brand" label="Renombrar" type="button" x-on:click="editing = true">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                                </x-icon-btn>
                                <form action="{{ route('admin.equipment_catalogs.update', [$catalog, $item->id]) }}" method="POST" class="inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="name" value="{{ $item->name }}">
                                    <input type="hidden" name="is_active" value="{{ $item->is_active ? 0 : 1 }}">
                                    <x-icon-btn :color="$item->is_active ? 'gray' : 'brand'" :label="$item->is_active ? 'Desactivar' : 'Activar'" type="submit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1 0 12.728 0M12 3v9"/></svg>
                                    </x-icon-btn>
                                </form>
                            @endcan
                            @can('delete equipment_catalogs')
                                <form action="{{ route('admin.equipment_catalogs.destroy', [$catalog, $item->id]) }}" method="POST" class="inline"
                                      onsubmit="return confirm('¿Eliminar esta opción del catálogo? Los valores ya guardados se conservan.');">
                                    @csrf @method('DELETE')
                                    <x-icon-btn color="red" label="Eliminar" type="submit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                    </x-icon-btn>
                                </form>
                            @endcan
                        </x-td-actions>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No hay opciones en este catálogo.</td></tr>
                @endforelse
            </x-data-table>
        </div>
    </div>
</x-app-layout>
