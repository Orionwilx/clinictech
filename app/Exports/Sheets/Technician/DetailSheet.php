<?php

namespace App\Exports\Sheets\Technician;

use App\Models\Technician;
use App\Models\WorkOrder;
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
        return Technician::withCount([
            'workOrders as total_orders',
            'workOrders as completed_orders' => fn ($q) => $q->where('status', 'completed'),
            'workOrders as active_orders'    => fn ($q) => $q->whereIn('status', WorkOrder::ACTIVE_STATUSES),
        ])
            ->when(($this->filters['status'] ?? '') === 'active',   fn ($q) => $q->where('is_active', true))
            ->when(($this->filters['status'] ?? '') === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderByDesc('total_orders');
    }

    public function title(): string
    {
        return 'Por técnico';
    }

    public function headings(): array
    {
        return ['Técnico', 'Documento', 'Especialidad', 'Estado', 'Total OT', 'OT activas', 'OT completadas', '% Completado'];
    }

    public function map($tech): array
    {
        $pct = $tech->total_orders > 0
            ? round(($tech->completed_orders / $tech->total_orders) * 100) . '%'
            : '0%';

        return [
            $tech->name,
            $tech->document,
            $tech->specialty ?? '—',
            $tech->is_active ? 'Activo' : 'Inactivo',
            $tech->total_orders,
            $tech->active_orders,
            $tech->completed_orders,
            $pct,
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 25, 'B' => 16, 'C' => 20, 'D' => 12, 'E' => 12, 'F' => 12, 'G' => 16, 'H' => 14];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0D9488']]],
        ];
    }
}
