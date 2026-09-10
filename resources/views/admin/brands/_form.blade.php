{{-- Espera: $brand (nullable) --}}
@php($editing = isset($brand) && $brand->exists)

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <x-input-label for="name" :value="__('Nombre de la marca')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $brand->name ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="manufacturer" :value="__('Fabricante')" />
        <x-text-input id="manufacturer" name="manufacturer" type="text" class="mt-1 block w-full"
                      :value="old('manufacturer', $brand->manufacturer ?? '')" />
        <p class="mt-1 text-xs text-gray-400">Se autodiligencia en el equipo al elegir esta marca.</p>
        <x-input-error :messages="$errors->get('manufacturer')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="origin_country" :value="__('País de origen')" />
        <x-text-input id="origin_country" name="origin_country" type="text" class="mt-1 block w-full"
                      :value="old('origin_country', $brand->origin_country ?? '')" />
        <x-input-error :messages="$errors->get('origin_country')" class="mt-2" />
    </div>
</div>

<div class="flex items-center gap-4 mt-6">
    <x-primary-button>{{ $editing ? __('Actualizar') : __('Crear') }}</x-primary-button>
    <a href="{{ route('admin.brands.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
</div>
