<?php

namespace App\Exports\Sheets\WorkOrders;

use App\Models\WorkOrder;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SummarySheet implements FromArray, WithTitle, WithColumnWidths, WithStyles
{
    public function __construct(private readonly array $filters) {}

    public function array(): array
    {
        $query = WorkOrder::query()
            ->when($this->filters['client_id'] ?? null, fn ($q, $v) => $q->where('client_id', $v))
            ->when($this->filters['technician_id'] ?? null, fn ($q, $v) => $q->where('technician_id', $v))
            ->when($this->filters['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($this->filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($this->filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($this->filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v));

        $total      = (clone $query)->count();
        $byType     = (clone $query)->selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type');
        $byStatus   = (clone $query)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $rows = [];
        $rows[] = ['RESUMEN — ÓRDENES DE TRABAJO'];
        $rows[] = [];
        $rows[] = ['Total de órdenes', $total];
        $rows[] = [];
        $rows[] = ['Por tipo', ''];
        foreach (WorkOrder::TYPES as $key => $label) {
            $rows[] = ['  ' . $label, $byType[$key] ?? 0];
        }
        $rows[] = [];
        $rows[] = ['Por estado', ''];
        foreach (WorkOrder::STATUSES as $key => $label) {
            $rows[] = ['  ' . $label, $byStatus[$key] ?? 0];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Resumen';
    }

    public function columnWidths(): array
    {
        return ['A' => 30, 'B' => 15];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 13]],
            3 => ['font' => ['bold' => true]],
        ];
    }
}
