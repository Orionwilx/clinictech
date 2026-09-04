<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Mis técnicos"
            :breadcrumbs="[['label' => 'Panel', 'href' => route('client.dashboard')], ['label' => 'Técnicos']]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Técnico</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Especialidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contacto</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">OT atendidas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($technicians as $tech)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $tech->name }}
                                    @if ($tech->document)
                                        <span class="block text-xs text-gray-400">{{ $tech->document }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $tech->specialty ?: '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $tech->email ?: '—' }}
                                    @if ($tech->phone)
                                        <span class="block text-xs text-gray-400">{{ $tech->phone }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900">
                                    {{ $tech->orders_count }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">
                                    Aún no hay técnicos asociados a tus órdenes de trabajo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
