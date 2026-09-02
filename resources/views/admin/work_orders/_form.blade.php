{{-- Espera: $workOrder (nullable), $clients (id=>nombre), $equipment (Collection), $technicians (id=>nombre) --}}
@php($editing = isset($workOrder) && $workOrder->exists)

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4"
         x-data="{
            client: '{{ old('client_id', $workOrder->client_id ?? request('client_id')) }}',
            equipmentId: '{{ old('equipment_id', $workOrder->equipment_id ?? request('equipment_id')) }}',
            equipment: {{ Illuminate\Support\Js::from($equipment->map->only('id', 'name', 'client_id')) }},
            get filteredEquipment() { return this.equipment.filter(e => String(e.client_id) === String(this.client)); }
         }">
        <div>
            <x-input-label for="client_id" :value="__('Cliente')" />
            <select id="client_id" name="client_id" x-model="client" @change="equipmentId = ''" required
                    class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                <option value="">— Selecciona —</option>
                @foreach ($clients as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="equipment_id" :value="__('Equipo (opcional)')" />
            <select id="equipment_id" name="equipment_id" x-model="equipmentId" :disabled="!client"
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
        <select id="technician_id" name="technician_id"
                class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
            <option value="">— Sin asignar —</option>
            @foreach ($technicians as $id => $name)
                <option value="{{ $id }}" @selected(old('technician_id', $workOrder->technician_id ?? '') == $id)>{{ $name }}</option>
            @endforeach
        </select>
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
</div>

<div class="flex items-center gap-4 mt-6">
    <x-primary-button>{{ $editing ? __('Actualizar') : __('Crear') }}</x-primary-button>
    <a href="{{ route('admin.work_orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
</div>
