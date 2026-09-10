<?php

namespace App\Http\Controllers\Client;

use App\Models\WorkOrder;
use Illuminate\View\View;

class DashboardController extends ClientPanelController
{
    public function index(): View
    {
        $client = $this->client()->load('equipment');

        $equipmentCount = $client->equipment()->count();

        $openOrdersCount = $client->workOrders()
            ->whereIn('status', WorkOrder::ACTIVE_STATUSES)
            ->count();

        $lastOrder = $client->workOrders()
            ->with('equipment')
            ->latest()
            ->first();

        return view('client.dashboard', compact('client', 'equipmentCount', 'openOrdersCount', 'lastOrder'));
    }
}
