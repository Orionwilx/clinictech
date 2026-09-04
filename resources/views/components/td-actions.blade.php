{{--
    Celda de acciones estándar de <x-data-table>: ancho reservado, alineada a la
    derecha y fija (`sticky right-0 md:static`) para no ser empujada por texto largo.
    `bg-inherit` → hereda el color de la fila (define un bg en el <tr>, ej. bg-white).
    Acepta atributos extra (p. ej. x-data / :class para elevar z-index del menú ⋮).
--}}
<td {{ $attributes->merge(['class' => 'px-6 py-4 align-top text-right text-sm font-medium sticky right-0 md:static bg-inherit border-l border-gray-100 md:border-l-0']) }}>
    <div class="flex items-center justify-end gap-0.5">
        {{ $slot }}
    </div>
</td>
