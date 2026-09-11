{{-- Espera: $workOrder (nullable), $clients (id=>nombre), $equipment (Collection), $technicians (id=>nombre), $taskOptions, $accessoryOptions --}}
@php($editing = isset($workOrder) && $workOrder->exists)
@php($eqFields = \App\Services\WorkOrderService::EQUIPMENT_FIELDS)
{{-- Equipo y cliente bloqueados cuando la OT ya está en curso (in_progress en adelante). --}}
@php($equipmentLocked = $editing && in_array($workOrder->status, ['in_progress', 'pending_review', 'completed', 'closed']))

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4"
     x-data="{
        editing: {{ $editing ? 'true' : 'false' }},
        repopulate: {{ old() ? 'true' : 'false' }},
        client: '{{ old('client_id', $workOrder->client_id ?? request('client_id')) }}',
        equipmentId: '{{ old('equipment_id', $workOrder->equipment_id ?? request('equipment_id')) }}',
        preEquipmentId: '{{ old('equipment_id', $workOrder->equipment_id ?? request('equipment_id')) }}',
        equipment: {{ Illuminate\Support\Js::from($equipment) }},
        taskCatalog: {{ Illuminate\Support\Js::from($taskOptions) }},
        accessoryCatalog: {{ Illuminate\Support\Js::from($accessoryOptions) }},
        selectedTasks: {{ Illuminate\Support\Js::from((array) old('maintenance_tasks', $workOrder->maintenance_tasks ?? [])) }},
        selectedAccessories: {{ Illuminate\Support\Js::from((array) old('accessories_checked', $workOrder->accessories_checked ?? [])) }},
        eq: {{ Illuminate\Support\Js::from(collect($eqFields)->mapWithKeys(fn ($f) => [$f => old('eq_'.$f, '')])->all()) }},
        newTask: '', newAccessory: '',
        get filteredEquipment() { return this.equipment.filter(e => String(e.client_id) === String(this.client)); },
        get selectedEquipment() { return this.equipment.find(e => String(e.id) === String(this.equipmentId)); },
        get allTasks() { return [...new Set([...this.taskCatalog, ...this.selectedTasks])]; },
        get allAccessories() { return [...new Set([...this.accessoryCatalog, ...this.selectedAccessories])]; },
        init() {
            // Espera a que el <select> (x-for) tenga sus opciones antes de fijar el equipo,
            // reasegura el valor del select y precarga los datos del equipo preseleccionado.
            this.$nextTick(() => {
                if (this.preEquipmentId && !this.repopulate) {
                    this.equipmentId = this.preEquipmentId;
                    const sel = document.getElementById('equipment_id');
                    if (sel) sel.value = this.preEquipmentId;
                    this.loadFromEquipment();
                }
            });
        },
        onClientChange() { this.equipmentId = ''; this.resetPanel(); },
        onEquipmentChange() { this.loadFromEquipment(); },
        loadFromEquipment() {
            const e = this.selectedEquipment;
            if (!e) { this.resetPanel(); return; }
            this.selectedTasks = e.maintenance_tasks ? [...e.maintenance_tasks] : [];
            this.selectedAccessories = e.accessories ? [...e.accessories] : [];
            {{ collect($eqFields)->map(fn ($f) => "this.eq.$f = e.$f ?? '';")->implode(' ') }}
        },
        resetPanel() {
            this.selectedTasks = []; this.selectedAccessories = [];
            {{ collect($eqFields)->map(fn ($f) => "this.eq.$f = '';")->implode(' ') }}
        },
        addItem(kind) {
            const isTask = kind === 'task';
            const name = (isTask ? this.newTask : this.newAccessory).trim();
            if (!name) return;
            if (isTask) { if (!this.selectedTasks.includes(name)) this.selectedTasks.push(name); this.newTask = ''; }
            else { if (!this.selectedAccessories.includes(name)) this.selectedAccessories.push(name); this.newAccessory = ''; }
        }
     }">
    <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="client_id" :value="__('Cliente')" />
            @if ($equipmentLocked)
                <input type="hidden" name="client_id" value="{{ $workOrder->client_id }}">
                <div class="mt-1 block w-full border border-gray-200 bg-gray-50 rounded-md shadow-sm px-3 py-2 text-sm text-gray-700">
                    {{ $workOrder->client?->name ?? '—' }}
                </div>
            @else
                <select id="client_id" name="client_id" x-model="client" @change="onClientChange()" required
                        class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                    <option value="">— Selecciona —</option>
                    @foreach ($clients as $id => $name)
                        <option value="{{ $id }}" @selected(old('client_id', $workOrder->client_id ?? request('client_id')) == $id)>{{ $name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
            @endif
        </div>

        <div>
            <x-input-label for="equipment_id" :value="__('Equipo')" />
            @if ($equipmentLocked)
                <input type="hidden" name="equipment_id" value="{{ $workOrder->equipment_id }}">
                <div class="mt-1 block w-full border border-gray-200 bg-gray-50 rounded-md shadow-sm px-3 py-2 text-sm text-gray-700">
                    {{ $workOrder->equipment?->name ?? '— Sin equipo —' }}
                    <span class="text-xs text-gray-400 ml-1">(OT en curso — no editable)</span>
                </div>
            @else
                <select id="equipment_id" name="equipment_id" x-model="equipmentId" @change="onEquipmentChange()" :disabled="!client"
                        class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm disabled:bg-gray-100">
                    <option value="">{{ __('— Sin equipo —') }}</option>
                    <template x-for="e in filteredEquipment" :key="e.id">
                        <option :value="e.id" x-text="e.name" :selected="String(e.id) === String(equipmentId)"></option>
                    </template>
                </select>
                <x-input-error :messages="$errors->get('equipment_id')" class="mt-2" />
            @endif
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
        <x-text-input id="scheduled_at" name="scheduled_at" type="date" class="mt-1 block w-full"
                      :value="old('scheduled_at', optional($workOrder->scheduled_at ?? null)->format('Y-m-d'))" />
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

    {{-- Panel del equipo: editable y persistente en su ficha --}}
    <div class="sm:col-span-2 border-t border-gray-100 pt-4" x-show="equipmentId" x-cloak>
        <h3 class="text-sm font-semibold text-brand-900 mb-1">Datos del equipo</h3>
        <p class="text-xs text-gray-400 mb-4">Estos campos se <strong>guardan en la ficha del equipo</strong> (y quedan registrados en esta orden). Edítalos aquí sin salir de la OT.</p>

        {{-- Resumen de identificación (solo lectura) --}}
        <div class="mb-4 rounded-lg bg-gray-50 border border-gray-100 p-3" x-show="selectedEquipment" x-cloak>
            <dl class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-2 text-sm">
                <template x-for="row in [
                    ['Marca', selectedEquipment?.brand],
                    ['Modelo', selectedEquipment?.model],
                    ['Categoría', selectedEquipment?.category],
                    ['Serial', selectedEquipment?.serial_number],
                    ['Área', selectedEquipment?.area],
                    ['Ubicación / sede', selectedEquipment?.location],
                    ['Registro INVIMA', selectedEquipment?.invima_registry],
                ]" :key="row[0]">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase" x-text="row[0]"></dt>
                        <dd class="text-gray-900" x-text="row[1] || '—'"></dd>
                    </div>
                </template>
            </dl>
        </div>

        {{-- Características técnicas --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div>
                <x-input-label for="eq_risk_class" :value="__('Clase de riesgo')" class="text-xs" />
                <select id="eq_risk_class" name="eq_risk_class" x-model="eq.risk_class"
                        class="mt-1 block w-full text-sm border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                    <option value="">—</option>
                    @foreach (\App\Models\Equipment::RISK_CLASSES as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @foreach ([
                'voltage' => 'Voltaje', 'amperage' => 'Amperaje', 'current' => 'Corriente',
                'power' => 'Potencia', 'temperature' => 'Temperatura', 'pressure' => 'Presión',
                'weight' => 'Peso', 'speed' => 'Velocidad', 'predominant_technology' => 'Tecnología',
            ] as $field => $label)
                <div>
                    <x-input-label :for="'eq_'.$field" :value="__($label)" class="text-xs" />
                    <input type="text" id="eq_{{ $field }}" name="eq_{{ $field }}" x-model="eq.{{ $field }}"
                           class="mt-1 block w-full text-sm border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                </div>
            @endforeach
        </div>

        {{-- Subtareas y accesorios --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-5">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase mb-2">Subtareas de mantenimiento</p>
                <div class="grid grid-cols-1 gap-1.5">
                    <template x-for="task in allTasks" :key="task">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="maintenance_tasks[]" :value="task" x-model="selectedTasks"
                                   class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span x-text="task"></span>
                        </label>
                    </template>
                    <p x-show="allTasks.length === 0" class="text-xs text-gray-400">Sin subtareas en el catálogo.</p>
                </div>
                <div class="mt-2 flex items-center gap-2">
                    <input type="text" x-model="newTask" @keydown.enter.prevent="addItem('task')"
                           placeholder="Agregar subtarea…"
                           class="block w-44 text-xs border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                    <button type="button" @click="addItem('task')" class="px-2.5 py-1.5 text-xs font-semibold text-brand-700 bg-brand-50 rounded-md hover:bg-brand-100">+ Agregar</button>
                </div>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase mb-2">Accesorios</p>
                <div class="grid grid-cols-1 gap-1.5">
                    <template x-for="accessory in allAccessories" :key="accessory">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="accessories_checked[]" :value="accessory" x-model="selectedAccessories"
                                   class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span x-text="accessory"></span>
                        </label>
                    </template>
                    <p x-show="allAccessories.length === 0" class="text-xs text-gray-400">Sin accesorios en el catálogo.</p>
                </div>
                <div class="mt-2 flex items-center gap-2">
                    <input type="text" x-model="newAccessory" @keydown.enter.prevent="addItem('accessory')"
                           placeholder="Agregar accesorio…"
                           class="block w-44 text-xs border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                    <button type="button" @click="addItem('accessory')" class="px-2.5 py-1.5 text-xs font-semibold text-brand-700 bg-brand-50 rounded-md hover:bg-brand-100">+ Agregar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="flex items-center gap-4 mt-6">
    <x-primary-button>{{ $editing ? __('Actualizar') : __('Crear') }}</x-primary-button>
    <a href="{{ route('admin.work_orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
</div>
