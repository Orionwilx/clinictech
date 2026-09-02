{{-- Espera: $brand (nullable) --}}
@php($editing = isset($brand) && $brand->exists)

<div>
    <x-input-label for="name" :value="__('Nombre de la marca')" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                  :value="old('name', $brand->name ?? '')" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="flex items-center gap-4 mt-6">
    <x-primary-button>{{ $editing ? __('Actualizar') : __('Crear') }}</x-primary-button>
    <a href="{{ route('admin.brands.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
</div>
