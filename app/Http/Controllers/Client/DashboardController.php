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

        // Equipos con mantenimiento vencido: última OT preventiva más antigua que la frecuencia
        $overdue = $client->equipment()
            ->whereNotNull('maintenance_frequency')
            ->with(['workOrders' => fn ($q) => $q->where('type', 'preventive')->latest()->limit(1)])
            ->get()
            ->filter(function ($eq) {
                $last = $eq->workOrders->first();
                if (! $last) return true;
                $months = (int) filter_var($eq->maintenance_frequency, FILTER_SANITIZE_NUMBER_INT);
                return $months > 0 && $last->created_at->addMonths($months)->isPast();
            });

        return view('client.dashboard', compact('client', 'equipmentCount', 'openOrdersCount', 'lastOrder', 'overdue'));
    }
}
