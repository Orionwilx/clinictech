<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="'Editar · '.$workOrder->code" :breadcrumbs="[['label' => 'Órdenes de trabajo', 'href' => route('admin.work_orders.index')], ['label' => $workOrder->code, 'href' => route('admin.work_orders.show', $workOrder)], ['label' => 'Editar']]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('admin.work_orders.update', $workOrder) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('admin.work_orders._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
