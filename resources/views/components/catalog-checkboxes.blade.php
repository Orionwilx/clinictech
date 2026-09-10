{{--
    Checkboxes alimentados por un catálogo de opciones, con alta rápida inline.
    - name:     nombre del input (se envía como name[])
    - options:  opciones activas del catálogo (lista de strings)
    - selected: valores marcados (strings; los que ya no existan en el catálogo se muestran igual)
    - catalog:  segmento del catálogo para la alta rápida (maintenance_tasks|accessories|specialties)
--}}
@props(['name', 'options', 'selected' => [], 'catalog', 'columns' => 'sm:grid-cols-3'])

@php
    $options = collect($options)->values();
    $selected = array_values(array_filter((array) $selected));
    $rendered = $options->merge($selected)->unique()->values();
@endphp

<div x-data="{
        extras: [],
        newName: '',
        error: '',
        async add() {
            const name = this.newName.trim();
            if (!name) return;
            this.error = '';
            const res = await fetch('{{ route('admin.equipment_catalogs.store', $catalog) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({ name }),
            });
            if (res.status === 201) {
                this.extras.push((await res.json()).name);
                this.newName = '';
            } else if (res.status === 422) {
                this.error = Object.values((await res.json()).errors ?? {}).flat()[0] ?? 'Valor inválido.';
            } else {
                this.error = 'No se pudo agregar la opción.';
            }
        }
    }">
    <div class="mt-2 grid grid-cols-1 {{ $columns }} gap-2">
        @foreach ($rendered as $option)
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="{{ $name }}[]" value="{{ $option }}"
                       @checked(in_array($option, $selected))
                       class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                {{ $option }}
            </label>
        @endforeach
        {{-- Opciones agregadas sobre la marcha (quedan marcadas) --}}
        <template x-for="option in extras" :key="option">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="{{ $name }}[]" :value="option" checked
                       class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                <span x-text="option"></span>
            </label>
        </template>
    </div>

    @can('create equipment_catalogs')
        <div class="mt-3 flex items-center gap-2">
            <input type="text" x-model="newName" @keydown.enter.prevent="add()"
                   placeholder="Agregar opción nueva al catálogo…"
                   class="block w-64 text-sm border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
            <button type="button" @click="add()"
                    class="px-3 py-2 text-xs font-semibold text-brand-700 bg-brand-50 rounded-md hover:bg-brand-100">
                + Agregar
            </button>
            <p x-show="error" x-text="error" class="text-xs text-red-600" x-cloak></p>
        </div>
    @endcan
</div>
