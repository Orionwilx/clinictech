{{--
    Data table estándar (ver DESIGN.md → «Tabla robusta ante texto variable»).
    Aporta: tarjeta + table-fixed + colgroup + thead. Las filas van en el slot,
    usando <x-td> (celdas truncables) y <x-td-actions> (columna de acciones fija).

    Props:
      - heads: array de cabeceras. Cada ítem: 'Etiqueta' o ['Etiqueta','right'|'center'].
               La última con align 'right' queda sticky (columna de acciones).
      - cols:  array opcional de clases de ancho para <colgroup> (misma longitud que heads).
--}}
@props(['heads' => [], 'cols' => null, 'stickyLast' => true])

<div {{ $attributes->merge(['class' => 'bg-white shadow-sm sm:rounded-lg overflow-x-auto']) }}>
    <table class="w-full table-fixed divide-y divide-gray-200">
        @if ($cols)
            <colgroup>
                @foreach ($cols as $c)
                    <col class="{{ $c }}">
                @endforeach
            </colgroup>
        @endif
        <thead class="bg-gray-50">
            <tr>
                @foreach ($heads as $i => $head)
                    @php
                        [$label, $align] = is_array($head) ? [$head[0], $head[1] ?? 'left'] : [$head, 'left'];
                        $isLast = $i === array_key_last($heads);
                    @endphp
                    <th @class([
                        'px-6 py-3 text-xs font-medium text-gray-500 uppercase',
                        'text-left' => $align === 'left',
                        'text-right' => $align === 'right',
                        'text-center' => $align === 'center',
                        'sticky right-0 md:static bg-gray-50' => $isLast && $align === 'right' && $stickyLast,
                    ])>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            {{ $slot }}
        </tbody>
    </table>
</div>
