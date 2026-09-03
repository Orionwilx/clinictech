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
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <form method="POST" action="{{ route('admin.reports.export') }}"
                  x-data="reportSelector()"
                  @submit="submitting = true">
                @csrf

                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">

                    {{-- Tipo de reporte --}}
                    <div>
                        <x-input-label for="report_type" :value="__('Tipo de reporte')" />
                        <select id="report_type" name="report_type"
                                x-model="type"
                                class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                            <option value="">— Selecciona un reporte —</option>
                            <option value="work_orders">Órdenes de trabajo</option>
                            <option value="maintenance">Mantenimientos</option>
                            <option value="technicians">Por técnico</option>
                            <option value="equipment">Por equipo</option>
                        </select>
                        <x-input-error :messages="$errors->get('report_type')" class="mt-1" />
                    </div>

                    {{-- Rango de fechas (siempre visible) --}}
                    <div class="grid grid-cols-2 gap-4" x-show="type !== ''" x-cloak>
                        <div>
                            <x-input-label for="date_from" :value="__('Fecha desde')" />
                            <x-text-input id="date_from" name="date_from" type="date"
                                          class="mt-1 block w-full"
                                          :value="old('date_from')" />
                        </div>
                        <div>
                            <x-input-label for="date_to" :value="__('Fecha hasta')" />
                            <x-text-input id="date_to" name="date_to" type="date"
                                          class="mt-1 block w-full"
                                          :value="old('date_to')" />
                        </div>
                    </div>

                    {{-- Filtros: Órdenes de trabajo --}}
                    <div class="space-y-4" x-show="type === 'work_orders'" x-cloak>
                        <div class="border-t pt-4">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Filtros adicionales</p>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="wo_client" :value="__('Cliente')" />
                                    <select id="wo_client" name="client_id"
                                            class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                                        <option value="">Todos</option>
                                        @foreach ($clients as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="wo_tech" :value="__('Técnico')" />
                                    <select id="wo_tech" name="technician_id"
                                            class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                                        <option value="">Todos</option>
                                        @foreach ($technicians as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="wo_type" :value="__('Tipo')" />
                                    <select id="wo_type" name="type"
                                            class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                                        <option value="">Todos</option>
                                        @foreach (\App\Models\WorkOrder::TYPES as $v => $l)
                                            <option value="{{ $v }}">{{ $l }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="wo_status" :value="__('Estado')" />
                                    <select id="wo_status" name="status"
                                            class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                                        <option value="">Todos</option>
                                        @foreach (\App\Models\WorkOrder::STATUSES as $v => $l)
                                            <option value="{{ $v }}">{{ $l }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Filtros: Mantenimientos --}}
                    <div class="space-y-4" x-show="type === 'maintenance'" x-cloak>
                        <div class="border-t pt-4">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Filtros adicionales</p>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="mt_client" :value="__('Cliente')" />
                                    <select id="mt_client" name="client_id"
                                            class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                                        <option value="">Todos</option>
                                        @foreach ($clients as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="mt_tech" :value="__('Técnico')" />
                                    <select id="mt_tech" name="technician_id"
                                            class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                                        <option value="">Todos</option>
                                        @foreach ($technicians as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="mt_type" :value="__('Subtipo')" />
                                    <select id="mt_type" name="type"
                                            class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                                        <option value="">Preventivo y Correctivo</option>
                                        <option value="preventive">Preventivo</option>
                                        <option value="corrective">Correctivo</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="mt_status" :value="__('Estado')" />
                                    <select id="mt_status" name="status"
                                            class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                                        <option value="">Todos</option>
                                        @foreach (\App\Models\WorkOrder::STATUSES as $v => $l)
                                            <option value="{{ $v }}">{{ $l }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Filtros: Por técnico --}}
                    <div x-show="type === 'technicians'" x-cloak>
                        <div class="border-t pt-4">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Filtros adicionales</p>
                            <div>
                                <x-input-label for="tech_status" :value="__('Estado del técnico')" />
                                <select id="tech_status" name="status"
                                        class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                                    <option value="">Todos</option>
                                    <option value="active">Activos</option>
                                    <option value="inactive">Inactivos</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Filtros: Por equipo --}}
                    <div x-show="type === 'equipment'" x-cloak>
                        <div class="border-t pt-4">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Filtros adicionales</p>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="eq_client" :value="__('Cliente')" />
                                    <select id="eq_client" name="client_id"
                                            class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                                        <option value="">Todos</option>
                                        @foreach ($clients as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="eq_status" :value="__('Estado del equipo')" />
                                    <select id="eq_status" name="status"
                                            class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
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
                    <div x-show="type !== ''" x-cloak class="flex items-center gap-4 pt-2 border-t">
                        <button type="submit"
                                :disabled="submitting"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-md hover:bg-brand-700 disabled:opacity-60 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            <span x-text="submitting ? 'Generando...' : 'Generar y descargar Excel'"></span>
                        </button>
                        <p class="text-xs text-gray-400">El archivo incluye todas las hojas del reporte seleccionado.</p>
                    </div>

                </div>

            </form>

            @if ($errors->any())
                <div class="mt-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>
    </div>

    <script>
        function reportSelector() {
            return {
                type: '{{ old('report_type', '') }}',
                submitting: false,
            }
        }
    </script>
</x-app-layout>
