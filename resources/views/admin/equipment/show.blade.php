<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Detalle de equipo" :breadcrumbs="[['label' => 'Equipos', 'href' => route('admin.equipment.index')], ['label' => 'Detalle']]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <dl class="divide-y divide-gray-100">
                    @foreach ([
                        'Nombre' => $equipment->name,
                        'Cliente' => optional($equipment->client)->name ?? '—',
                        'Área' => optional($equipment->area)->name ?: '—',
                        'Tipo' => $equipment->type ?: '—',
                        'Marca' => optional($equipment->brand)->name ?: '—',
                        'Modelo' => optional($equipment->model)->name ?: '—',
                        'Serial' => $equipment->serial_number,
                        'Estado' => $equipment->statusLabel(),
                        'Fecha de compra' => optional($equipment->purchase_date)->format('Y-m-d') ?: '—',
                        'Vencimiento de garantía' => optional($equipment->warranty_expiry)->format('Y-m-d') ?: '—',
                        'Ubicación / sede' => $equipment->location ?: '—',
                        'Observaciones' => $equipment->notes ?: '—',
                    ] as $label => $value)
                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">{{ $label }}</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>

                <div class="flex items-center gap-4 mt-6">
                    <a href="{{ route('admin.equipment.edit', $equipment) }}"
                       class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">
                        {{ __('Editar') }}
                    </a>
                    <a href="{{ route('admin.equipment.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Volver</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
