<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Capacitaciones (PEC)"
            :breadcrumbs="[['label' => 'Panel', 'href' => route('client.dashboard')], ['label' => 'Capacitaciones']]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <p class="mb-4 text-sm text-gray-500">Programa de Educación Continua: capacitaciones en PDF cargadas por INGSOLN para tu empresa.</p>

            <x-data-table :sticky-last="false"
                :cols="['w-[40%]', 'w-[20%]', 'w-40', 'w-32']"
                :heads="[['Capacitación'], ['Tamaño'], ['Fecha'], ['', 'right']]">
                @forelse ($pec as $item)
                    <tr class="bg-white">
                        <x-td :title="$item->label" :sub="$item->original_name">{{ $item->label }}</x-td>
                        <x-td muted>{{ $item->humanSize() }}</x-td>
                        <x-td muted>{{ $item->created_at->format('d/m/Y') }}</x-td>
                        <x-td plain align="right">
                            <a href="{{ route('client.pec.download', $item) }}" class="text-brand-600 hover:text-brand-800 text-sm font-medium">Descargar</a>
                        </x-td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">Aún no hay capacitaciones disponibles.</td></tr>
                @endforelse
            </x-data-table>
        </div>
    </div>
</x-app-layout>
