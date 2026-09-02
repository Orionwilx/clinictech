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

    <div class="py-12" x-data="{ tab: '{{ request('tab', 'datos') }}' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @if ($client->logoUrl())
                <div class="mb-6 flex items-center gap-4">
                    <img src="{{ $client->logoUrl() }}" alt="Logo de {{ $client->name }}"
                         class="h-16 w-16 rounded-lg object-contain border border-gray-200 bg-white p-1.5">
                    <div>
                        <p class="text-lg font-bold text-gray-900">{{ $client->name }}</p>
                        <p class="text-sm text-gray-500">NIT {{ $client->nit }}</p>
                    </div>
                </div>
            @endif

            {{-- Pestañas (segmentado prominente) --}}
            <div class="mb-6 inline-flex flex-wrap gap-1 rounded-xl bg-gray-100 p-1" role="tablist" aria-label="Secciones del cliente">
                @php($tabs = [
                    'datos' => ['Datos', null],
                    'areas' => ['Áreas', $client->areas->count()],
                    'equipos' => ['Equipos', $client->equipment->count()],
                    'ordenes' => ['Órdenes', $client->workOrders->count()],
                    'pendientes' => ['OT pendientes', $pendingEquipment->count()],
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

            {{-- Áreas (gestión en línea) --}}
            <div x-show="tab === 'areas'" x-cloak class="bg-white shadow-sm sm:rounded-lg p-6">
                @can('create areas')
                    <form action="{{ route('admin.clients.areas.store', $client) }}" method="POST" class="flex flex-wrap items-end gap-3 mb-6">
                        @csrf
                        <div class="flex-1 min-w-[12rem]">
                            <x-input-label for="area_name" :value="__('Nueva área')" />
                            <x-text-input id="area_name" name="name" type="text" class="mt-1 block w-full"
                                          :value="old('name')" placeholder="Ej. UCI, Urgencias…" required />
                        </div>
                        <div class="flex-1 min-w-[12rem]">
                            <x-input-label for="area_description" :value="__('Descripción (opcional)')" />
                            <x-text-input id="area_description" name="description" type="text" class="mt-1 block w-full"
                                          :value="old('description')" />
                        </div>
                        <x-primary-button>{{ __('Agregar') }}</x-primary-button>
                    </form>
                    <x-input-error :messages="$errors->get('name')" class="mb-4" />
                @endcan

                <div class="overflow-hidden border border-gray-100 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Área</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Equipos</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($client->areas as $area)
                                <tr x-data="{ editing: false }">
                                    {{-- Vista --}}
                                    <td class="px-6 py-4 text-sm text-gray-900" x-show="!editing">{{ $area->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500" x-show="!editing">{{ $area->description ?: '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500" x-show="!editing">{{ $area->equipment_count }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-medium space-x-2" x-show="!editing">
                                        @can('update areas')
                                            <button type="button" @click="editing = true" class="text-brand-600 hover:text-brand-800">Editar</button>
                                        @endcan
                                        @can('delete areas')
                                            <form action="{{ route('admin.areas.destroy', $area) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('¿Eliminar esta área? Los equipos quedarán sin área.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                            </form>
                                        @endcan
                                    </td>
                                    {{-- Edición en línea --}}
                                    <td colspan="4" class="px-6 py-4" x-show="editing" x-cloak>
                                        <form action="{{ route('admin.areas.update', $area) }}" method="POST" class="flex flex-wrap items-center gap-3">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="name" value="{{ $area->name }}" required
                                                   class="flex-1 min-w-[10rem] border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm" />
                                            <input type="text" name="description" value="{{ $area->description }}" placeholder="Descripción"
                                                   class="flex-1 min-w-[10rem] border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm" />
                                            <x-primary-button>{{ __('Guardar') }}</x-primary-button>
                                            <button type="button" @click="editing = false" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Este cliente no tiene áreas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Área</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($client->equipment as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->name }}
                                        <span class="block text-xs text-gray-400">{{ optional($item->brand)->name }} {{ optional($item->model)->name }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->serial_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($item->area)->name ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->statusLabel() }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <a href="{{ route('admin.equipment.show', $item) }}" class="text-brand-600 hover:text-brand-800">Ver</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Este cliente no tiene equipos.</td></tr>
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

            {{-- OT pendientes / en proceso (por equipo) --}}
            <div x-show="tab === 'pendientes'" x-cloak>
                <p class="mb-4 text-sm text-gray-500">Equipos con órdenes de trabajo abiertas, asignadas o en proceso.</p>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Equipo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Área</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marca / Modelo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">N. Serie</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Obs. técnicas</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">OT pendientes</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Opciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($pendingEquipment as $item)
                                <tr>
                                    <td class="px-4 py-4 text-sm text-gray-900">{{ $item->name }}
                                        <span class="block text-xs text-gray-400">{{ $item->type }}</span>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-500">{{ optional($item->area)->name ?: '—' }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-500">{{ optional($item->brand)->name }} {{ optional($item->model)->name }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-500">{{ $item->serial_number }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm">
                                        <span @class([
                                            'inline-flex rounded-full px-2 text-xs font-semibold',
                                            'bg-green-100 text-green-800' => $item->status === 'active',
                                            'bg-gray-100 text-gray-800' => $item->status === 'inactive',
                                            'bg-amber-100 text-amber-800' => $item->status === 'maintenance',
                                            'bg-red-100 text-red-800' => $item->status === 'retired',
                                        ])>{{ $item->statusLabel() }}</span>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $item->technical_observations }}">{{ $item->technical_observations ?: '—' }}</td>
                                    <td class="px-4 py-4 text-sm">
                                        <span class="inline-flex items-center justify-center rounded-full bg-amber-100 text-amber-800 px-2 py-0.5 text-xs font-semibold">{{ $item->pending_count }}</span>
                                        <div class="mt-1 space-y-0.5">
                                            @foreach ($item->workOrders as $order)
                                                <a href="{{ route('admin.work_orders.show', $order) }}" class="block text-xs text-brand-600 hover:underline">{{ $order->code }} · {{ $order->statusLabel() }}</a>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm">
                                        <a href="{{ route('admin.equipment.show', $item) }}" class="text-brand-600 hover:text-brand-800">Hoja de vida</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-4 py-4 text-center text-sm text-gray-500">No hay equipos con OT pendientes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
