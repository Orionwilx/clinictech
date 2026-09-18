<?php

namespace App\Http\Controllers\Technician;

use App\Models\Upload;
use App\Models\WorkOrder;
use App\Services\WorkOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkOrderSignatureController extends TechnicianPanelController
{
    public function __construct(private readonly WorkOrderService $service) {}

    /** Sube (reemplazando) la firma del técnico o del cliente. */
    public function store(Request $request, WorkOrder $workOrder, string $kind): JsonResponse
    {
        abort_if($workOrder->technician_id !== $this->technician()->id, 403);
        abort_unless(in_array($workOrder->status, ['assigned', 'in_progress'], true), 403);

        $request->validate(['signature' => ['required', 'image', 'max:4096']]);

        $upload = $this->service->storeSignature($workOrder, $request->file('signature'), $kind);

        return response()->json(['id' => $upload->id, 'url' => $upload->url()], 201);
    }

    public function destroy(WorkOrder $workOrder, Upload $signature): JsonResponse
    {
        abort_if($workOrder->technician_id !== $this->technician()->id, 403);
        abort_if($signature->uploadable_id !== $workOrder->id, 404);
        abort_unless(in_array($workOrder->status, ['assigned', 'in_progress'], true), 403);

        $signature->purge();

        return response()->json(['deleted' => true]);
    }
}
