<?php

namespace App\Http\Controllers\Client;

use App\Models\Technician;
use Illuminate\View\View;

class TechnicianController extends ClientPanelController
{
    public function index(): View
    {
        $clientId = $this->client()->id;

        $technicians = Technician::whereHas('workOrders', fn ($q) =>
            $q->where('client_id', $clientId)
        )
        ->withCount(['workOrders as orders_count' => fn ($q) =>
            $q->where('client_id', $clientId)
        ])
        ->orderBy('name')
        ->get();

        return view('client.technicians.index', compact('technicians'));
    }
}
