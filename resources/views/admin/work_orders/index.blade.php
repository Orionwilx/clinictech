<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Órdenes de trabajo" :breadcrumbs="[['label' => 'Órdenes de trabajo']]">
            <x-slot:actions>
                @can('create work_orders')
                    <a href="{{ route('admin.work_orders.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">
                        {{ __('Nueva orden') }}
                    </a>
                @endcan
            </x-slot:actions>
        </x-page-header>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Filtros --}}
            <form method="GET" class="mb-4 bg-white shadow-sm sm:rounded-lg p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                    <div class="lg:col-span-2">
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                               placeholder="Nº o asunto…"
                               class="block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                    </div>
                    <select name="client_id" class="block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                        <option value="">Todos los clientes</option>
                        @foreach ($clients as $id => $name)
                            <option value="{{ $id }}" @selected(($filters['client_id'] ?? '') == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                    <select name="technician_id" class="block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                        <option value="">Todos los técnicos</option>
                        @foreach ($technicians as $id => $name)
                            <option value="{{ $id }}" @selected(($filters['technician_id'] ?? '') == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                    <select name="type" class="block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                        <option value="">Todos los tipos</option>
                        @foreach (\App\Models\WorkOrder::TYPES as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                        <option value="">Todos los estados</option>
                        @foreach (\App\Models\WorkOrder::STATUSES as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="priority" class="block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                        <option value="">Todas las prioridades</option>
                        @foreach (\App\Models\WorkOrder::PRIORITIES as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['priority'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-3 mt-3">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">
                        Filtrar
                    </button>
                    @if (array_filter($filters))
                        <a href="{{ route('admin.work_orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Limpiar</a>
                    @endif
                </div>
            </form>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nº / Asunto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente / Equipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Técnico</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prioridad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($workOrders as $order)
                            <tr class="{{ $order->trashed() ? 'bg-red-50' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $order->code }}
                                    <span class="block text-xs text-gray-400">{{ $order->title }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ optional($order->client)->name ?? '—' }}
                                    <span class="block text-xs text-gray-400">{{ optional($order->equipment)->name ?? 'Sin equipo' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($order->technician)->name ?? 'Sin asignar' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span @class([
                                        'inline-flex rounded-full px-2 text-xs font-semibold',
                                        'bg-gray-100 text-gray-800' => $order->priority === 'low',
                                        'bg-blue-100 text-blue-800' => $order->priority === 'medium',
                                        'bg-red-100 text-red-800' => $order->priority === 'high',
                                    ])>{{ $order->priorityLabel() }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($order->trashed())
                                        <span class="inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold text-red-800">Eliminada</span>
                                    @else
                                        <span @class([
                                            'inline-flex rounded-full px-2 text-xs font-semibold',
                                            'bg-gray-100 text-gray-800' => $order->status === 'open',
                                            'bg-indigo-100 text-indigo-800' => $order->status === 'assigned',
                                            'bg-amber-100 text-amber-800' => $order->status === 'in_progress',
                                            'bg-green-100 text-green-800' => $order->status === 'completed',
                                            'bg-brand-100 text-brand-800' => $order->status === 'closed',
                                            'bg-red-100 text-red-800' => $order->status === 'cancelled',
                                        ])>{{ $order->statusLabel() }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    @if ($order->trashed())
                                        @can('delete work_orders')
                                            <form action="{{ route('admin.work_orders.restore', $order->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="text-green-600 hover:text-green-900">Restaurar</button>
                                            </form>
                                        @endcan
                                    @else
                                        <a href="{{ route('admin.work_orders.show', $order) }}" class="text-gray-600 hover:text-gray-900">Ver</a>
                                        @can('update work_orders')
                                            <a href="{{ route('admin.work_orders.edit', $order) }}" class="text-brand-600 hover:text-brand-800">Editar</a>
                                        @endcan
                                        @can('delete work_orders')
                                            <form action="{{ route('admin.work_orders.destroy', $order) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('¿Eliminar esta orden?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                            </form>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No hay órdenes de trabajo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $workOrders->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
