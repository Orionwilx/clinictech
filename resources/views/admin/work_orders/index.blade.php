<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Órdenes de trabajo" :breadcrumbs="[['label' => 'Órdenes de trabajo']]">
            <x-slot:actions>
                @can('create work_orders')
                    <x-primary-link :href="route('admin.work_orders.create')">{{ __('Nueva orden') }}</x-primary-link>
                @endcan
            </x-slot:actions>
        </x-page-header>
    </x-slot>

    @php($selectableIds = $workOrders->getCollection()->filter(fn ($o) => ! $o->trashed())->pluck('id')->values())

    <div class="py-12" x-data="workOrderBoard(@js($selectableIds))">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Pestañas / bandejas --}}
            @php($tabs = ['action' => 'Requieren tu acción', 'active' => 'En curso', 'all' => 'Todas', 'trashed' => 'Papelera'])
            <div class="mb-4 border-b border-gray-200">
                <nav class="-mb-px flex flex-wrap gap-x-6">
                    @foreach ($tabs as $key => $label)
                        <a href="{{ route('admin.work_orders.index', array_merge($filters, ['tab' => $key])) }}"
                           @class([
                               'group inline-flex items-center gap-2 border-b-2 py-3 px-1 text-sm font-medium',
                               'border-brand-500 text-brand-700' => $tab === $key,
                               'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' => $tab !== $key,
                           ])>
                            {{ $label }}
                            @if (isset($counts[$key]) && $counts[$key] > 0)
                                <span @class([
                                    'inline-flex items-center justify-center rounded-full px-2 text-xs font-semibold',
                                    'bg-amber-100 text-amber-800' => $key === 'action',
                                    'bg-gray-100 text-gray-700' => $key !== 'action',
                                ])>{{ $counts[$key] }}</span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </div>

            {{-- Filtros --}}
            <form method="GET" class="mb-4 bg-white shadow-sm sm:rounded-lg p-4">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                    <div class="lg:col-span-2">
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                               placeholder="Nº o asunto…"
                               class="block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                    </div>
                    <select name="client_id" class="block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                        <option value="">Todos los clientes</option>
                        @foreach ($clients as $id => $name)
                            <option value="{{ $id }}" @selected(($filters['client_id'] ?? '') == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                    <select name="technician_id" class="block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                        <option value="">Todos los técnicos</option>
                        @foreach ($technicians as $id => $name)
                            <option value="{{ $id }}" @selected(($filters['technician_id'] ?? '') == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                    <select name="type" class="block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                        <option value="">Todos los tipos</option>
                        @foreach (\App\Models\WorkOrder::TYPES as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                        <option value="">Todos los estados</option>
                        @foreach (\App\Models\WorkOrder::STATUSES as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="priority" class="block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                        <option value="">Todas las prioridades</option>
                        @foreach (\App\Models\WorkOrder::PRIORITIES as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['priority'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-3 mt-3">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700">
                        Filtrar
                    </button>
                    @if (array_filter($filters))
                        <a href="{{ route('admin.work_orders.index', ['tab' => $tab]) }}" class="text-sm text-gray-600 hover:text-gray-900">Limpiar</a>
                    @endif
                </div>
            </form>

            {{-- overflow-x-auto en móvil (con columna de acciones sticky); visible en ≥md para no recortar el menú ⋮ --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto md:overflow-x-visible">
                <table class="w-full table-fixed divide-y divide-gray-200">
                    <colgroup>
                        <col class="w-12">
                        <col class="w-[26%]">
                        <col class="w-[26%]">
                        <col class="w-40">
                        <col class="w-28">
                        <col class="w-36">
                        <col class="w-32">
                    </colgroup>
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">
                                <input type="checkbox" @change="toggleAll($event)"
                                       :checked="allSelected" :disabled="!selectableIds.length"
                                       class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nº / Asunto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente / Equipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Técnico</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prioridad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase sticky right-0 bg-gray-50 border-l border-gray-100 md:border-l-0">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($workOrders as $order)
                            <tr :class="selected.includes({{ $order->id }}) ? 'bg-brand-50' : '{{ $order->trashed() ? 'bg-red-50' : 'bg-white' }}'">
                                <td class="px-4 py-4 align-top">
                                    @unless ($order->trashed())
                                        <input type="checkbox" value="{{ $order->id }}" x-model.number="selected"
                                               class="mt-0.5 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                    @endunless
                                </td>
                                <x-td :title="$order->code" :sub="$order->title" :subTitle="$order->title">{{ $order->code }}</x-td>
                                <x-td muted
                                      :title="optional($order->client)->name"
                                      :sub="optional($order->equipment)->name ?? 'Sin equipo'"
                                      :subTitle="optional($order->equipment)->name">{{ optional($order->client)->name ?? '—' }}</x-td>
                                <x-td muted :title="optional($order->technician)->name">{{ optional($order->technician)->name ?? 'Sin asignar' }}</x-td>
                                <x-td plain>
                                    <span @class([
                                        'inline-flex rounded-full px-2 text-xs font-semibold',
                                        'bg-gray-100 text-gray-800' => $order->priority === 'low',
                                        'bg-blue-100 text-blue-800' => $order->priority === 'medium',
                                        'bg-red-100 text-red-800' => $order->priority === 'high',
                                    ])>{{ $order->priorityLabel() }}</span>
                                </x-td>
                                <x-td plain>
                                    <x-work-order-status-badge :order="$order" />
                                </x-td>
                                {{-- md:static → en escritorio la celda NO crea stacking context (el dropdown escapa
                                     al contexto raíz y cubre las filas de abajo). En móvil sigue sticky y se eleva
                                     con z-30 solo cuando su menú está abierto. --}}
                                <td class="px-6 py-4 align-top text-right text-sm font-medium sticky right-0 md:static bg-inherit border-l border-gray-100 md:border-l-0"
                                    x-data="{ open: false }" :class="open ? 'z-30' : ''"
                                    @keydown.escape.window="open = false">
                                    @include('admin.work_orders._row_actions', ['order' => $order, 'technicians' => $technicians])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No hay órdenes en esta bandeja.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $workOrders->links() }}
            </div>
        </div>

        {{-- Barra flotante de acciones masivas --}}
        <div x-show="selected.length" x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="fixed inset-x-0 bottom-6 z-30 flex justify-center px-4 pointer-events-none">
            <div class="pointer-events-auto flex items-center gap-2 rounded-full bg-gray-900 text-white shadow-2xl pl-5 pr-3 py-2">
                <span class="inline-flex items-center gap-1.5 text-sm font-semibold">
                    <svg class="w-4 h-4 text-brand-300" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <span x-text="selected.length"></span>
                </span>
                <span class="h-5 w-px bg-white/20"></span>
                <button type="button" @click="submitApprove()" title="Aprobar seleccionadas"
                        class="inline-flex items-center justify-center rounded-full bg-green-600 hover:bg-green-700 w-9 h-9">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                </button>
                <button type="button" @click="rejectOpen = true" title="Rechazar / devolver seleccionadas"
                        class="inline-flex items-center justify-center rounded-full bg-red-600 hover:bg-red-700 w-9 h-9">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
                <button type="button" @click="assignOpen = true" title="Asignar técnico"
                        class="inline-flex items-center justify-center rounded-full bg-brand-600 hover:bg-brand-700 w-9 h-9">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                </button>
                <span class="h-5 w-px bg-white/20"></span>
                <button type="button" @click="selected = []" title="Limpiar selección"
                        class="inline-flex items-center justify-center rounded-full hover:bg-white/10 w-9 h-9">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Formulario oculto que ejecuta la acción masiva --}}
        <form method="POST" action="{{ route('admin.work_orders.batch') }}" x-ref="batchForm" class="hidden">
            @csrf
            <input type="hidden" name="action" x-model="batchAction">
            <input type="hidden" name="technician_id" x-model="batchTech">
            <input type="hidden" name="rejection_reason" x-model="batchReason">
            <template x-for="id in selected" :key="id">
                <input type="hidden" name="ids[]" :value="id">
            </template>
        </form>

        {{-- Modal: rechazar/devolver en lote --}}
        <div x-show="rejectOpen" x-cloak class="fixed inset-0 z-40 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40" @click="rejectOpen = false"></div>
            <div class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900">Rechazar / devolver <span x-text="selected.length"></span> órdenes</h3>
                <p class="mt-1 text-sm text-gray-500">Solicitudes de cliente se rechazan; trabajos en revisión se devuelven al técnico. El resto se omite.</p>
                <label class="block mt-4 text-sm font-medium text-gray-700">Motivo</label>
                <textarea x-model="batchReason" rows="3"
                          class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm"
                          placeholder="Se comunicará al cliente/técnico…"></textarea>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" @click="rejectOpen = false" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</button>
                    <button type="button" @click="submitReject()"
                            class="inline-flex items-center px-4 py-2 bg-red-600 rounded-md text-xs font-semibold text-white uppercase tracking-widest hover:bg-red-700">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal: asignar técnico en lote --}}
        <div x-show="assignOpen" x-cloak class="fixed inset-0 z-40 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40" @click="assignOpen = false"></div>
            <div class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900">Asignar técnico a <span x-text="selected.length"></span> órdenes</h3>
                <p class="mt-1 text-sm text-gray-500">Aplica a órdenes en curso. Deja vacío para desasignar.</p>
                <label class="block mt-4 text-sm font-medium text-gray-700">Técnico</label>
                <select x-model="batchTech"
                        class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                    <option value="">— Sin asignar —</option>
                    @foreach ($technicians as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" @click="assignOpen = false" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</button>
                    <button type="button" @click="submitAssign()"
                            class="inline-flex items-center px-4 py-2 bg-brand-600 rounded-md text-xs font-semibold text-white uppercase tracking-widest hover:bg-brand-700">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal de fila (compartido): aprobar+asignar / rechazar+motivo de UNA OT.
             Los flujos con input NO viven en el menú ⋮ estrecho, sino aquí. --}}
        <div x-show="rowModal.open" x-cloak class="fixed inset-0 z-40 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40" @click="rowModal.open = false"></div>
            <div class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900">
                    <span x-text="rowModal.heading"></span>
                    <span class="ml-1 text-sm font-normal text-gray-400" x-text="rowModal.code"></span>
                </h3>

                <template x-if="rowModal.mode === 'assign'">
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Técnico</label>
                        <p class="text-xs text-gray-500 mb-1">Se aprueba la solicitud y se asigna. Deja vacío para aprobar sin asignar.</p>
                        <select x-model="rowModal.tech"
                                class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
                            <option value="">— Sin asignar —</option>
                            @foreach ($technicians as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </template>

                <template x-if="rowModal.mode === 'reject'">
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Motivo <span x-show="rowModal.required" class="text-red-500">*</span>
                        </label>
                        <textarea x-model="rowModal.reason" rows="3"
                                  class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm"
                                  placeholder="Se comunicará al cliente/técnico…"></textarea>
                    </div>
                </template>

                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" @click="rowModal.open = false" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</button>
                    <button type="button" @click="submitRowModal()"
                            :disabled="rowModal.mode === 'reject' && rowModal.required && !rowModal.reason.trim()"
                            class="inline-flex items-center px-4 py-2 bg-brand-600 rounded-md text-xs font-semibold text-white uppercase tracking-widest hover:bg-brand-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>

        {{-- Form oculto que ejecuta la acción de fila (destino dinámico) --}}
        <form method="POST" x-ref="rowForm" class="hidden">
            @csrf
            <input type="hidden" name="technician_id" :value="rowModal.tech">
            <input type="hidden" name="rejection_reason" :value="rowModal.reason">
        </form>
    </div>

    @push('scripts')
        <script>
            function workOrderBoard(selectableIds) {
                return {
                    selectableIds,
                    selected: [],
                    batchAction: '',
                    batchTech: '',
                    batchReason: '',
                    rejectOpen: false,
                    assignOpen: false,
                    // Modal de fila (una OT)
                    rowModal: { open: false, kind: '', mode: '', orderId: null, code: '', heading: '', required: false, tech: '', reason: '' },
                    rowUrls: {
                        advance: '{{ route('admin.work_orders.advance', ['work_order' => '__ID__']) }}',
                        regress: '{{ route('admin.work_orders.regress', ['work_order' => '__ID__']) }}',
                    },
                    get allSelected() {
                        return this.selectableIds.length > 0 && this.selected.length === this.selectableIds.length;
                    },
                    toggleAll(e) {
                        this.selected = e.target.checked ? [...this.selectableIds] : [];
                    },
                    submitApprove() {
                        this.batchAction = 'approve';
                        this.$refs.batchForm.submit();
                    },
                    submitReject() {
                        this.batchAction = 'reject';
                        this.$refs.batchForm.submit();
                    },
                    submitAssign() {
                        this.batchAction = 'assign';
                        this.$refs.batchForm.submit();
                    },
                    openRowModal(cfg) {
                        this.rowModal = { open: true, tech: '', reason: '', required: false, ...cfg };
                    },
                    submitRowModal() {
                        const form = this.$refs.rowForm;
                        form.action = this.rowUrls[this.rowModal.kind].replace('__ID__', this.rowModal.orderId);
                        form.submit();
                    },
                };
            }
        </script>
    @endpush
</x-app-layout>
