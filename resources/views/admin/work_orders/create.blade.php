<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Nueva orden de trabajo" :breadcrumbs="[['label' => 'Órdenes de trabajo', 'href' => route('admin.work_orders.index')], ['label' => 'Nueva']]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('admin.work_orders.store') }}" method="POST">
                    @csrf
                    @include('admin.work_orders._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
