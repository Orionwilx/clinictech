<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Clientes') }}
            </h2>
            <a href="{{ route('admin.clients.create') }}"
               class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">
                {{ __('Nuevo cliente') }}
            </a>
        </div>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Empresa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIT</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Correo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ciudad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($clients as $client)
                            <tr class="{{ $client->trashed() ? 'bg-red-50' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $client->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $client->nit }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $client->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $client->city ?: '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($client->trashed())
                                        <span class="inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold text-red-800">Eliminado</span>
                                    @elseif ($client->is_active)
                                        <span class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold text-green-800">Activo</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold text-gray-800">Inactivo</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    @if ($client->trashed())
                                        <form action="{{ route('admin.clients.restore', $client->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="text-green-600 hover:text-green-900">Restaurar</button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.clients.show', $client) }}" class="text-gray-600 hover:text-gray-900">Ver</a>
                                        <a href="{{ route('admin.clients.edit', $client) }}" class="text-brand-600 hover:text-brand-800">Editar</a>
                                        <form action="{{ route('admin.clients.destroy', $client) }}" method="POST" class="inline"
                                              onsubmit="return confirm('¿Eliminar este cliente?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No hay clientes.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $clients->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
