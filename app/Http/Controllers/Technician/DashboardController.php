<?php

namespace App\Http\Controllers\Technician;

use App\Models\WorkOrder;
use Illuminate\View\View;

class DashboardController extends TechnicianPanelController
{
    public function index(): View
    {
        $techId = $this->technician()->id;

        $pending = WorkOrder::where('technician_id', $techId)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->with('client', 'equipment')
            ->latest()
            ->get();

        $forReview = WorkOrder::where('technician_id', $techId)
            ->where('status', 'pending_review')
            ->count();

        $completed = WorkOrder::where('technician_id', $techId)
            ->whereIn('status', ['closed', 'completed'])
            ->count();

        return view('technician.dashboard', compact('pending', 'forReview', 'completed'));
    }
}
