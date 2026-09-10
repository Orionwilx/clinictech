<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\ExportReportRequest;
use App\Jobs\GenerateReportJob;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\Report;
use App\Models\Technician;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public const TYPES = [
        'work_orders' => 'Órdenes de trabajo',
        'maintenance' => 'Mantenimientos',
        'technicians' => 'Por técnico',
        'equipment' => 'Por equipo',
    ];

    public function index(): View
    {
        $this->authorize('view reports');

        $clients = Client::orderBy('name')->pluck('name', 'id');
        $technicians = Technician::orderBy('name')->pluck('name', 'id');
        $history = Report::with(['generator', 'downloader'])
            ->latest()
            ->limit(50)
            ->get();

        return view('admin.reports.index', compact('clients', 'technicians', 'history'));
    }

    public function export(ExportReportRequest $request): RedirectResponse
    {
        $filters = array_filter($request->only([
            'date_from', 'date_to',
            'client_id', 'technician_id',
            'type', 'status',
        ]));

        $report = Report::create([
            'type' => $request->report_type,
            'filters' => $filters ?: null,
            'status' => 'pending',
            'generated_by' => auth()->id(),
        ]);

        GenerateReportJob::dispatch($report);

        return redirect()->route('admin.reports.index')
            ->with('status', 'Reporte en cola. Estará disponible en unos segundos.');
    }

    public function download(Report $report): StreamedResponse
    {
        $this->authorize('view reports');

        abort_if($report->status !== 'done', 404, 'El reporte aún no está disponible.');
        abort_if(! $report->file_path || ! Storage::disk('local')->exists($report->file_path), 404, 'Archivo no encontrado.');

        if (! $report->downloaded_at) {
            $report->update([
                'downloaded_by' => auth()->id(),
                'downloaded_at' => now(),
            ]);
        }

        $filename = $report->type.'_'.$report->id.'.xlsx';

        return Storage::disk('local')->download($report->file_path, $filename);
    }

    public function indicators(): View
    {
        $this->authorize('view reports');

        $stats = [
            'clients' => Client::count(),
            'equipment' => Equipment::count(),
            'work_orders' => WorkOrder::count(),
            'open_orders' => WorkOrder::whereIn('status', WorkOrder::ACTIVE_STATUSES)->count(),
            'technicians' => Technician::where('is_active', true)->count(),
            'preventive' => WorkOrder::where('type', 'preventive')->count(),
            'corrective' => WorkOrder::where('type', 'corrective')->count(),
        ];

        return view('admin.reports.indicators', compact('stats'));
    }
}
