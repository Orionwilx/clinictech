<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Nueva marca" :breadcrumbs="[['label' => 'Marcas', 'href' => route('admin.brands.index')], ['label' => 'Nueva']]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('admin.brands.store') }}" method="POST">
                    @csrf
                    @include('admin.brands._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
