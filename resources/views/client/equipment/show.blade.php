<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Hoja de vida del equipo"
            :breadcrumbs="[['label' => 'Panel', 'href' => route('client.dashboard')], ['label' => 'Equipos', 'href' => route('client.equipment.index')], ['label' => $equipment->name]]" />
    </x-slot>

    @php
        $orders = $equipment->workOrders;
        $preventiveCount = $orders->where('type', 'preventive')->count();
        $correctiveCount = $orders->where('type', 'corrective')->count();
        $lastIntervention = $orders->sortByDesc('created_at')->first();
    @endphp

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

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
                        'Área' => optional($equipment->area)->name ?: '—',
                        'Tipo' => $equipment->type ?: '—',
                        'Ubicación / sede' => $equipment->location ?: '—',
                        'Clasificación por riesgo' => $equipment->riskClassLabel() ?: '—',
                        'Registro INVIMA' => $equipment->invima_registry ?: '—',
                        'Fabricante' => $equipment->manufacturer ?: '—',
                        'País de origen' => $equipment->origin_country ?: '—',
                        'Periodicidad' => $equipment->frequencyLabel() ?: '—',
                        'Especialidad' => $equipment->specialtyLabels() ? implode(', ', $equipment->specialtyLabels()) : '—',
                    ] as $label => $value)
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">{{ $label }}</dt>
                            <dd class="text-sm text-gray-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

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
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Historial de intervenciones</h3>
                </div>

                @forelse ($orders as $order)
                    <div class="px-6 py-4 border-b border-gray-100 last:border-b-0">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('client.work_orders.show', $order) }}"
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
                                <p class="text-xs text-gray-400 mt-1">{{ optional($order->technician)->name ?? 'Sin técnico' }}</p>
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
                <a href="{{ route('client.equipment.index') }}" class="text-sm text-gray-600 hover:text-gray-900">← Volver a equipos</a>
            </div>
        </div>
    </div>
</x-app-layout>
