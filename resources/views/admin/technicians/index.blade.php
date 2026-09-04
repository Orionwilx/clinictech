<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Técnicos" :breadcrumbs="[['label' => 'Técnicos']]">
            <x-slot:actions>
                <x-primary-link :href="route('admin.technicians.create')">{{ __('Nuevo técnico') }}</x-primary-link>
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

            <form method="GET" class="mb-4 bg-white shadow-sm sm:rounded-lg p-4">
                <div class="flex flex-wrap gap-3">
                    <div class="relative flex-1 min-w-40">
                        <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0Z"/></svg>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nombre, documento o especialidad…"
                               class="pl-9 pr-3 py-2 w-full text-sm border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                    </div>
                    <select name="status" class="py-2 pl-3 pr-8 text-sm border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                        <option value="">Todos los estados</option>
                        <option value="active"   @selected(($filters['status'] ?? '') === 'active')>Activo</option>
                        <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactivo</option>
                        <option value="deleted"  @selected(($filters['status'] ?? '') === 'deleted')>Eliminado</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-md hover:bg-brand-700">Filtrar</button>
                    @if (array_filter($filters))
                        <a href="{{ route('admin.technicians.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Limpiar</a>
                    @endif
                </div>
            </form>

            <x-data-table
                :cols="['w-[30%]', 'w-40', 'w-[26%]', 'w-32', 'w-32']"
                :heads="[['Nombre'], ['Documento'], ['Especialidad'], ['Estado'], ['Acciones', 'right']]">
                @forelse ($technicians as $technician)
                    <tr class="{{ $technician->trashed() ? 'bg-red-50' : 'bg-white' }}">
                        <x-td :title="$technician->name">{{ $technician->name }}</x-td>
                        <x-td muted>{{ $technician->document }}</x-td>
                        <x-td :title="$technician->specialty" muted>{{ $technician->specialty ?: '—' }}</x-td>
                        <x-td plain>
                            @if ($technician->trashed())
                                <span class="inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold text-red-800">Eliminado</span>
                            @elseif ($technician->is_active)
                                <span class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold text-green-800">Activo</span>
                            @else
                                <span class="inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold text-gray-800">Inactivo</span>
                            @endif
                        </x-td>
                        <x-td-actions>
                            @if ($technician->trashed())
                                <form action="{{ route('admin.technicians.restore', $technician->id) }}" method="POST" class="inline">
                                    @csrf @method('PUT')
                                    <x-icon-btn color="green" label="Restaurar" type="submit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                    </x-icon-btn>
                                </form>
                            @else
                                <x-icon-btn :href="route('admin.technicians.show', $technician)" color="gray" label="Ver">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                </x-icon-btn>
                                <x-icon-btn :href="route('admin.technicians.edit', $technician)" color="brand" label="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                                </x-icon-btn>
                                <form action="{{ route('admin.technicians.destroy', $technician) }}" method="POST" class="inline"
                                      onsubmit="return confirm('¿Eliminar este técnico?');">
                                    @csrf @method('DELETE')
                                    <x-icon-btn color="red" label="Eliminar" type="submit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                    </x-icon-btn>
                                </form>
                            @endif
                        </x-td-actions>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No hay técnicos.</td></tr>
                @endforelse
            </x-data-table>

            <div class="mt-4">
                {{ $technicians->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
