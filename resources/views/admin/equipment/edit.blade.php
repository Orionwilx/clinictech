<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Editar equipo" :breadcrumbs="[['label' => 'Equipos', 'href' => route('admin.equipment.index')], ['label' => 'Editar']]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('admin.equipment.update', $equipment) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('admin.equipment._form', ['equipment' => $equipment, 'clients' => $clients])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
