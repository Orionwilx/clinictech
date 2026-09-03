<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="$technician->name"
                       :breadcrumbs="[['label' => 'Técnicos', 'href' => route('admin.technicians.index')], ['label' => $technician->name]]">
            <x-slot:actions>
                @can('update technicians')
                    <a href="{{ route('admin.technicians.edit', $technician) }}"
                       class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">
                        {{ __('Editar') }}
                    </a>
                @endcan
            </x-slot:actions>
        </x-page-header>
    </x-slot>

    <div class="py-12" x-data="{ tab: '{{ request('tab', 'datos') }}' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            {{-- Pestañas --}}
            <div class="mb-6 inline-flex flex-wrap gap-1 rounded-xl bg-gray-100 p-1" role="tablist">
                @php($tabs = [
                    'datos'    => ['Datos', null],
                    'ordenes'  => ['Órdenes', $technician->workOrders->count()],
                    'equipos'  => ['Equipos trabajados', $workedEquipment->count()],
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
                        'Nombre'           => $technician->name,
                        'Documento'        => $technician->document,
                        'Correo'           => $technician->email,
                        'Celular'          => $technician->phone ?: '—',
                        'Especialidad'     => $technician->specialty ?: '—',
                        'Usuario de acceso'=> optional($technician->user)->name ?: '—',
                        'Estado'           => $technician->is_active ? 'Activo' : 'Inactivo',
                    ] as $label => $value)
                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">{{ $label }}</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            {{-- Órdenes de trabajo --}}
            <div x-show="tab === 'ordenes'" x-cloak>
                <div class="flex justify-end mb-3">
                    @can('create work_orders')
                        <a href="{{ route('admin.work_orders.create', ['technician_id' => $technician->id]) }}"
                           class="inline-flex items-center px-3 py-2 bg-brand-600 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">+ Nueva OT</a>
                    @endcan
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nº / Asunto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Equipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($technician->workOrders->sortByDesc('created_at') as $order)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->code }}
                                        <span class="block text-xs text-gray-400">{{ $order->title }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($order->client)->name ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($order->equipment)->name ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->typeLabel() }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span @class([
                                            'inline-flex rounded-full px-2 text-xs font-semibold',
                                            'bg-amber-100 text-amber-800' => in_array($order->status, \App\Models\WorkOrder::ACTIVE_STATUSES),
                                            'bg-green-100 text-green-800' => $order->status === 'completed',
                                            'bg-gray-100 text-gray-800'   => in_array($order->status, ['closed', 'cancelled']),
                                        ])>{{ $order->statusLabel() }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <a href="{{ route('admin.work_orders.show', $order) }}" class="text-brand-600 hover:text-brand-800">Ver</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Este técnico no tiene órdenes de trabajo.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Equipos trabajados --}}
            <div x-show="tab === 'equipos'" x-cloak>
                <p class="mb-4 text-sm text-gray-500">Equipos en los que este técnico ha tenido al menos una orden de trabajo.</p>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Equipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marca / Modelo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Serial</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">OT</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($workedEquipment as $item)
                                @php
                                    $otCount = $technician->workOrders->where('equipment_id', $item->id)->count();
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ optional($item->brand)->name }} {{ optional($item->model)->name ?: '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->serial_number ?: '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($item->client)->name ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span @class([
                                            'inline-flex rounded-full px-2 text-xs font-semibold',
                                            'bg-green-100 text-green-800'  => $item->status === 'active',
                                            'bg-gray-100 text-gray-800'    => $item->status === 'inactive',
                                            'bg-amber-100 text-amber-800'  => $item->status === 'maintenance',
                                            'bg-red-100 text-red-800'      => $item->status === 'retired',
                                        ])>{{ $item->statusLabel() }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">{{ $otCount }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <a href="{{ route('admin.equipment.show', $item) }}" class="text-brand-600 hover:text-brand-800">Hoja de vida</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Sin equipos trabajados aún.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
