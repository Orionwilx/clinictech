<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="'Orden '.$workOrder->code" :breadcrumbs="[['label' => 'Órdenes de trabajo', 'href' => route('admin.work_orders.index')], ['label' => $workOrder->code]]" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                {{-- Datos generales en horizontal (tarjetas en cuadrícula) --}}
                <dl class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach ([
                        'Nº de orden' => $workOrder->code,
                        'Asunto' => $workOrder->title,
                        'Cliente' => optional($workOrder->client)->name ?? '—',
                        'Equipo' => optional($workOrder->equipment)->name ?? '—',
                        'Técnico asignado' => optional($workOrder->technician)->name ?? 'Sin asignar',
                        'Tipo' => $workOrder->typeLabel(),
                        'Prioridad' => $workOrder->priorityLabel(),
                        'Estado' => $workOrder->statusLabel(),
                        'Fecha programada' => optional($workOrder->scheduled_at)->format('Y-m-d') ?: '—',
                        'Inicio' => optional($workOrder->started_at)->format('Y-m-d') ?: '—',
                        'Completada' => optional($workOrder->completed_at)->format('Y-m-d') ?: '—',
                        'Cerrada' => optional($workOrder->closed_at)->format('Y-m-d') ?: '—',
                    ] as $label => $value)
                        <div class="rounded-lg bg-gray-50 border border-gray-100 p-3">
                            <dt class="text-xs font-medium text-gray-500 uppercase">{{ $label }}</dt>
                            <dd class="mt-0.5 text-sm text-gray-900 break-words">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>

                {{-- Textos largos a lo ancho --}}
                <div class="mt-6 space-y-4">
                    @foreach ([
                        'Descripción' => $workOrder->description,
                        'Diagnóstico' => $workOrder->diagnosis,
                        'Actividades realizadas' => $workOrder->work_performed,
                    ] as $label => $value)
                        @if ($value)
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 mb-1">{{ $label }}</h3>
                                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $value }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>

                @if ($workOrder->maintenance_tasks || $workOrder->accessories_checked)
                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">Subtareas ejecutadas</h3>
                            @if ($workOrder->maintenance_tasks)
                                <ul class="grid grid-cols-1 gap-1 text-sm text-gray-700">
                                    @foreach ($workOrder->maintenance_tasks as $key)
                                        <li class="flex items-center gap-2"><span class="text-brand-600">✓</span>{{ $key }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-400">—</p>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">Accesorios revisados</h3>
                            @if ($workOrder->accessories_checked)
                                <ul class="grid grid-cols-1 gap-1 text-sm text-gray-700">
                                    @foreach ($workOrder->accessories_checked as $key)
                                        <li class="flex items-center gap-2"><span class="text-brand-600">✓</span>{{ $key }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-400">—</p>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($workOrder->additional_observations)
                    <div class="mt-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">Observaciones adicionales</h3>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $workOrder->additional_observations }}</p>
                    </div>
                @endif

                {{-- Evidencias fotográficas: el admin puede anexar/quitar (llena OT del técnico) --}}
                <div class="mt-6"
                     x-data="{
                        photos: {{ Illuminate\Support\Js::from($workOrder->photos->map(fn ($p) => ['id' => $p->id, 'url' => $p->url(), 'name' => $p->original_name, 'label' => $p->label])) }},
                        uploading: 0, error: '',
                        async saveLabel(photo, label) {
                            photo.label = label;
                            await fetch(`{{ url('admin/work_orders/'.$workOrder->id.'/photos') }}/${photo.id}`, {
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
                                    const res = await fetch('{{ route('admin.work_orders.photos.store', $workOrder) }}', {
                                        method: 'POST',
                                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
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
                            const res = await fetch(`{{ url('admin/work_orders/'.$workOrder->id.'/photos') }}/${photo.id}`, {
                                method: 'DELETE',
                                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            });
                            if (res.ok) this.photos = this.photos.filter(p => p.id !== photo.id);
                        }
                     }">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-gray-900">Evidencias fotográficas</h3>
                        <span class="text-xs text-gray-400" x-show="uploading > 0" x-cloak>Subiendo y comprimiendo…</span>
                    </div>

                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-3" x-show="photos.length" x-cloak>
                        <template x-for="photo in photos" :key="photo.id">
                            <div>
                                <div class="relative group">
                                    <a :href="photo.url" target="_blank">
                                        <img :src="photo.url" :alt="photo.label || photo.name" class="h-24 w-full object-cover rounded-lg border border-gray-200">
                                    </a>
                                    @can('update work_orders')
                                        <button type="button" @click="remove(photo)"
                                                class="absolute -top-2 -right-2 hidden group-hover:flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-white text-xs shadow"
                                                title="Eliminar foto">✕</button>
                                    @endcan
                                </div>
                                @can('update work_orders')
                                    <input type="text" :value="photo.label ?? ''" @change="saveLabel(photo, $event.target.value)"
                                           placeholder="Descripción…" maxlength="255"
                                           class="mt-1 block w-full text-xs border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                                @else
                                    <p class="mt-1 text-xs text-gray-500" x-show="photo.label" x-text="photo.label"></p>
                                @endcan
                            </div>
                        </template>
                    </div>
                    <p x-show="!photos.length" x-cloak class="text-sm text-gray-400">Sin evidencias fotográficas.</p>

                    @can('update work_orders')
                        <label class="mt-3 inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-brand-700 bg-brand-50 rounded-md hover:bg-brand-100 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316ZM16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z"/></svg>
                            Agregar fotos
                            <input type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden"
                                   x-ref="fileInput" @change="upload($event.target.files)">
                        </label>
                        <p class="mt-1 text-xs text-gray-400">Se comprimen automáticamente al subir.</p>
                        <p class="mt-1 text-xs text-red-600" x-show="error" x-text="error" x-cloak></p>
                    @endcan
                </div>

                {{-- Firmas (técnico y cliente) --}}
                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Firmas</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <x-signature-slot
                            label="Firma del técnico"
                            :current="$workOrder->technicianSignature?->url()"
                            :currentId="$workOrder->technicianSignature?->id"
                            :storeUrl="route('admin.work_orders.signatures.store', [$workOrder, 'technician'])"
                            :destroyUrlBase="url('admin/work_orders/'.$workOrder->id.'/signatures')"
                            :editable="auth()->user()->can('update work_orders')" />
                        <x-signature-slot
                            label="Firma del cliente (conformidad)"
                            :current="$workOrder->clientSignature?->url()"
                            :currentId="$workOrder->clientSignature?->id"
                            :storeUrl="route('admin.work_orders.signatures.store', [$workOrder, 'client'])"
                            :destroyUrlBase="url('admin/work_orders/'.$workOrder->id.'/signatures')"
                            :editable="auth()->user()->can('update work_orders')" />
                    </div>
                </div>

                {{-- Acciones según estado del flujo colaborativo --}}
                <div class="mt-6 space-y-3">

                    {{-- Solicitud del cliente (draft) --}}
                    @if ($workOrder->status === 'draft')
                        @if ($workOrder->requested_by_client)
                            <div class="rounded-md bg-blue-50 border border-blue-200 p-4">
                                <p class="text-sm font-semibold text-blue-800 mb-3">Solicitud del cliente — pendiente de revisión</p>
                                <div class="flex flex-wrap items-start gap-4">
                                    <form method="POST" action="{{ route('admin.work_orders.approve-request', $workOrder) }}" class="flex items-end gap-2">
                                        @csrf
                                        <div>
                                            <label class="block text-xs text-blue-700 mb-1">Asignar técnico (opcional)</label>
                                            <x-searchable-select name="technician_id"
                                                :options="\App\Models\Technician::orderBy('name')->pluck('name','id')"
                                                placeholder="— Sin asignar —" />
                                        </div>
                                        <button type="submit"
                                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 shrink-0">
                                            Aprobar solicitud
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.work_orders.reject-request', $workOrder) }}" class="flex items-end gap-2"
                                          data-confirm="¿Rechazar esta solicitud?" data-confirm-title="Rechazar solicitud" data-confirm-button="Rechazar">
                                        @csrf
                                        <div>
                                            <label class="block text-xs text-blue-700 mb-1">Motivo del rechazo</label>
                                            <x-text-input name="rejection_reason" type="text" class="block" placeholder="Opcional…" />
                                        </div>
                                        <button type="submit"
                                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 shrink-0">
                                            Rechazar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- Trabajo del técnico listo para revisión --}}
                    @if ($workOrder->status === 'pending_review')
                        <div class="rounded-md bg-purple-50 border border-purple-200 p-4">
                            <p class="text-sm font-semibold text-purple-800 mb-3">El técnico completó el formulario — revisión pendiente</p>
                            <div class="flex flex-wrap items-start gap-4">
                                <form method="POST" action="{{ route('admin.work_orders.approve-work', $workOrder) }}">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                        Aprobar trabajo
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.work_orders.reject-work', $workOrder) }}" class="flex items-end gap-2">
                                    @csrf
                                    <div>
                                        <label class="block text-xs text-purple-700 mb-1">Motivo de devolución <span class="text-red-500">*</span></label>
                                        <x-text-input name="rejection_reason" type="text" class="block" placeholder="Indica qué debe corregir el técnico…" required />
                                    </div>
                                    <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-orange-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-600 shrink-0">
                                        Devolver al técnico
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    {{-- OT cerrada, lista para enviar al cliente --}}
                    @if ($workOrder->status === 'closed' && ! $workOrder->visible_to_client)
                        <div class="rounded-md bg-green-50 border border-green-200 p-4">
                            <p class="text-sm font-semibold text-green-800 mb-2">OT aprobada — pendiente de envío al cliente</p>
                            <form method="POST" action="{{ route('admin.work_orders.send-to-client', $workOrder) }}">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">
                                    Enviar al cliente
                                </button>
                            </form>
                        </div>
                    @endif

                    @if ($workOrder->visible_to_client)
                        <div class="rounded-md bg-gray-50 border border-gray-200 px-4 py-3">
                            <span class="text-sm text-gray-600">✓ Esta OT ya fue enviada al cliente.</span>
                        </div>
                    @endif

                    {{-- Acciones estándar --}}
                    <div class="flex items-center gap-4 pt-2">
                        <a href="{{ route('admin.work_orders.pdf', $workOrder) }}" target="_blank"
                           class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Generar PDF
                        </a>
                        @can('update work_orders')
                            <a href="{{ route('admin.work_orders.edit', $workOrder) }}"
                               class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">
                                Editar
                            </a>
                        @endcan
                        <a href="{{ route('admin.work_orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
