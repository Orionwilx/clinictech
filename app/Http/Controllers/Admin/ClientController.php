<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Models\Client;
use App\Models\WorkOrder;
use App\Services\ClientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function __construct(private readonly ClientService $clients) {}

    public function index(Request $request): View
    {
        $this->authorize('view clients');

        $filters = $request->only(['search', 'status']);

        $clients = Client::withTrashed()
            ->when($filters['search'] ?? null, fn ($q, $s) =>
                $q->where(fn ($q) => $q->where('name', 'like', "%$s%")
                    ->orWhere('nit', 'like', "%$s%")
                    ->orWhere('city', 'like', "%$s%"))
            )
            ->when(isset($filters['status']), function ($q) use ($filters) {
                match ($filters['status']) {
                    'deleted'  => $q->onlyTrashed(),
                    'inactive' => $q->where('is_active', false),
                    'active'   => $q->where('is_active', true),
                    default    => null,
                };
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.clients.index', compact('clients', 'filters'));
    }

    public function create(): View
    {
        $this->authorize('create clients');

        return view('admin.clients.create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $this->clients->create($data);

        return redirect()->route('admin.clients.index')
            ->with('status', 'Cliente creado correctamente.');
    }

    public function show(Client $client): View
    {
        $this->authorize('view clients');

        $client->load([
            'user',
            'areas' => fn ($q) => $q->withCount('equipment')->orderBy('name'),
            'equipment' => fn ($q) => $q->with('area')->latest(),
            'workOrders' => fn ($q) => $q->with(['equipment', 'technician'])->latest(),
        ]);

        $active = WorkOrder::ACTIVE_STATUSES;

        // Equipos del cliente con OT pendientes / en proceso.
        $pendingEquipment = $client->equipment()
            ->with(['area', 'brand', 'model'])
            ->whereHas('workOrders', fn ($q) => $q->whereIn('status', $active))
            ->withCount(['workOrders as pending_count' => fn ($q) => $q->whereIn('status', $active)])
            ->with(['workOrders' => fn ($q) => $q->whereIn('status', $active)->with('technician')->latest()])
            ->latest()
            ->get();

        return view('admin.clients.show', compact('client', 'pendingEquipment'));
    }

    public function edit(Client $client): View
    {
        $this->authorize('update clients');

        $client->load('user');

        return view('admin.clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            if ($client->logo_path) {
                Storage::disk('public')->delete($client->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $this->clients->update($client, $data);

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
