<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Editar categoría" :breadcrumbs="[['label' => 'Categorías de equipos', 'href' => route('admin.equipment_categories.index')], ['label' => $category->name]]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('admin.equipment_categories.update', $category) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('admin.equipment_categories._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
