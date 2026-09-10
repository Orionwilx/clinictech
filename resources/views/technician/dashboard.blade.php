<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Mi panel" :breadcrumbs="[['label' => 'Panel']]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Métricas --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-3xl font-bold text-brand-600">{{ $pending->count() }}</p>
                    <p class="text-xs text-gray-500 uppercase mt-1">Pendientes</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-3xl font-bold text-amber-600">{{ $forReview }}</p>
                    <p class="text-xs text-gray-500 uppercase mt-1">En revisión admin</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-3xl font-bold text-gray-900">{{ $completed }}</p>
                    <p class="text-xs text-gray-500 uppercase mt-1">Completadas</p>
                </div>
            </div>

            {{-- OTs pendientes --}}
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">Órdenes pendientes</h3>
                    <a href="{{ route('technician.work_orders.index') }}" class="text-sm text-brand-600 hover:underline">Ver todas</a>
                </div>

                @forelse ($pending as $order)
                    <div class="px-6 py-4 border-b border-gray-100 last:border-b-0">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('technician.work_orders.show', $order) }}"
                                       class="text-sm font-semibold text-brand-700 hover:underline">{{ $order->code }}</a>
                                    <span @class([
                                        'inline-flex rounded-full px-2 text-xs font-semibold',
                                        'bg-sky-100 text-sky-800' => $order->type === 'preventive',
                                        'bg-orange-100 text-orange-800' => $order->type === 'corrective',
                                    ])>{{ $order->typeLabel() }}</span>
                                    <span @class([
                                        'inline-flex rounded-full px-2 text-xs font-semibold',
                                        'bg-indigo-100 text-indigo-800' => $order->status === 'assigned',
                                        'bg-amber-100 text-amber-800' => $order->status === 'in_progress',
                                    ])>{{ $order->statusLabel() }}</span>
                                </div>
                                <p class="text-sm text-gray-900 mt-1">{{ $order->title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ optional($order->client)->name }}
                                    @if ($order->equipment) · {{ $order->equipment->name }} @endif
                                </p>
                            </div>
                            <a href="{{ route('technician.work_orders.show', $order) }}"
                               class="shrink-0 text-xs text-brand-600 hover:underline font-medium">Diligenciar →</a>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-gray-500">No tienes órdenes pendientes.</div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
