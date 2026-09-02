<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Marcas" :breadcrumbs="[['label' => 'Marcas']]">
            <x-slot:actions>
                @can('create brands')
                    <a href="{{ route('admin.brands.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">
                        {{ __('Nueva marca') }}
                    </a>
                @endcan
            </x-slot:actions>
        </x-page-header>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marca</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Modelos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Equipos</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($brands as $brand)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $brand->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $brand->models_count }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $brand->equipment_count }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    @can('update brands')
                                        <a href="{{ route('admin.brands.edit', $brand) }}" class="text-brand-600 hover:text-brand-800">Editar</a>
                                    @endcan
                                    @can('delete brands')
                                        <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" class="inline"
                                              onsubmit="return confirm('¿Eliminar esta marca? Se eliminarán también sus modelos.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No hay marcas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $brands->links() }}</div>
        </div>
    </div>
</x-app-layout>
