<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Upload;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Sirve archivos privados con control de acceso por rol.
 * Ningún archivo es accesible por URL directa.
 */
class MediaController extends Controller
{
    public function serve(Upload $upload): StreamedResponse
    {
        abort_if(! Storage::disk($upload->disk)->exists($upload->path), 404);

        $this->authorizeAccess($upload);

        return Storage::disk($upload->disk)->response($upload->path);
    }

    private function authorizeAccess(Upload $upload): void
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return;
        }

        $uploadable = $upload->uploadable;

        if ($upload->uploadable_type === Client::class) {
            if ($user->hasRole('tecnico')) {
                return;
            }
            // Cliente: solo su propia empresa.
            abort_unless(
                $user->hasRole('cliente') && optional($user->client)->id === $uploadable?->id,
                403
            );

            return;
        }

        if ($upload->uploadable_type === WorkOrder::class) {
            /** @var WorkOrder $workOrder */
            $workOrder = $uploadable;

            if ($user->hasRole('tecnico')) {
                abort_unless($workOrder?->technician_id === optional($user->technician)->id, 403);

                return;
            }

            // Cliente: solo OT enviadas de su empresa.
            abort_unless(
                $user->hasRole('cliente')
                && $workOrder?->visible_to_client
                && $workOrder->client_id === optional($user->client)->id,
                403
            );

            return;
        }

        abort(403);
    }
}
