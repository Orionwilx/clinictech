{{-- Espera: $workOrder (nullable), $clients (id=>nombre), $equipment (Collection), $technicians (id=>nombre) --}}
@php($editing = isset($workOrder) && $workOrder->exists)

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4"
     x-data="{
        editing: {{ $editing ? 'true' : 'false' }},
        client: '{{ old('client_id', $workOrder->client_id ?? request('client_id')) }}',
        equipmentId: '{{ old('equipment_id', $workOrder->equipment_id ?? request('equipment_id')) }}',
        equipment: {{ Illuminate\Support\Js::from($equipment->map->only('id', 'name', 'client_id', 'maintenance_tasks', 'accessories')) }},
        selectedTasks: {{ Illuminate\Support\Js::from((array) old('maintenance_tasks', $workOrder->maintenance_tasks ?? [])) }},
        selectedAccessories: {{ Illuminate\Support\Js::from((array) old('accessories_checked', $workOrder->accessories_checked ?? [])) }},
        get filteredEquipment() { return this.equipment.filter(e => String(e.client_id) === String(this.client)); },
        init() { if (!this.editing && this.equipmentId && this.selectedTasks.length === 0 && this.selectedAccessories.length === 0) this.applyTemplate(); },
        onClientChange() { this.equipmentId = ''; if (!this.editing) { this.selectedTasks = []; this.selectedAccessories = []; } },
        applyTemplate() {
            if (this.editing) return;
            const eq = this.equipment.find(e => String(e.id) === String(this.equipmentId));
            this.selectedTasks = eq && eq.maintenance_tasks ? [...eq.maintenance_tasks] : [];
            this.selectedAccessories = eq && eq.accessories ? [...eq.accessories] : [];
        }
     }">
    <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="client_id" :value="__('Cliente')" />
            <select id="client_id" name="client_id" x-model="client" @change="onClientChange()" required
                    class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                <option value="">— Selecciona —</option>
                @foreach ($clients as $id => $name)
                    <option value="{{ $id }}" @selected(old('client_id', $workOrder->client_id ?? request('client_id')) == $id)>{{ $name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="equipment_id" :value="__('Equipo (opcional)')" />
            <select id="equipment_id" name="equipment_id" x-model="equipmentId" @change="applyTemplate()" :disabled="!client"
                    class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm disabled:bg-gray-100">
                <option value="">{{ __('— Sin equipo —') }}</option>
                <template x-for="e in filteredEquipment" :key="e.id">
                    <option :value="e.id" x-text="e.name" :selected="String(e.id) === String(equipmentId)"></option>
                </template>
            </select>
            <x-input-error :messages="$errors->get('equipment_id')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="technician_id" :value="__('Técnico asignado (opcional)')" />
        <x-searchable-select name="technician_id" id="technician_id"
            :options="$technicians"
            :selected="old('technician_id', $workOrder->technician_id ?? request('technician_id'))"
            placeholder="— Sin asignar —" />
        <x-input-error :messages="$errors->get('technician_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="title" :value="__('Asunto')" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                      :value="old('title', $workOrder->title ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="type" :value="__('Tipo')" />
        <select id="type" name="type" required
                class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
            @foreach (\App\Models\WorkOrder::TYPES as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $workOrder->type ?? request('type', 'corrective')) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('type')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="priority" :value="__('Prioridad')" />
        <select id="priority" name="priority" required
                class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
            @foreach (\App\Models\WorkOrder::PRIORITIES as $value => $label)
                <option value="{{ $value }}" @selected(old('priority', $workOrder->priority ?? 'medium') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('priority')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="status" :value="__('Estado')" />
        <select id="status" name="status" required
                class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
            @foreach (\App\Models\WorkOrder::STATUSES as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $workOrder->status ?? 'open') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="scheduled_at" :value="__('Fecha programada')" />
        <x-text-input id="scheduled_at" name="scheduled_at" type="datetime-local" class="mt-1 block w-full"
                      :value="old('scheduled_at', optional($workOrder->scheduled_at ?? null)->format('Y-m-d\TH:i'))" />
        <x-input-error :messages="$errors->get('scheduled_at')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="description" :value="__('Descripción de la solicitud / falla')" />
        <textarea id="description" name="description" rows="3"
                  class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">{{ old('description', $workOrder->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="diagnosis" :value="__('Diagnóstico')" />
        <textarea id="diagnosis" name="diagnosis" rows="3"
                  class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">{{ old('diagnosis', $workOrder->diagnosis ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('diagnosis')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="work_performed" :value="__('Actividades realizadas / solución')" />
        <textarea id="work_performed" name="work_performed" rows="3"
                  class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">{{ old('work_performed', $workOrder->work_performed ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('work_performed')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="additional_observations" :value="__('Observaciones adicionales')" />
        <textarea id="additional_observations" name="additional_observations" rows="2"
                  class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">{{ old('additional_observations', $workOrder->additional_observations ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('additional_observations')" class="mt-2" />
    </div>

    {{-- Checklist de mantenimiento (plantilla del equipo, ejecución en la OT) --}}
    <div class="sm:col-span-2 border-t border-gray-100 pt-4" x-show="equipmentId" x-cloak>
        <h3 class="text-sm font-semibold text-brand-900 mb-1">Checklist de mantenimiento</h3>
        <p class="text-xs text-gray-400 mb-3">Se precargan las subtareas y accesorios definidos en el equipo. Marca lo ejecutado/revisado en esta orden.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase mb-2">Subtareas ejecutadas</p>
                <div class="grid grid-cols-1 gap-1.5">
                    @foreach (\App\Models\Equipment::MAINTENANCE_TASKS as $value => $label)
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="maintenance_tasks[]" value="{{ $value }}" x-model="selectedTasks"
                                   class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase mb-2">Accesorios revisados</p>
                <div class="grid grid-cols-1 gap-1.5">
                    @foreach (\App\Models\Equipment::ACCESSORIES as $value => $label)
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="accessories_checked[]" value="{{ $value }}" x-model="selectedAccessories"
                                   class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="flex items-center gap-4 mt-6">
    <x-primary-button>{{ $editing ? __('Actualizar') : __('Crear') }}</x-primary-button>
    <a href="{{ route('admin.work_orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
</div>
