<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Mis técnicos"
            :breadcrumbs="[['label' => 'Panel', 'href' => route('client.dashboard')], ['label' => 'Técnicos']]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <x-data-table :sticky-last="false"
                :cols="['w-[30%]', 'w-[24%]', 'w-[30%]', 'w-40']"
                :heads="[['Técnico'], ['Especialidad'], ['Contacto'], ['OT atendidas', 'right']]">
                @forelse ($technicians as $tech)
                    <tr class="bg-white">
                        <x-td :title="$tech->name" :sub="$tech->document ?: null">{{ $tech->name }}</x-td>
                        <x-td :title="$tech->specialty" muted>{{ $tech->specialty ?: '—' }}</x-td>
                        <x-td :title="$tech->email" muted :sub="$tech->phone ?: null">{{ $tech->email ?: '—' }}</x-td>
                        <x-td plain align="right"><span class="text-sm font-semibold text-gray-900">{{ $tech->orders_count }}</span></x-td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">Aún no hay técnicos asociados a tus órdenes de trabajo.</td></tr>
                @endforelse
            </x-data-table>
        </div>
    </div>
</x-app-layout>
