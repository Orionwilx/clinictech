<?php

namespace App\Exports\Sheets\Maintenance;

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
        $base = WorkOrder::whereIn('type', ['preventive', 'corrective'])
            ->when($this->filters['client_id'] ?? null, fn ($q, $v) => $q->where('client_id', $v))
            ->when($this->filters['technician_id'] ?? null, fn ($q, $v) => $q->where('technician_id', $v))
            ->when(in_array($this->filters['type'] ?? '', ['preventive', 'corrective']), fn ($q) => $q->where('type', $this->filters['type']))
            ->when($this->filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($this->filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($this->filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v));

        $preventive = (clone $base)->where('type', 'preventive')->count();
        $corrective = (clone $base)->where('type', 'corrective')->count();
        $byStatus   = (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $rows = [];
        $rows[] = ['RESUMEN — MANTENIMIENTOS'];
        $rows[] = [];
        $rows[] = ['Preventivos',  $preventive];
        $rows[] = ['Correctivos',  $corrective];
        $rows[] = ['Total',        $preventive + $corrective];
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
        ];
    }
}
