<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="'Orden '.$workOrder->code"
            :breadcrumbs="[['label' => 'Panel', 'href' => route('technician.dashboard')], ['label' => 'Órdenes', 'href' => route('technician.work_orders.index')], ['label' => $workOrder->code]]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            @if (in_array($workOrder->status, ['assigned', 'in_progress']) && ! auth()->user()->signatureBase64())
                <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="text-sm text-amber-800">No tienes una firma registrada. Cárgala en tu perfil para que aparezca en las órdenes.</p>
                        <a href="{{ route('profile.edit') }}" class="text-sm font-medium text-amber-700 underline hover:text-amber-900">Ir a mi perfil →</a>
                    </div>
                </div>
            @endif

            {{-- Motivo de devolución --}}
            @if ($workOrder->rejection_reason && $workOrder->status === 'in_progress')
                <div class="rounded-md bg-red-50 border border-red-200 p-4">
                    <p class="text-sm font-semibold text-red-800">El administrador devolvió esta orden:</p>
                    <p class="text-sm text-red-700 mt-1">{{ $workOrder->rejection_reason }}</p>
                </div>
            @endif

            {{-- Datos de la OT --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach ([
                        'Nº de orden' => $workOrder->code,
                        'Asunto' => $workOrder->title,
                        'Cliente' => optional($workOrder->client)->name ?? '—',
                        'Equipo' => optional($workOrder->equipment)->name ?? '—',
                        'Tipo' => $workOrder->typeLabel(),
                        'Prioridad' => $workOrder->priorityLabel(),
                        'Estado' => $workOrder->statusLabel(),
                        'Fecha programada' => optional($workOrder->scheduled_at)->format('Y-m-d') ?: '—',
                    ] as $label => $value)
                        <div class="rounded-lg bg-gray-50 border border-gray-100 p-3">
                            <dt class="text-xs font-medium text-gray-500 uppercase">{{ $label }}</dt>
                            <dd class="mt-0.5 text-sm text-gray-900 break-words">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
                @if ($workOrder->description)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs font-medium text-gray-500 uppercase mb-1">Descripción de la solicitud</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $workOrder->description }}</p>
                    </div>
                @endif
            </div>

            {{-- Formulario de diligenciamiento (solo si está en progreso o asignado) --}}
            @if (in_array($workOrder->status, ['assigned', 'in_progress']))
                <div class="bg-white shadow-sm sm:rounded-lg p-6"
                     x-data="{
                        dirty: false, saving: false, saved: false, error: false,
                        init() {
                            this.$refs.form.addEventListener('input', () => { this.dirty = true; this.saved = false; });
                            setInterval(() => { if (this.dirty && !this.saving) this.autosave(); }, 20000);
                        },
                        async autosave() {
                            this.saving = true; this.error = false;
                            const data = new FormData(this.$refs.form);
                            data.set('_method', 'PUT');
                            try {
                                const res = await fetch('{{ route('technician.work_orders.update', $workOrder) }}', {
                                    method: 'POST',
                                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                    body: data,
                                });
                                if (!res.ok) throw new Error();
                                this.dirty = false; this.saved = true;
                            } catch { this.error = true; }
                            this.saving = false;
                        }
                     }">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-900">Diligenciar formulario de mantenimiento</h3>
                        <span class="text-xs" x-cloak>
                            <span x-show="saving" class="text-gray-400">Guardando borrador…</span>
                            <span x-show="!saving && saved" class="text-green-600">✓ Borrador guardado</span>
                            <span x-show="!saving && error" class="text-red-600">Sin conexión — tu avance se reintentará</span>
                        </span>
                    </div>

                    <form method="POST" action="{{ route('technician.work_orders.update', $workOrder) }}" x-ref="form">
                        @csrf @method('PUT')

                        <div class="space-y-4">
                            <div>
                                <x-input-label for="diagnosis" value="Diagnóstico" />
                                <textarea id="diagnosis" name="diagnosis" rows="3"
                                          class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">{{ old('diagnosis', $workOrder->diagnosis) }}</textarea>
                            </div>
                            <div>
                                <x-input-label for="work_performed" value="Actividades realizadas / solución" />
                                <textarea id="work_performed" name="work_performed" rows="3"
                                          class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">{{ old('work_performed', $workOrder->work_performed) }}</textarea>
                            </div>
                            <div>
                                <x-input-label for="additional_observations" value="Observaciones adicionales" />
                                <textarea id="additional_observations" name="additional_observations" rows="2"
                                          class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">{{ old('additional_observations', $workOrder->additional_observations) }}</textarea>
                            </div>

                            @if ($workOrder->equipment)
                            @php($eq = $workOrder->equipment)
                            @php($taskCatalog = collect($taskOptions)->merge($eq->maintenance_tasks ?? [])->merge((array) $workOrder->maintenance_tasks)->unique()->values())
                            @php($accessoryCatalog = collect($accessoryOptions)->merge($eq->accessories ?? [])->merge((array) $workOrder->accessories_checked)->unique()->values())
                            <div class="pt-3 border-t border-gray-100"
                                 x-data="{
                                    tasks: {{ Illuminate\Support\Js::from($taskCatalog) }},
                                    accessories: {{ Illuminate\Support\Js::from($accessoryCatalog) }},
                                    selectedTasks: {{ Illuminate\Support\Js::from((array) old('maintenance_tasks', $workOrder->maintenance_tasks ?? $eq->maintenance_tasks ?? [])) }},
                                    selectedAccessories: {{ Illuminate\Support\Js::from((array) old('accessories_checked', $workOrder->accessories_checked ?? $eq->accessories ?? [])) }},
                                    newTask: '', newAccessory: '',
                                    addItem(kind) {
                                        const isTask = kind === 'task';
                                        const name = (isTask ? this.newTask : this.newAccessory).trim();
                                        if (!name) return;
                                        if (isTask) { if (!this.tasks.includes(name)) this.tasks.push(name); if (!this.selectedTasks.includes(name)) this.selectedTasks.push(name); this.newTask = ''; }
                                        else { if (!this.accessories.includes(name)) this.accessories.push(name); if (!this.selectedAccessories.includes(name)) this.selectedAccessories.push(name); this.newAccessory = ''; }
                                    }
                                 }">
                                <p class="text-sm font-semibold text-brand-900 mb-1">Datos del equipo</p>
                                <p class="text-xs text-gray-400 mb-3">Se <strong>guardan en la ficha del equipo</strong> y quedan registrados en esta orden.</p>

                                {{-- Resumen de identificación (solo lectura) --}}
                                <div class="mb-4 rounded-lg bg-gray-50 border border-gray-100 p-3">
                                    <dl class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-2 text-sm">
                                        @foreach ([
                                            'Marca' => optional($eq->brand)->name,
                                            'Modelo' => optional($eq->model)->name,
                                            'Categoría' => optional($eq->category)->name,
                                            'Serial' => $eq->serial_number,
                                            'Área' => optional($eq->area)->name,
                                            'Ubicación / sede' => $eq->location,
                                            'Registro INVIMA' => $eq->invima_registry,
                                        ] as $label => $value)
                                            <div>
                                                <dt class="text-xs font-medium text-gray-500 uppercase">{{ $label }}</dt>
                                                <dd class="text-gray-900">{{ $value ?: '—' }}</dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                </div>

                                {{-- Características técnicas del equipo (editables) --}}
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                                    <div>
                                        <x-input-label for="eq_risk_class" value="Clase de riesgo" class="text-xs" />
                                        <select id="eq_risk_class" name="eq_risk_class"
                                                class="mt-1 block w-full text-sm border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                                            <option value="">—</option>
                                            @foreach (\App\Models\Equipment::RISK_CLASSES as $value => $label)
                                                <option value="{{ $value }}" @selected(old('eq_risk_class', $eq->risk_class) === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @foreach ([
                                        'voltage' => 'Voltaje', 'amperage' => 'Amperaje', 'current' => 'Corriente',
                                        'power' => 'Potencia', 'temperature' => 'Temperatura', 'pressure' => 'Presión',
                                        'weight' => 'Peso', 'speed' => 'Velocidad', 'predominant_technology' => 'Tecnología',
                                    ] as $field => $label)
                                        <div>
                                            <x-input-label :for="'eq_'.$field" :value="$label" class="text-xs" />
                                            <input type="text" id="eq_{{ $field }}" name="eq_{{ $field }}"
                                                   value="{{ old('eq_'.$field, $eq->$field) }}"
                                                   class="mt-1 block w-full text-sm border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Subtareas y accesorios (editables + agregar) --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase mb-2">Subtareas de mantenimiento</p>
                                        <template x-for="task in tasks" :key="task">
                                            <label class="flex items-center gap-2 text-sm text-gray-700 mb-1">
                                                <input type="checkbox" name="maintenance_tasks[]" :value="task" x-model="selectedTasks"
                                                       class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                                <span x-text="task"></span>
                                            </label>
                                        </template>
                                        <p x-show="tasks.length === 0" class="text-xs text-gray-400">Sin subtareas.</p>
                                        <div class="mt-2 flex items-center gap-2">
                                            <input type="text" x-model="newTask" @keydown.enter.prevent="addItem('task')" placeholder="Agregar subtarea…"
                                                   class="block w-40 text-xs border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                                            <button type="button" @click="addItem('task')" class="px-2.5 py-1.5 text-xs font-semibold text-brand-700 bg-brand-50 rounded-md hover:bg-brand-100">+ Agregar</button>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase mb-2">Accesorios</p>
                                        <template x-for="accessory in accessories" :key="accessory">
                                            <label class="flex items-center gap-2 text-sm text-gray-700 mb-1">
                                                <input type="checkbox" name="accessories_checked[]" :value="accessory" x-model="selectedAccessories"
                                                       class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                                <span x-text="accessory"></span>
                                            </label>
                                        </template>
                                        <p x-show="accessories.length === 0" class="text-xs text-gray-400">Sin accesorios.</p>
                                        <div class="mt-2 flex items-center gap-2">
                                            <input type="text" x-model="newAccessory" @keydown.enter.prevent="addItem('accessory')" placeholder="Agregar accesorio…"
                                                   class="block w-40 text-xs border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                                            <button type="button" @click="addItem('accessory')" class="px-2.5 py-1.5 text-xs font-semibold text-brand-700 bg-brand-50 rounded-md hover:bg-brand-100">+ Agregar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-3 mt-6">
                            <x-primary-button>Guardar borrador</x-primary-button>
                            <span class="text-xs text-gray-400">El borrador también se guarda automáticamente mientras escribes.</span>
                        </div>
                    </form>

                    {{-- Evidencias fotográficas (subida inmediata, comprimidas en servidor) --}}
                    <div class="mt-6 pt-4 border-t border-gray-100"
                         x-data="{
                            photos: {{ Illuminate\Support\Js::from($workOrder->photos->map(fn ($p) => ['id' => $p->id, 'url' => $p->url(), 'name' => $p->original_name, 'label' => $p->label])) }},
                            uploading: 0, error: '',
                            async saveLabel(photo, label) {
                                photo.label = label;
                                await fetch(`{{ url('technician/work_orders/'.$workOrder->id.'/photos') }}/${photo.id}`, {
                                    method: 'PATCH',
                                    headers: {
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                    },
                                    body: JSON.stringify({ label }),
                                });
                            },
                            async upload(files) {
                                this.error = '';
                                for (const file of files) {
                                    this.uploading++;
                                    const data = new FormData();
                                    data.append('photo', file);
                                    try {
                                        const res = await fetch('{{ route('technician.work_orders.photos.store', $workOrder) }}', {
                                            method: 'POST',
                                            headers: {
                                                'Accept': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                            },
                                            body: data,
                                        });
                                        if (res.status === 201) this.photos.push(await res.json());
                                        else if (res.status === 422) this.error = Object.values((await res.json()).errors ?? {}).flat()[0] ?? 'Archivo inválido.';
                                        else this.error = 'No se pudo subir la foto.';
                                    } catch { this.error = 'Sin conexión: la foto no se subió, inténtalo de nuevo.'; }
                                    this.uploading--;
                                }
                                this.$refs.fileInput.value = '';
                            },
                            async remove(photo) {
                                if (!await window.appConfirm('¿Eliminar esta foto?', { title: 'Eliminar foto', confirmLabel: 'Eliminar' })) return;
                                const res = await fetch(`{{ url('technician/work_orders/'.$workOrder->id.'/photos') }}/${photo.id}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                    },
                                });
                                if (res.ok) this.photos = this.photos.filter(p => p.id !== photo.id);
                            }
                         }">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-medium text-gray-500 uppercase">Evidencias fotográficas</p>
                            <span class="text-xs text-gray-400" x-show="uploading > 0" x-cloak>Subiendo y comprimiendo…</span>
                        </div>
                        <p class="text-xs text-gray-400 mb-3">Las fotos se suben y guardan al instante (se comprimen automáticamente); no se pierden aunque no envíes el formulario.</p>

                        <div class="grid grid-cols-3 sm:grid-cols-5 gap-3 mb-3" x-show="photos.length" x-cloak>
                            <template x-for="photo in photos" :key="photo.id">
                                <div>
                                    <div class="relative group">
                                        <a :href="photo.url" target="_blank">
                                            <img :src="photo.url" :alt="photo.name" class="h-24 w-full object-cover rounded-lg border border-gray-200">
                                        </a>
                                        <button type="button" @click="remove(photo)"
                                                class="absolute -top-2 -right-2 hidden group-hover:flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-white text-xs shadow"
                                                title="Eliminar foto">✕</button>
                                    </div>
                                    <input type="text" :value="photo.label ?? ''" @change="saveLabel(photo, $event.target.value)"
                                           placeholder="Descripción…" maxlength="255"
                                           class="mt-1 block w-full text-xs border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                                </div>
                            </template>
                        </div>

                        <label class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-brand-700 bg-brand-50 rounded-md hover:bg-brand-100 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316ZM16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z"/></svg>
                            Agregar fotos
                            <input type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden"
                                   x-ref="fileInput" @change="upload($event.target.files)">
                        </label>
                        <p class="mt-2 text-xs text-red-600" x-show="error" x-text="error" x-cloak></p>
                    </div>

                    {{-- Firmas (solo lectura) --}}
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <p class="text-xs font-medium text-gray-500 uppercase mb-3">Firmas</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            @php($techSig = $workOrder->technician?->user?->signatureBase64())
                            <div class="text-center">
                                @if ($techSig)
                                    <img src="{{ $techSig }}" alt="Firma técnico" class="h-20 mx-auto mb-2 object-contain rounded border border-gray-200 bg-white p-1">
                                @else
                                    <div class="h-20 border-b border-gray-400 mb-2"></div>
                                @endif
                                <p class="text-sm font-medium text-gray-900">{{ $workOrder->technician?->name ?? '—' }}</p>
                                <p class="text-xs text-gray-500">Técnico responsable</p>
                            </div>
                            @php($companySig = \App\Support\CompanySignature::base64())
                            <div class="text-center">
                                @if ($companySig)
                                    <img src="{{ $companySig }}" alt="Firma empresa" class="h-20 mx-auto mb-2 object-contain rounded border border-gray-200 bg-white p-1">
                                @else
                                    <div class="h-20 border-b border-gray-400 mb-2"></div>
                                @endif
                                <p class="text-sm font-medium text-gray-900">Administrador</p>
                                <p class="text-xs text-gray-500">Administrador</p>
                            </div>
                        </div>
                    </div>

                    {{-- Botón enviar a revisión --}}
                    @if ($workOrder->status === 'in_progress')
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <form method="POST" action="{{ route('technician.work_orders.submit', $workOrder) }}"
                                  data-confirm="¿Confirmas que el formulario está completo y listo para revisión del administrador?">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                    Enviar a revisión del administrador
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

            {{-- Vista de solo lectura si ya fue enviado --}}
            @else
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Formulario diligenciado</h3>
                    <dl class="space-y-3">
                        @foreach ([
                            'Diagnóstico' => $workOrder->diagnosis,
                            'Actividades realizadas' => $workOrder->work_performed,
                            'Observaciones adicionales' => $workOrder->additional_observations,
                        ] as $label => $value)
                            @if ($value)
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase">{{ $label }}</dt>
                                    <dd class="text-sm text-gray-900 mt-1 whitespace-pre-line">{{ $value }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                    @if ($workOrder->photos->isNotEmpty())
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-xs font-medium text-gray-500 uppercase mb-2">Evidencias fotográficas</p>
                            <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
                                @foreach ($workOrder->photos as $photo)
                                    <figure>
                                        <a href="{{ $photo->url() }}" target="_blank">
                                            <img src="{{ $photo->url() }}" alt="{{ $photo->label ?: $photo->original_name }}" class="h-24 w-full object-cover rounded-lg border border-gray-200">
                                        </a>
                                        @if ($photo->label)
                                            <figcaption class="mt-1 text-xs text-gray-500">{{ $photo->label }}</figcaption>
                                        @endif
                                    </figure>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @php($techSigRo = $workOrder->technician?->user?->signatureBase64())
                    @php($companySigRo = \App\Support\CompanySignature::base64())
                    @if ($techSigRo || $companySigRo)
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-xs font-medium text-gray-500 uppercase mb-2">Firmas</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div class="text-center">
                                    @if ($techSigRo)
                                        <img src="{{ $techSigRo }}" alt="Firma técnico" class="h-20 mx-auto mb-2 object-contain rounded border border-gray-200 bg-white p-1">
                                    @else
                                        <div class="h-20 border-b border-gray-400 mb-2"></div>
                                    @endif
                                    <p class="text-sm font-medium text-gray-900">{{ $workOrder->technician?->name ?? '—' }}</p>
                                    <p class="text-xs text-gray-500">Técnico responsable</p>
                                </div>
                                <div class="text-center">
                                    @if ($companySigRo)
                                        <img src="{{ $companySigRo }}" alt="Firma empresa" class="h-20 mx-auto mb-2 object-contain rounded border border-gray-200 bg-white p-1">
                                    @else
                                        <div class="h-20 border-b border-gray-400 mb-2"></div>
                                    @endif
                                    <p class="text-sm font-medium text-gray-900">Administrador</p>
                                    <p class="text-xs text-gray-500">Administrador</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <span @class([
                            'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold',
                            'bg-purple-100 text-purple-800' => $workOrder->status === 'pending_review',
                            'bg-green-100 text-green-800' => $workOrder->status === 'closed',
                            'bg-gray-100 text-gray-800' => !in_array($workOrder->status, ['pending_review', 'closed']),
                        ])>{{ $workOrder->statusLabel() }}</span>
                    </div>
                </div>
            @endif

            <div>
                <a href="{{ route('technician.work_orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900">← Volver</a>
            </div>
        </div>
    </div>
</x-app-layout>
