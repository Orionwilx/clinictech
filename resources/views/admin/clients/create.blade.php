<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Nuevo cliente" :breadcrumbs="[['label' => 'Clientes', 'href' => route('admin.clients.index')], ['label' => 'Nuevo']]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('admin.clients.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @include('admin.clients._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
