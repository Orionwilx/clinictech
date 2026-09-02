{{-- Espera: $equipmentModel (nullable), $brands (id=>nombre) --}}
@php($editing = isset($equipmentModel) && $equipmentModel->exists)

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="brand_id" :value="__('Marca')" />
        <select id="brand_id" name="brand_id" required
                class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
            <option value="">— Selecciona —</option>
            @foreach ($brands as $id => $name)
                <option value="{{ $id }}" @selected(old('brand_id', $equipmentModel->brand_id ?? request('brand_id')) == $id)>{{ $name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('brand_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="name" :value="__('Nombre del modelo')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $equipmentModel->name ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
</div>

<div class="flex items-center gap-4 mt-6">
    <x-primary-button>{{ $editing ? __('Actualizar') : __('Crear') }}</x-primary-button>
    <a href="{{ route('admin.equipment_models.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
</div>
