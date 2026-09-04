<?php

namespace App\Http\Controllers\Client;

use App\Models\WorkOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class WorkOrderController extends ClientPanelController
{
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'type', 'status']);

        $workOrders = $this->client()->workOrders()
            ->with(['equipment', 'technician'])
            ->when($filters['search'] ?? null, fn ($q, $s) =>
                $q->where(fn ($q) => $q->where('code', 'like', "%$s%")
                    ->orWhere('title', 'like', "%$s%"))
            )
            ->when($filters['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('client.work_orders.index', [
            'workOrders' => $workOrders,
            'filters' => $filters,
            'types' => WorkOrder::TYPES,
            'statuses' => WorkOrder::STATUSES,
        ]);
    }

    public function show(WorkOrder $workOrder): View
    {
        abort_if($workOrder->client_id !== $this->client()->id, 403);

        $workOrder->load(['client', 'equipment', 'technician']);

        return view('client.work_orders.show', compact('workOrder'));
    }

    public function pdf(WorkOrder $workOrder): Response
    {
        abort_if($workOrder->client_id !== $this->client()->id, 403);

        $workOrder->load(['client', 'equipment.brand', 'equipment.model', 'equipment.area', 'technician']);

        $logoBase64 = null;
        if ($workOrder->client?->logo_path) {
            $path = storage_path('app/public/'.$workOrder->client->logo_path);
            if (file_exists($path)) {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $mime = match ($ext) {
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    default => 'image/jpeg',
                };
                $logoBase64 = "data:{$mime};base64,".base64_encode(file_get_contents($path));
            }
        }

        $pdf = Pdf::loadView('admin.work_orders.pdf', compact('workOrder', 'logoBase64'))
            ->setPaper('A4', 'portrait');

        return $pdf->download("OT-{$workOrder->code}.pdf");
    }
}
