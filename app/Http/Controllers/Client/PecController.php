<?php

namespace App\Http\Controllers\Client;

use App\Models\Client;
use App\Models\Upload;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PecController extends ClientPanelController
{
    public function index(): View
    {
        $pec = $this->client()->pec()->latest()->get();

        return view('client.pec.index', compact('pec'));
    }

    public function download(Upload $upload): StreamedResponse
    {
        abort_if(
            $upload->collection !== 'pec'
            || $upload->uploadable_type !== Client::class
            || $upload->uploadable_id !== $this->client()->id,
            404
        );

        return Storage::disk($upload->disk)->download($upload->path, $upload->original_name);
    }
}
