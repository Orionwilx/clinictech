<?php

namespace App\Http\Controllers\Technician;

use App\Http\Requests\Technician\StoreWorkOrderPhotoRequest;
use App\Models\WorkOrder;
use App\Models\WorkOrderPhoto;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

/**
 * Evidencias fotográficas del diligenciamiento. Las fotos se suben por AJAX
 * en cuanto se seleccionan (independiente del guardado del formulario) para
 * que el técnico no pierda avance si se corta la conexión.
 */
class WorkOrderPhotoController extends TechnicianPanelController
{
    public function store(StoreWorkOrderPhotoRequest $request, WorkOrder $workOrder, ImageService $images): JsonResponse
    {
        $file = $request->file('photo');
        $stored = $images->storeCompressed($file, "work_order_photos/{$workOrder->id}");

        $photo = $workOrder->photos()->create([
            'path' => $stored['path'],
            'original_name' => $file->getClientOriginalName(),
            'size' => $stored['size'],
        ]);

        return response()->json([
            'id' => $photo->id,
            'url' => $photo->url(),
            'name' => $photo->original_name,
        ], 201);
    }

    public function destroy(WorkOrder $workOrder, WorkOrderPhoto $photo): JsonResponse
    {
        abort_if($workOrder->technician_id !== $this->technician()->id, 403);
        abort_if($photo->work_order_id !== $workOrder->id, 404);
        abort_unless(in_array($workOrder->status, ['assigned', 'in_progress'], true), 403);

        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        return response()->json(['deleted' => true]);
    }
}
