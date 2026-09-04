<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkOrder\AssignWorkOrderRequest;
use App\Http\Requests\WorkOrder\BatchWorkOrderRequest;
use App\Http\Requests\WorkOrder\RejectWorkOrderRequest;
use App\Http\Requests\WorkOrder\ReturnWorkOrderRequest;
use App\Http\Requests\WorkOrder\StoreWorkOrderRequest;
use App\Http\Requests\WorkOrder\UpdateWorkOrderRequest;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\Technician;
use App\Models\WorkOrder;
use App\Services\WorkOrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class WorkOrderController extends Controller
{
    public function __construct(private readonly WorkOrderService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('view work_orders');

        $filters = $request->only(['search', 'client_id', 'technician_id', 'type', 'priority', 'status']);
        $tab = in_array($request->input('tab'), ['action', 'active', 'all', 'trashed'], true)
            ? $request->input('tab')
            : 'all';

        $applyFilters = fn ($q) => $q
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(fn ($q) => $q->where('code', 'like', "%{$search}%")
                ->orWhere('title', 'like', "%{$search}%")))
            ->when($filters['client_id'] ?? null, fn ($q, $v) => $q->where('client_id', $v))
            ->when($filters['technician_id'] ?? null, fn ($q, $v) => $q->where('technician_id', $v))
            ->when($filters['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($filters['priority'] ?? null, fn ($q, $v) => $q->where('priority', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v));

        $query = WorkOrder::with(['client', 'equipment', 'technician']);
        match ($tab) {
            'action' => $query->awaitingAdminAction(),
            'active' => $query->whereIn('status', WorkOrder::ACTIVE_STATUSES),
            'trashed' => $query->onlyTrashed(),
            default => $query,
        };

        $workOrders = $applyFilters($query)->latest()->paginate(15)->withQueryString();

        return view('admin.work_orders.index', [
            'workOrders' => $workOrders,
            'filters' => $filters,
            'tab' => $tab,
            'counts' => [
                'action' => WorkOrder::awaitingAdminAction()->count(),
                'active' => WorkOrder::whereIn('status', WorkOrder::ACTIVE_STATUSES)->count(),
                'trashed' => WorkOrder::onlyTrashed()->count(),
            ],
            'clients' => Client::orderBy('name')->pluck('name', 'id'),
            'technicians' => Technician::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create work_orders');

        return view('admin.work_orders.create', $this->formOptions());
    }

    public function store(StoreWorkOrderRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('admin.work_orders.index')
            ->with('status', 'Orden de trabajo creada correctamente.');
    }

    public function show(WorkOrder $workOrder): View
    {
        $this->authorize('view work_orders');

        $workOrder->load(['client', 'equipment', 'technician']);

        return view('admin.work_orders.show', compact('workOrder'));
    }

    public function edit(WorkOrder $workOrder): View
    {
        $this->authorize('update work_orders');

        return view('admin.work_orders.edit', array_merge(
            ['workOrder' => $workOrder],
            $this->formOptions()
        ));
    }

    public function update(UpdateWorkOrderRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        $this->service->update($workOrder, $request->validated());

        return redirect()->route('admin.work_orders.index')
            ->with('status', 'Orden de trabajo actualizada correctamente.');
    }

    public function destroy(WorkOrder $workOrder): RedirectResponse
    {
        $this->authorize('delete work_orders');

        $workOrder->delete();

        return redirect()->route('admin.work_orders.index')
            ->with('status', 'Orden de trabajo eliminada (recuperable).');
    }

    public function pdf(WorkOrder $workOrder): Response
    {
        $this->authorize('view work_orders');

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

    // ─── Transiciones del flujo colaborativo ─────────────────────────────────

    public function approveRequest(AssignWorkOrderRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        abort_if($workOrder->status !== 'draft', 403);

        $this->service->approveClientRequest($workOrder, $request->input('technician_id'));

        return back()->with('status', 'Solicitud aprobada.');
    }

    public function rejectRequest(RejectWorkOrderRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        abort_if($workOrder->status !== 'draft', 403);

        $this->service->rejectClientRequest($workOrder, $request->input('rejection_reason'));

        return back()->with('status', 'Solicitud rechazada.');
    }

    public function approveWork(WorkOrder $workOrder): RedirectResponse
    {
        $this->authorize('update work_orders');

        abort_if($workOrder->status !== 'pending_review', 403);

        $this->service->approveWork($workOrder);

        return back()->with('status', 'Trabajo aprobado. Ahora puedes enviarlo al cliente.');
    }

    public function rejectWork(ReturnWorkOrderRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        abort_if($workOrder->status !== 'pending_review', 403);

        $this->service->rejectWork($workOrder, $request->input('rejection_reason'));

        return back()->with('status', 'Trabajo devuelto al técnico.');
    }

    public function sendToClient(WorkOrder $workOrder): RedirectResponse
    {
        $this->authorize('update work_orders');

        abort_if($workOrder->status !== 'closed', 403);
        abort_if($workOrder->visible_to_client, 403);

        $this->service->sendToClient($workOrder);

        return back()->with('status', 'OT enviada al cliente.');
    }

    // ─── Acciones rápidas desde la lista (una sola OT) ────────────────────────

    /** Avance positivo contextual: aprueba solicitud / aprueba trabajo / envía al cliente. */
    public function advance(AssignWorkOrderRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        abort_unless($this->service->advanceForAdmin($workOrder, $request->input('technician_id')), 403);

        return back()->with('status', "Orden {$workOrder->code} actualizada.");
    }

    /** Retroceso contextual: rechaza solicitud / devuelve al técnico. */
    public function regress(RejectWorkOrderRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        abort_unless($this->service->regressForAdmin($workOrder, $request->input('rejection_reason')), 403);

        return back()->with('status', "Orden {$workOrder->code} devuelta.");
    }

    /** Asigna/reasigna técnico sin abrir la OT. */
    public function assign(AssignWorkOrderRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        $this->service->assignTechnician($workOrder, $request->input('technician_id'));

        return back()->with('status', "Técnico actualizado en {$workOrder->code}.");
    }

    /** Acción masiva: aprobar / rechazar / asignar sobre varias OT. */
    public function batch(BatchWorkOrderRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $affected = $this->service->batchForAdmin(
            $data['action'], $data['ids'], $data['technician_id'] ?? null, $data['rejection_reason'] ?? null,
        );

        return back()->with('status', "{$affected} órdenes actualizadas.");
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function restore(int $id): RedirectResponse
    {
        $this->authorize('delete work_orders');

        WorkOrder::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.work_orders.index')
            ->with('status', 'Orden de trabajo recuperada correctamente.');
    }

    /**
     * Opciones para los selectores del formulario.
     *
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'clients' => Client::orderBy('name')->pluck('name', 'id'),
            'equipment' => Equipment::orderBy('name')->get(['id', 'name', 'client_id', 'maintenance_tasks', 'accessories']),
            'technicians' => Technician::orderBy('name')->pluck('name', 'id'),
        ];
    }
}
