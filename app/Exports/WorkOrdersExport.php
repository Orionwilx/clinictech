<?php

namespace App\Exports;

use App\Exports\Sheets\WorkOrders\DetailSheet;
use App\Exports\Sheets\WorkOrders\SummarySheet;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class WorkOrdersExport implements Export, WithMultipleSheets
{
    use Exportable;

    public function __construct(private readonly array $filters) {}

    public function sheets(): array
    {
        return [
            new SummarySheet($this->filters),
            new DetailSheet($this->filters),
        ];
    }
}
