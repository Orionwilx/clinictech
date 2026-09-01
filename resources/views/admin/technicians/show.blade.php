<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Detalle de técnico') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <dl class="divide-y divide-gray-100">
                    @foreach ([
                        'Nombre' => $technician->name,
                        'Documento' => $technician->document,
                        'Correo' => $technician->email,
                        'Celular' => $technician->phone ?: '—',
                        'Especialidad' => $technician->specialty ?: '—',
                        'Estado' => $technician->is_active ? 'Activo' : 'Inactivo',
                    ] as $label => $value)
                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">{{ $label }}</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>

                <div class="flex items-center gap-4 mt-6">
                    <a href="{{ route('admin.technicians.edit', $technician) }}"
                       class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">
                        {{ __('Editar') }}
                    </a>
                    <a href="{{ route('admin.technicians.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Volver</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
