<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Hoja de vida del equipo" :breadcrumbs="[['label' => 'Equipos', 'href' => route('admin.equipment.index')], ['label' => $equipment->name]]">
            <x-slot:actions>
                @can('update equipment')
                    <a href="{{ route('admin.equipment.edit', $equipment) }}"
                       class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">
                        {{ __('Editar') }}
                    </a>
                @endcan
            </x-slot:actions>
        </x-page-header>
    </x-slot>

    @php
        $orders = $equipment->workOrders;
        $preventiveCount = $orders->where('type', 'preventive')->count();
        $correctiveCount = $orders->where('type', 'corrective')->count();
        $lastIntervention = $orders->sortByDesc('created_at')->first();
    @endphp

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            {{-- Identidad del equipo --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">{{ $equipment->name }}</h2>
                        <p class="text-sm text-gray-500">
                            {{ optional($equipment->brand)->name }} {{ optional($equipment->model)->name }}
                            @if ($equipment->serial_number) · Serial {{ $equipment->serial_number }} @endif
                        </p>
                    </div>
                    <span @class([
                        'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold shrink-0',
                        'bg-green-100 text-green-800' => $equipment->status === 'active',
                        'bg-gray-100 text-gray-800' => $equipment->status === 'inactive',
                        'bg-amber-100 text-amber-800' => $equipment->status === 'maintenance',
                        'bg-red-100 text-red-800' => $equipment->status === 'retired',
                    ])>{{ $equipment->statusLabel() }}</span>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-3">
                    @foreach ([
                        'Cliente' => optional($equipment->client)->name ?: '—',
                        'Área' => optional($equipment->area)->name ?: '—',
                        'Tipo' => $equipment->type ?: '—',
                        'Ubicación / sede' => $equipment->location ?: '—',
                        'Fecha de ingreso' => optional($equipment->entry_date)->format('Y-m-d') ?: '—',
                        'Fecha de compra' => optional($equipment->purchase_date)->format('Y-m-d') ?: '—',
                        'Garantía' => $equipment->warrantyStatusLabel() ?: '—',
                        'Vencimiento de garantía' => optional($equipment->warranty_expiry)->format('Y-m-d') ?: '—',
                        'Clasificación por riesgo' => $equipment->riskClassLabel() ?: '—',
                        'Registro INVIMA' => $equipment->invima_registry ?: '—',
                        'Fabricante' => $equipment->manufacturer ?: '—',
                        'País de origen' => $equipment->origin_country ?: '—',
                        'Periodicidad' => $equipment->frequencyLabel() ?: '—',
                        'Tipo de adquisición' => $equipment->acquisitionTypeLabel() ?: '—',
                        'Especialidad' => $equipment->specialtyLabels() ? implode(', ', $equipment->specialtyLabels()) : '—',
                    ] as $label => $value)
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">{{ $label }}</dt>
                            <dd class="text-sm text-gray-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                    @if ($equipment->notes)
                        <div class="sm:col-span-3">
                            <dt class="text-xs font-medium text-gray-500 uppercase">Observaciones</dt>
                            <dd class="text-sm text-gray-900">{{ $equipment->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Características técnicas --}}
            @php($techFields = array_filter([
                'Voltaje' => $equipment->voltage,
                'Amperaje' => $equipment->amperage,
                'Corriente' => $equipment->current,
                'Potencia' => $equipment->power,
                'Temperatura' => $equipment->temperature,
                'Presión' => $equipment->pressure,
                'Peso' => $equipment->weight,
                'Velocidad' => $equipment->speed,
                'Tecnología predominante' => $equipment->predominant_technology,
            ]))
            @if ($techFields || $equipment->technical_observations || $equipment->general_observations)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Características técnicas</h3>
                    <dl class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-3">
                        @foreach ($techFields as $label => $value)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase">{{ $label }}</dt>
                                <dd class="text-sm text-gray-900">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    @if ($equipment->technical_observations)
                        <p class="text-sm text-gray-700 mt-4"><span class="font-medium">Obs. técnicas:</span> {{ $equipment->technical_observations }}</p>
                    @endif
                    @if ($equipment->general_observations)
                        <p class="text-sm text-gray-700 mt-1"><span class="font-medium">Obs. generales:</span> {{ $equipment->general_observations }}</p>
                    @endif
                </div>
            @endif

            {{-- Plantilla de mantenimiento y accesorios --}}
            @if ($equipment->maintenance_tasks || $equipment->accessories || $equipment->components)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-semibold text-gray-900 mb-3">Subtareas de mantenimiento</h3>
                        @if ($equipment->maintenance_tasks)
                            <ul class="grid grid-cols-1 gap-1 text-sm text-gray-700">
                                @foreach ($equipment->maintenance_tasks as $key)
                                    <li class="flex items-center gap-2"><span class="text-brand-600">✓</span>{{ \App\Models\Equipment::MAINTENANCE_TASKS[$key] ?? $key }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-400">Sin subtareas definidas.</p>
                        @endif
                    </div>
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-semibold text-gray-900 mb-3">Accesorios</h3>
                        @if ($equipment->accessories)
                            <ul class="grid grid-cols-1 gap-1 text-sm text-gray-700">
                                @foreach ($equipment->accessories as $key)
                                    <li class="flex items-center gap-2"><span class="text-brand-600">✓</span>{{ \App\Models\Equipment::ACCESSORIES[$key] ?? $key }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-400">Sin accesorios definidos.</p>
                        @endif
                        @if ($equipment->components)
                            <p class="text-sm text-gray-700 mt-3"><span class="font-medium">Detalle:</span> {{ $equipment->components }}</p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Resumen de intervenciones --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <p class="text-2xl font-bold text-gray-900">{{ $orders->count() }}</p>
                    <p class="text-xs text-gray-500 uppercase">Intervenciones</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <p class="text-2xl font-bold text-gray-900">{{ $preventiveCount }}</p>
                    <p class="text-xs text-gray-500 uppercase">Preventivas</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <p class="text-2xl font-bold text-gray-900">{{ $correctiveCount }}</p>
                    <p class="text-xs text-gray-500 uppercase">Correctivas</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm font-bold text-gray-900">{{ optional(optional($lastIntervention)->created_at)->format('Y-m-d') ?: '—' }}</p>
                    <p class="text-xs text-gray-500 uppercase">Última intervención</p>
                </div>
            </div>

            {{-- Historial de intervenciones --}}
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Historial de intervenciones</h3>
                    @can('create work_orders')
                        <a href="{{ route('admin.work_orders.create', ['client_id' => $equipment->client_id, 'equipment_id' => $equipment->id]) }}"
                           class="text-sm text-brand-600 hover:text-brand-800 font-medium">+ Nueva orden</a>
                    @endcan
                </div>

                @forelse ($orders as $order)
                    <div class="px-6 py-4 border-b border-gray-100 last:border-b-0">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('admin.work_orders.show', $order) }}"
                                       class="text-sm font-semibold text-brand-700 hover:underline">{{ $order->code }}</a>
                                    <span @class([
                                        'inline-flex rounded-full px-2 text-xs font-semibold',
                                        'bg-sky-100 text-sky-800' => $order->type === 'preventive',
                                        'bg-orange-100 text-orange-800' => $order->type === 'corrective',
                                    ])>{{ $order->typeLabel() }}</span>
                                    <span @class([
                                        'inline-flex rounded-full px-2 text-xs font-semibold',
                                        'bg-gray-100 text-gray-800' => $order->status === 'open',
                                        'bg-indigo-100 text-indigo-800' => $order->status === 'assigned',
                                        'bg-amber-100 text-amber-800' => $order->status === 'in_progress',
                                        'bg-green-100 text-green-800' => $order->status === 'completed',
                                        'bg-brand-100 text-brand-800' => $order->status === 'closed',
                                        'bg-red-100 text-red-800' => $order->status === 'cancelled',
                                    ])>{{ $order->statusLabel() }}</span>
                                </div>
                                <p class="text-sm text-gray-900 mt-1">{{ $order->title }}</p>
                                @if ($order->diagnosis)
                                    <p class="text-xs text-gray-500 mt-1"><span class="font-medium">Diagnóstico:</span> {{ $order->diagnosis }}</p>
                                @endif
                                @if ($order->work_performed)
                                    <p class="text-xs text-gray-500 mt-0.5"><span class="font-medium">Trabajo realizado:</span> {{ $order->work_performed }}</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ optional($order->technician)->name ?? 'Sin técnico' }}
                                </p>
                            </div>
                            <div class="text-right text-xs text-gray-400 shrink-0">
                                {{ $order->created_at->format('Y-m-d') }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-gray-500">
                        Este equipo aún no tiene intervenciones registradas.
                    </div>
                @endforelse
            </div>

            <div>
                <a href="{{ route('admin.equipment.index') }}" class="text-sm text-gray-600 hover:text-gray-900">← Volver a equipos</a>
            </div>
        </div>
    </div>
</x-app-layout>
