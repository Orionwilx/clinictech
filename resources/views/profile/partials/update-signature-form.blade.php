<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Firma</h2>
        <p class="mt-1 text-sm text-gray-600">Tu firma aparecerá en las órdenes de trabajo que tengas asignadas.</p>
    </header>

    @if (session('status') === 'signature-updated')
        <div class="mt-4 rounded-md bg-green-50 p-3 text-sm text-green-700">Firma actualizada correctamente.</div>
    @endif
    @if (session('status') === 'signature-deleted')
        <div class="mt-4 rounded-md bg-green-50 p-3 text-sm text-green-700">Firma eliminada.</div>
    @endif

    @php $sig64 = auth()->user()->signatureBase64(); @endphp

    @if ($sig64)
        <div class="mt-4">
            <p class="text-xs font-medium text-gray-500 uppercase mb-2">Firma actual</p>
            <img src="{{ $sig64 }}" alt="Tu firma" class="h-24 object-contain rounded border border-gray-200 bg-white p-2">
        </div>
    @else
        <p class="mt-4 text-sm text-gray-400">No tienes una firma registrada.</p>
    @endif

    <form method="POST" action="{{ route('profile.signature.update') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
        @csrf
        <div>
            <x-input-label for="signature" value="{{ $sig64 ? 'Reemplazar firma' : 'Subir firma' }}" />
            <input type="file" id="signature" name="signature" accept="image/*"
                   class="mt-1 block w-full text-sm text-gray-700 border border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500">
            <p class="mt-1 text-xs text-gray-400">PNG o JPEG, máx. 4 MB.</p>
            @error('signature')
                <x-input-error :messages="$message" class="mt-1" />
            @enderror
        </div>
        <x-primary-button>Guardar firma</x-primary-button>
    </form>

    @if ($sig64)
        <form method="POST" action="{{ route('profile.signature.destroy') }}" class="mt-3"
              data-confirm="¿Eliminar tu firma?" data-confirm-title="Eliminar firma" data-confirm-button="Eliminar">
            @csrf @method('DELETE')
            <x-danger-button type="submit">Eliminar firma</x-danger-button>
        </form>
    @endif
</section>
