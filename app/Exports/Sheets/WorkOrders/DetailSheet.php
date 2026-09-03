<?php

namespace App\Exports\Sheets\WorkOrders;

use App\Models\WorkOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DetailSheet implements FromQuery, WithTitle, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    public function __construct(private readonly array $filters) {}

    public function query(): Builder
    {
        return WorkOrder::with(['client', 'equipment', 'technician'])
            ->when($this->filters['client_id'] ?? null, fn ($q, $v) => $q->where('client_id', $v))
            ->when($this->filters['technician_id'] ?? null, fn ($q, $v) => $q->where('technician_id', $v))
            ->when($this->filters['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($this->filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($this->filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($this->filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest();
    }

    public function title(): string
    {
        return 'Detalle';
    }

    public function headings(): array
    {
        return ['Código', 'Asunto', 'Cliente', 'Equipo', 'Técnico', 'Tipo', 'Prioridad', 'Estado', 'Fecha creación', 'Fecha cierre'];
    }

    public function map($order): array
    {
        return [
            $order->code,
            $order->title,
            optional($order->client)->name ?? '—',
            optional($order->equipment)->name ?? '—',
            optional($order->technician)->name ?? 'Sin asignar',
            $order->typeLabel(),
            $order->priorityLabel(),
            $order->statusLabel(),
            $order->created_at->format('d/m/Y'),
            $order->closed_at?->format('d/m/Y') ?? '—',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14, 'B' => 35, 'C' => 25, 'D' => 25,
            'E' => 22, 'F' => 14, 'G' => 12, 'H' => 14,
            'I' => 16, 'J' => 14,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0D9488']], 'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}
