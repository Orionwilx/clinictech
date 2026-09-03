<?php

namespace App\Http\Controllers\Admin;

use App\Exports\EquipmentExport;
use App\Exports\MaintenanceExport;
use App\Exports\TechnicianExport;
use App\Exports\WorkOrdersExport;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\Technician;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /** Tipos de reporte disponibles con sus filtros adicionales. */
    public const TYPES = [
        'work_orders' => 'Órdenes de trabajo',
        'maintenance' => 'Mantenimientos',
        'technicians' => 'Por técnico',
        'equipment'   => 'Por equipo',
    ];

    public function index(Request $request): View
    {
        $this->authorize('view reports');

        $clients     = Client::orderBy('name')->pluck('name', 'id');
        $technicians = Technician::orderBy('name')->pluck('name', 'id');

        return view('admin.reports.index', compact('clients', 'technicians'));
    }

    public function export(Request $request)
    {
        $this->authorize('view reports');

        $request->validate([
            'report_type' => ['required', 'in:' . implode(',', array_keys(self::TYPES))],
            'date_from'   => ['nullable', 'date'],
            'date_to'     => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $filters = $request->only([
            'date_from', 'date_to',
            'client_id', 'technician_id',
            'type', 'status',
        ]);

        [$export, $filename] = match ($request->report_type) {
            'work_orders' => [new WorkOrdersExport($filters), 'ordenes-de-trabajo'],
            'maintenance' => [new MaintenanceExport($filters), 'mantenimientos'],
            'technicians' => [new TechnicianExport($filters), 'tecnicos'],
            'equipment'   => [new EquipmentExport($filters),  'equipos'],
        };

        $name = $filename . '_' . now()->format('Ymd_His') . '.xlsx';

        return $export->download($name);
    }

    public function indicators(): View
    {
        $this->authorize('view reports');

        $stats = [
            'clients'     => Client::count(),
            'equipment'   => Equipment::count(),
            'work_orders' => WorkOrder::count(),
            'open_orders' => WorkOrder::whereIn('status', WorkOrder::ACTIVE_STATUSES)->count(),
            'technicians' => Technician::where('is_active', true)->count(),
            'preventive'  => WorkOrder::where('type', 'preventive')->count(),
            'corrective'  => WorkOrder::where('type', 'corrective')->count(),
        ];

        return view('admin.reports.indicators', compact('stats'));
    }
}
