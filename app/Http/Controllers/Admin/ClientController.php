<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function __construct(private readonly ClientService $clients) {}

    public function index(): View
    {
        $this->authorize('view clients');

        $clients = Client::withTrashed()->latest()->paginate(15);

        return view('admin.clients.index', compact('clients'));
    }

    public function create(): View
    {
        $this->authorize('create clients');

        return view('admin.clients.create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $this->clients->create($request->validated());

        return redirect()->route('admin.clients.index')
            ->with('status', 'Cliente creado correctamente.');
    }

    public function show(Client $client): View
    {
        $this->authorize('view clients');

        $client->load('user');

        return view('admin.clients.show', compact('client'));
    }

    public function edit(Client $client): View
    {
        $this->authorize('update clients');

        $client->load('user');

        return view('admin.clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $this->clients->update($client, $request->validated());

        return redirect()->route('admin.clients.index')
            ->with('status', 'Cliente actualizado correctamente.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete clients');

        $this->clients->delete($client);

        return redirect()->route('admin.clients.index')
            ->with('status', 'Cliente eliminado (recuperable).');
    }

    public function restore(int $id): RedirectResponse
    {
        $this->authorize('delete clients');

        Client::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.clients.index')
            ->with('status', 'Cliente recuperado correctamente.');
    }
}
