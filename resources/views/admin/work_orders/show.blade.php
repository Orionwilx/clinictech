<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Orden de trabajo') }} · {{ $workOrder->code }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <dl class="divide-y divide-gray-100">
                    @foreach ([
                        'Nº de orden' => $workOrder->code,
                        'Asunto' => $workOrder->title,
                        'Cliente' => optional($workOrder->client)->name ?? '—',
                        'Equipo' => optional($workOrder->equipment)->name ?? '—',
                        'Técnico asignado' => optional($workOrder->technician)->name ?? 'Sin asignar',
                        'Tipo' => $workOrder->typeLabel(),
                        'Prioridad' => $workOrder->priorityLabel(),
                        'Estado' => $workOrder->statusLabel(),
                        'Descripción' => $workOrder->description ?: '—',
                        'Diagnóstico' => $workOrder->diagnosis ?: '—',
                        'Actividades realizadas' => $workOrder->work_performed ?: '—',
                        'Fecha programada' => optional($workOrder->scheduled_at)->format('Y-m-d H:i') ?: '—',
                        'Inicio' => optional($workOrder->started_at)->format('Y-m-d H:i') ?: '—',
                        'Completada' => optional($workOrder->completed_at)->format('Y-m-d H:i') ?: '—',
                        'Cerrada' => optional($workOrder->closed_at)->format('Y-m-d H:i') ?: '—',
                    ] as $label => $value)
                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">{{ $label }}</dt>
                            <dd class="text-sm text-gray-900 col-span-2 whitespace-pre-line">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>

                <div class="flex items-center gap-4 mt-6">
                    @can('update work_orders')
                        <a href="{{ route('admin.work_orders.edit', $workOrder) }}"
                           class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">
                            {{ __('Editar') }}
                        </a>
                    @endcan
                    <a href="{{ route('admin.work_orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Volver</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
