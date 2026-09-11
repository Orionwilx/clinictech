<?php

namespace App\Http\Controllers\Technician;

use App\Http\Requests\Technician\StoreWorkOrderPhotoRequest;
use App\Models\Upload;
use App\Models\WorkOrder;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;

class WorkOrderPhotoController extends TechnicianPanelController
{
    public function store(StoreWorkOrderPhotoRequest $request, WorkOrder $workOrder, ImageService $images): JsonResponse
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
        ], 201);
    }

    public function destroy(WorkOrder $workOrder, Upload $photo): JsonResponse
    {
        abort_if($workOrder->technician_id !== $this->technician()->id, 403);
        abort_if($photo->uploadable_id !== $workOrder->id, 404);
        abort_unless(in_array($workOrder->status, ['assigned', 'in_progress'], true), 403);

        $photo->purge();

        return response()->json(['deleted' => true]);
    }
}
