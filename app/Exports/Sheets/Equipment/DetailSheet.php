<?php

namespace App\Exports\Sheets\Equipment;

use App\Models\Equipment;
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
        return Equipment::with(['client', 'brand', 'model', 'area'])
            ->withCount('workOrders as total_interventions')
            ->when($this->filters['client_id'] ?? null, fn ($q, $v) => $q->where('client_id', $v))
            ->when($this->filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->orderByDesc('total_interventions');
    }

    public function title(): string
    {
        return 'Por equipo';
    }

    public function headings(): array
    {
        return ['Equipo', 'Marca', 'Modelo', 'Serial', 'Cliente', 'Área', 'Estado', 'Intervenciones'];
    }

    public function map($item): array
    {
        return [
            $item->name,
            optional($item->brand)->name ?? '—',
            optional($item->model)->name ?? '—',
            $item->serial_number ?? '—',
            optional($item->client)->name ?? '—',
            optional($item->area)->name ?? '—',
            $item->statusLabel(),
            $item->total_interventions,
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 28, 'B' => 18, 'C' => 18, 'D' => 18, 'E' => 25, 'F' => 18, 'G' => 14, 'H' => 16];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0D9488']]],
        ];
    }
}
