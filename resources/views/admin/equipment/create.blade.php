<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Nuevo equipo" :breadcrumbs="[['label' => 'Equipos', 'href' => route('admin.equipment.index')], ['label' => 'Nuevo']]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('admin.equipment.store') }}" method="POST">
                    @csrf
                    @include('admin.equipment._form', ['clients' => $clients])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
