<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Upload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientAttachmentController extends Controller
{
    public function store(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('update clients');

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('file');

        $client->uploads()->create([
            'collection' => 'document',
            'disk' => 'private',
            'path' => $file->store("client_attachments/{$client->id}", 'private'),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'label' => $request->input('name'),
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('status', 'Documento adjuntado correctamente.')->withFragment('tab-documentos');
    }

    public function download(Client $client, Upload $upload): StreamedResponse
    {
        $this->authorize('view clients');
        abort_if($upload->uploadable_type !== Client::class || $upload->uploadable_id !== $client->id, 404);

        return Storage::disk($upload->disk)
            ->download($upload->path, $upload->original_name);
    }

    public function destroy(Client $client, Upload $upload): RedirectResponse
    {
        $this->authorize('update clients');
        abort_if($upload->uploadable_type !== Client::class || $upload->uploadable_id !== $client->id, 404);

        $upload->purge();

        return back()->with('status', 'Documento eliminado.')->withFragment('tab-documentos');
    }
}
