<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Area\StoreAreaRequest;
use App\Http\Requests\Area\UpdateAreaRequest;
use App\Models\Area;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;

class AreaController extends Controller
{
    public function store(StoreAreaRequest $request, Client $client): RedirectResponse
    {
        $client->areas()->create($request->validated());

        return redirect()->route('admin.clients.show', [$client, 'tab' => 'areas'])
            ->with('status', 'Área creada correctamente.');
    }

    public function update(UpdateAreaRequest $request, Area $area): RedirectResponse
    {
        $area->update($request->validated());

        return redirect()->route('admin.clients.show', [$area->client_id, 'tab' => 'areas'])
            ->with('status', 'Área actualizada correctamente.');
    }

    public function destroy(Area $area): RedirectResponse
    {
        $this->authorize('delete areas');

        $clientId = $area->client_id;
        $area->delete();

        return redirect()->route('admin.clients.show', [$clientId, 'tab' => 'areas'])
            ->with('status', 'Área eliminada.');
    }
}
