<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Editar marca" :breadcrumbs="[['label' => 'Marcas', 'href' => route('admin.brands.index')], ['label' => 'Editar']]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('admin.brands.update', $brand) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('admin.brands._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
