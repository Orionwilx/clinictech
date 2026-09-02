<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkOrder\StoreWorkOrderRequest;
use App\Http\Requests\WorkOrder\UpdateWorkOrderRequest;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\Technician;
use App\Models\WorkOrder;
use App\Services\WorkOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WorkOrderController extends Controller
{
    public function __construct(private readonly WorkOrderService $service) {}

    public function index(): View
    {
        $this->authorize('view work_orders');

        $workOrders = WorkOrder::with(['client', 'equipment', 'technician'])
            ->withTrashed()->latest()->paginate(15);

        return view('admin.work_orders.index', compact('workOrders'));
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
            'equipment' => Equipment::orderBy('name')->get(['id', 'name', 'client_id']),
            'technicians' => Technician::orderBy('name')->pluck('name', 'id'),
        ];
    }
}
