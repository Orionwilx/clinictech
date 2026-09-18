<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Firma de empresa"
            :breadcrumbs="[['label' => 'Firma de empresa']]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-1">Firma de empresa</h2>
                <p class="text-xs text-gray-500 mb-4">Esta firma aparecerá como «Administrador» en todas las órdenes de trabajo y sus PDFs.</p>

                @if ($base64)
                    <div class="mb-4">
                        <p class="text-xs font-medium text-gray-500 uppercase mb-2">Firma actual</p>
                        <img src="{{ $base64 }}" alt="Firma de empresa" class="h-24 object-contain rounded border border-gray-200 bg-white p-2">
                    </div>
                @else
                    <p class="text-sm text-gray-400 mb-4">No hay firma de empresa configurada.</p>
                @endif

                <form method="POST" action="{{ route('admin.company-signature.update') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <x-input-label for="signature" value="{{ $base64 ? 'Reemplazar firma' : 'Subir firma' }}" />
                        <input type="file" id="signature" name="signature" accept="image/*"
                               class="mt-1 block w-full text-sm text-gray-700 border border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        <p class="mt-1 text-xs text-gray-400">PNG o JPEG, máx. 4 MB. Se recomienda fondo transparente (PNG).</p>
                        @error('signature')
                            <x-input-error :messages="$message" class="mt-1" />
                        @enderror
                    </div>
                    <x-primary-button>Guardar firma</x-primary-button>
                </form>

                @if ($upload)
                    <form method="POST" action="{{ route('admin.company-signature.destroy') }}" class="mt-4"
                          data-confirm="¿Eliminar la firma de empresa?" data-confirm-title="Eliminar firma" data-confirm-button="Eliminar">
                        @csrf @method('DELETE')
                        <x-danger-button type="submit">Eliminar firma</x-danger-button>
                    </form>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
