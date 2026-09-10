{{-- Espera: $category (nullable), $specialtyOptions, $taskOptions, $accessoryOptions --}}
@php($editing = isset($category) && $category->exists)
@php($specialties = (array) old('specialties', $category->specialties ?? []))
@php($tasks = (array) old('maintenance_tasks', $category->maintenance_tasks ?? []))
@php($accessories = (array) old('accessories', $category->accessories ?? []))

<div class="space-y-8">
    {{-- Datos de la categoría --}}
    <div>
        <h3 class="text-sm font-semibold text-brand-900 border-b border-gray-100 pb-2 mb-4">Categoría</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="name" :value="__('Nombre (p. ej. Compresor)')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                              :value="old('name', $category->name ?? '')" required autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="description" :value="__('Descripción')" />
                <x-text-input id="description" name="description" type="text" class="mt-1 block w-full"
                              :value="old('description', $category->description ?? '')" />
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>
        </div>
        <p class="mt-3 text-xs text-gray-400">Los campos de abajo son la <strong>plantilla</strong>: se prediligencian al crear un equipo de esta categoría y pueden ajustarse por unidad. Cambiarlos aquí NO modifica equipos ya creados.</p>
    </div>

    {{-- Plantilla: identificación --}}
    <div>
        <h3 class="text-sm font-semibold text-brand-900 border-b border-gray-100 pb-2 mb-4">Identificación (plantilla)</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="risk_class" :value="__('Clasificación por riesgo (INVIMA)')" />
                <select id="risk_class" name="risk_class"
                        class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                    <option value="">— Selecciona —</option>
                    @foreach (\App\Models\Equipment::RISK_CLASSES as $value => $label)
                        <option value="{{ $value }}" @selected(old('risk_class', $category->risk_class ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('risk_class')" class="mt-2" />
            </div>
            <div class="sm:col-span-2">
                <x-input-label :value="__('Clasificación por especialidad')" />
                <x-catalog-checkboxes name="specialties" catalog="specialties" columns="sm:grid-cols-4"
                    :options="$specialtyOptions" :selected="$specialties" />
                <x-input-error :messages="$errors->get('specialties')" class="mt-2" />
            </div>
        </div>
    </div>

    {{-- Plantilla: características técnicas --}}
    <div>
        <h3 class="text-sm font-semibold text-brand-900 border-b border-gray-100 pb-2 mb-4">Características técnicas (plantilla)</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach ([
                'voltage' => 'Voltaje',
                'amperage' => 'Amperaje',
                'current' => 'Corriente',
                'power' => 'Potencia',
                'temperature' => 'Temperatura',
                'pressure' => 'Presión',
                'weight' => 'Peso',
                'speed' => 'Velocidad',
                'predominant_technology' => 'Tecnología predominante',
            ] as $field => $label)
                <div>
                    <x-input-label :for="$field" :value="__($label)" />
                    <x-text-input :id="$field" :name="$field" type="text" class="mt-1 block w-full"
                                  :value="old($field, $category->$field ?? '')" />
                    <x-input-error :messages="$errors->get($field)" class="mt-2" />
                </div>
            @endforeach
        </div>
    </div>

    {{-- Plantilla: subtareas de mantenimiento --}}
    <div>
        <h3 class="text-sm font-semibold text-brand-900 border-b border-gray-100 pb-2 mb-4">Subtareas de mantenimiento (plantilla)</h3>
        <p class="text-xs text-gray-400 mb-3">Subtareas que aplican por defecto a los equipos de esta categoría.</p>
        <x-catalog-checkboxes name="maintenance_tasks" catalog="maintenance_tasks"
            :options="$taskOptions" :selected="$tasks" />
        <x-input-error :messages="$errors->get('maintenance_tasks')" class="mt-2" />
    </div>

    {{-- Plantilla: accesorios --}}
    <div>
        <h3 class="text-sm font-semibold text-brand-900 border-b border-gray-100 pb-2 mb-4">Accesorios (plantilla)</h3>
        <p class="text-xs text-gray-400 mb-3">Accesorios con los que cuentan por defecto los equipos de esta categoría.</p>
        <x-catalog-checkboxes name="accessories" catalog="accessories"
            :options="$accessoryOptions" :selected="$accessories" />
        <x-input-error :messages="$errors->get('accessories')" class="mt-2" />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
            <div>
                <x-input-label for="components" :value="__('Componentes / accesorios (detalle)')" />
                <textarea id="components" name="components" rows="2"
                          class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">{{ old('components', $category->components ?? '') }}</textarea>
                <x-input-error :messages="$errors->get('components')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="default_ot_observations" :value="__('Observaciones por defecto para OT')" />
                <textarea id="default_ot_observations" name="default_ot_observations" rows="2"
                          class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">{{ old('default_ot_observations', $category->default_ot_observations ?? '') }}</textarea>
                <x-input-error :messages="$errors->get('default_ot_observations')" class="mt-2" />
            </div>
        </div>
    </div>
</div>

<div class="flex items-center gap-4 mt-6">
    <x-primary-button>{{ $editing ? __('Actualizar') : __('Crear') }}</x-primary-button>
    <a href="{{ route('admin.equipment_categories.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
</div>
