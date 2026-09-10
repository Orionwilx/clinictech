{{-- Espera: $equipment (nullable), $clients, $areas, $brands, $models, $categories (plantillas), $specialtyOptions, $taskOptions, $accessoryOptions --}}
@php($editing = isset($equipment) && $equipment->exists)
@php($specialties = (array) old('specialties', $equipment->specialties ?? []))
@php($tasks = (array) old('maintenance_tasks', $equipment->maintenance_tasks ?? []))
@php($accessories = (array) old('accessories', $equipment->accessories ?? []))

<div class="space-y-8"
     x-data="{
        editing: @js($editing),
        category: '{{ old('category_id', $equipment->category_id ?? '') }}',
        brand: '{{ old('brand_id', $equipment->brand_id ?? '') }}',
        model: '{{ old('model_id', $equipment->model_id ?? '') }}',
        categories: {{ Illuminate\Support\Js::from($categories) }},
        brandNames: {{ Illuminate\Support\Js::from($brands) }},
        models: {{ Illuminate\Support\Js::from($models->map->only('id', 'name', 'brand_id', 'category_id')) }},
        get filteredBrands() {
            const ids = [...new Set(this.models.filter(m => String(m.category_id) === String(this.category)).map(m => m.brand_id))];
            return ids.map(id => ({ id, name: this.brandNames[id] })).filter(b => b.name).sort((a, b) => a.name.localeCompare(b.name));
        },
        get filteredModels() {
            return this.models.filter(m => String(m.brand_id) === String(this.brand) && String(m.category_id) === String(this.category));
        },
        onCategoryChange() {
            this.brand = ''; this.model = '';
            const t = this.categories.find(c => String(c.id) === String(this.category));
            if (!t) return;
            if (this.editing && !confirm('¿Aplicar la plantilla de esta categoría al equipo? Se reemplazarán los valores prediligenciados.')) return;
            this.applyTemplate(t);
        },
        applyTemplate(t) {
            const nameInput = document.getElementById('name');
            if (nameInput && (!this.editing || !nameInput.value)) nameInput.value = t.name ?? '';
            ['risk_class', 'maintenance_frequency', 'manufacturer', 'origin_country',
             'voltage', 'amperage', 'current', 'power', 'temperature', 'pressure', 'weight', 'speed',
             'predominant_technology', 'technical_observations', 'general_observations',
             'components', 'default_ot_observations'].forEach(f => {
                const el = document.getElementById(f);
                if (el) el.value = t[f] ?? '';
            });
            this.setChecks('specialties', t.specialties ?? []);
            this.setChecks('maintenance_tasks', t.maintenance_tasks ?? []);
            this.setChecks('accessories', t.accessories ?? []);
        },
        setChecks(field, values) {
            document.querySelectorAll(`input[name='${field}[]']`).forEach(cb => cb.checked = values.includes(cb.value));
        }
     }">
    {{-- Inventario --}}
    <div>
        <h3 class="text-sm font-semibold text-brand-900 border-b border-gray-100 pb-2 mb-4">Inventario</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4"
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
                        <option value="{{ $id }}" @selected(old('client_id', $equipment->client_id ?? request('client_id')) == $id)>{{ $name }}</option>
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
    </div>

    {{-- Identificación del equipo: categoría → marca → modelo --}}
    <div>
        <h3 class="text-sm font-semibold text-brand-900 border-b border-gray-100 pb-2 mb-4">Identificación del equipo</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <x-input-label for="category_id" :value="__('Categoría')" />
                <select id="category_id" name="category_id" x-model="category" @change="onCategoryChange()" required
                        class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                    <option value="">— Selecciona —</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat['id'] }}" @selected(old('category_id', $equipment->category_id ?? '') == $cat['id'])>{{ $cat['name'] }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-400">Al elegir la categoría se prediligencia la plantilla; puedes ajustar cualquier campo.</p>
                <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="brand_id" :value="__('Marca')" />
                <select id="brand_id" name="brand_id" x-model="brand" @change="model = ''" :disabled="!category"
                        class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm disabled:bg-gray-100">
                    <option value="">{{ __('— Selecciona una categoría primero —') }}</option>
                    <template x-for="b in filteredBrands" :key="b.id">
                        <option :value="b.id" x-text="b.name" :selected="String(b.id) === String(brand)"></option>
                    </template>
                </select>
                <x-input-error :messages="$errors->get('brand_id')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="model_id" :value="__('Modelo')" />
                <select id="model_id" name="model_id" x-model="model" :disabled="!brand"
                        class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm disabled:bg-gray-100">
                    <option value="">{{ __('— Selecciona una marca primero —') }}</option>
                    <template x-for="m in filteredModels" :key="m.id">
                        <option :value="m.id" x-text="m.name" :selected="String(m.id) === String(model)"></option>
                    </template>
                </select>
                <x-input-error :messages="$errors->get('model_id')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="name" :value="__('Equipo (p. ej. Autoclave)')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                              :value="old('name', $equipment->name ?? '')" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="risk_class" :value="__('Clasificación por riesgo (INVIMA)')" />
                <select id="risk_class" name="risk_class"
                        class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                    <option value="">— Selecciona —</option>
                    @foreach (\App\Models\Equipment::RISK_CLASSES as $value => $label)
                        <option value="{{ $value }}" @selected(old('risk_class', $equipment->risk_class ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('risk_class')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="maintenance_frequency" :value="__('Periodicidad de mantenimiento')" />
                <select id="maintenance_frequency" name="maintenance_frequency"
                        class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                    <option value="">— Selecciona —</option>
                    @foreach (\App\Models\Equipment::FREQUENCIES as $value => $label)
                        <option value="{{ $value }}" @selected(old('maintenance_frequency', $equipment->maintenance_frequency ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('maintenance_frequency')" class="mt-2" />
            </div>
            <div class="sm:col-span-3">
                <x-input-label :value="__('Clasificación por especialidad')" />
                <x-catalog-checkboxes name="specialties" catalog="specialties" columns="sm:grid-cols-4"
                    :options="$specialtyOptions" :selected="$specialties" />
                <x-input-error :messages="$errors->get('specialties')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="invima_registry" :value="__('Registro INVIMA')" />
                <x-text-input id="invima_registry" name="invima_registry" type="text" class="mt-1 block w-full"
                              :value="old('invima_registry', $equipment->invima_registry ?? '')" />
                <x-input-error :messages="$errors->get('invima_registry')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="manufacturer" :value="__('Fabricante')" />
                <x-text-input id="manufacturer" name="manufacturer" type="text" class="mt-1 block w-full"
                              :value="old('manufacturer', $equipment->manufacturer ?? '')" />
                <x-input-error :messages="$errors->get('manufacturer')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="origin_country" :value="__('País de origen')" />
                <x-text-input id="origin_country" name="origin_country" type="text" class="mt-1 block w-full"
                              :value="old('origin_country', $equipment->origin_country ?? '')" />
                <x-input-error :messages="$errors->get('origin_country')" class="mt-2" />
            </div>
        </div>
    </div>

    {{-- Datos específicos del cliente --}}
    <div>
        <h3 class="text-sm font-semibold text-brand-900 border-b border-gray-100 pb-2 mb-4">Datos específicos del cliente</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="entry_date" :value="__('Fecha de ingreso')" />
                <x-text-input id="entry_date" name="entry_date" type="date" class="mt-1 block w-full"
                              :value="old('entry_date', optional($equipment->entry_date ?? null)->format('Y-m-d'))" />
                <x-input-error :messages="$errors->get('entry_date')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="purchase_date" :value="__('Fecha de compra')" />
                <x-text-input id="purchase_date" name="purchase_date" type="date" class="mt-1 block w-full"
                              :value="old('purchase_date', optional($equipment->purchase_date ?? null)->format('Y-m-d'))" />
                <x-input-error :messages="$errors->get('purchase_date')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="serial_number" :value="__('Número de serie')" />
                <x-text-input id="serial_number" name="serial_number" type="text" class="mt-1 block w-full"
                              :value="old('serial_number', $equipment->serial_number ?? '')" required />
                <x-input-error :messages="$errors->get('serial_number')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="status" :value="__('Estado del equipo')" />
                <select id="status" name="status" required
                        class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                    @foreach (\App\Models\Equipment::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $equipment->status ?? 'active') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('status')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="warranty_status" :value="__('Equipo en garantía')" />
                <select id="warranty_status" name="warranty_status"
                        class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                    <option value="">— Selecciona —</option>
                    @foreach (\App\Models\Equipment::WARRANTY_STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected(old('warranty_status', $equipment->warranty_status ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('warranty_status')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="warranty_expiry" :value="__('Vencimiento de garantía')" />
                <x-text-input id="warranty_expiry" name="warranty_expiry" type="date" class="mt-1 block w-full"
                              :value="old('warranty_expiry', optional($equipment->warranty_expiry ?? null)->format('Y-m-d'))" />
                <x-input-error :messages="$errors->get('warranty_expiry')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="acquisition_type" :value="__('Tipo de adquisición')" />
                <select id="acquisition_type" name="acquisition_type"
                        class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                    <option value="">— Selecciona —</option>
                    @foreach (\App\Models\Equipment::ACQUISITION_TYPES as $value => $label)
                        <option value="{{ $value }}" @selected(old('acquisition_type', $equipment->acquisition_type ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('acquisition_type')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="location" :value="__('Ubicación / sede (dirección de la instalación)')" />
                <x-text-input id="location" name="location" type="text" class="mt-1 block w-full"
                              :value="old('location', $equipment->location ?? '')" />
                <x-input-error :messages="$errors->get('location')" class="mt-2" />
            </div>
        </div>
    </div>

    {{-- Características técnicas --}}
    <div>
        <h3 class="text-sm font-semibold text-brand-900 border-b border-gray-100 pb-2 mb-4">Características técnicas</h3>
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
                                  :value="old($field, $equipment->$field ?? '')" />
                    <x-input-error :messages="$errors->get($field)" class="mt-2" />
                </div>
            @endforeach
            <div class="sm:col-span-3">
                <x-input-label for="technical_observations" :value="__('Observaciones técnicas')" />
                <textarea id="technical_observations" name="technical_observations" rows="2"
                          class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">{{ old('technical_observations', $equipment->technical_observations ?? '') }}</textarea>
                <x-input-error :messages="$errors->get('technical_observations')" class="mt-2" />
            </div>
            <div class="sm:col-span-3">
                <x-input-label for="general_observations" :value="__('Observaciones generales')" />
                <textarea id="general_observations" name="general_observations" rows="2"
                          class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">{{ old('general_observations', $equipment->general_observations ?? '') }}</textarea>
                <x-input-error :messages="$errors->get('general_observations')" class="mt-2" />
            </div>
        </div>
    </div>

    {{-- Subtareas de mantenimiento (snapshot de la plantilla) --}}
    <div>
        <h3 class="text-sm font-semibold text-brand-900 border-b border-gray-100 pb-2 mb-4">Subtareas de mantenimiento</h3>
        <p class="text-xs text-gray-400 mb-3">Marca las subtareas que aplican a este equipo. Se propondrán al crear una orden de mantenimiento.</p>
        <x-catalog-checkboxes name="maintenance_tasks" catalog="maintenance_tasks"
            :options="$taskOptions" :selected="$tasks" />
        <x-input-error :messages="$errors->get('maintenance_tasks')" class="mt-2" />
    </div>

    {{-- Estado de accesorios (snapshot de la plantilla) --}}
    <div>
        <h3 class="text-sm font-semibold text-brand-900 border-b border-gray-100 pb-2 mb-4">Estado de accesorios</h3>
        <p class="text-xs text-gray-400 mb-3">Marca los accesorios con los que cuenta este equipo.</p>
        <x-catalog-checkboxes name="accessories" catalog="accessories"
            :options="$accessoryOptions" :selected="$accessories" />
        <x-input-error :messages="$errors->get('accessories')" class="mt-2" />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
            <div>
                <x-input-label for="components" :value="__('Componentes / accesorios (detalle)')" />
                <textarea id="components" name="components" rows="2"
                          class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">{{ old('components', $equipment->components ?? '') }}</textarea>
                <x-input-error :messages="$errors->get('components')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="default_ot_observations" :value="__('Observaciones por defecto para OT')" />
                <textarea id="default_ot_observations" name="default_ot_observations" rows="2"
                          class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">{{ old('default_ot_observations', $equipment->default_ot_observations ?? '') }}</textarea>
                <x-input-error :messages="$errors->get('default_ot_observations')" class="mt-2" />
            </div>
        </div>
    </div>

    {{-- Observaciones generales del equipo --}}
    <div>
        <x-input-label for="notes" :value="__('Notas / observaciones del equipo')" />
        <textarea id="notes" name="notes" rows="3"
                  class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">{{ old('notes', $equipment->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
    </div>
</div>

<div class="flex items-center gap-4 mt-6">
    <x-primary-button>{{ $editing ? __('Actualizar') : __('Crear') }}</x-primary-button>
    <a href="{{ route('admin.equipment.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
</div>
