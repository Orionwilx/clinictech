<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Nuevo equipo') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('admin.equipment.store') }}" method="POST">
                    @csrf
                    @include('admin.equipment._form', ['clients' => $clients])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
