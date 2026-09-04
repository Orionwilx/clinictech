<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="'Orden '.$workOrder->code" :breadcrumbs="[['label' => 'Órdenes de trabajo', 'href' => route('admin.work_orders.index')], ['label' => $workOrder->code]]" />
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

                @if ($workOrder->maintenance_tasks || $workOrder->accessories_checked)
                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">Subtareas ejecutadas</h3>
                            @if ($workOrder->maintenance_tasks)
                                <ul class="grid grid-cols-1 gap-1 text-sm text-gray-700">
                                    @foreach ($workOrder->maintenance_tasks as $key)
                                        <li class="flex items-center gap-2"><span class="text-brand-600">✓</span>{{ \App\Models\Equipment::MAINTENANCE_TASKS[$key] ?? $key }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-400">—</p>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">Accesorios revisados</h3>
                            @if ($workOrder->accessories_checked)
                                <ul class="grid grid-cols-1 gap-1 text-sm text-gray-700">
                                    @foreach ($workOrder->accessories_checked as $key)
                                        <li class="flex items-center gap-2"><span class="text-brand-600">✓</span>{{ \App\Models\Equipment::ACCESSORIES[$key] ?? $key }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-400">—</p>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($workOrder->additional_observations)
                    <div class="mt-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">Observaciones adicionales</h3>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $workOrder->additional_observations }}</p>
                    </div>
                @endif

                {{-- Acciones según estado del flujo colaborativo --}}
                <div class="mt-6 space-y-3">

                    {{-- Solicitud del cliente (draft) --}}
                    @if ($workOrder->status === 'draft')
                        @if ($workOrder->requested_by_client)
                            <div class="rounded-md bg-blue-50 border border-blue-200 p-4">
                                <p class="text-sm font-semibold text-blue-800 mb-3">Solicitud del cliente — pendiente de revisión</p>
                                <div class="flex flex-wrap items-start gap-4">
                                    <form method="POST" action="{{ route('admin.work_orders.approve-request', $workOrder) }}" class="flex items-end gap-2">
                                        @csrf
                                        <div>
                                            <label class="block text-xs text-blue-700 mb-1">Asignar técnico (opcional)</label>
                                            <x-searchable-select name="technician_id"
                                                :options="\App\Models\Technician::orderBy('name')->pluck('name','id')"
                                                placeholder="— Sin asignar —" />
                                        </div>
                                        <button type="submit"
                                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 shrink-0">
                                            Aprobar solicitud
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.work_orders.reject-request', $workOrder) }}" class="flex items-end gap-2">
                                        @csrf
                                        <div>
                                            <label class="block text-xs text-blue-700 mb-1">Motivo del rechazo</label>
                                            <x-text-input name="rejection_reason" type="text" class="block" placeholder="Opcional…" />
                                        </div>
                                        <button type="submit"
                                                onclick="return confirm('¿Rechazar esta solicitud?')"
                                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 shrink-0">
                                            Rechazar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- Trabajo del técnico listo para revisión --}}
                    @if ($workOrder->status === 'pending_review')
                        <div class="rounded-md bg-purple-50 border border-purple-200 p-4">
                            <p class="text-sm font-semibold text-purple-800 mb-3">El técnico completó el formulario — revisión pendiente</p>
                            <div class="flex flex-wrap items-start gap-4">
                                <form method="POST" action="{{ route('admin.work_orders.approve-work', $workOrder) }}">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                        Aprobar trabajo
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.work_orders.reject-work', $workOrder) }}" class="flex items-end gap-2">
                                    @csrf
                                    <div>
                                        <label class="block text-xs text-purple-700 mb-1">Motivo de devolución <span class="text-red-500">*</span></label>
                                        <x-text-input name="rejection_reason" type="text" class="block" placeholder="Indica qué debe corregir el técnico…" required />
                                    </div>
                                    <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-orange-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-600 shrink-0">
                                        Devolver al técnico
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    {{-- OT cerrada, lista para enviar al cliente --}}
                    @if ($workOrder->status === 'closed' && ! $workOrder->visible_to_client)
                        <div class="rounded-md bg-green-50 border border-green-200 p-4">
                            <p class="text-sm font-semibold text-green-800 mb-2">OT aprobada — pendiente de envío al cliente</p>
                            <form method="POST" action="{{ route('admin.work_orders.send-to-client', $workOrder) }}">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">
                                    Enviar al cliente
                                </button>
                            </form>
                        </div>
                    @endif

                    @if ($workOrder->visible_to_client)
                        <div class="rounded-md bg-gray-50 border border-gray-200 px-4 py-3">
                            <span class="text-sm text-gray-600">✓ Esta OT ya fue enviada al cliente.</span>
                        </div>
                    @endif

                    {{-- Acciones estándar --}}
                    <div class="flex items-center gap-4 pt-2">
                        <a href="{{ route('admin.work_orders.pdf', $workOrder) }}" target="_blank"
                           class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Generar PDF
                        </a>
                        @can('update work_orders')
                            <a href="{{ route('admin.work_orders.edit', $workOrder) }}"
                               class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">
                                Editar
                            </a>
                        @endcan
                        <a href="{{ route('admin.work_orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
