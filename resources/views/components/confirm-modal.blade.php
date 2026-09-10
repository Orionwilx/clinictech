{{--
    Modal de confirmación estándar del proyecto — reemplaza el confirm() nativo del navegador.
    Se incluye UNA vez en el layout. Dos formas de uso:

    1. Declarativa (formularios): <form ... data-confirm="¿Eliminar este cliente?"
       data-confirm-title="Eliminar cliente" data-confirm-button="Eliminar">
       El submit se intercepta y solo continúa si el usuario confirma.

    2. Programática (Alpine/JS): const ok = await window.appConfirm('¿Eliminar esta foto?');
--}}
<div x-data="{
        open: false,
        title: '',
        message: '',
        confirmLabel: 'Confirmar',
        resolver: null,
        init() {
            window.appConfirm = (message, { title = '¿Estás seguro?', confirmLabel = 'Confirmar' } = {}) =>
                new Promise((resolve) => {
                    this.message = message;
                    this.title = title;
                    this.confirmLabel = confirmLabel;
                    this.resolver = resolve;
                    this.open = true;
                });

            // Intercepta el submit de cualquier formulario con data-confirm.
            document.addEventListener('submit', (event) => {
                const form = event.target;
                if (!(form instanceof HTMLFormElement) || !form.dataset.confirm || form.dataset.confirmed) return;
                event.preventDefault();
                event.stopPropagation();
                window.appConfirm(form.dataset.confirm, {
                    title: form.dataset.confirmTitle ?? '¿Estás seguro?',
                    confirmLabel: form.dataset.confirmButton ?? 'Confirmar',
                }).then((ok) => {
                    if (!ok) return;
                    form.dataset.confirmed = 'true';
                    form.requestSubmit ? form.requestSubmit() : form.submit();
                });
            }, true);
        },
        accept() { this.open = false; this.resolver?.(true); this.resolver = null; },
        cancel() { this.open = false; this.resolver?.(false); this.resolver = null; }
     }"
     x-on:keydown.escape.window="open && cancel()"
     x-show="open" x-cloak
     class="fixed inset-0 z-[10000] flex items-center justify-center p-4"
     role="dialog" aria-modal="true">

    {{-- Overlay --}}
    <div class="absolute inset-0 bg-gray-900/50" x-show="open"
         x-transition.opacity.duration.150ms @click="cancel()"></div>

    {{-- Panel --}}
    <div class="relative w-full max-w-md bg-white rounded-xl shadow-xl p-6"
         x-show="open"
         x-transition:enter="ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        <div class="flex items-start gap-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100">
                <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-base font-semibold text-gray-900" x-text="title"></h3>
                <p class="mt-1 text-sm text-gray-600" x-text="message"></p>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="cancel()"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                Cancelar
            </button>
            <button type="button" @click="accept()" x-ref="confirmButton"
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500"
                    x-text="confirmLabel"></button>
        </div>
    </div>
</div>
