<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="'Orden '.$workOrder->code"
            :breadcrumbs="[['label' => 'Panel', 'href' => route('client.dashboard')], ['label' => 'Órdenes', 'href' => route('client.work_orders.index')], ['label' => $workOrder->code]]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                {{-- Datos generales en horizontal (tarjetas en cuadrícula) --}}
                <dl class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach ([
                        'Nº de orden' => $workOrder->code,
                        'Asunto' => $workOrder->title,
                        'Equipo' => optional($workOrder->equipment)->name ?? '—',
                        'Técnico asignado' => optional($workOrder->technician)->name ?? 'Sin asignar',
                        'Tipo' => $workOrder->typeLabel(),
                        'Prioridad' => $workOrder->priorityLabel(),
                        'Estado' => $workOrder->statusLabel(),
                        'Fecha programada' => optional($workOrder->scheduled_at)->format('Y-m-d') ?: '—',
                        'Inicio' => optional($workOrder->started_at)->format('Y-m-d') ?: '—',
                        'Completada' => optional($workOrder->completed_at)->format('Y-m-d') ?: '—',
                        'Cerrada' => optional($workOrder->closed_at)->format('Y-m-d') ?: '—',
                    ] as $label => $value)
                        <div class="rounded-lg bg-gray-50 border border-gray-100 p-3">
                            <dt class="text-xs font-medium text-gray-500 uppercase">{{ $label }}</dt>
                            <dd class="mt-0.5 text-sm text-gray-900 break-words">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>

                {{-- Textos largos a lo ancho --}}
                <div class="mt-6 space-y-4">
                    @foreach ([
                        'Descripción' => $workOrder->description,
                        'Diagnóstico' => $workOrder->diagnosis,
                        'Actividades realizadas' => $workOrder->work_performed,
                    ] as $label => $value)
                        @if ($value)
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 mb-1">{{ $label }}</h3>
                                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $value }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>

                @if ($workOrder->maintenance_tasks || $workOrder->accessories_checked)
                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">Subtareas ejecutadas</h3>
                            @if ($workOrder->maintenance_tasks)
                                <ul class="grid grid-cols-1 gap-1 text-sm text-gray-700">
                                    @foreach ($workOrder->maintenance_tasks as $key)
                                        <li class="flex items-center gap-2"><span class="text-brand-600">✓</span>{{ $key }}</li>
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
                                        <li class="flex items-center gap-2"><span class="text-brand-600">✓</span>{{ $key }}</li>
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

                @if ($workOrder->photos->isNotEmpty())
                    <div class="mt-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Evidencias fotográficas</h3>
                        <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
                            @foreach ($workOrder->photos as $photo)
                                <figure>
                                    <a href="{{ $photo->url() }}" target="_blank">
                                        <img src="{{ $photo->url() }}" alt="{{ $photo->label ?: $photo->original_name }}" class="h-24 w-full object-cover rounded-lg border border-gray-200">
                                    </a>
                                    @if ($photo->label)
                                        <figcaption class="mt-1 text-xs text-gray-500">{{ $photo->label }}</figcaption>
                                    @endif
                                </figure>
                            @endforeach
                        </div>
                    </div>
                @endif

                @php
                    $techSig = $workOrder->technician?->user?->signatureBase64();
                    $companySig = \App\Support\CompanySignature::base64();
                @endphp
                @if ($techSig || $companySig)
                    <div class="mt-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Firmas</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="text-center">
                                @if ($techSig)
                                    <img src="{{ $techSig }}" alt="Firma técnico" class="h-20 mx-auto mb-2 object-contain rounded border border-gray-200 bg-white p-1">
                                @else
                                    <div class="h-20 border-b border-gray-400 mb-2"></div>
                                @endif
                                <p class="text-sm font-medium text-gray-900">{{ $workOrder->technician?->name ?? '—' }}</p>
                                <p class="text-xs text-gray-500">Técnico responsable</p>
                            </div>
                            <div class="text-center">
                                @if ($companySig)
                                    <img src="{{ $companySig }}" alt="Firma empresa" class="h-20 mx-auto mb-2 object-contain rounded border border-gray-200 bg-white p-1">
                                @else
                                    <div class="h-20 border-b border-gray-400 mb-2"></div>
                                @endif
                                <p class="text-sm font-medium text-gray-900">Administrador</p>
                                <p class="text-xs text-gray-500">Administrador</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="flex items-center gap-4 mt-6">
                    <a href="{{ route('client.work_orders.pdf', $workOrder) }}" target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Generar PDF
                    </a>
                    <a href="{{ route('client.work_orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Volver</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
