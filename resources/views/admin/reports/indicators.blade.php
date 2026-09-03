<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Indicadores" :breadcrumbs="[['label' => 'Reportes', 'href' => route('admin.reports.index')], ['label' => 'Indicadores']]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
                @foreach ([
                    ['Clientes activos',  $stats['clients'],     'brand'],
                    ['Equipos',           $stats['equipment'],   'brand'],
                    ['OT abiertas',       $stats['open_orders'], 'amber'],
                    ['Técnicos activos',  $stats['technicians'], 'green'],
                ] as [$label, $value, $color])
                    <div class="bg-white shadow-sm sm:rounded-lg p-5">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $label }}</p>
                        <p @class([
                            'mt-1 text-3xl font-bold',
                            'text-brand-700' => $color === 'brand',
                            'text-amber-600' => $color === 'amber',
                            'text-green-600' => $color === 'green',
                        ])>{{ $value }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total OT</p>
                    <p class="mt-1 text-3xl font-bold text-gray-800">{{ $stats['work_orders'] }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Mantenimientos preventivos</p>
                    <p class="mt-1 text-3xl font-bold text-brand-700">{{ $stats['preventive'] }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Mantenimientos correctivos</p>
                    <p class="mt-1 text-3xl font-bold text-amber-600">{{ $stats['corrective'] }}</p>
                </div>
            </div>

            <p class="mt-8 text-xs text-gray-400 text-center">Próximamente: gráficas de evolución histórica.</p>
        </div>
    </div>
</x-app-layout>
