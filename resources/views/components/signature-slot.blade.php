@props([
    'label',
    'current' => null,
    'currentId' => null,
    'storeUrl',
    'destroyUrlBase',
    'editable' => true,
])
<div x-data="{
    url: @js($current),
    id: @js($currentId),
    uploading: false, error: '',
    async upload(file) {
        if (!file) return;
        this.uploading = true; this.error = '';
        const data = new FormData(); data.append('signature', file);
        try {
            const res = await fetch('{{ $storeUrl }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: data,
            });
            if (res.status === 201) { const j = await res.json(); this.url = j.url; this.id = j.id; }
            else if (res.status === 422) this.error = 'Imagen inválida (máx 4 MB).';
            else this.error = 'No se pudo subir la firma.';
        } catch { this.error = 'Sin conexión: la firma no se subió.'; }
        this.uploading = false;
        if (this.$refs.input) this.$refs.input.value = '';
    },
    async remove() {
        if (!this.id) return;
        if (!await window.appConfirm('¿Eliminar esta firma?', { title: 'Eliminar firma', confirmLabel: 'Eliminar' })) return;
        const res = await fetch(`{{ $destroyUrlBase }}/${this.id}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        });
        if (res.ok) { this.url = null; this.id = null; }
    }
}">
    <p class="text-xs font-medium text-gray-500 uppercase mb-1">{{ $label }}</p>
    <template x-if="url">
        <div class="relative inline-block">
            <img :src="url" alt="Firma" class="h-24 max-w-full object-contain rounded border border-gray-200 bg-white p-1">
            @if ($editable)
                <button type="button" @click="remove()"
                        class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-white text-xs shadow"
                        title="Eliminar firma">✕</button>
            @endif
        </div>
    </template>
    <template x-if="!url">
        <p class="text-sm text-gray-400">Sin firma.</p>
    </template>

    @if ($editable)
        <div class="mt-2 flex items-center gap-2">
            <label class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-brand-700 bg-brand-50 rounded-md hover:bg-brand-100 cursor-pointer">
                <span x-text="url ? 'Reemplazar firma' : 'Subir firma'"></span>
                <input type="file" accept="image/*" class="hidden" x-ref="input" @change="upload($event.target.files[0])">
            </label>
            <span class="text-xs text-gray-400" x-show="uploading" x-cloak>Subiendo…</span>
        </div>
        <p class="mt-1 text-xs text-red-600" x-show="error" x-text="error" x-cloak></p>
    @endif
</div>
