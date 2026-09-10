<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="'Orden '.$workOrder->code"
            :breadcrumbs="[['label' => 'Panel', 'href' => route('technician.dashboard')], ['label' => 'Órdenes', 'href' => route('technician.work_orders.index')], ['label' => $workOrder->code]]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            {{-- Motivo de devolución --}}
            @if ($workOrder->rejection_reason && $workOrder->status === 'in_progress')
                <div class="rounded-md bg-red-50 border border-red-200 p-4">
                    <p class="text-sm font-semibold text-red-800">El administrador devolvió esta orden:</p>
                    <p class="text-sm text-red-700 mt-1">{{ $workOrder->rejection_reason }}</p>
                </div>
            @endif

            {{-- Datos de la OT --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <dl class="divide-y divide-gray-100">
                    @foreach ([
                        'Nº de orden' => $workOrder->code,
                        'Estado' => $workOrder->statusLabel(),
                        'Tipo' => $workOrder->typeLabel(),
                        'Prioridad' => $workOrder->priorityLabel(),
                        'Cliente' => optional($workOrder->client)->name ?? '—',
                        'Equipo' => optional($workOrder->equipment)->name ?? '—',
                        'Fecha programada' => optional($workOrder->scheduled_at)->format('Y-m-d H:i') ?: '—',
                    ] as $label => $value)
                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">{{ $label }}</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
                @if ($workOrder->description)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs font-medium text-gray-500 uppercase mb-1">Descripción de la solicitud</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $workOrder->description }}</p>
                    </div>
                @endif
            </div>

            {{-- Formulario de diligenciamiento (solo si está en progreso o asignado) --}}
            @if (in_array($workOrder->status, ['assigned', 'in_progress']))
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Diligenciar formulario de mantenimiento</h3>

                    <form method="POST" action="{{ route('technician.work_orders.update', $workOrder) }}">
                        @csrf @method('PUT')

                        <div class="space-y-4">
                            <div>
                                <x-input-label for="diagnosis" value="Diagnóstico" />
                                <textarea id="diagnosis" name="diagnosis" rows="3"
                                          class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">{{ old('diagnosis', $workOrder->diagnosis) }}</textarea>
                            </div>
                            <div>
                                <x-input-label for="work_performed" value="Actividades realizadas / solución" />
                                <textarea id="work_performed" name="work_performed" rows="3"
                                          class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">{{ old('work_performed', $workOrder->work_performed) }}</textarea>
                            </div>
                            <div>
                                <x-input-label for="additional_observations" value="Observaciones adicionales" />
                                <textarea id="additional_observations" name="additional_observations" rows="2"
                                          class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">{{ old('additional_observations', $workOrder->additional_observations) }}</textarea>
                            </div>

                            @if ($workOrder->equipment)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2 border-t border-gray-100">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase mb-2">Subtareas ejecutadas</p>
                                    @foreach (\App\Models\Equipment::MAINTENANCE_TASKS as $value => $label)
                                        <label class="flex items-center gap-2 text-sm text-gray-700 mb-1">
                                            <input type="checkbox" name="maintenance_tasks[]" value="{{ $value }}"
                                                   @checked(in_array($value, (array) $workOrder->maintenance_tasks))
                                                   class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase mb-2">Accesorios revisados</p>
                                    @foreach (\App\Models\Equipment::ACCESSORIES as $value => $label)
                                        <label class="flex items-center gap-2 text-sm text-gray-700 mb-1">
                                            <input type="checkbox" name="accessories_checked[]" value="{{ $value }}"
                                                   @checked(in_array($value, (array) $workOrder->accessories_checked))
                                                   class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-3 mt-6">
                            <x-primary-button>Guardar borrador</x-primary-button>
                        </div>
                    </form>

                    {{-- Botón enviar a revisión --}}
                    @if ($workOrder->status === 'in_progress')
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <form method="POST" action="{{ route('technician.work_orders.submit', $workOrder) }}"
                                  onsubmit="return confirm('¿Confirmas que el formulario está completo y listo para revisión del administrador?')">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                    Enviar a revisión del administrador
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

            {{-- Vista de solo lectura si ya fue enviado --}}
            @else
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Formulario diligenciado</h3>
                    <dl class="space-y-3">
                        @foreach ([
                            'Diagnóstico' => $workOrder->diagnosis,
                            'Actividades realizadas' => $workOrder->work_performed,
                            'Observaciones adicionales' => $workOrder->additional_observations,
                        ] as $label => $value)
                            @if ($value)
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase">{{ $label }}</dt>
                                    <dd class="text-sm text-gray-900 mt-1 whitespace-pre-line">{{ $value }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <span @class([
                            'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold',
                            'bg-purple-100 text-purple-800' => $workOrder->status === 'pending_review',
                            'bg-green-100 text-green-800' => $workOrder->status === 'closed',
                            'bg-gray-100 text-gray-800' => !in_array($workOrder->status, ['pending_review', 'closed']),
                        ])>{{ $workOrder->statusLabel() }}</span>
                    </div>
                </div>
            @endif

            <div>
                <a href="{{ route('technician.work_orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900">← Volver</a>
            </div>
        </div>
    </div>
</x-app-layout>
