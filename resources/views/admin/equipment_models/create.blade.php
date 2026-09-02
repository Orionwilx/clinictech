<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Nuevo modelo" :breadcrumbs="[['label' => 'Modelos', 'href' => route('admin.equipment_models.index')], ['label' => 'Nuevo']]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('admin.equipment_models.store') }}" method="POST">
                    @csrf
                    @include('admin.equipment_models._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
