{{--
    Celda estándar de <x-data-table>. Trunca con puntos suspensivos y expone el
    valor completo por tooltip (`title`). Opcionalmente una segunda línea (`sub`).

    Props:
      - title:    texto del tooltip de la línea principal (por defecto, el texto del slot).
      - sub:      segunda línea (gris pequeña). subTitle: su tooltip.
      - align:    'left' | 'right' | 'center'.
      - muted:    línea principal en gris (text-gray-500) en vez de gray-900.
      - plain:    sin truncado; renderiza el slot tal cual (badges, botones, contenido libre).
      - colspan.
--}}
@props(['title' => null, 'sub' => null, 'subTitle' => null, 'align' => 'left', 'muted' => false, 'plain' => false, 'colspan' => null])

<td @if ($colspan) colspan="{{ $colspan }}" @endif
    @class([
        'px-6 py-4 align-top text-sm',
        'text-left' => $align === 'left',
        'text-right' => $align === 'right',
        'text-center' => $align === 'center',
    ])>
    @if ($plain)
        {{ $slot }}
    @else
        <div class="truncate {{ $muted ? 'text-gray-500' : 'text-gray-900' }}" title="{{ $title ?? trim(strip_tags((string) $slot)) }}">{{ $slot }}</div>
        @if ($sub !== null)
            <div class="truncate text-xs text-gray-400" title="{{ $subTitle ?? trim(strip_tags((string) $sub)) }}">{{ $sub }}</div>
        @endif
    @endif
</td>
