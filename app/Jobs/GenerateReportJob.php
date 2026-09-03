<?php

namespace App\Jobs;

use App\Exports\EquipmentExport;
use App\Exports\MaintenanceExport;
use App\Exports\TechnicianExport;
use App\Exports\WorkOrdersExport;
use App\Models\Report;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Maatwebsite\Excel\Facades\Excel;

class GenerateReportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Report $report) {}

    public function handle(): void
    {
        $start = microtime(true);

        $this->report->update(['status' => 'processing']);

        try {
            $filters  = $this->report->filters ?? [];
            $filename = $this->report->type . '_' . $this->report->id . '_' . now()->format('Ymd_His') . '.xlsx';
            $path     = 'reports/' . $filename;

            $export = match ($this->report->type) {
                'work_orders' => new WorkOrdersExport($filters),
                'maintenance' => new MaintenanceExport($filters),
                'technicians' => new TechnicianExport($filters),
                'equipment'   => new EquipmentExport($filters),
            };

            Excel::store($export, $path, 'local');

            $this->report->update([
                'status'      => 'done',
                'file_path'   => $path,
                'duration_ms' => (int) round((microtime(true) - $start) * 1000),
            ]);
        } catch (\Throwable $e) {
            $this->report->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
