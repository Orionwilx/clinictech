<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Mi panel" :breadcrumbs="[['label' => 'Panel']]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Cabecera del cliente --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6 flex items-center gap-6">
                @if ($client->logo_path)
                    <img src="{{ Storage::url($client->logo_path) }}" alt="{{ $client->name }}"
                         class="h-16 w-16 rounded-full object-cover shrink-0">
                @else
                    <div class="h-16 w-16 rounded-full bg-brand-100 flex items-center justify-center shrink-0">
                        <span class="text-2xl font-bold text-brand-600">{{ mb_substr($client->name, 0, 1) }}</span>
                    </div>
                @endif
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $client->name }}</h2>
                    @if ($client->nit) <p class="text-sm text-gray-500">NIT {{ $client->nit }}</p> @endif
                    @if ($client->city) <p class="text-sm text-gray-400">{{ $client->city }}{{ $client->country ? ', '.$client->country : '' }}</p> @endif
                </div>
            </div>

            {{-- Métricas rápidas --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-3xl font-bold text-gray-900">{{ $equipmentCount }}</p>
                    <p class="text-xs text-gray-500 uppercase mt-1">Equipos</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-3xl font-bold text-brand-600">{{ $openOrdersCount }}</p>
                    <p class="text-xs text-gray-500 uppercase mt-1">Órdenes activas</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm font-bold text-gray-900">
                        {{ optional(optional($lastOrder)->created_at)->format('Y-m-d') ?: '—' }}
                    </p>
                    <p class="text-xs text-gray-500 uppercase mt-1">Última OT</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-3xl font-bold {{ $overdue->count() > 0 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $overdue->count() }}
                    </p>
                    <p class="text-xs text-gray-500 uppercase mt-1">Mant. vencidos</p>
                </div>
            </div>

            {{-- Última orden de trabajo --}}
            @if ($lastOrder)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Última orden de trabajo</h3>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('client.work_orders.show', $lastOrder) }}"
                                   class="font-semibold text-brand-700 hover:underline text-sm">{{ $lastOrder->code }}</a>
                                <span @class([
                                    'inline-flex rounded-full px-2 text-xs font-semibold',
                                    'bg-sky-100 text-sky-800' => $lastOrder->type === 'preventive',
                                    'bg-orange-100 text-orange-800' => $lastOrder->type === 'corrective',
                                ])>{{ $lastOrder->typeLabel() }}</span>
                                <span @class([
                                    'inline-flex rounded-full px-2 text-xs font-semibold',
                                    'bg-gray-100 text-gray-800' => $lastOrder->status === 'open',
                                    'bg-indigo-100 text-indigo-800' => $lastOrder->status === 'assigned',
                                    'bg-amber-100 text-amber-800' => $lastOrder->status === 'in_progress',
                                    'bg-green-100 text-green-800' => $lastOrder->status === 'completed',
                                    'bg-brand-100 text-brand-800' => $lastOrder->status === 'closed',
                                    'bg-red-100 text-red-800' => $lastOrder->status === 'cancelled',
                                ])>{{ $lastOrder->statusLabel() }}</span>
                            </div>
                            <p class="text-sm text-gray-900 mt-1">{{ $lastOrder->title }}</p>
                            @if ($lastOrder->equipment)
                                <p class="text-xs text-gray-400 mt-0.5">Equipo: {{ $lastOrder->equipment->name }}</p>
                            @endif
                        </div>
                        <span class="text-xs text-gray-400 shrink-0">{{ $lastOrder->created_at->format('Y-m-d') }}</span>
                    </div>
                </div>
            @endif

            {{-- Equipos con mantenimiento vencido --}}
            @if ($overdue->count() > 0)
                <div class="bg-red-50 border border-red-200 shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-red-800 mb-3">Equipos con mantenimiento vencido</h3>
                    <ul class="divide-y divide-red-100">
                        @foreach ($overdue as $eq)
                            <li class="py-2 flex items-center justify-between gap-4">
                                <div>
                                    <a href="{{ route('client.equipment.show', $eq) }}"
                                       class="text-sm font-medium text-red-700 hover:underline">{{ $eq->name }}</a>
                                    <p class="text-xs text-red-500">Frecuencia: {{ $eq->frequencyLabel() }}</p>
                                </div>
                                <span class="text-xs text-red-500">
                                    Última preventiva: {{ optional(optional($eq->workOrders->first())->created_at)->format('Y-m-d') ?: 'Nunca' }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Accesos rápidos --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach ([
                    ['label' => 'Mis equipos', 'route' => 'client.equipment.index', 'icon' => 'M4 7h16M4 7v10a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7M4 7l2-3h12l2 3M9 12h6'],
                    ['label' => 'Mis OT', 'route' => 'client.work_orders.index', 'icon' => 'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9 2 2 4-4'],
                    ['label' => 'Mis técnicos', 'route' => 'client.technicians.index', 'icon' => 'M12 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM5 20a7 7 0 0 1 14 0M17 8l1.5-1.5M18.5 3.5 20 2'],
                ] as $link)
                    <a href="{{ route($link['route']) }}"
                       class="bg-white shadow-sm sm:rounded-lg p-5 flex flex-col items-center gap-3 hover:bg-brand-50 hover:border-brand-200 border border-transparent transition-colors">
                        <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700">{{ $link['label'] }}</span>
                    </a>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>
