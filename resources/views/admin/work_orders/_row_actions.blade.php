{{-- Acciones por fila. Requiere: $order, $technicians (array id=>name).
     Primarias visibles (flujo + Ver) · menú ⋮ SOLO con acciones simples.
     Los flujos con input (asignar / rechazar) abren un MODAL compartido (openRowModal). --}}
@php($action = $order->trashed() ? null : $order->primaryAdminAction())

<div class="flex items-center justify-end gap-0.5">
    @if ($order->trashed())
        @can('delete work_orders')
            <form action="{{ route('admin.work_orders.restore', $order->id) }}" method="POST" class="inline">
                @csrf @method('PUT')
                <x-icon-btn color="green" label="Restaurar" type="submit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                </x-icon-btn>
            </form>
        @endcan
    @else
        {{-- Acción primaria del flujo (1 clic, sin input) --}}
        @if ($action)
            @can('update work_orders')
                <form method="POST" action="{{ route('admin.work_orders.advance', $order) }}" class="inline">
                    @csrf
                    @if ($action['key'] === 'send')
                        <x-icon-btn color="brand" label="Enviar al cliente" type="submit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                        </x-icon-btn>
                    @else
                        <x-icon-btn color="green" label="Aprobar" type="submit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </x-icon-btn>
                    @endif
                </form>
            @endcan
        @endif

        {{-- Ver: acción secundaria más frecuente, siempre visible --}}
        <x-icon-btn :href="route('admin.work_orders.show', $order)" color="gray" label="Ver">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
        </x-icon-btn>

        {{-- Menú de acciones (⋮): solo enlaces/acciones simples.
             El estado `open` vive en el <td> contenedor (index) para poder elevar su z-index. --}}
        <div class="relative">
            <x-icon-btn color="gray" label="Más acciones" type="button" x-on:click="open = !open">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75h.008v.008H12V6.75Zm0 5.25h.008v.008H12V12Zm0 5.25h.008v.008H12v-.008Z"/></svg>
            </x-icon-btn>

            <div x-show="open" x-cloak @click.outside="open = false" x-transition.origin.top.right
                 class="absolute right-0 top-full z-30 mt-1 w-56 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5 text-left">

                {{-- Disparadores de flujo → abren MODAL (no formularios embebidos) --}}
                @if ($action && $action['needs_technician'])
                    @can('update work_orders')
                        <button type="button"
                                @click="open = false; openRowModal({ kind:'advance', mode:'assign', orderId:{{ $order->id }}, code:@js($order->code), heading:'Aprobar y asignar técnico', required:false })"
                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                            Aprobar y asignar…
                        </button>
                    @endcan
                @endif
                @if ($action && $action['can_reject'])
                    @can('update work_orders')
                        <button type="button"
                                @click="open = false; openRowModal({ kind:'regress', mode:'reject', orderId:{{ $order->id }}, code:@js($order->code), heading:@js($action['reject_label'].' orden'), required:{{ $action['reject_required'] ? 'true' : 'false' }} })"
                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            {{ $action['reject_label'] }}…
                        </button>
                    @endcan
                @endif

                @if ($action && ($action['needs_technician'] || $action['can_reject']))
                    <div class="my-1 border-t border-gray-100"></div>
                @endif

                {{-- Acciones simples --}}
                <a href="{{ route('admin.work_orders.pdf', $order) }}" target="_blank"
                   class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                    Descargar PDF
                </a>
                @can('update work_orders')
                    <a href="{{ route('admin.work_orders.edit', $order) }}"
                       class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                        Editar
                    </a>
                @endcan
                @can('delete work_orders')
                    <form action="{{ route('admin.work_orders.destroy', $order) }}" method="POST"
                          onsubmit="return confirm('¿Eliminar esta orden?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                            Eliminar
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    @endif
</div>
