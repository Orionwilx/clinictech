<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="$client->name"
                       :breadcrumbs="[['label' => 'Clientes', 'href' => route('admin.clients.index')], ['label' => $client->name]]">
            <x-slot:actions>
                @can('update clients')
                    <a href="{{ route('admin.clients.edit', $client) }}"
                       class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">
                        {{ __('Editar') }}
                    </a>
                @endcan
            </x-slot:actions>
        </x-page-header>
    </x-slot>

    <div class="py-12" x-data="{ tab: 'datos' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Pestañas (segmentado prominente) --}}
            <div class="mb-6 inline-flex flex-wrap gap-1 rounded-xl bg-gray-100 p-1" role="tablist" aria-label="Secciones del cliente">
                @php($tabs = [
                    'datos' => ['Datos', null],
                    'equipos' => ['Equipos', $client->equipment->count()],
                    'ordenes' => ['Órdenes', $client->workOrders->count()],
                ])
                @foreach ($tabs as $key => [$label, $count])
                    <button type="button" @click="tab = '{{ $key }}'" role="tab"
                            :class="tab === '{{ $key }}' ? 'bg-white text-brand-700 shadow-sm' : 'text-gray-500 hover:text-gray-800'"
                            class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-colors">
                        {{ $label }}
                        @if (! is_null($count))
                            <span :class="tab === '{{ $key }}' ? 'bg-brand-100 text-brand-700' : 'bg-gray-200 text-gray-600'"
                                  class="inline-flex items-center justify-center rounded-full px-2 py-0.5 text-xs font-semibold">{{ $count }}</span>
                        @endif
                    </button>
                @endforeach
            </div>

            {{-- Datos --}}
            <div x-show="tab === 'datos'" x-cloak class="bg-white shadow-sm sm:rounded-lg p-6">
                <dl class="divide-y divide-gray-100">
                    @foreach ([
                        'Empresa' => $client->name,
                        'NIT' => $client->nit,
                        'Correo' => $client->email,
                        'Ciudad' => $client->city ?: '—',
                        'País' => $client->country ?: '—',
                        'WhatsApp' => $client->whatsapp ?: '—',
                        'Celular' => $client->phone ?: '—',
                        'Usuario de acceso' => optional($client->user)->name ?: '—',
                        'Estado' => $client->is_active ? 'Activo' : 'Inactivo',
                    ] as $label => $value)
                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">{{ $label }}</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            {{-- Equipos --}}
            <div x-show="tab === 'equipos'" x-cloak>
                <div class="flex justify-end mb-3">
                    @can('create equipment')
                        <a href="{{ route('admin.equipment.create', ['client_id' => $client->id]) }}"
                           class="inline-flex items-center px-3 py-2 bg-brand-600 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">+ Nuevo equipo</a>
                    @endcan
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Equipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Serial</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($client->equipment as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->name }}
                                        <span class="block text-xs text-gray-400">{{ $item->brand }} {{ $item->model }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->serial_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->statusLabel() }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <a href="{{ route('admin.equipment.show', $item) }}" class="text-brand-600 hover:text-brand-800">Ver</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Este cliente no tiene equipos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Órdenes de trabajo (incluye mantenimientos: OT tipo preventivo/correctivo) --}}
            <div x-show="tab === 'ordenes'" x-cloak>
                <div class="flex justify-end gap-2 mb-3">
                    @can('create work_orders')
                        <a href="{{ route('admin.work_orders.create', ['client_id' => $client->id, 'type' => 'preventive']) }}"
                           class="inline-flex items-center px-3 py-2 bg-white border border-brand-600 rounded-md font-semibold text-xs text-brand-700 uppercase tracking-widest hover:bg-brand-50">+ OT preventiva</a>
                        <a href="{{ route('admin.work_orders.create', ['client_id' => $client->id, 'type' => 'corrective']) }}"
                           class="inline-flex items-center px-3 py-2 bg-brand-600 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">+ OT correctiva</a>
                    @endcan
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nº / Asunto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Equipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Técnico</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($client->workOrders as $order)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->code }}
                                        <span class="block text-xs text-gray-400">{{ $order->title }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->typeLabel() }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($order->equipment)->name ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($order->technician)->name ?? 'Sin asignar' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->statusLabel() }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <a href="{{ route('admin.work_orders.show', $order) }}" class="text-brand-600 hover:text-brand-800">Ver</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Este cliente no tiene órdenes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
