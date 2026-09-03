<?php

namespace App\Exports;

use App\Exports\Sheets\Equipment\DetailSheet;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EquipmentExport implements Export, WithMultipleSheets
{
    use Exportable;

    public function __construct(private readonly array $filters) {}

    public function sheets(): array
    {
        return [
            new DetailSheet($this->filters),
        ];
    }
}
