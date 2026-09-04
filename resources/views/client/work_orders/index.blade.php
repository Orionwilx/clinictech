<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Mis órdenes de trabajo"
            :breadcrumbs="[['label' => 'Panel', 'href' => route('client.dashboard')], ['label' => 'Órdenes de trabajo']]">
            <x-slot:actions>
                <x-primary-link :href="route('client.work_orders.create')">Solicitar mantenimiento</x-primary-link>
            </x-slot:actions>
        </x-page-header>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Filtros --}}
            <form method="GET" class="mb-4 bg-white shadow-sm sm:rounded-lg p-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                               placeholder="Nº o asunto…"
                               class="block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                    </div>
                    <select name="type" class="block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                        <option value="">Todos los tipos</option>
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                        <option value="">Todos los estados</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-3 mt-3">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">
                        Filtrar
                    </button>
                    @if (array_filter($filters))
                        <a href="{{ route('client.work_orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Limpiar</a>
                    @endif
                </div>
            </form>

            <x-data-table
                :cols="['w-[26%]', 'w-[24%]', 'w-40', 'w-32', 'w-36', 'w-24']"
                :heads="[['Nº / Asunto'], ['Equipo'], ['Técnico'], ['Tipo'], ['Estado'], ['Acciones', 'right']]">
                @forelse ($workOrders as $order)
                    <tr class="bg-white">
                        <x-td :title="$order->code" :sub="$order->title" :subTitle="$order->title">{{ $order->code }}</x-td>
                        <x-td :title="optional($order->equipment)->name" muted>{{ optional($order->equipment)->name ?? '—' }}</x-td>
                        <x-td :title="optional($order->technician)->name" muted>{{ optional($order->technician)->name ?? 'Sin asignar' }}</x-td>
                        <x-td plain>
                            <span @class([
                                'inline-flex rounded-full px-2 text-xs font-semibold',
                                'bg-sky-100 text-sky-800' => $order->type === 'preventive',
                                'bg-orange-100 text-orange-800' => $order->type === 'corrective',
                            ])>{{ $order->typeLabel() }}</span>
                        </x-td>
                        <x-td plain>
                            <x-work-order-status-badge :order="$order" />
                        </x-td>
                        <x-td-actions>
                            <x-icon-btn :href="route('client.work_orders.show', $order)" color="gray" label="Ver">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            </x-icon-btn>
                            <x-icon-btn :href="route('client.work_orders.pdf', $order)" color="gray" label="PDF" target="_blank">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                            </x-icon-btn>
                        </x-td-actions>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">No hay órdenes de trabajo.</td></tr>
                @endforelse
            </x-data-table>

            <div class="mt-4">{{ $workOrders->links() }}</div>
        </div>
    </div>
</x-app-layout>
