<?php

namespace App\Http\Controllers\Client;

use Illuminate\View\View;

class DashboardController extends ClientPanelController
{
    public function index(): View
    {
        $client = $this->client()->load('equipment');

        $equipmentCount = $client->equipment()->count();

        // El cliente solo ve OT aprobadas y enviadas por el admin (+ sus solicitudes).
        $receivedOrdersCount = $client->workOrders()->sentToClient()->count();

        $pendingRequestsCount = $client->workOrders()
            ->where('requested_by_client', true)
            ->where('status', 'draft')
            ->count();

        $lastOrder = $client->workOrders()
            ->listableForClient()
            ->with('equipment')
            ->latest()
            ->first();

        return view('client.dashboard', compact('client', 'equipmentCount', 'receivedOrdersCount', 'pendingRequestsCount', 'lastOrder'));
    }
}
