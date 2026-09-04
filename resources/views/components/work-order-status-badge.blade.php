@props(['order'])

@php
    $isTrashed = method_exists($order, 'trashed') && $order->trashed();

    if ($isTrashed) {
        $label = 'Eliminada';
        $classes = 'bg-red-100 text-red-800';
    } elseif ($order->status === 'draft' && $order->requested_by_client) {
        $label = 'Solicitud';
        $classes = 'bg-amber-100 text-amber-800';
    } elseif ($order->visible_to_client && $order->status === 'closed') {
        $label = 'Enviada al cliente';
        $classes = 'bg-green-100 text-green-800';
    } else {
        $label = $order->statusLabel();
        $classes = match ($order->status) {
            'draft' => 'bg-amber-100 text-amber-800',
            'open' => 'bg-gray-100 text-gray-800',
            'assigned' => 'bg-indigo-100 text-indigo-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'pending_review' => 'bg-purple-100 text-purple-800',
            'completed' => 'bg-green-100 text-green-800',
            'closed' => 'bg-brand-100 text-brand-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
@endphp

<span {{ $attributes->merge(['class' => "inline-flex rounded-full px-2 text-xs font-semibold {$classes}"]) }}>{{ $label }}</span>
