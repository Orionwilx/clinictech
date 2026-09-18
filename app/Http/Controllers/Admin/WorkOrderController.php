<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkOrder\AssignWorkOrderRequest;
use App\Http\Requests\WorkOrder\BatchWorkOrderRequest;
use App\Http\Requests\WorkOrder\RejectWorkOrderRequest;
use App\Http\Requests\WorkOrder\ReturnWorkOrderRequest;
use App\Http\Requests\WorkOrder\StoreWorkOrderPhotoRequest;
use App\Http\Requests\WorkOrder\StoreWorkOrderRequest;
use App\Http\Requests\WorkOrder\UpdateWorkOrderRequest;
use App\Models\Accessory;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\MaintenanceTask;
use App\Models\Technician;
use App\Models\Upload;
use App\Models\WorkOrder;
use App\Services\ImageService;
use App\Services\WorkOrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
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
        [$orderData, $equipmentData] = $this->service->splitEquipmentData($request->validated());
        $workOrder = $this->service->create($orderData);
        $this->service->syncEquipmentData($workOrder, $equipmentData, $orderData);

        // Redirige a la ficha para poder anexar evidencias fotográficas de inmediato.
        return redirect()->route('admin.work_orders.show', $workOrder)
            ->with('status', 'Orden de trabajo creada correctamente. Puedes anexar fotos abajo.');
    }

    public function show(WorkOrder $workOrder): View
    {
        $this->authorize('view work_orders');

        $workOrder->load(['client', 'equipment', 'technician', 'photos', 'technicianSignature', 'clientSignature']);

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
        [$orderData, $equipmentData] = $this->service->splitEquipmentData($request->validated());
        $this->service->update($workOrder, $orderData);
        $this->service->syncEquipmentData($workOrder, $equipmentData, $orderData);

        return redirect()->route('admin.work_orders.show', $workOrder)
            ->with('status', 'Orden de trabajo actualizada correctamente.');
    }

    public function destroy(WorkOrder $workOrder): RedirectResponse
    {
        $this->authorize('delete work_orders');

        $workOrder->delete();

        return redirect()->route('admin.work_orders.index')
            ->with('status', 'Orden de trabajo eliminada (recuperable).');
    }

    /**
     * Sube una evidencia fotográfica a la OT (el admin llena OT en cualquier estado).
     * Comprimida por ImageService; subida AJAX inmediata desde la ficha.
     */
    public function storePhoto(StoreWorkOrderPhotoRequest $request, WorkOrder $workOrder, ImageService $images): JsonResponse
    {
        $file = $request->file('photo');
        $stored = $images->storeCompressed($file, "work_order_photos/{$workOrder->id}");

        $photo = $workOrder->uploads()->create([
            'collection' => 'photo',
            'disk' => 'private',
            'path' => $stored['path'],
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => 'image/jpeg',
            'size' => $stored['size'],
            'uploaded_by' => auth()->id(),
        ]);

        return response()->json([
            'id' => $photo->id,
            'url' => $photo->url(),
            'name' => $photo->original_name,
            'label' => $photo->label,
        ], 201);
    }

    /** Guarda/edita la descripción (label) de una foto de la OT. */
    public function updatePhoto(Request $request, WorkOrder $workOrder, Upload $photo): JsonResponse
    {
        $this->authorize('update work_orders');
        abort_if($photo->uploadable_id !== $workOrder->id, 404);

        $data = $request->validate(['label' => ['nullable', 'string', 'max:255']]);
        $photo->update(['label' => $data['label'] ?? null]);

        return response()->json(['id' => $photo->id, 'label' => $photo->label]);
    }

    public function destroyPhoto(WorkOrder $workOrder, Upload $photo): JsonResponse
    {
        $this->authorize('update work_orders');
        abort_if($photo->uploadable_id !== $workOrder->id, 404);

        $photo->purge();

        return response()->json(['deleted' => true]);
    }

    /** Firma (técnico/cliente): el admin también llena la OT del técnico. */
    public function storeSignature(Request $request, WorkOrder $workOrder, string $kind): JsonResponse
    {
        $this->authorize('update work_orders');
        $request->validate(['signature' => ['required', 'image', 'max:4096']]);

        $upload = $this->service->storeSignature($workOrder, $request->file('signature'), $kind);

        return response()->json(['id' => $upload->id, 'url' => $upload->url()], 201);
    }

    public function destroySignature(WorkOrder $workOrder, Upload $signature): JsonResponse
    {
        $this->authorize('update work_orders');
        abort_if($signature->uploadable_id !== $workOrder->id, 404);

        $signature->purge();

        return response()->json(['deleted' => true]);
    }

    public function pdf(WorkOrder $workOrder): Response
    {
        $this->authorize('view work_orders');

        $workOrder->load(['client.logo', 'equipment.brand', 'equipment.model', 'equipment.area', 'technician', 'photos', 'technicianSignature', 'clientSignature']);

        $logoBase64 = $workOrder->client?->logoBase64();

        $pdf = Pdf::loadView('admin.work_orders.pdf', compact('workOrder', 'logoBase64'))
            ->setPaper('A4', 'portrait');

        return $pdf->download($workOrder->pdfFileName());
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
        // Datos del equipo embebidos para el prellenado y el resumen (marca/modelo/…).
        $equipment = Equipment::with(['brand:id,name', 'model:id,name', 'category:id,name', 'area:id,name'])
            ->orderBy('name')
            ->get()
            ->map(fn (Equipment $e) => [
                'id' => $e->id,
                'name' => $e->name,
                'client_id' => $e->client_id,
                'serial_number' => $e->serial_number,
                'location' => $e->location,
                'brand' => $e->brand?->name,
                'model' => $e->model?->name,
                'category' => $e->category?->name,
                'area' => $e->area?->name,
                'invima_registry' => $e->invima_registry,
                'maintenance_tasks' => $e->maintenance_tasks ?? [],
                'accessories' => $e->accessories ?? [],
                ...collect(WorkOrderService::EQUIPMENT_FIELDS)->mapWithKeys(fn ($f) => [$f => $e->{$f}])->all(),
            ]);

        return [
            'clients' => Client::orderBy('name')->pluck('name', 'id'),
            'equipment' => $equipment,
            'technicians' => Technician::orderBy('name')->pluck('name', 'id'),
            'taskOptions' => MaintenanceTask::active()->orderBy('name')->pluck('name'),
            'accessoryOptions' => Accessory::active()->orderBy('name')->pluck('name'),
        ];
    }
}
