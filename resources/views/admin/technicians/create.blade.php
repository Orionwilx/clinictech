<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Nuevo técnico" :breadcrumbs="[['label' => 'Técnicos', 'href' => route('admin.technicians.index')], ['label' => 'Nuevo']]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('admin.technicians.store') }}" method="POST">
                    @csrf
                    @include('admin.technicians._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
