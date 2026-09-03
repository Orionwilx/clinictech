<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Reportes" :breadcrumbs="[['label' => 'Reportes']]">
            <x-slot:actions>
                <a href="{{ route('admin.reports.indicators') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                    Indicadores
                </a>
            </x-slot:actions>
        </x-page-header>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            {{-- ── Sección 1: Formulario ── --}}
            <form method="POST" action="{{ route('admin.reports.export') }}"
                  x-data="{ type: '{{ old('report_type', '') }}', submitting: false }"
                  @submit="submitting = true">
                @csrf

                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Generar reporte</h2>

                    {{-- Tipo --}}
                    <div>
                        <x-input-label for="report_type" :value="__('Tipo de reporte')" />
                        <select id="report_type" name="report_type" x-model="type"
                                class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                            <option value="">— Selecciona un reporte —</option>
                            <option value="work_orders">Órdenes de trabajo</option>
                            <option value="maintenance">Mantenimientos</option>
                            <option value="technicians">Por técnico</option>
                            <option value="equipment">Por equipo</option>
                        </select>
                        <x-input-error :messages="$errors->get('report_type')" class="mt-1" />
                    </div>

                    {{-- Fechas (siempre visibles) --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="date_from" :value="__('Fecha desde')" />
                            <x-text-input id="date_from" name="date_from" type="date" class="mt-1 block w-full" :value="old('date_from')" />
                        </div>
                        <div>
                            <x-input-label for="date_to" :value="__('Fecha hasta')" />
                            <x-text-input id="date_to" name="date_to" type="date" class="mt-1 block w-full" :value="old('date_to')" />
                        </div>
                    </div>

                    {{-- Filtros adicionales: Órdenes --}}
                    <div x-show="type === 'work_orders'" x-cloak>
                        <div class="border-t pt-4">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Filtros adicionales</p>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="wo_client" :value="__('Cliente')" />
                                    <select id="wo_client" name="client_id" class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                                        <option value="">Todos</option>
                                        @foreach ($clients as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="wo_tech" :value="__('Técnico')" />
                                    <select id="wo_tech" name="technician_id" class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                                        <option value="">Todos</option>
                                        @foreach ($technicians as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="wo_type" :value="__('Tipo')" />
                                    <select id="wo_type" name="type" class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                                        <option value="">Todos</option>
                                        @foreach (\App\Models\WorkOrder::TYPES as $v => $l)
                                            <option value="{{ $v }}">{{ $l }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="wo_status" :value="__('Estado')" />
                                    <select id="wo_status" name="status" class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                                        <option value="">Todos</option>
                                        @foreach (\App\Models\WorkOrder::STATUSES as $v => $l)
                                            <option value="{{ $v }}">{{ $l }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Filtros adicionales: Mantenimientos --}}
                    <div x-show="type === 'maintenance'" x-cloak>
                        <div class="border-t pt-4">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Filtros adicionales</p>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="mt_client" :value="__('Cliente')" />
                                    <select id="mt_client" name="client_id" class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                                        <option value="">Todos</option>
                                        @foreach ($clients as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="mt_tech" :value="__('Técnico')" />
                                    <select id="mt_tech" name="technician_id" class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                                        <option value="">Todos</option>
                                        @foreach ($technicians as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="mt_type" :value="__('Subtipo')" />
                                    <select id="mt_type" name="type" class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                                        <option value="">Preventivo y Correctivo</option>
                                        <option value="preventive">Preventivo</option>
                                        <option value="corrective">Correctivo</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="mt_status" :value="__('Estado')" />
                                    <select id="mt_status" name="status" class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                                        <option value="">Todos</option>
                                        @foreach (\App\Models\WorkOrder::STATUSES as $v => $l)
                                            <option value="{{ $v }}">{{ $l }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Filtros adicionales: Técnicos --}}
                    <div x-show="type === 'technicians'" x-cloak>
                        <div class="border-t pt-4">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Filtros adicionales</p>
                            <div>
                                <x-input-label for="tech_status" :value="__('Estado del técnico')" />
                                <select id="tech_status" name="status" class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                                    <option value="">Todos</option>
                                    <option value="active">Activos</option>
                                    <option value="inactive">Inactivos</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Filtros adicionales: Equipos --}}
                    <div x-show="type === 'equipment'" x-cloak>
                        <div class="border-t pt-4">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Filtros adicionales</p>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="eq_client" :value="__('Cliente')" />
                                    <select id="eq_client" name="client_id" class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                                        <option value="">Todos</option>
                                        @foreach ($clients as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="eq_status" :value="__('Estado del equipo')" />
                                    <select id="eq_status" name="status" class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                                        <option value="">Todos</option>
                                        @foreach (\App\Models\Equipment::STATUSES as $v => $l)
                                            <option value="{{ $v }}">{{ $l }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Botón --}}
                    <div class="flex items-center gap-4 pt-2 border-t">
                        <button type="submit" :disabled="type === '' || submitting"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-md hover:bg-brand-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0 0 21 18V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v12a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                            <span x-text="submitting ? 'Enviando...' : 'Generar reporte'"></span>
                        </button>
                        <p class="text-xs text-gray-400">El reporte se genera en segundo plano y aparecerá en el historial cuando esté listo.</p>
                    </div>

                </div>
            </form>

            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-700">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- ── Sección 2: Historial ── --}}
            <div>
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">Historial de reportes</h2>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Creado por</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duración</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descargado por</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($history as $report)
                                <tr>
                                    <td class="px-5 py-4 whitespace-nowrap text-xs text-gray-400">{{ $report->id }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">{{ $report->typeLabel() }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm">
                                        <span @class([
                                            'inline-flex rounded-full px-2 text-xs font-semibold',
                                            'bg-gray-100 text-gray-600'   => $report->status === 'pending',
                                            'bg-amber-100 text-amber-800' => $report->status === 'processing',
                                            'bg-green-100 text-green-800' => $report->status === 'done',
                                            'bg-red-100 text-red-800'     => $report->status === 'failed',
                                        ])>{{ $report->statusLabel() }}</span>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($report->generator)->name ?? '—' }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">{{ $report->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">{{ $report->durationLabel() }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if ($report->downloader)
                                            {{ $report->downloader->name }}
                                            <span class="block text-xs text-gray-400">{{ $report->downloaded_at->format('d/m/Y H:i') }}</span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right">
                                        @if ($report->status === 'done')
                                            <a href="{{ route('admin.reports.download', $report) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-brand-600 text-white text-xs font-semibold rounded-md hover:bg-brand-700">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                                Descargar
                                            </a>
                                        @elseif ($report->status === 'failed')
                                            <span class="text-xs text-red-500" title="{{ $report->error_message }}">Error</span>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-5 py-6 text-center text-sm text-gray-500">No hay reportes generados aún.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
