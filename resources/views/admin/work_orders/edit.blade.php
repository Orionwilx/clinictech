<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Editar orden de trabajo') }} · {{ $workOrder->code }}</h2>
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
