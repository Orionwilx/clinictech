<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Mis órdenes de trabajo"
            :breadcrumbs="[['label' => 'Panel', 'href' => route('technician.dashboard')], ['label' => 'Órdenes']]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <form method="GET" class="mb-4 bg-white shadow-sm sm:rounded-lg p-4">
                <div class="flex items-center gap-3">
                    <select name="status" class="block border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                        <option value="">Todos los estados</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">
                        Filtrar
                    </button>
                    @if (array_filter($filters))
                        <a href="{{ route('technician.work_orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Limpiar</a>
                    @endif
                </div>
            </form>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nº / Asunto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente / Equipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($workOrders as $order)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $order->code }}
                                    <span class="block text-xs text-gray-400">{{ $order->title }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ optional($order->client)->name ?? '—' }}
                                    <span class="block text-xs text-gray-400">{{ optional($order->equipment)->name ?? 'Sin equipo' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span @class([
                                        'inline-flex rounded-full px-2 text-xs font-semibold',
                                        'bg-sky-100 text-sky-800' => $order->type === 'preventive',
                                        'bg-orange-100 text-orange-800' => $order->type === 'corrective',
                                    ])>{{ $order->typeLabel() }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span @class([
                                        'inline-flex rounded-full px-2 text-xs font-semibold',
                                        'bg-indigo-100 text-indigo-800' => $order->status === 'assigned',
                                        'bg-amber-100 text-amber-800' => $order->status === 'in_progress',
                                        'bg-purple-100 text-purple-800' => $order->status === 'pending_review',
                                        'bg-green-100 text-green-800' => $order->status === 'completed',
                                        'bg-brand-100 text-brand-800' => $order->status === 'closed',
                                        'bg-gray-100 text-gray-800' => !in_array($order->status, ['assigned','in_progress','pending_review','completed','closed']),
                                    ])>{{ $order->statusLabel() }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <x-icon-btn :href="route('technician.work_orders.show', $order)" color="gray" label="Ver">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                    </x-icon-btn>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No hay órdenes.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $workOrders->links() }}</div>
        </div>
    </div>
</x-app-layout>
