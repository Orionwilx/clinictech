<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Editar técnico') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('admin.technicians.update', $technician) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('admin.technicians._form', ['technician' => $technician])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
