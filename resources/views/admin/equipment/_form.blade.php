{{-- Espera: $equipment (nullable), $clients (Collection id=>nombre) --}}
@php($editing = isset($equipment) && $equipment->exists)

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4"
         x-data="{
            client: '{{ old('client_id', $equipment->client_id ?? request('client_id')) }}',
            area: '{{ old('area_id', $equipment->area_id ?? '') }}',
            areas: {{ Illuminate\Support\Js::from($areas->map->only('id', 'name', 'client_id')) }},
            get filteredAreas() { return this.areas.filter(a => String(a.client_id) === String(this.client)); }
         }">
        <div>
            <x-input-label for="client_id" :value="__('Cliente')" />
            <select id="client_id" name="client_id" x-model="client" @change="area = ''" required
                    class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                <option value="">— Selecciona —</option>
                @foreach ($clients as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="area_id" :value="__('Área (opcional)')" />
            <select id="area_id" name="area_id" x-model="area" :disabled="!client"
                    class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm disabled:bg-gray-100">
                <option value="">{{ __('— Sin área —') }}</option>
                <template x-for="a in filteredAreas" :key="a.id">
                    <option :value="a.id" x-text="a.name" :selected="String(a.id) === String(area)"></option>
                </template>
            </select>
            <x-input-error :messages="$errors->get('area_id')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="name" :value="__('Nombre del equipo')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $equipment->name ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="type" :value="__('Tipo')" />
        <x-text-input id="type" name="type" type="text" class="mt-1 block w-full"
                      :value="old('type', $equipment->type ?? '')" />
        <x-input-error :messages="$errors->get('type')" class="mt-2" />
    </div>
    <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4"
         x-data="{
            brand: '{{ old('brand_id', $equipment->brand_id ?? '') }}',
            model: '{{ old('model_id', $equipment->model_id ?? '') }}',
            models: {{ Illuminate\Support\Js::from($models->map->only('id', 'name', 'brand_id')) }},
            get filtered() { return this.models.filter(m => String(m.brand_id) === String(this.brand)); }
         }">
        <div>
            <x-input-label for="brand_id" :value="__('Marca')" />
            <select id="brand_id" name="brand_id" x-model="brand" @change="model = ''"
                    class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                <option value="">— Selecciona —</option>
                @foreach ($brands as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('brand_id')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="model_id" :value="__('Modelo')" />
            <select id="model_id" name="model_id" x-model="model" :disabled="!brand"
                    class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm disabled:bg-gray-100">
                <option value="">{{ __('— Selecciona una marca primero —') }}</option>
                <template x-for="m in filtered" :key="m.id">
                    <option :value="m.id" x-text="m.name" :selected="String(m.id) === String(model)"></option>
                </template>
            </select>
            <x-input-error :messages="$errors->get('model_id')" class="mt-2" />
        </div>
    </div>
    <div>
        <x-input-label for="serial_number" :value="__('Serial')" />
        <x-text-input id="serial_number" name="serial_number" type="text" class="mt-1 block w-full"
                      :value="old('serial_number', $equipment->serial_number ?? '')" required />
        <x-input-error :messages="$errors->get('serial_number')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="status" :value="__('Estado')" />
        <select id="status" name="status" required
                class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
            @foreach (\App\Models\Equipment::STATUSES as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $equipment->status ?? 'active') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="purchase_date" :value="__('Fecha de compra')" />
        <x-text-input id="purchase_date" name="purchase_date" type="date" class="mt-1 block w-full"
                      :value="old('purchase_date', optional($equipment->purchase_date ?? null)->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('purchase_date')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="warranty_expiry" :value="__('Vencimiento de garantía')" />
        <x-text-input id="warranty_expiry" name="warranty_expiry" type="date" class="mt-1 block w-full"
                      :value="old('warranty_expiry', optional($equipment->warranty_expiry ?? null)->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('warranty_expiry')" class="mt-2" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="location" :value="__('Ubicación / sede (dirección de la instalación)')" />
        <x-text-input id="location" name="location" type="text" class="mt-1 block w-full"
                      :value="old('location', $equipment->location ?? '')" />
        <x-input-error :messages="$errors->get('location')" class="mt-2" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="notes" :value="__('Observaciones')" />
        <textarea id="notes" name="notes" rows="3"
                  class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">{{ old('notes', $equipment->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
    </div>
</div>

<div class="flex items-center gap-4 mt-6">
    <x-primary-button>{{ $editing ? __('Actualizar') : __('Crear') }}</x-primary-button>
    <a href="{{ route('admin.equipment.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
</div>
