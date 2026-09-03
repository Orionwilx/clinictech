<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Dashboard" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Accesos rápidos --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">

                @can('create clients')
                <a href="{{ route('admin.clients.create') }}"
                   class="bg-white shadow-sm sm:rounded-lg p-6 flex items-center gap-4 hover:shadow-md transition-shadow group">
                    <div class="flex-shrink-0 w-12 h-12 bg-brand-50 rounded-lg flex items-center justify-center group-hover:bg-brand-100 transition-colors">
                        <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Registrar empresa</p>
                        <p class="text-xs text-gray-500">Nuevo cliente / institución</p>
                    </div>
                </a>
                @endcan

                @can('view equipment')
                <a href="{{ route('admin.equipment.index') }}"
                   class="bg-white shadow-sm sm:rounded-lg p-6 flex items-center gap-4 hover:shadow-md transition-shadow group">
                    <div class="flex-shrink-0 w-12 h-12 bg-brand-50 rounded-lg flex items-center justify-center group-hover:bg-brand-100 transition-colors">
                        <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21a48.25 48.25 0 0 1-8.135-.687c-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Equipos</p>
                        <p class="text-xs text-gray-500">Inventario de equipos biomédicos</p>
                    </div>
                </a>
                @endcan

                @can('view work_orders')
                <a href="{{ route('admin.work_orders.index') }}"
                   class="bg-white shadow-sm sm:rounded-lg p-6 flex items-center gap-4 hover:shadow-md transition-shadow group">
                    <div class="flex-shrink-0 w-12 h-12 bg-brand-50 rounded-lg flex items-center justify-center group-hover:bg-brand-100 transition-colors">
                        <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Órdenes de trabajo</p>
                        <p class="text-xs text-gray-500">Historial y gestión de OT</p>
                    </div>
                </a>
                @endcan

            </div>

            {{-- Tabla de clientes --}}
            @can('view clients')
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden"
                 x-data="{
                     search: '',
                     status: '',
                     matches(name, nit, city, state) {
                         const q = this.search.toLowerCase();
                         const textOk = !q || name.toLowerCase().includes(q) || nit.toLowerCase().includes(q) || city.toLowerCase().includes(q);
                         const statusOk = !this.status || state === this.status;
                         return textOk && statusOk;
                     }
                 }">
                <div class="px-6 py-3 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center gap-3">
                    <h2 class="text-sm font-semibold text-gray-700 shrink-0">Clientes</h2>
                    <div class="flex items-center gap-2 sm:ml-auto">
                        <div class="relative">
                            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0Z"/>
                            </svg>
                            <input type="text" x-model="search" placeholder="Buscar…"
                                   class="pl-8 pr-3 py-1.5 text-xs border border-gray-200 rounded-md focus:border-brand-400 focus:ring-1 focus:ring-brand-300 outline-none w-44 transition">
                        </div>
                        <select x-model="status"
                                class="py-1.5 pl-2 pr-7 text-xs border border-gray-200 rounded-md focus:border-brand-400 focus:ring-1 focus:ring-brand-300 outline-none transition">
                            <option value="">Todos</option>
                            <option value="active">Activo</option>
                            <option value="inactive">Inactivo</option>
                        </select>
                    </div>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Empresa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIT</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ciudad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($clients as $client)
                            @php
                                $rowStatus = $client->trashed() ? 'deleted' : ($client->is_active ? 'active' : 'inactive');
                            @endphp
                            <tr class="hover:bg-gray-50"
                                x-show="matches('{{ addslashes($client->name) }}', '{{ addslashes($client->nit ?? '') }}', '{{ addslashes($client->city ?? '') }}', '{{ $rowStatus }}')"
                                x-cloak>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        @if ($client->logoUrl())
                                            <img src="{{ $client->logoUrl() }}" alt="{{ $client->name }}"
                                                 class="h-8 w-8 rounded object-contain border border-gray-100 bg-white flex-shrink-0">
                                        @else
                                            <div class="h-8 w-8 rounded bg-brand-50 flex items-center justify-center flex-shrink-0">
                                                <span class="text-xs font-bold text-brand-600">{{ mb_strtoupper(mb_substr($client->name, 0, 2)) }}</span>
                                            </div>
                                        @endif
                                        <span class="text-sm font-medium text-gray-900">{{ $client->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $client->nit ?: '—' }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $client->city ?: '—' }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm">
                                    @if ($client->trashed())
                                        <span class="inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold text-red-800">Eliminado</span>
                                    @elseif ($client->is_active)
                                        <span class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold text-green-800">Activo</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold text-gray-700">Inactivo</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-right text-sm font-medium">
                                    @unless ($client->trashed())
                                        <a href="{{ route('admin.clients.show', $client) }}"
                                           class="text-brand-600 hover:text-brand-800">Ver</a>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-sm text-gray-400">No hay clientes registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endcan

        </div>
    </div>
</x-app-layout>
